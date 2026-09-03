### 1. Buka 'public/index.php' . Baca dari atas ke bawah. Tulis dalam 3 kalimat apa yang dilakukan berkas ini.
Jawaban : 
Berkas ini menerima permintaan dari browser saat pengguna membuka aplikasi Laravel. Kemudian, berkas ini menyiapkan dan menjalankan aplikasi Laravel. Setelah itu, permintaan diproses dan hasilnya dikirim kembali ke browser.

### 2. Buka bootstrap/app.php. Identifikasi bagian mana yang mengurus route, mana yang mengurus middleware, mana yang mengurus exception.
Jawaban : 
- Route:bagian withRouting mengatur jalur atau alamat halaman dalam aplikasi.
- Middleware: bagian withMiddleware mengatur proses yang dilakukan sebelum atau sesudah halaman dijalankan.
- Exception: bagian withExceptions mengatur bagaimana aplikasi menangani kesalahan atau error.

### 3. Buka routes/web.php. Temukan route yang menghasilkan halaman selamat datang. Ubah teksnya, muat ulang browser, pastikan berubah.
Jawaban : 
![alt text](image.png)ketika mengubah teks nya menjadi 'Selamat' halaman browser menjadi error karena pada routes/web.php bertugas untuk mencari pada resource/welcome.blade.php dan file welcome.blade.php nya tidak diubah menjadi 'Selamat'
![alt text](image-3.png)

### 4. Jalankan php artisan route:list. Cocokkan keluarannya dengan isi routes/web.php
Jawaban : 
