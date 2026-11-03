### Read 

1. Jalankan `php artisan install:api`. Baca perubahan yang terjadi di `bootstrap/app.php`.

    Jawaban : Perintah ini digunakan untuk mengaktifkan fitur API di Laravel. Setelah dijalankan, ada penambahan konfigurasi pada bootstrap/app.php yang menghubungkan route API ke file routes/api.php.

2. Buat satu endpoint GET /api/v1/courses sederhana.

    Jawaban : Saya membuat endpoint untuk mengambil data mata kuliah dari database. Data tersebut kemudian ditampilkan dalam bentuk JSON dan bisa diakses melalui alamat /api/v1/courses.
    
3. Bandingkan dengan CourseController versi web yang sudah ada. Tulis di catatan: apa yang sama dan apa yang berbeda di antara keduanya?

    Jawaban : Persamaannya, keduanya sama-sama mengambil data mata kuliah dari database menggunakan model Course. Perbedaannya, API menampilkan data dalam bentuk JSON, sedangkan CourseController versi web menampilkan data melalui halaman website. Controller web juga memiliki fitur tambahan seperti pencarian, filter, dan pagination.

4. Panggil endpoint API tanpa header Accept: application/json. Lalu dengan header itu. Catat bedanya.

    Jawaban : Saat dipanggil tanpa header, data tetap muncul dalam bentuk JSON. Begitu juga saat menggunakan header Accept: application/json. Hasilnya sama karena endpoint memang sudah diatur untuk mengembalikan JSON.

5. Jalankan php artisan route:list --path=api. Cocokkan dengan kontrak di spesifikasi.

    Jawaban : Perintah ini digunakan untuk mengecek apakah endpoint API sudah terdaftar. Saya memastikan method yang digunakan adalah GET dan alamatnya sudah sesuai, yaitu /api/v1/courses, kemudian mencocokkannya dengan spesifikasi API.

### Break

