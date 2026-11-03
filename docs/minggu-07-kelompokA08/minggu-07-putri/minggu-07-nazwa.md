## Read 

1. Berkas mana yang menangani POST login? Method apa?

    Jawaban : Proses login ditangani oleh AuthenticatedSessionController.php, tepatnya pada method store(). Method ini menerima data email dan password yang dikirim melalui form login.

2. Di baris mana Auth::attempt() atau setaranya dipanggil?

    Jawaban : Auth::attempt() dipanggil di dalam method store() pada AuthenticatedSessionController.php. Fungsinya untuk mencocokkan email dan password yang dimasukkan dengan data pengguna di database.

3. Temukan session()->regenerate(). Kenapa ia ada di situ?

    Jawaban : session()->regenerate() dipanggil setelah login berhasil. Tujuannya untuk membuat ID session baru agar session lama tidak bisa digunakan kembali. Hal ini juga membantu mencegah serangan session fixation.

4. Di mana kata sandi di-hash? Cari cast hashed di model User.

    Jawaban : Pengaturan hash kata sandi terdapat di file app/Models/User.php, tepatnya pada bagian $casts dengan atribut 'password' => 'hashed'. Pengaturan ini membuat kata sandi otomatis di-hash sebelum disimpan ke database sehingga tidak tersimpan dalam bentuk teks biasa.

5. Buka DevTools → Cookies sebelum dan sesudah login. Bandingkan nilai cookie session.

    Jawaban : Sebelum dan sesudah login, nilai cookie session biasanya berbeda karena Laravel memperbarui ID session setelah login berhasil. Perubahan ini dilakukan untuk menjaga keamanan akun pengguna.

6. Logout, lalu tekan tombol back. Apa yang terjadi? Kenapa?

    Jawaban : Setelah logout, ketika tombol back ditekan, halaman sebelumnya mungkin masih terlihat karena tersimpan di cache browser. Namun, ketika halaman dimuat ulang atau mencoba mengakses fitur yang membutuhkan login, pengguna akan diarahkan kembali ke halaman login karena session sudah dihapus.

