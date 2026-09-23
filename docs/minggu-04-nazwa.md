READ

1. Method apa yang menerima request? Di controller mana?
jawaban : 
Method yang menerima request adalah method store() yang ada di CourseController.

2. Di titik mana persisnya validasi terjadi — sebelum atau sesudah baris pertama method controller?
jawaban :
Validasi terjadi sebelum baris pertama di dalam method store() dijalankan. Jadi saat SKS diisi 99, Laravel akan memeriksa validasi terlebih dahulu. Karena 99 tidak sesuai aturan, proses tidak dilanjutkan ke isi method store().

3. Ke mana Laravel me-redirect setelah gagal? Siapa yang menentukan tujuannya?
jawaban :
Setelah validasi gagal, Laravel kembali ke halaman form tambah mata kuliah. Tujuan kembali tersebut ditentukan oleh Laravel melalui proses validasi pada Form Request.

4. Dari mana @error('sks') mengambil pesannya?
jawaban :
@error('sks') mengambil pesan kesalahan dari error validasi yang disimpan Laravel di session. Karena yang dipanggil adalah sks, maka yang ditampilkan adalah pesan kesalahan untuk bagian SKS.

5. Dari mana old('sks') mengambil nilainya? Berapa lama nilai itu bertahan?
jawaban : 
old('sks') mengambil nilai SKS yang sebelumnya dimasukkan pada form. Nilai tersebut disimpan sementara oleh Laravel di session ketika validasi gagal. Nilainya hanya bertahan untuk request berikutnya, sehingga setelah halaman ditampilkan kembali, nilai tersebut tidak terus disimpan.

6. Buka DevTools → Application → Cookies. Temukan cookie session Laravel. Catat namanya.
jawaban : 
Pada DevTools bagian Application, Cookies, nama cookie session Laravel biasanya adalah laravel_session. Cookie tersebut digunakan Laravel untuk mengenali session pengguna.

BREAK
