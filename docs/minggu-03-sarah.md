## Read
### 1. Gambar ulang ERD dari spesifikasi di papan/kertas, tanpa melihat dokumen.
*Jawaban* 

![alt text](<WhatsApp Image 2026-09-20 at 18.53.46.jpeg>)

### 2. Untuk setiap foreign key, tentukan perilaku onDelete-nya dan tuliskan alasannya.
*Jawaban*

Untuk foreign key yang datanya bergantung pada tabel lain, digunakan cascade, jadi kalau data utama dihapus, data yang terkait juga ikut terhapus. Sedangkan RESTRICT digunakan supaya data yang masih dibutuhkan tidak langsung ikut terhapus.

### 3. kalau seorang dosen dihapus, apa yang terjadi pada mata kuliahnya? Kenapa dirancang begitu?
*Jawaban* 

Dosen tidak bisa langsung dihapus kalau masih menjadi dosen pada suatu mata kuliah. Ini karena lecturer_id menggunakan RESTRICT. Tujuannya supaya mata kuliah yang masih ada tidak ikut terhapus hanya karena data dosennya dihapus

### 4. kenapa grades.submission_id bersifat unique, bukan sekadar index biasa?
*Jawaban*

Karena satu submission hanya boleh punya satu nilai. Jadi submission_id dibuat unique supaya satu pengumpulan tugas tidak bisa memiliki beberapa data nilai. Kalau hanya menggunakan index biasa, submission yang sama masih bisa memiliki lebih dari satu nilai.


## Break

### 1. Hapus `unique(['course_id','user_id'])` dari `course_user`, lalu daftarkan mahasiswa yang sama dua kali
*jawaban* 

![alt text](image-3.png)

Data ganda lolos tanpa keluhan karena di line `unique(['course_id','user_id'])` di hapus dan akhirnya sistem menjalankan dengan baik karena database tidak lagi memvalidasi keunikan kombinasi. 

### 2. Tambahkan role ke `$fillable` model User, lalu kirim request pembuatan user dengan `role=admin` lewat form yang tidak punya `field role`
*jawaban*

![alt text](image-4.png)

Ketika `'role'` dimasukkan ke dalam `$fillable`, terjadi celah keamanan `(Mass Assignment)`. Pengguna biasa bisa secara diam-diam menyelipkan data role=admin saat mendaftar. Karena 'role' terdaftar di `$fillable`, Laravel mengira input itu memang diizinkan, sehingga pengguna biasa tersebut bisa langsung berubah jadi Admin.

### 3. Ganti seluruh `$fillable` dengan `protected $guarded = [];` lalu ulangi nomor 2
*jawaban*

 ![alt text](<Screenshot (1472).png>)

  Mengosongkan `$guarded` `(protected $guarded = [];)` sangat berbahaya karena mematikan seluruh proteksi `mass assignment`. Efeknya, semua kolom database tanpa terkecuali bisa diisi secara bebas dari input pengguna, sehingga role bisa langsung diubah jadi Admin saat pembuatan akun.

### 4. Kosongkan isi `down()` di satu migrasi, lalu jalankan `php artisan migrate:refresh`.
*jawaban* 

Mengosongkan fungsi `down()` membuat migrasi bersifat non-reversible (tidak dapat dibatalkan). Meskipun di terminal terlihat DONE, proses rollback sebenarnya gagal menghapus tabel dari database `(efek silent failure pada SQLite)`. Akibatnya, struktur tabel lama tertinggal dan tidak bisa di-reset dengan bersih ke kondisi awal.

### 5. Ubah `restrictOnDelete` pada `lecturer_id` menjadi `cascadeOnDelete`, lalu hapus satu dosen
*jawaban*

![alt text](image-5.png)

Dengan menetapkan `cascadeOnDelete()` pada `kolom lecturer_id` di file migrasi, database secara otomatis menghapus seluruh data mata kuliah (Course) yang terhubung saat data pengampunya (User) dihapus. Hal ini mencegah terciptanya `orphan records`(data tanpa relasi) di dalam database.