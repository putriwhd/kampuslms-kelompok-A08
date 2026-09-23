### Read 

1.  Method apa yang menerima request? Di controller mana?
    jawaban : 
    Method yang menerima request adalah method `store()` dan ada di controller `CourseController`

2. Di titik mana persisnya validasi terjadi — sebelum atau sesudah baris pertama method controller?
    jawaban : Validasi terjadi sebelum baris pertama dari method controller dieksekusi. Ketika request dikirim laravel akan langsung menyerahkan data ke form request terlebih dahulu sebelum mengizinkan data masuk ke controller, memastikan nilai SKS tidak lebih dari batas maksimal. Jika data yang diisi tidak valid, proses akan langsung dihentikan. Kode utama akan benar benar dieksekusi hanya jika seluruh data formulir sudah lolos diperiksa oleh form request

3. Ke mana Laravel me-redirect setelah gagal? Siapa yang menentukan tujuannya?

    Jawaban : 
    Ketika validasi gagal. laravel secara otomatis me redirect pengguna kembali ke halaman form dan yang menentukan tujuan nya adalah sistem penanganan form request bawaan laravel. sistem ini membaca header HTTP referer yang dikirimkan oleh browser untuk mengetahui URL asal halaman form tersebut dan langsung mengarahkan pengguna kembali. 

4. Dari mana @error('sks') mengambil pesannya?

    Jaawaban :
     @error('sks') mengambil pesannya dari Session Flash Data pada variabel `$errors` (Error Bag). Saat validasi gagal, Laravel otomatis menyimpan daftar pesan kesalahan ke dalam session sementara. Fungsi `@error` lalu membaca pesan yang cocok dengan kunci yang ditentukan (sks) dari variabel `$errors` tersebut untuk ditampilkan di halaman form.

5. Dari mana old('sks') mengambil nilainya? Berapa lama nilai itu bertahan?

    Jawaban : 
    
    old('sks') mengambil nilainya dari Session Flash Data yang berisi data input dari request sebelumnya (dikirim otomatis oleh Laravel lewat method withInput() saat validasi gagal). Nilai tersebut bertahan selama 1 kali request berikutnya saja (flash data). Begitu halaman form dirender atau dimuat ulang sekali oleh pengguna, data old('sks') akan langsung terhapus otomatis dari session.

6. Buka DevTools → Application → Cookies. Temukan cookie session Laravel. Catat namanya.

    jawaban : 
    
    Pada DevTools bagian Application, Cookies, nama cookie session Laravel biasanya adalah laravel_session. Cookie tersebut digunakan Laravel untuk mengenali session pengguna.