## READ

### 1. Method apa yang menerima request? Di controller mana?

*jawaban*

method yang menerima ialah `store()` di file app/http/Controllers/coursecontroller. untuk alurnya pertama dari `Routes/web.php` yang memetakan request bernilai `post/courses`  yang secara otomatis ke method `store()` di coursecontroller. dan sebelum kode didalam method `store` dijalankan maka laravel akan memproses request tersebut melalui `StoreCourseRequest`. karena itulah saat payload cURL dikirim, validasi di `StoreCourseRequest` akan menghadangnya sebelum data sempat masuk ke database. 

### 2. Di titik mana persisnya validasi terjadi — sebelum atau sesudah baris pertama method `controller?`

*jawaban*

Validasi data pada Laravel terjadi sebelum baris pertama method `controller` dijalankan. Ketika request dikirim ke rute `POST /courses`, Laravel menggunakan mekanisme `Dependency Injection` untuk membaca `StoreCourseRequest` terlebih dahulu. Sistem secara otomatis memeriksa otorisasi dan aturan validasi di dalam kelas tersebut. Jika ada data yang tidak sesuai, eksekusi program langsung dihentikan, lalu Laravel mengembalikan respon error validasi (HTTP 422). Kode di dalam `CourseController` sama sekali tidak tersentuh apabila validasi gagal.

### 3. Ke mana Laravel `me-redirect` setelah gagal? Siapa yang menentukan tujuannya?

*jawaban*

Validasi Laravel berjalan sebelum `controller` dieksekusi. jika data tidak valid, kode `controller` tidak akan pernah tersentuh. Kegagalan validasi akan di-redirect kembali ke halaman form sebelumnya untuk pengguna browser (berdasarkan header HTTP_REFERER), sedangkan untuk `API/cURL` langsung mengembalikan respon JSON HTTP 422.

### 4. Dari mana `@error('sks')` mengambil pesannya?

*jawaban*

Directive `@error('sks')` mengambil pesan error dari Session Flash Data `($errors MessageBag)` yang dikirim Laravel saat validasi S`toreCourseRequest` gagal. 
Isi pesannya berasal dari `method messages()` di file `Form Request` atau file bawaan `validation.php`, lalu disimpan sementara di `session` dan dibaca oleh Blade melalui variabel `$message`.

### 5. Dari mana `old('sks')` mengambil nilainya? Berapa lama nilai itu bertahan?

*jawaban*

`old('sks')` mengambil nilainya dari `Session Flash` Data bawaan Laravel. Ketika validasi di `StoreCourseRequest` gagal, Laravel secara otomatis menyimpan seluruh input yang diketik oleh pengguna ke dalam `session` sementara melalui perintah `withInput()` sebelum melakukan `redirect` kembali ke halaman form. Nilai ini hanya bertahan sangat singkat, yaitu selama satu kali request saja (single HTTP request). Karena menggunakan mekanisme `flash data`, input lama tersebut akan langsung dihapus secara otomatis dari `session` setelah halaman form selesai dimuat ulang (refresh) atau diakses kembali oleh pengguna.

### 6. Buka DevTools → Application → Cookies. Temukan cookie session Laravel. Catat namanya.

*jawaban*

![alt text](image-7.png)

namanya `laravel_session` dan `XSRF-TOKEN` 


## Break 

