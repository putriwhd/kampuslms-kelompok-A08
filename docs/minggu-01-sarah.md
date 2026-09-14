### 1. Buka public/index.php. Baca dari atas ke bawah. Tulis dalam 3 kalimat apa yang dilakukan berkas ini. 
*jawaban*

Berkas `public/index.php` ini sebagai pintu masuk laravel ketika membuka http website. jadi ketika membuka browser, akan ke public/index.php ini kemudian akan mengirimkan ke laravel yang kemudian akan di masuk ke proses request dan akan mengirimkan hasil kembali ke browser. dan `public/index.php` ini juga akan memanggil `bootstrap/app.php` untuk menyiapkan aplikasi laravel tersebut yang kemudian laravel tersebut memproses hasil request dan mengembalikan kembali request tersebut ke browser. 

### 2. buka `bootstrap/app.php`. Identifikasi bagian mana yang mengurus route, mana yang mengurus middleware, mana yang mengurus exception.
*jawaban*

- Untuk yang mengurus route ada di bagian `withRouting()` yang menentukan alamat apa yang akan di jalankan oleh laravel.

- Untuk yang mengurus middleware ada di bagian `withMiddleware()` yang bertugas sebagai proses pemeriksaan request sebelum di proses lebih lanjut.

- untuk yang mengurus exception ada di bagian `withExceptions()` yang bertugas sebagai penanganan error yang terjadi dalam laravel tersebut.

 ### 3. Buka routes/web.php. Temukan route yang menghasilkan halaman selamat datang. ubah teksnya, muat ulang browser,pastikan berubah.
*jawaban*

#### Berikut ini adalah hasil dari perubahan teks pada `routes/web.php`
 

disini saya merubah di bagian selamat menjadi halo adel

![alt text](<Screenshot 2026-09-03 010642.png>)

kemudian saya jalankan seperti ini yang kemudian menampilkan url tersebut.

setelah saya buka di browser url tersebut hasilnya seperti ini: 
![alt text](<Screenshot (1261).png>)

dan kenapa error karena routing menerima request dari browser tetapi ketika route menjalankan`view(halo adel)`laravel tidak menemukan view tersebut yang kemudian exception menangani error tersebut dan memberikan informasi mengenai letak serta penyebab error.

### 4. Jalankan php artisan route:list. Cocokkan keluarannya dengan isi routes/web.php
*jawaban*
![alt text](<Screenshot 2026-09-03 010642-1.png>)
