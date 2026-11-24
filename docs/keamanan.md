# Keamanan dan Pencegahan IDOR

Semua pemeriksaan akses dilakukan di server. Menyembunyikan tombol dengan Blade `@can` hanya membantu antarmuka dan bukan pengganti Policy, pemeriksaan controller, middleware, atau pembatasan query.

## Matriks Akses per Role

| Role | Akses yang Diizinkan |
|---|---|
| **Admin** (`admin@kampuslms.test`, `admin2@kampuslms.test`) | Melihat semua course, materi, tugas, submission, dan nilai; mengelola pengguna, course, materi, tugas, serta pendaftaran mahasiswa ke course; menghapus submission. Admin tidak membuat atau mengubah nilai. |
| **Dosen** | Melihat course yang diampu serta materi dan tugasnya (termasuk status `draft`); mengelola metadata materi dan tugas pada course sendiri; melihat dan menghapus submission pada course sendiri; membuat, mengubah, dan menghapus nilai pada course sendiri. Tidak dapat mengelola pengguna atau course milik dosen lain. |
| **Mahasiswa** | Melihat course yang diikuti dan berstatus bukan `draft`, materi pada course tersebut, tugas berstatus `published`, submission milik sendiri, serta nilai milik sendiri. Dapat mengirim submission pada tugas `published` di course yang diikuti (pembaruan submission tidak memiliki endpoint aktif di web maupun API); tidak dapat mengelola course, materi, tugas, submission orang lain, maupun nilai. |

Pendaftaran akun mahasiswa dapat dilakukan melalui route `/register` yang aktif secara publik, namun tautan pendaftaran disembunyikan dari antarmuka halaman login (`/login`) sehingga harus diakses langsung melalui URL. Nilai `role` tidak diterima dari input pendaftar (selalu ditetapkan sebagai `mahasiswa` oleh server), sedangkan pendaftaran mahasiswa ke course dilakukan oleh Admin melalui halaman detail course. Dosen hanya dapat melihat dan menilai submission pada course yang diampunya, dan mahasiswa hanya menerima nilai dari submission miliknya sendiri.

Pada data demo, `dosen2@kampuslms.test` memiliki course terpisah untuk menguji batas kepemilikan pengampu, sedangkan `mahasiswa2@kampuslms.test` sengaja tidak didaftarkan pada course demo untuk menguji penolakan akses lintas pemilik serta mahasiswa yang tidak terdaftar.

## Matriks Perlindungan IDOR

| Data / Percobaan IDOR | Permukaan | Perlindungan Server | Test |
|---|---|---|---|
| Mengganti `course` pada daftar atau detail course | Web dan `GET /api/v1/courses/{id}` | `CoursePolicy::view`; `CourseController@index` dan `Api\CourseController@index` membatasi query sesuai role (Admin melihat semua course, dosen hanya course yang diampu, mahasiswa hanya course yang diikuti dan bukan `draft`). Pada detail course API, request mahasiswa terhadap course `draft` menghasilkan `404`, mahasiswa yang mengakses course non-draft yang tidak diikuti menghasilkan `403`, dan dosen yang mengakses course dosen lain menghasilkan `403`. | `CourseAccessTest::test_student_and_lecturer_cannot_access_unauthorized_course_or_draft` |
| Mendaftarkan mahasiswa ke course atau mengubah ID pengguna | Web `PUT /admin/courses/{course}/students` | `CoursePolicy::manageStudents` hanya mengizinkan Admin; setiap ID peserta divalidasi di server agar dipastikan merupakan akun dengan role `mahasiswa`. | `CriticalAccessControlTest::test_non_admin_cannot_manage_courses` |
| Mengganti ID materi atau pasangan course/materi | Web dan API `/courses/{id}/materials`, `/materials/{id}` | Route web menerapkan `scopeBindings()`; `MaterialController::showInCourse` memverifikasi kecocokan `course_id`; `MaterialPolicy::create`, `update`, dan `delete` hanya mengizinkan Admin atau dosen pengampu, sedangkan `view` mengizinkan Admin, dosen pengampu, dan mahasiswa terdaftar. API memanggil Gate/Policy sebelum operasi tulis, hanya mengelola metadata, dan tidak melayani unggahan berkas fisik via API. | `CriticalAccessControlTest::test_other_lecturer_cannot_upload_material_to_another_course` |
| Mengganti ID tugas atau pasangan course/tugas | Web `/.../courses/{course}/assignments/{assignment}` dan API course assignments | `scopeBindings()` serta pemeriksaan relasi course pada controller web; `AssignmentPolicy::create`, `view`, `update`, `delete`, dan `viewSubmissions` memvalidasi role, pengampu, dan keikutsertaan kelas. API memanggil Gate/Policy, dan daftar tugas mahasiswa dibatasi hanya pada status `published`. | `AssignmentAccessTest::test_unauthorized_user_cannot_access_or_modify_assignment` |
| Membaca atau mengubah submission milik mahasiswa lain | Web submission/nilai dan API submission | `SubmissionPolicy::view` membatasi akses baca hanya untuk Admin, dosen pengampu, atau mahasiswa pemilik; `delete` dibatasi untuk Admin/pengampu dan `grade` hanya untuk dosen pengampu. Endpoint aktif saat ini hanya melayani pembuatan submission baru (belum ada route aktif untuk pembaruan submission meskipun policy telah disiapkan). Pemeriksaan role, enrollment, pengampu, dan status tugas `published` dijalankan di controller serta policy. | `CriticalAccessControlTest::test_unenrolled_student_cannot_submit_assignment`, `SubmissionBackendTest::test_student_cannot_submit_draft_assignment` |
| Mengubah nilai dengan ID submission/grade milik course lain | `GradePolicy` dan `PUT /api/v1/submissions/{id}/grade` | `GradePolicy` mengatur hak kelola dosen pengampu; endpoint API memeriksa langsung bahwa dosen yang login adalah pengampu course dari submission terkait. | `CriticalAccessControlTest::test_other_lecturer_cannot_grade_submission_on_web` |
| Membaca atau menandai notifikasi pengguna lain | `GET /api/v1/notifications` dan `POST /api/v1/notifications/{id}/read` | `Api\NotificationController` mengambil data melalui relasi notifikasi pengguna yang sedang terautentikasi; ID milik pengguna lain tidak dapat diakses maupun ditandai telah dibaca. | `NotificationApiTest::test_user_cannot_read_or_mark_other_users_notification` |
| Membuka route admin/dosen/mahasiswa dengan mengganti URL atau parameter query `as` | Route web dan API | Middleware `auth`, `role:*`, dan `auth:sanctum`; parameter query `as` tidak memberikan eskalasi hak akses apa pun. | `CriticalAccessControlTest::test_guest_and_unauthorized_role_cannot_bypass_route_middleware` |

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