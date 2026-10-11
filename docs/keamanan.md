# Keamanan dan pencegahan IDOR

Semua pemeriksaan akses dilakukan di server. Menyembunyikan tombol dengan
Blade `@can` hanya membantu antarmuka dan bukan pengganti Policy, pemeriksaan
controller, middleware, atau pembatasan query.

## Matriks perlindungan IDOR

| Data / percobaan IDOR | Permukaan | Perlindungan server |
|---|---|---|
| Mengganti `course` pada daftar atau detail course | Web dan `GET /api/v1/courses/{id}` | `CoursePolicy::view`; `CourseController@index` dan `Api\CourseController@index` membatasi query ke course pengampu/terdaftar. Course draft tidak ditampilkan ke mahasiswa. |
| Mengganti ID materi atau pasangan course/materi | Web `/.../courses/{course}/materials/{material}` | `scopeBindings()` mengikat materi ke course pada route; `MaterialController::showInCourse` memastikan `course_id` cocok; `MaterialPolicy::view`, `update`, dan `delete` memeriksa peran dan relasi course. |
| Mengganti ID tugas atau pasangan course/tugas | Web `/.../courses/{course}/assignments/{assignment}` dan API course assignments | `scopeBindings()` serta pemeriksaan course pada controller web; `AssignmentPolicy::view`, `update`, `delete`, dan `viewSubmissions` memeriksa pengampu/keikutsertaan. API memanggil `CoursePolicy::view`; daftar mahasiswa hanya berisi tugas `published`. |
| Membaca atau mengubah submission milik mahasiswa lain | Web submission/nilai dan API submission | `SubmissionPolicy::view`/`update` membatasi mahasiswa ke submission sendiri; `delete` membatasi ke admin/pengampu dan `grade` ke dosen pengampu. Endpoint API memakai pemeriksaan role, enrollment, dan pengampu langsung di controller. |
| Mengubah nilai dengan ID submission/grade milik course lain | Policy Grade dan `PUT /api/v1/submissions/{id}/grade` | `GradePolicy` mendefinisikan hak kelola dosen pengampu; endpoint API memeriksa langsung bahwa dosen adalah pengampu course submission. |
| Membaca atau menandai notifikasi pengguna lain | `GET /api/v1/notifications` dan `POST /api/v1/notifications/{id}/read` | `Api\NotificationController` mengambil notifikasi melalui relasi notifikasi pengguna yang sedang login; ID asing tidak dapat digunakan untuk menandai notifikasi. |
| Membuka route admin/dosen/mahasiswa dengan mengganti URL atau query `as` | Route web dan API | Middleware `auth`, `role:*`, dan `auth:sanctum`; parameter query `as` tidak memberikan hak akses. |

## Route sensitif

- Semua endpoint API versi 1 selain `POST /api/v1/auth/login` berada di grup
  `auth:sanctum` dan throttle `60` request per menit. Login dibatasi `5`
  request per menit.
- Route web akademik menggunakan middleware autentikasi dan role; aksi terhadap
  resource juga diperiksa oleh Policy/Gate.
- Endpoint API pembuatan, perubahan, dan penghapusan tugas dibatasi ke dosen
  dan memeriksa kepemilikan course di controller.
- Route `/test-leak` tidak didaftarkan.

## Celah yang perlu ditangani di luar cakupan Anggota 4

Kedua endpoint pengumpulan berikut belum menolak tugas berstatus `draft`:
`POST /api/v1/assignments/{id}/submissions` dan `POST /api/v1/submissions`.
Controller API saat ini memeriksa role mahasiswa dan enrollment, tetapi belum
memeriksa status tugas `published`. Blade dan daftar API mahasiswa menyembunyikan
tugas draft, namun itu tidak mencegah request langsung. Perbaikannya memerlukan
perubahan controller API anggota lain; skrip Anggota 4 memeriksa visibilitas
daftar draft dan tidak mengklaim bahwa submit langsung untuk tugas draft sudah
aman.

## Menjalankan uji otorisasi API

Siapkan database demo dan jalankan aplikasi:

```bash
php artisan db:seed
php artisan serve
```

Di terminal lain, jalankan:

```bash
bash scripts/test-authz.sh
```

Skrip menggunakan akun demo seeder (kata sandi `password`), mengambil ID
course/tugas dari API, dan memeriksa akses tanpa token, akses lintas role,
ownership dosen, enrollment mahasiswa, serta visibilitas tugas draft. URL API
dan kredensial dapat diganti melalui variabel lingkungan yang dijelaskan di
header skrip. `API_BASE_URL` harus mencakup prefix versi, misalnya
`http://localhost:8000/api/v1`; token dapat diberikan melalui variabel
`ADMIN_TOKEN`, `LECTURER_TOKEN`, `STUDENT_TOKEN`, `OTHER_STUDENT_TOKEN`, dan
`OTHER_LECTURER_TOKEN` jika sudah tersedia.
