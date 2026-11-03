## Read 

1. Matikan worker. Buat satu tugas baru. Buka tabel jobs — apa isinya?

    Jawaban : Tabel jobs berisi job yang masih menunggu untuk diproses. Karena worker dimatikan, job notifikasi yang dibuat setelah tugas disimpan akan menumpuk di tabel tersebut dan belum dijalankan.

2. Baca kolom payload. Bisakah Anda mengenali nama Listener dan ID tugasnya?

    Jawaban : Bisa. Kolom payload menyimpan informasi job yang akan diproses, termasuk nama Listener dan data yang dibutuhkan. Dari informasi tersebut, kita dapat mengenali Listener yang bertugas mengirim notifikasi serta ID tugas yang berkaitan.

3. Jalankan php artisan queue:work, amati keluarannya. Periksa tabel jobs lagi.

    Jawaban : Setelah perintah php artisan queue:work dijalankan, worker akan mengambil dan memproses job yang menunggu. Jika berhasil, job akan dihapus dari tabel jobs karena sudah selesai diproses.

4. Periksa tabel notifications. Berapa baris yang muncul? Cocok dengan jumlah mahasiswa?

    Jawaban : Jumlah notifikasi seharusnya sesuai dengan jumlah mahasiswa yang menerima notifikasi. Misalnya, jika terdapat 40 mahasiswa dalam mata kuliah tersebut, maka akan muncul 40 baris notifikasi apabila setiap mahasiswa menerima satu notifikasi.

5. Baca kolom data pada satu notifikasi. Adakah yang tidak seharusnya tersimpan di situ?

    Jawaban : Kolom data berisi informasi notifikasi, seperti ID tugas, nama mata kuliah, judul tugas, tanggal pengumpulan, dan URL tugas. Tidak boleh ada data sensitif seperti nilai mahasiswa atau isi feedback karena data tersebut tidak terenkripsi dan dapat dibaca oleh pihak yang memiliki akses ke tabel notifikasi.

