# Keamanan dan pencegahan IDOR

Semua pemeriksaan akses dilakukan di server. Menyembunyikan tombol dengan
Blade `@can` hanya membantu antarmuka dan bukan pengganti Policy, pemeriksaan
controller, middleware, atau pembatasan query.

## Matriks akses per role

| Role | Akses yang diizinkan |
|---|---|
| Admin (`admin@kampuslms.test`, `admin2@kampuslms.test`) | Melihat semua course, materi, tugas, submission, dan nilai; mengelola pengguna/course/materi/tugas dan pendaftaran mahasiswa ke course; menghapus submission. Admin tidak membuat atau mengubah nilai. |
| Dosen | Melihat course yang diampu serta materi dan tugasnya (termasuk draft); mengelola metadata materi/tugas; melihat dan menghapus submission pada course sendiri; membuat/mengubah/menghapus nilai pada course sendiri. Tidak dapat mengelola pengguna atau course dosen lain. |
| Mahasiswa | Melihat course yang diikuti dan bukan draft, materi pada course tersebut, tugas `published`, submission sendiri, dan nilai sendiri. Dapat membuat (mengirim) submission sendiri pada tugas `published` yang diikuti (pembaruan submission tidak memiliki endpoint aktif di web maupun API); tidak dapat mengelola course, materi, tugas, submission orang lain, atau nilai. |

Pendaftaran akun mahasiswa dapat dilakukan melalui route `/register` yang aktif
secara publik, namun tautan pendaftaran telah disembunyikan dari halaman antarmuka
login (`/login`) sehingga harus diakses langsung via URL. Nilai `role` tidak
diterima dari input pendaftar (selalu ditetapkan sebagai mahasiswa oleh server);
pendaftaran mahasiswa ke course dilakukan Admin dari detail course. Dosen hanya
dapat melihat dan menilai submission pada course yang diampunya. Mahasiswa hanya
menerima nilai dari submission miliknya.

Di data demo, `dosen2@kampuslms.test` memiliki course untuk menguji batas
pengampu, sedangkan `mahasiswa2@kampuslms.test` sengaja tidak terdaftar pada
course demo. Kedua akun tersebut menguji penolakan akses lintas pemilik dan
mahasiswa yang tidak terdaftar.

## Matriks perlindungan IDOR

| Data / percobaan IDOR | Permukaan | Perlindungan server |
|---|---|---|
| Mengganti `course` pada daftar atau detail course | Web dan `GET /api/v1/courses/{id}` | `CoursePolicy::view`; `CourseController@index` dan `Api\CourseController@index` membatasi query: Admin melihat semua course, dosen hanya course yang diampu, mahasiswa hanya course yang diikuti dengan status bukan draft. Pada detail course API, request mahasiswa terhadap course berstatus `draft` menghasilkan `404`; mahasiswa yang mengakses course non-draft yang tidak diikutinya menghasilkan `403`; dosen yang mengakses course milik dosen lain menghasilkan `403`. |
| Mendaftarkan mahasiswa ke course atau mengubah ID pengguna | Web `PUT /admin/courses/{course}/students` | `CoursePolicy::manageStudents` hanya mengizinkan Admin; setiap ID divalidasi agar merupakan akun mahasiswa. |
| Mengganti ID materi atau pasangan course/materi | Web dan API `/courses/{id}/materials`, `/materials/{id}` | Route web memakai `scopeBindings()`; `MaterialController::showInCourse` memastikan `course_id` cocok; `MaterialPolicy::create`, `update`, dan `delete` hanya mengizinkan Admin atau dosen pengampu, sedangkan `view` mengizinkan Admin, dosen pengampu, dan mahasiswa terdaftar. API memanggil Gate/Policy sebelum operasi tulis, hanya mengelola metadata, dan tidak melayani unggahan berkas fisik via API. |
| Mengganti ID tugas atau pasangan course/tugas | Web `/.../courses/{course}/assignments/{assignment}` dan API course assignments | `scopeBindings()` serta pemeriksaan course pada controller web; `AssignmentPolicy::create`, `view`, `update`, `delete`, dan `viewSubmissions` memeriksa role dan pengampu/keikutsertaan. API memanggil Gate/Policy; daftar mahasiswa hanya berisi tugas `published`. |
| Membaca atau mengubah submission milik mahasiswa lain | Web submission/nilai dan API submission | `SubmissionPolicy::view` membatasi akses baca ke admin, dosen pengampu, atau mahasiswa pemilik; `delete` membatasi ke admin/pengampu dan `grade` ke dosen pengampu. Endpoint yang tersedia di web/API saat ini hanya melayani pembuatan submission baru (belum ada rute aktif untuk update submission meskipun policy telah disiapkan). Pemeriksaan role, enrollment, dan pengampu dijalankan langsung di controller dan policy. |
| Mengubah nilai dengan ID submission/grade milik course lain | Policy Grade dan `PUT /api/v1/submissions/{id}/grade` | `GradePolicy` mendefinisikan hak kelola dosen pengampu; endpoint API memeriksa langsung bahwa dosen adalah pengampu course submission. |
| Membaca atau menandai notifikasi pengguna lain | `GET /api/v1/notifications` dan `POST /api/v1/notifications/{id}/read` | `Api\NotificationController` mengambil notifikasi melalui relasi notifikasi pengguna yang sedang login; ID asing tidak dapat digunakan untuk menandai notifikasi. |
| Membuka route admin/dosen/mahasiswa dengan mengganti URL atau query `as` | Route web dan API | Middleware `auth`, `role:*`, dan `auth:sanctum`; parameter query `as` tidak memberikan hak akses. |

## Route sensitif

- Semua endpoint API versi 1 selain `POST /api/v1/auth/login` berada di grup
  `auth:sanctum` dan throttle `60` request per menit. Login dibatasi `5`
  request per menit.
- Route web akademik menggunakan middleware autentikasi dan role; aksi terhadap
  resource juga diperiksa oleh Policy/Gate.
- Pendaftaran publik membuat role mahasiswa di server. Penetapan dosen/admin
  hanya tersedia melalui pengelolaan akun oleh Admin.
- Penilaian web hanya dapat dilakukan dosen pengampu; nilai mahasiswa dibatasi
  pada submission milik mahasiswa yang sedang login.
- Endpoint API pembuatan, perubahan, dan penghapusan materi/tugas menerima
  Admin dan dosen; Gate/Policy tetap membatasi dosen pada course yang diampu.
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

> [!WARNING]
> Menjalankan `php artisan db:seed` berulang kali pada database yang sudah berisi data akan menambah 5 mata kuliah demo baru beserta data terkait pada setiap pemanggilan karena seeder menggunakan factory create. Untuk mereset basis data ke kondisi awal yang bersih, gunakan `php artisan migrate:fresh --seed`.

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
