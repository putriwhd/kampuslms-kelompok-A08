### READ 

1. Jalankan php artisan install:api. Baca perubahan yang terjadi di bootstrap/app.php.

Jawab : 
Perintah php artisan install:api digunakan untuk mengaktifkan routing API dan memasang Laravel Sanctum. Pada bootstrap/app.php, ditambahkan konfigurasi:
    api: __DIR__.'/../routes/api.php'
Artinya, Laravel sekarang menggunakan routes/api.php sebagai tempat untuk mendefinisikan route API.

2. Buat satu endpoint GET /api/v1/courses sederhana.

Jawab : 

3. Bandingkan dengan CourseController versi web yang sudah ada. Tulis di catatan: apa yang sama dan apa yang berbeda di antara keduanya?
4. Panggil endpoint API tanpa header Accept: application/json. Lalu dengan header itu. Catat bedanya.
5. Jalankan php artisan route:list --path=api. Cocokkan dengan kontrak di spesifikasi.


##BREAK
![alt text](image.png)
Saat endpoint mengembalikan response()->json(User::all()), data pengguna seperti email dan kolom lainnya tampil dalam response JSON. Ketika $hidden pada model User dihapus, field password yang berupa hash dan remember_token juga ikut tampil. Hal ini menunjukkan bahwa mengembalikan model mentah dapat membocorkan data sensitif, sehingga sebaiknya menggunakan API Resource untuk menentukan data yang boleh ditampilkan.

