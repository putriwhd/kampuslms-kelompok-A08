## Read 

### 1. Jalankan `php artisan route:list --except-vendor`. Salin keluarannya ke catatan.

Jawaban : 

![alt text](image-15.png)

ketika menjalankan diterminal `php artisan route:list --except-vendor` maka yang keluar adalah fungsi yang menampilkan daftar seluruh alur `URL (route)` bawaan proyek Laravel. Daftar ini mencantumkan metode `HTTP (GET, POST, PUT, DELETE)`, alamat URL (seperti admin/courses atau admin/users), nama `route`, serta `Controller` yang bertugas memproses halaman atau data tersebut. Perintah `--except-vendor` digunakan agar daftar yang muncul hanya berfokus pada fitur proyek Anda tanpa tercampur route dari pustaka luar.

### 2. Tandai setiap route yang menerima parameter model `({course}, {assignment}, dst).`

Jawaban : 

1.  Parameter `{course} `

- GET|HEAD `admin/courses/{course} (Route: admin.courses.show)` 
- PUT|PATCH `admin/courses/{course} (Route: admin.courses.update)`  
- DELETE `admin/courses/{course} (Route: admin.courses.destroy)` 
-  GET|HEAD `admin/courses/{course}/edit (Route: admin.courses.edit)`  
- GET|HEAD `admin/courses/{course}/enrollments (Route: admin.courses.enrollments.index)`  
- POST `admin/courses/{course}/enrollments (Route: admin.courses.enrollments.store)` 

2. Parameter `{enrollment}`

- DELETE `admin/enrollments/{enrollment} (Route: admin.enrollments.destroy)`

3. Parameter `{user}`

- GET|HEAD `admin/users/{user} (Route: admin.users.show)` 
- PUT|PATCH `admin/users/{user} (Route: admin.users.update)`  
- DELETE `admin/users/{user} (Route: admin.users.destroy)` 
- GET|HEAD `admin/users/{user}/edit (Route: admin.users.edit)`

4. Parameter `{assignment}`

- GET|HEAD dosen/assignments/{assignment} (Route: dosen.assignments.show) 
- PUT|PATCH dosen/assignments/{assignment} (Route: dosen.assignments.update)   
- DELETE dosen/assignments/{assignment} (Route: dosen.assignments.destroy)   

### 3. Untuk setiap route bertanda, jawab: siapa saja yang seharusnya boleh mengaksesnya, dan apa yang saat ini mencegah orang lain? Kemungkinan besar jawabannya "belum ada apa-apa" — itu wajar, dan itulah pekerjaan minggu ini dan minggu 7.

Jawaban : 

1. Group Route `{course} & {enrollment}`

- Fungsi Route:
    - `course`: Mengelola katalog mata kuliah (menampilkan daftar kelas, membuat kelas baru, memperbarui informasi SKS/dosen, dan menghapus kelas).

    - `enrollment`  : Mengelola pendaftaran mahasiswa ke dalam kelas (enroll / unenroll).

- Siapa yang Seharusnya Boleh Mengakses:

    - Admin: Hak akses penuh untuk seluruh operasi CRUD pada mata kuliah dan pendaftaran mahasiswa.

    - Dosen / Mahasiswa: Hanya boleh melihat `Read/Show` detail mata kuliah yang mereka ikuti/ampu.

- Apa yang Saat Ini Mencegah Orang Lain:

    - Belum ada apa-apa atau baru sebatas middleware `auth` dasar yang sekadar memastikan pengguna sudah login.

    - Tanpa aturan otorisasi berbasis role atau policy, pengguna biasa dapat memanggil endpoint tambah/hapus mata kuliah atau mendaftarkan dirinya sendiri ke kelas mana saja secara ilegal.

2. Group Route `{user}`

- Fungsi Route, Mengelola entitas pengguna dalam sistem (menampilkan daftar akun, registrasi akun baru, mengedit data profil/password, dan menghapus akun).

- Siapa yang seharusnya boleh mengakses:

    - Admin: Akses penuh untuk mengelola seluruh data pengguna sistem.

    - User Pemilik Akun (Resource Owner): Hanya boleh melihat dan memperbarui profil miliknya sendiri (misal: `/users/{id}` tempat `{id} ` adalah ID dirinya sendiri).

- Apa yang saat ini mencegah orang lain:

    - Belum ada apa-apa.

    - Tanpa pemeriksaan Resource Ownership seperti `UserPolicy`, terjadi kerentanan IDOR , di mana seorang pengguna cukup mengganti angka ID pada URL, misal: `/users/5/edit` menjadi `/users/6/edit` untuk melihat atau mengedit profil orang lain.

3. Group Route `{assignment}`

- Fungsi Route, Mengelola alur pemberian tugas akademik (menerbitkan tugas baru, melihat instruksi & deadline, memperbarui batas waktu, dan menghapus tugas).

- Siapa yang seharusnya boleh mengakses:

    - Dosen Pengampu: Hanya dosen yang mengajar mata kuliah terkait yang boleh membuat `store`, mengedit `update`, atau menghapus `destroy` tugas.

    - Mahasiswa: Hanya boleh melihat `show` detail instruksi tugas pada kelas yang diikutinya.

- Apa yang saat ini mencegah orang lain:

    - Belum ada apa-apa.

    -   Tanpa pengecekan relasi otorisasi di controller atau policy, seorang mahasiswa bisa mengirim request untuk mengubah/menghapus tugas, atau Dosen A bisa secara sengaja/tidak sengaja mengedit tugas milik Dosen B hanya dengan mengganti ID tugas pada URL.da URL.

### 4. Buat tabel di `docs/minggu-05-<nama>.md` berjudul "Daftar Titik Rawan IDOR". Tabel ini akan Anda pakai lagi di minggu 7 dan saat interview.

Jawaban : 

## Daftar Titik Rawan IDOR

Tabel berikut mencatat seluruh `route` yang menerima parameter model (seperti `{course}`, `{user}`, `{assignment}`, dll.) yang berpotensi menjadi titik kerentanan `IDOR (Insecure Direct Object Reference)` jika tidak dilindungi dengan proteksi otorisasi yang memadai misal: `Middleware Role/Permission, Gate, atau Policy`.


| No | Method | Route / Path | Parameter | Hak Akses Ideal (Who Should Access) | Mekanisme Pencegahan Saat Ini | Potensi Risiko IDOR |
|:--:|:------:|:-------------|:---------:|:------------------------------------|:------------------------------|:--------------------|
| 1 | `GET\|HEAD` | `admin/courses/{course}` | `{course}` | Admin, Dosen/Mahasiswa terkait | Belum ada apa-apa / Middleware `auth` dasar | Pengguna dapat melihat detail course yang tidak seharusnya diakses hanya dengan mengganti ID course di URL. |
| 2 | `PUT\|PATCH` | `admin/courses/{course}` | `{course}` | Admin saja | Belum ada apa-apa | Pengguna non-admin/dosen lain dapat mengubah data course pengguna lain. |
| 3 | `DELETE` | `admin/courses/{course}` | `{course}` | Admin saja | Belum ada apa-apa | Pengguna non-admin dapat menghapus data course milik orang lain. |
| 4 | `GET\|HEAD` | `admin/courses/{course}/edit` | `{course}` | Admin saja | Belum ada apa-apa | Akses ke form edit course tanpa verifikasi peran admin. |
| 5 | `GET\|HEAD` | `admin/courses/{course}/enrollments` | `{course}` | Admin saja | Belum ada apa-apa | Mengintip daftar pendaftaran mahasiswa pada course tertentu. |
| 6 | `POST` | `admin/courses/{course}/enrollments` | `{course}` | Admin saja | Belum ada apa-apa | Menambahkan/mendaftarkan pengguna ke course tanpa izin. |
| 7 | `DELETE` | `admin/enrollments/{enrollment}` | `{enrollment}` | Admin saja | Belum ada apa-apa | Menghapus pendaftaran (*unenroll*) pengguna lain dari course. |
| 8 | `GET\|HEAD` | `admin/users/{user}` | `{user}` | Admin, Pemilik Akun | Belum ada apa-apa | Melihat informasi pribadi/sensitif milik user lain dengan mengganti ID user. |
| 9 | `PUT\|PATCH` | `admin/users/{user}` | `{user}` | Admin, Pemilik Akun | Belum ada apa-apa | Mengubah data profil/akun milik user lain secara ilegal. |
| 10 | `DELETE` | `admin/users/{user}` | `{user}` | Admin saja | Belum ada apa-apa | Menghapus akun user lain secara langsung melalui manipulasi ID. |
| 11 | `GET\|HEAD` | `admin/users/{user}/edit` | `{user}` | Admin, Pemilik Akun | Belum ada apa-apa | Mengakses antarmuka/form edit akun user lain. |
| 12 | `GET\|HEAD` | `dosen/assignments/{assignment}` | `{assignment}` | Dosen pengampu, Mahasiswa kelas terkait | Belum ada apa-apa | Mengintip detail tugas dari kelas/dosen lain. |
| 13 | `PUT\|PATCH` | `dosen/assignments/{assignment}` | `{assignment}` | Dosen pengampu tugas tersebut saja | Belum ada apa-apa | Dosen/user lain dapat mengubah isi atau ketentuan tugas milik dosen lain. |
| 14 | `DELETE` | `dosen/assignments/{assignment}` | `{assignment}` | Dosen pengampu tugas tersebut saja | Belum ada apa-apa | Menghapus tugas milik dosen lain dengan mengganti ID assignment pada request. |

---

*Catatan: Dokumentasi ini dibuat pada Minggu 5 dan akan diperbarui pada Minggu 7 setelah pengujian dan penambahan proteksi `(Policy/Gate)`.

## Break

### 1. Login sebagai user A. Buka submission milik user B dengan mengubah angka di URL

Jawaban : 

secrenshoot: mengakses data sendiri 

![alt text](image.png)

secrenshoot 2 : Berhasil mengakses data tugas ID 2 milik dosen lain (Pembuat ID: 20) tanpa halangan otorisasi.

![alt text](image-1.png)

Berdasarkan hasil break test pada endpoint `/dosen/assignments/{id}`, ditemukan adanya celah keamanan berupa IDOR. Ketika pengguna yang telah terautentikasi mengubah parameter URL dari ID 1 menjadi ID 2, sistem tetap dapat menampilkan detail tugas yang dimiliki oleh pengguna atau dosen lain dengan `created_by`: 20. Kondisi tersebut menunjukkan bahwa aplikasi belum melakukan pemeriksaan hak akses berdasarkan kepemilikan data, seperti memastikan nilai `created_by` sesuai dengan `Auth::id()`. Dengan demikian, pengontrol belum menerapkan validasi otorisasi terhadap entitas sebelum data ditampilkan kepada pengguna.


### 2. Buka /courses/1/assignments/99 di mana tugas 99 milik mata kuliah lain

Jawaban : 

![alt text](image-2.png)

Hasil pengujian pada rute bersarang `/dosen/courses/{course}/assignments/{assignment}` menunjukkan adanya celah keamanan berupa *Broken Object Level Authorization* (BOLA) atau *Missing Scoping*. Tugas dengan ID 99 yang seharusnya terhubung dengan Mata Kuliah ID 9 masih dapat diakses melalui URL yang menggunakan Mata Kuliah ID 8, yaitu `/dosen/courses/8/assignments/99`. Kondisi ini terjadi karena *controller* mengambil data tugas hanya berdasarkan ID tugas tanpa melakukan validasi terhadap kesesuaian `course_id` dengan ID mata kuliah yang terdapat pada rute induk. Akibatnya, sistem tidak memastikan bahwa tugas tersebut benar-benar merupakan bagian dari mata kuliah yang diminta pada URL.


### 3. Aktifkan Route::scopeBindings(), ulangi nomor 2

Jawaban : 

hasil :

![alt text](image-3.png)

Setelah `Route::scopeBindings()` diterapkan pada rute bersarang, dilakukan pengujian ulang dengan mengakses URL `/dosen/courses/8/assignments/99`. Hasil pengujian menunjukkan respons `404 | TIDAK DITEMUKAN`. Kondisi ini terjadi karena *Scope Bindings* pada Laravel melakukan validasi terhadap hubungan antara entitas *parent* dan *child*, yaitu `Course` dan `Assignment`. Karena `Assignment` dengan ID 99 sebenarnya terkait dengan `Course` ID 9, bukan `Course` ID 8, Laravel tidak menemukan data yang sesuai dan mengembalikan status `404`. Dengan demikian, penerapan `Route::scopeBindings()` berhasil mencegah akses terhadap data yang tidak sesuai dengan relasi *parent-child* dan mengatasi celah BOLA/IDOR tersebut.


### 4. Daftarkan middleware di app/Http/Kernel.php seperti tutorial lama

Jawaban : 

SSaat mengikuti instruksi untuk mendaftarkan `middleware EnsureUserHasRole` pada `app/Http/Kernel.php`, berkas tersebut tidak ditemukan. Kondisi ini menunjukkan bahwa proyek menggunakan arsitektur Laravel versi 12 yang tidak lagi menggunakan `Kernel.php` sebagai tempat pendaftaran middleware.

**Solusi dan Implementasi:**
Pada Laravel 12, pendaftaran middleware dilakukan melalui berkas `bootstrap/app.php` dengan menggunakan metode `->withMiddleware()`. Middleware `EnsureUserHasRole` kemudian didaftarkan menggunakan sintaks `$middleware->alias(['role' => EnsureUserHasRole::class])`. Dengan demikian, alias `'role'` tetap dapat digunakan pada berkas rute `routes/web.php` seperti biasa.


### 5. Pasang `role:admin` pada grup, lalu akses sebagai dosen

Jawaban : 
 hasil pengujian 

![alt text](image-4.png)

Ketika grup rute `Admin (/admin/*)` diberikan `middleware role:admin`, kemudian diakses menggunakan akun dengan role `Dosen`, sistem berhasil menolak akses dan menampilkan respons `403 | AKSES DITOLAK`. Hal ini menunjukkan bahwa `Middleware EnsureUserHasRole` yang telah didaftarkan dengan alias `'role'` pada `bootstrap/app.php` berhasil memeriksa kesesuaian role pengguna dengan role yang ditentukan pada rute.

Karena akun yang digunakan memiliki role `Dosen`, sedangkan rute tersebut hanya dapat diakses oleh role `Admin`, maka `middleware` menghentikan proses request melalui `abort(403)`. Dengan demikian, penerapan kontrol akses berdasarkan peran atau **Role-Based Access Control (RBAC)** pada rute aplikasi telah berhasil diterapkan dan berjalan sesuai dengan ketentuan akses yang ditentukan.

### 6. Sebagai dosen A, edit mata kuliah milik dosen B (keduanya lolos role:dosen)

Jawaban : 

hasil pengujian 

![alt text](image-5.png)

Pengujian akses pada rute `/dosen/courses/9/edit` menggunakan akun Dosen A menunjukkan adanya celah keamanan **Broken Object Level Authorization (BOLA)** pada tingkat horizontal. Meskipun Dosen A dan Dosen B memiliki role yang sama dan sama-sama berhasil melewati validasi `middleware role:dosen`, sistem tetap menampilkan halaman formulir untuk mengedit mata kuliah Keamanan Informasi dengan ID 9 yang merupakan milik Dosen B (ID Dosen 24).

Celah tersebut terjadi karena `DosenCourseController` mengambil data mata kuliah hanya menggunakan `Course::findOrFail($id)` tanpa melakukan pemeriksaan terhadap kepemilikan data berdasarkan `lecturer_id`. Akibatnya, sistem hanya memvalidasi role pengguna tanpa memastikan apakah objek yang diakses memang menjadi hak pengguna tersebut. Kondisi ini memungkinkan pengguna dengan role yang sama untuk mengakses dan berpotensi mengubah data milik dosen lain.

Untuk mengatasi celah tersebut, pengontrol perlu menambahkan validasi kepemilikan berdasarkan pengguna yang sedang login. Alternatif lainnya adalah menerapkan **Policy** pada Laravel agar proses otorisasi terhadap objek dilakukan secara lebih terstruktur dan konsisten.
