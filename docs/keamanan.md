# Keamanan dan Pencegahan IDOR

Semua pemeriksaan akses dilakukan di server. Menyembunyikan tombol dengan Blade `@can` hanya membantu antarmuka dan bukan pengganti Policy, pemeriksaan controller, middleware, atau pembatasan query.

## Matriks Akses per Role

| Role | Akses yang Diizinkan |
|---|---|
| **Admin** (`admin@kampuslms.test`, `admin2@kampuslms.test`) | Melihat semua course, materi, tugas, submission, dan nilai; mengelola pengguna, course, materi, tugas, serta pendaftaran mahasiswa ke course; menghapus submission. Admin tidak membuat atau mengubah nilai. |
| **Dosen** | Melihat course yang diampu serta materi dan tugasnya (termasuk status `draft`); mengelola metadata materi dan tugas serta pendaftaran mahasiswa pada course sendiri; melihat dan menghapus submission pada course sendiri; membuat, mengubah, dan menghapus nilai pada course sendiri. Tidak dapat mengelola pengguna atau course milik dosen lain. |
| **Mahasiswa** | Melihat course yang diikuti dan berstatus bukan `draft`, materi pada course tersebut, tugas berstatus `published`, submission milik sendiri, serta nilai milik sendiri. Dapat mengirim submission pada tugas `published` di course yang diikuti (pembaruan submission tidak memiliki endpoint aktif di web maupun API); tidak dapat mengelola course, materi, tugas, submission orang lain, maupun nilai. |

Pendaftaran akun mahasiswa dapat dilakukan melalui route `/register` yang aktif secara publik, namun tautan pendaftaran disembunyikan dari antarmuka halaman login (`/login`) sehingga harus diakses langsung melalui URL. Nilai `role` tidak diterima dari input pendaftar (selalu ditetapkan sebagai `mahasiswa` oleh server), sedangkan pendaftaran mahasiswa ke course dapat dikelola Admin atau dosen pengampu sesuai batas akses course masing-masing. Dosen hanya dapat melihat dan menilai submission pada course yang diampunya, dan mahasiswa hanya menerima nilai dari submission miliknya sendiri.

Pada data demo, `dosen2@kampuslms.test` memiliki course terpisah untuk menguji batas kepemilikan pengampu, sedangkan `mahasiswa2@kampuslms.test` sengaja tidak didaftarkan pada course demo untuk menguji penolakan akses lintas pemilik serta mahasiswa yang tidak terdaftar.

## Matriks Perlindungan IDOR

| Data / Percobaan IDOR | Permukaan | Perlindungan Server | Test |
|---|---|---|---|
| Mengganti `course` pada daftar atau detail course | Web dan `GET /api/v1/courses/{id}` | `CoursePolicy::view`; controller membatasi daftar course sesuai role. Pada detail API, dosen lain dan mahasiswa yang tidak terdaftar mendapat `403`, sedangkan mahasiswa yang meminta course `draft` mendapat `404`. | `ApiEndpointsTest::test_course_collection_is_paginated_and_includes_loaded_counts`; `CriticalAccessControlTest::test_unenrolled_student_is_forbidden_from_viewing_course_details`; `WeekSevenDeliveryTest::test_student_course_and_assignment_lists_are_scoped_in_web_and_api` |
| Mengubah enrollment dengan ID course milik dosen lain | Web `PUT /admin/courses/{course}/students` dan `/dosen/courses/{course}/students` | `CoursePolicy::manageEnrollment` hanya mengizinkan Admin atau dosen pengampu course terkait; controller membatasi peserta ke akun mahasiswa. | `AdminWebAccessTest::test_lecturer_can_enroll_and_unenroll_students_only_in_their_course` |
| Mengganti ID materi atau pasangan course/materi | Web dan API `/courses/{id}/materials`, `/materials/{id}` | Route web menerapkan `scopeBindings()`; controller memverifikasi kecocokan course/materi; Policy membatasi operasi sesuai role, kepemilikan course, dan enrollment. | `CriticalAccessControlTest::test_non_course_lecturer_cannot_upload_material_to_course`; `WeekSevenDeliveryTest::test_material_views_and_crud_are_limited_to_admin_owner_lecturer_and_enrolled_students`; `ApiEndpointsTest::test_admin_and_lecturer_can_manage_materials_with_policy_scoped_access` |
| Mengganti ID tugas atau pasangan course/tugas | Web `/.../courses/{course}/assignments/{assignment}` dan API course assignments | `scopeBindings()` dan pemeriksaan relasi course membatasi akses; `AssignmentPolicy` memeriksa role, pengampu, dan enrollment. Daftar tugas mahasiswa hanya memuat status `published`. | `ApiEndpointsTest::test_assignment_endpoints_use_schema_fields_and_enforce_course_ownership`; `ApiEndpointsTest::test_admin_and_lecturer_can_manage_assignments_only_within_allowed_courses`; `WeekSevenDeliveryTest::test_student_course_and_assignment_lists_are_scoped_in_web_and_api` |
| Membaca atau mengubah submission milik mahasiswa lain | Web submission/nilai dan API submission | `SubmissionPolicy` membatasi baca ke Admin, dosen pengampu, atau pemilik submission; aksi hapus dan penilaian dibatasi untuk role yang berwenang. Pengumpulan baru mensyaratkan mahasiswa terdaftar dan tugas `published`. | `SubmissionBackendTest::test_submission_policy_view_restricts_download_to_owner_lecturer_and_admin`; `CriticalAccessControlTest::test_other_student_is_forbidden_from_downloading_submission_file`; `CriticalAccessControlTest::test_non_enrolled_student_cannot_submit_assignment`; `SubmissionBackendTest::test_draft_assignment_rejects_submission_with_forbidden_response` |
| Mengubah nilai dengan ID submission/grade milik course lain | `GradePolicy` dan `PUT /api/v1/submissions/{id}/grade` | Policy/controller memastikan dosen yang login adalah pengampu course submission. | `CriticalAccessControlTest::test_non_course_lecturer_cannot_grade_submission_via_web`; `ApiEndpointsTest::test_only_the_course_lecturer_can_grade_a_submission` |
| Membaca atau menandai notifikasi pengguna lain | `GET /api/v1/notifications` dan `POST /api/v1/notifications/{id}/read` | `Api\NotificationController` mengambil notifikasi dari relasi pengguna yang sedang login; ID pengguna lain ditolak. | `ApiEndpointsTest::test_nested_collections_eager_load_resources_and_notifications_are_scoped`; `ApiEndpointsTest::test_assignment_and_submission_events_are_dispatched_and_notifications_are_managed_safely` |
| Membuka route admin/dosen/mahasiswa dengan mengganti URL atau parameter query `as` | Route web dan API | Middleware `auth`, `role:*`, dan `auth:sanctum`; parameter query `as` tidak memberikan eskalasi hak akses. | `RoleRouteTest::test_query_parameter_cannot_grant_access_to_a_role_route`; `RoleRouteTest::test_authenticated_user_must_have_route_role`; `CriticalAccessControlTest::test_unauthenticated_guest_accessing_protected_routes_is_redirected_to_login` |

## Route Sensitif

- Seluruh endpoint API versi 1 selain `POST /api/v1/auth/login` berada di dalam grup middleware `auth:sanctum` dengan batas *rate limit* (`throttle`) `60` request per menit, sedangkan endpoint login dibatasi `5` request per menit.
- Route web akademik dilindungi middleware autentikasi (`auth`) dan pemeriksaan peran (`role:*`), serta divalidasi kembali di level resource menggunakan Policy/Gate.
- Pendaftaran publik selalu menetapkan role `mahasiswa` di sisi server; penetapan role `dosen` atau `admin` hanya dapat dilakukan melalui pengelolaan akun oleh Admin.
- Penilaian melalui antarmuka web hanya dapat dilakukan oleh dosen pengampu course, dan tampilan nilai mahasiswa dibatasi hanya pada submission milik mahasiswa yang sedang login.
- Endpoint API untuk pembuatan, perubahan, dan penghapusan materi maupun tugas hanya menerima role Admin dan dosen, dengan pembatasan kepemilikan course oleh Gate/Policy.
- Route debugging `/test-leak` tidak didaftarkan di aplikasi.

## Pengumpulan Tugas Draft

`SubmissionController` dan `SubmissionPolicy::create` menolak pengumpulan tugas yang masih berstatus `draft` dengan respons **HTTP 403 Forbidden** sebelum berkas disimpan ke storage. Penolakan ini berlaku konsisten pada endpoint API (`POST /api/v1/assignments/{id}/submissions` dan `POST /api/v1/submissions`) maupun formulir web.

## Menjalankan Uji Otorisasi API

Siapkan database demo dan jalankan server aplikasi:

```bash
php artisan db:seed
php artisan serve