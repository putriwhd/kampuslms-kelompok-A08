### Read 

1. Jalankan `php artisan install:api`. Baca perubahan yang terjadi di `bootstrap/app.php`.

    Jawaban : 
    Perintah `php artisan install:api` secara otomatis menambahkan pendaftaran file `routes/api.php` di dalam rantai metode `withRouting()` pada file `bootstrap/app.php`. Mengaktifkan file routes/api.php, Secara otomatis memberikan prefix /api untuk seluruh rute yang didaftarkan di dalam file tersebut, Menerapkan middleware group api (termasuk rate limiting/throttle dan sifat stateless). 

( masukin kode nya )

2. Buat satu endpoint `GET /api/v1/courses` sederhana.

    Jawaban : 
    Untuk membuat endpoint API ini, terdapat 2 file yang perlu ditambahkan kodenya:
    - Daftarkan Rute di `routes/api.php`    
    Tambahkan kode berikut ke dalam file `routes/api.php` (file ini otomatis dibuat setelah menjalankan php artisan install:api)
    ( MASUKIN KODE NYA )
    - Buat Controller API di `app/Http/Controllers/Api/V1/CourseApiController.php`
    Buat controller baru yang bertugas mengambil data dari database dan mengembalikannya dalam format JSON
    ( MASUKIN KODE NYA )

    Endpoint `GET /api/v1/courses` adalah jalur akses data untuk aplikasi lain, seperti Frontend React/Vue atau Mobile App. Tidak seperti rute web yang mengirimkan tampilan HTML (`view()`), endpoint API ini khusus mengirimkan data mentah berformat JSON melalui `response()->json()`.

3. Perbandingan CourseController versi Web vs API**


    Jawaban : 

    Persamaan utama dari `CourseController` versi web dan `CourseApiController` versi API terletak pada logika query datanya. Keduanya sama-sama memanggil model Eloquent yang sama untuk mengambil data dari database, contohnya dengan perintah `Course::all()`. Perbedaannya terletak pada tiga hal utama. Pertama pada format response: controller web mengembalikan tampilan HTML via `return view(...)`, sedangkan controller API mengembalikan data mentah terstruktur berformat JSON via `return response()->json(...)`. Kedua pada sistem autentikasi: controller web bergantung pada Session dan Cookie browser, sedangkan controller API bersifat stateless menggunakan Token (seperti Laravel Sanctum). Ketiga pada penanganan error: jika terjadi kesalahan, controller web biasanya melakukan redirect kembali ke halaman sebelumnya, sedangkan controller API langsung mengembalikan HTTP Status Code (seperti 422 atau 401) beserta pesan error berformat JSON.

4. Panggil endpoint API tanpa header Accept: application/json. Lalu dengan header itu. Catat bedanya.

    Jawaban : 
    
    Saat memanggil endpoint API tanpa header `Accept: application/json`, Laravel menganggap permintaan tersebut datang dari navigasi browser biasa. Akibatnya, jika terjadi kesalahan (seperti error validasi 422 atau unauthenticated 401), Laravel akan mencoba melakukan *redirect* atau mengembalikan halaman antarmuka web berformat HTML. Sebaliknya, saat header `Accept: application/json` ditambahkan pada permintaan, Laravel dipaksa untuk mengenali klien sebagai aplikasi konsumen data (seperti Frontend/Mobile App). Dengan demikian, seluruh tanggapan dari server, baik dalam kondisi berhasil maupun saat terjadi error, akan selalu dikembalikan dalam struktur data mentah berformat JSON beserta kode status HTTP yang sesuai.
    (SESUAIKAN LAGI SAMA KODE)

5. Jalankan `php artisan route:list --path=api`. Cocokkan dengan kontrak di spesifikasi.