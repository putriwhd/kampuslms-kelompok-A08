## Read 

1. Temukan berkas fisiknya di sistem berkas. Path lengkapnya apa?

    Jawaban : Berkas materi kuliah disimpan di folder storage/app/private. Untuk submission, struktur foldernya adalah storage/app/private/submissions/{id_tugas}/, sedangkan nama file fisiknya dibuat secara acak.

2. Buka baris database yang bersangkutan. Bandingkan file_path dengan lokasi fisiknya.

    Jawaban : Nilai file_path di database berisi lokasi relatif file, misalnya submissions/12/abc123.pdf. Jika digabungkan dengan folder penyimpanan privat, lokasi fisiknya menjadi storage/app/private/submissions/12/abc123.pdf.

3. Coba akses berkas itu langsung lewat browser dengan menebak URL-nya. Berhasil atau tidak? Kenapa?

    Jawaban : Tidak berhasil, karena file disimpan di folder privat dan tidak memiliki URL publik. Untuk mengunduhnya, pengguna harus melalui controller yang memeriksa hak akses terlebih dahulu.

4. Buka config/filesystems.php. Identifikasi disk local dan public — apa persis bedanya?

    Jawaban : Disk local digunakan untuk menyimpan file secara privat di folder storage/app/private, sedangkan disk public digunakan untuk menyimpan file yang boleh diakses secara langsung melalui browser melalui folder public/storage.

5. Jalankan php artisan storage:link. Apa yang dibuatnya? Kenapa materi Anda tidak boleh ada di sana?

    Jawaban : Perintah tersebut membuat symbolic link dari public/storage menuju storage/app/public. Materi kuliah tidak boleh disimpan di sana karena siapa pun yang mengetahui URL file bisa mengunduhnya tanpa login atau pemeriksaan hak akses.