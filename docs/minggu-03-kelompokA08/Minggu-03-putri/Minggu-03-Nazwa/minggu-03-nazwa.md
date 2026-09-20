<<<<<<< HEAD
READ

1. Gambar ulang ERD dari spesifikasi di papan/kertas, tanpa melihat dokumen.
Jawaban : 
![alt text](ERD-1.jpeg)

2. Untuk setiap foreign key, tentukan perilaku onDelete-nya dan tuliskan alasannya.
Jawaban : 
Untuk foreign key yang datanya bergantung pada tabel lain, digunakan cascade, jadi kalau data utama dihapus, data yang terkait juga ikut terhapus. Sedangkan RESTRICT digunakan supaya data yang masih dibutuhkan tidak langsung ikut terhapus.

3. kalau seorang dosen dihapus, apa yang terjadi pada mata kuliahnya? Kenapa dirancang begitu?
Jawaban : 
Dosen tidak bisa langsung dihapus kalau masih menjadi dosen pada suatu mata kuliah. Ini karena lecturer_id menggunakan RESTRICT. Tujuannya supaya mata kuliah yang masih ada tidak ikut terhapus hanya karena data dosennya dihapus

4. kenapa grades.submission_id bersifat unique, bukan sekadar index biasa?
Jawaban : 
Karena satu submission hanya boleh punya satu nilai. Jadi submission_id dibuat unique supaya satu pengumpulan tugas tidak bisa memiliki beberapa data nilai. Kalau hanya menggunakan index biasa, submission yang sama masih bisa memiliki lebih dari satu nilai.

BREAK
1. Hapus unique(['course_id','user_id']) dari course_user, lalu daftarkan mahasiswa yang sama dua kali
jawaban
![alt text](<break 1 mg 3.jpeg>)


Data ganda lolos tanpa keluhan karena di line unique(['course_id','user_id']) di hapus dan akhirnya sistem menjalankan dengan baik karena database tidak lagi memvalidasi keunikan kombinasi.

2. Tambahkan role ke $fillable model User, lalu kirim request pembuatan user dengan role=admin lewat form yang tidak punya field role
jawaban
![alt text](<break 2 mg 3.jpeg>)



Ketika 'role' dimasukkan ke dalam $fillable, terjadi celah keamanan (Mass Assignment). Pengguna biasa bisa secara diam-diam menyelipkan data role=admin saat mendaftar. Karena 'role' terdaftar di $fillable, Laravel mengira input itu memang diizinkan, sehingga pengguna biasa tersebut bisa langsung berubah jadi Admin.

3. Ganti seluruh $fillable dengan protected $guarded = []; lalu ulangi nomor 2
jawaban
![alt text](<break 3 mg 3.jpeg>)


Mengosongkan $guarded (protected $guarded = [];) sangat berbahaya karena mematikan seluruh proteksi mass assignment. Efeknya, semua kolom database tanpa terkecuali bisa diisi secara bebas dari input pengguna, sehingga role bisa langsung diubah jadi Admin saat pembuatan akun.

4. Kosongkan isi down() di satu migrasi, lalu jalankan php artisan migrate:refresh.
jawaban

Mengosongkan fungsi down() membuat migrasi bersifat non-reversible (tidak dapat dibatalkan). Meskipun di terminal terlihat DONE, proses rollback sebenarnya gagal menghapus tabel dari database (efek silent failure pada SQLite). Akibatnya, struktur tabel lama tertinggal dan tidak bisa di-reset dengan bersih ke kondisi awal.

5. Ubah restrictOnDelete pada lecturer_id menjadi cascadeOnDelete, lalu hapus satu dosen
jawaban
![alt text](<break 5 mg 3.jpeg>)


Dengan menetapkan cascadeOnDelete() pada kolom lecturer_id di file migrasi, database secara otomatis menghapus seluruh data mata kuliah (Course) yang terhubung saat data pengampunya (User) dihapus. Hal ini mencegah terciptanya orphan records(data tanpa relasi) di dalam database.
=======
# Read

## 1. Gambar ulang ERD dari spesifikasi di papan/kertas, tanpa melihat dokumen.
### Jawaban : 

## 2.Untuk setiap foreign key, tentukan perilaku onDelete-nya dan tuliskan alasannya.
### Jawaban :
1. Foreign Key lecturer_id pada tabel Foreign key lecturer_id pada tabel courses yang mengacu pada id pada tabel users menggunakan restrictOnDelete, karena mata kuliah tidak boleh ikut terhapus ketika dosen dihapus. Data mata kuliah tetap perlu dipertahankan.
2. Foreign key course_id pada tabel course_user yang mengacu pada id pada tabel courses menggunakan cascadeOnDelete, karena ketika mata kuliah dihapus, data mahasiswa yang terdaftar pada mata kuliah tersebut juga tidak diperlukan.
3. Foreign key user_id pada tabel course_user yang mengacu pada id pada tabel users menggunakan cascadeOnDelete, karena ketika user dihapus, data pendaftarannya pada mata kuliah juga ikut dihapus.
4. Foreign key course_id pada tabel materials yang mengacu pada id pada tabel courses menggunakan cascadeOnDelete, karena materi merupakan bagian dari mata kuliah sehingga ikut terhapus ketika mata kuliahnya dihapus.
5. Foreign key uploaded_by pada tabel materials yang mengacu pada id pada tabel users menggunakan restrictOnDelete, karena materi tetap perlu dipertahankan meskipun user yang mengunggahnya dihapus.
6. Foreign key course_id pada tabel assignments yang mengacu pada id pada tabel courses menggunakan cascadeOnDelete, karena tugas merupakan bagian dari mata kuliah sehingga ikut terhapus ketika mata kuliahnya dihapus.
7. Foreign key created_by pada tabel assignments yang mengacu pada id pada tabel users menggunakan restrictOnDelete, karena tugas tetap perlu dipertahankan meskipun user yang membuatnya dihapus.
8. Foreign key assignment_id pada tabel submissions yang mengacu pada id pada tabel assignments menggunakan cascadeOnDelete, karena submission bergantung pada tugas. Jika tugas dihapus, submission yang terkait juga ikut dihapus.
9. Foreign key user_id pada tabel submissions yang mengacu pada id pada tabel users menggunakan restrictOnDelete, karena submission merupakan data akademik yang perlu dipertahankan meskipun user dihapus.
10. Foreign key submission_id pada tabel grades yang mengacu pada id pada tabel submissions menggunakan cascadeOnDelete, karena nilai bergantung pada submission. Jika submission dihapus, nilai yang terkait juga ikut dihapus.
11. Foreign key graded_by pada tabel grades yang mengacu pada id pada tabel users menggunakan restrictOnDelete, karena nilai tetap perlu dipertahankan meskipun user yang memberikan nilai dihapus.

## 3. Jawab: kalau seorang dosen dihapus, apa yang terjadi pada mata kuliahnya? Kenapa dirancang begitu?
### Jawaban : 
 Jika seorang dosen dihapus, mata kuliah yang diajarnya tidak ikut terhapus. Hal ini karena lecturer_id menggunakan resticondelete, sehingga dosen yang masih memiliki mata kuliah tidak dapat langsung dihapus. Hal ini dirancang agar mata kuliah dan data akademik didalamnbya, seperti materi, tugas, submission, dan nilai, tetap aman dan tidak ikut terhapus.

## 4. Jawab: kenapa grades.submission_id bersifat unique, bukan sekadar index biasa?
### Jawaban : 
 Karena satu submission hanya boleh memiliki satu nilai. Dengan unique, database dapat mencegah satu submission memiliki lebih dari satu nilai. Sedangkan index hanya digunakan untuk mempercepat pencairan data dan tidak mencegah data yang sama dibuat lebih dari sekali. Jadi, unique digunakan untuk memastikan hubungan antara submission dan grade tetap satu submission memiliki maksimal satu grade.
>>>>>>> 36082a2b9c28c40225d5f613702da30cc2679637
