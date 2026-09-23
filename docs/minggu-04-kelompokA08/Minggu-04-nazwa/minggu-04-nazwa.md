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
1. Hapus @csrf dari form, lalu kirim
 ![alt text](<break 4 no 1.png>)

2. Ganti $request->validated() menjadi $request->all(), lalu kirim field liar lewat curl
Pengujian menunjukkan bahwa penggunaan $request->all() membuka kembali risiko mass assignment karena seluruh data dari request diberikan ke proses penyimpanan. Perbaikannya adalah hanya menggunakan data yang sudah lolos validasi
![alt text](<break 4 no 2.jpeg>)

3. Hapus validasi exists:users,id pada lecturer_id, kirim lecturer_id=99999
Hasil percobaan membuktikan bahwa fungsi F`ormRequest` (Validation Layer) bukan hanya untuk merapikan input pengguna, melainkan berfungsi sebagai Garda Depan (First Line of Defense) untuk mencegah data yang tidak valid/tidak lengkap menyentuh database.Tanpa adanya validasi di FormRequest, kesalahan input akan langsung menghantam skema database dan menyebabkan aplikasi crash (Error 500) alih-alih memberikan pesan peringatan yang rapi kepada pengguna.Jika skema database di panduan mensyaratkan kolom tersebut nullable, data tanpa dosen memang akan lolos dan menjadi data yatim (data tanpa relasi/penanggung jawab yang jelas).
![alt text](<break 4 no 3.jpeg>)

4. Hapus validasi in:... pada status, kirim status=superadmin
Hasil percobaan Status Jebol  dengan status  "superadmin" berhasil lolos melewati validasi dan resmi tersimpan ke dalam database. Penyebab utamanya Validasi in:Active,Draft,Archive di StoreCourseRequest.php dihapus dan Tipe data kolom status di migrasi database dilonggarkan menjadi string sehingga di FormRequest, pengguna bisa memasukkan nilai status liar/invalid yang berpotensi merusak logika bisnis aplikasi. 
![alt text](<break 4 no 4.jpeg>) output 

5. Hapus ->withQueryString(), lakukan pencarian lalu klik halaman 2
Jika sebelumnya ketika menambahkan parameter seperti &search=Dosen atau &role=Mahasiswa di URL, begitu kamu menekan tombol Next atau tombol angka halaman 2, parameter tersebut hilang begitu saja dan hanya menyisakan ?as=admin&page=2. Dan Tanpa method withQueryString(), tautan pagination yang digenerate oleh Laravel tidak akan mempertahankan kondisi pencarian/filter pengguna. Hal ini memaksa halaman kembali menampilkan seluruh data umum tanpa filter setiap kali pengguna berpindah halaman.
![alt text](<break 4 no 5.jpeg>)

6. Ganti return redirect() menjadi return view() pada store, lalu tekan F5 setelah simpan
Skenario Menggunakan return view() (Bug Terbukti):

Browser tetap berada pada metode HTTP POST. Saat pengguna tidak sengaja menekan F5, browser akan mengeksekusi ulang pengiriman form. Hal ini menyebabkan data ganda tersimpan (jika tidak ada batasan unique), atau server mengalami crash / Error 500 (karena memicu duplicate entry / constraint violation seperti pada gambar).Skenario Menggunakan return redirect()`Setelah proses simpan data `POST selesai, server langsung mengalihkan browser ke metode HTTP GET via redirect(). Jika pengguna menekan F5, yang di-refresh hanyalah tampilan data (GET), sehingga aman dari eksekusi simpan ulang.
![alt text](<break 4 no 6.jpeg>)

7. Hapus old(...) dari semua input, lalu kirim form dengan satu kesalahan
Saat atribut required di HTML dihapus, validasi diserahkan sepenuhnya ke backend Laravel. Ketika validasi gagal (misalnya karena code wajib diisi), pengguna dilempar balik ke form`dan tanpa `old(), semua inputan panjang yang sudah diketik tadi langsung hangus/bersih.
