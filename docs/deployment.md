# Dokumentasi Deployment dan Pengujian KampusLMS

## 1. Informasi Aplikasi

KampusLMS merupakan aplikasi Learning Management System (LMS) berbasis Laravel yang memiliki tiga peran pengguna, yaitu Admin, Dosen, dan Mahasiswa.

Aplikasi telah berhasil di-deploy ke platform Railway dan dapat diakses secara online melalui koneksi HTTPS.

| Informasi | Keterangan |
|---|---|
| Nama aplikasi | KampusLMS |
| Platform deployment | Railway |
| URL produksi | https://kampuslms-kelompok-a08-production-66912567.up.railway.app |
| Status | Online |
| Protokol | HTTPS |

### 1.1 Bukti Akses HTTPS

Berdasarkan pemeriksaan URL produksi, diperoleh respons berikut:

HTTP/2 200
server: Railway
strict-transport-security: max-age=31536000; includeSubDomains

Hasil tersebut menunjukkan bahwa aplikasi berhasil memberikan respons HTTP 200 dan server menerapkan header HTTP Strict Transport Security (HSTS).

## 2. Spesifikasi Server

Deployment aplikasi KampusLMS menggunakan platform Railway dengan lingkungan aplikasi berbasis Docker.

| Komponen | Spesifikasi |
|---|---|
| Platform deployment | Railway |
| Runtime aplikasi | Docker |
| Versi PHP | PHP 8.4 |
| Web server | Apache |
| Database | MySQL Managed Database |
| Process manager | Supervisor |

### 2.1 Konfigurasi Supervisor

Aplikasi menggunakan Supervisor melalui berkas `supervisord.conf` untuk mengelola dua proses utama secara bersamaan:

1. Apache (`apache2-foreground`) sebagai web server yang menangani permintaan pengguna.
2. Laravel Queue Worker (`php artisan queue:work`) untuk memproses pekerjaan antrean di latar belakang.

Dengan konfigurasi tersebut, kedua proses dapat berjalan secara bersamaan pada lingkungan produksi.

## 3. Langkah Deployment

Proses deployment dilakukan melalui Railway dengan menghubungkan repositori GitHub KampusLMS ke layanan aplikasi.

Tahapan deployment dari awal secara umum adalah sebagai berikut.

### 3.1 Persiapan Proyek

1. Memastikan source code aplikasi tersedia di repositori GitHub.
2. Memastikan berkas Dockerfile dan konfigurasi deployment tersedia.
3. Memastikan perubahan kode sudah di-push ke branch yang akan digunakan.
4. Memastikan konfigurasi aplikasi telah disiapkan untuk lingkungan produksi.

### 3.2 Membuat Project Railway

1. Masuk ke akun Railway.
2. Membuat project baru atau membuka project yang akan digunakan.
3. Menghubungkan project dengan repositori GitHub KampusLMS.
4. Memilih branch sumber deployment.
5. Memastikan konfigurasi build menggunakan Dockerfile proyek.
6. Menjalankan proses deployment dan memantau hasil build.

### 3.3 Membuat dan Menghubungkan Database

Aplikasi menggunakan MySQL Managed Database.

Tahapan yang perlu dilakukan:

1. Menambahkan layanan database MySQL pada project Railway.
2. Memastikan database berhasil dibuat.
3. Menghubungkan konfigurasi database dengan layanan aplikasi.
4. Memastikan variabel koneksi database tersedia pada environment aplikasi.
5. Menjalankan migrasi database setelah koneksi berhasil.

Contoh variabel environment yang diperlukan:

```env
APP_ENV=production
APP_DEBUG=false
APP_KEY=
APP_URL=https://kampuslms-kelompok-a08-production-66912567.up.railway.app

DB_CONNECTION=mysql
DB_HOST=
DB_PORT=
DB_DATABASE=
DB_USERNAME=
DB_PASSWORD=
```

Nilai `APP_KEY` dan kredensial database harus diisi melalui konfigurasi environment Railway. Informasi rahasia tidak boleh dimasukkan ke repositori publik.

### 3.4 Menjalankan Aplikasi

Setelah konfigurasi aplikasi dan database tersedia, proses persiapan aplikasi dilakukan sesuai kebutuhan proyek.

Contoh perintah Laravel untuk persiapan lingkungan produksi:

```bash
composer install --no-dev --optimize-autoloader
php artisan migrate --force
```

Jika proyek menggunakan proses build frontend, pastikan aset frontend juga dibangun sesuai konfigurasi Dockerfile dan proyek.

Setelah deployment selesai, aplikasi diperiksa melalui URL produksi untuk memastikan halaman dapat diakses.

## 4. Skrip Deployment

Berkas `deploy.sh` digunakan untuk membantu proses deployment ulang aplikasi pada lingkungan server.

Berdasarkan laporan Putri, skrip tersebut menjalankan beberapa proses, yaitu:

1. Mengaktifkan mode maintenance.
2. Memperbarui kode dari repositori.
3. Memasang dependensi PHP tanpa dependensi development.
4. Menjalankan migrasi database.
5. Membuat cache konfigurasi Laravel.
6. Membuat cache route.
7. Membuat cache tampilan Laravel.
8. Meminta Laravel Queue Worker melakukan restart.

Perintah untuk menjalankan skrip:

```bash
bash deploy.sh
```

Skrip dijalankan setelah konfigurasi database tersedia dan lingkungan deployment siap digunakan.

**Catatan:** Isi kode lengkap `deploy.sh` perlu dicantumkan sesuai berkas aktual yang tersimpan di repositori. Pastikan skrip tersebut sudah diuji dan memiliki komentar pada tahapan penting sebelum dikumpulkan.

## 5. Deployment Ulang

Deployment ulang dilakukan ketika terdapat perubahan kode atau perbaikan pada aplikasi.

Tahapan umum deployment ulang:

1. Melakukan perubahan kode pada branch proyek.
2. Memastikan perubahan sudah diuji.
3. Melakukan commit dan push ke GitHub.
4. Memastikan Railway mendeteksi perubahan pada branch yang terhubung.
5. Menunggu proses build dan deployment selesai.
6. Memeriksa hasil deployment melalui URL produksi.
7. Memeriksa status Apache dan Laravel Queue Worker.
8. Menguji kembali fitur utama aplikasi.

Apabila deployment dilakukan melalui terminal server sesuai konfigurasi proyek, skrip deployment dapat dijalankan menggunakan:

```bash
bash deploy.sh
```

Setelah deployment ulang, pastikan aplikasi dapat diakses dan tidak terdapat kesalahan pada proses build maupun runtime.

## 6. Pemantauan Supervisor

Supervisor digunakan untuk mengelola proses Apache dan Laravel Queue Worker.

Pemeriksaan status proses dilakukan melalui console Railway menggunakan perintah:

```bash
supervisorctl status
```

### 6.1 Hasil Pemeriksaan

Berdasarkan laporan Putri, hasil pemeriksaan pada console server live Railway adalah:

```text
root@b800351702ce:/var/www/html# supervisorctl status
apache2                          RUNNING   pid 9, uptime 0:18:12
laravel-worker                   RUNNING   pid 10, uptime 0:18:12
```

Hasil tersebut menunjukkan bahwa:

- Apache berstatus `RUNNING`.
- Laravel Queue Worker berstatus `RUNNING`.

**Kesimpulan:** Kedua proses utama aplikasi berhasil berjalan melalui Supervisor.

**Bukti:** 
![alt text](<WhatsApp Image 2026-11-12 at 12.35.21.jpeg>)

## 7. Cara Membaca Log

Log digunakan untuk mengetahui aktivitas aplikasi dan membantu menemukan kesalahan yang terjadi pada lingkungan produksi.

### 7.1 Log Laravel

Log Laravel secara umum tersimpan pada direktori:

```text
storage/logs/
```

Untuk melihat 100 baris terakhir:

```bash
tail -n 100 storage/logs/laravel.log
```

Untuk memantau log secara langsung:

```bash
tail -f storage/logs/laravel.log
```

Log Laravel dapat membantu menemukan masalah seperti:

- Kesalahan aplikasi.
- Kesalahan koneksi database.
- Kesalahan autentikasi.
- Kesalahan ketika menjalankan fitur aplikasi.

### 7.2 Log Railway

Pemeriksaan log deployment dapat dilakukan melalui dashboard Railway:

1. Membuka project KampusLMS.
2. Memilih service aplikasi.
3. Membuka bagian deployment atau logs.
4. Memilih deployment yang ingin diperiksa.
5. Membaca pesan build dan runtime untuk menemukan informasi error.

Lokasi dan jenis log yang tersedia mengikuti konfigurasi service pada Railway.

### 7.3 Pemeriksaan Supervisor

Status proses dapat diperiksa menggunakan:

```bash
supervisorctl status
```

Jika proses tidak berjalan, periksa konfigurasi Supervisor dan log proses terkait sebelum melakukan perbaikan.

## 8. Kendala dan Penyelesaian Selama Deployment

Selama proses deployment KampusLMS, terdapat beberapa kendala yang ditemukan. Kendala tersebut berkaitan dengan proses build Docker, konfigurasi database, tampilan aplikasi, serta data dan notifikasi pengguna.

### 8.1 Proses Deployment Mengalami Kegagalan

**Kendala:**

Pada saat aplikasi akan di-deploy, proses deployment mengalami kegagalan berulang kali. Dockerfile sudah mengalami perubahan sebanyak tiga kali, tetapi ketika dilakukan redeployment, perubahan kode terbaru tetap belum diterapkan.

Setelah diperiksa kembali, ditemukan bahwa konfigurasi atau bagian matriks yang dibutuhkan masih kosong sehingga proses deployment tidak berjalan sesuai harapan.

**Penanganan:**

Proses deployment kemudian disusun ulang dari awal dengan memulai kembali tahapan konfigurasi. Setiap langkah diperiksa kembali agar konfigurasi yang dibutuhkan tidak terlewat.

**Hasil:** Aplikasi akhirnya berhasil di-deploy dan dapat diakses melalui URL produksi Railway.

### 8.2 Database Belum Dibuat Sebelum Deployment

**Kendala:**

Pada percobaan deployment sebelumnya, proses deployment sudah dilakukan ketika database belum dibuat. Akibatnya, ketika ingin memeriksa database, layanan database belum tersedia.

**Penanganan:**

Proses konfigurasi dibuat ulang dari tahap awal. Kali ini, tahapan pembuatan database diperhatikan terlebih dahulu sebelum melanjutkan deployment aplikasi.

Urutan konfigurasi diperiksa kembali agar aplikasi memiliki database yang tersedia dan dapat dihubungkan ketika dijalankan.

**Hasil:** Aplikasi berhasil menggunakan MySQL Managed Database pada lingkungan Railway.

### 8.3 Tampilan Aplikasi Tidak Sesuai dengan Tampilan Proyek

**Kendala:**

Setelah aplikasi berhasil di-deploy, tampilan web belum rapi dan antarmuka pengguna (UI) belum muncul sebagaimana tampilan pada proyek yang dikembangkan sebelumnya.

**Penanganan:**

Tampilan aplikasi perlu diperiksa kembali dengan mencocokkan konfigurasi frontend, aset CSS dan JavaScript, serta proses build frontend pada lingkungan produksi.

### 8.4 Notifikasi Tugas untuk Mahasiswa Tidak Masuk

**Kendala:**

Notifikasi kepada mahasiswa ketika dosen memberikan tugas belum masuk sebagaimana yang diharapkan. Namun, ketika mahasiswa mengumpulkan tugas, notifikasi dapat masuk.

**Penanganan dan tindak lanjut:**

Perlu dilakukan pemeriksaan terhadap proses pembuatan notifikasi ketika dosen memberikan tugas, termasuk pemanggilan fungsi notifikasi, penerima notifikasi, dan proses penyimpanan notifikasi.

Perlu dipastikan bahwa mekanisme notifikasi saat dosen memberikan tugas berjalan sebagaimana mekanisme notifikasi saat mahasiswa mengumpulkan tugas.

### 8.5 Notifikasi Pendaftaran Mahasiswa Tidak Masuk

**Kendala:**

Pada proses pendaftaran mandiri oleh mahasiswa, notifikasi yang seharusnya diterima belum masuk.

**Penanganan dan tindak lanjut:**

Perlu dilakukan pemeriksaan terhadap proses pendaftaran mandiri dan pemanggilan notifikasi setelah mahasiswa berhasil mendaftar.

Pemeriksaan juga perlu memastikan penerima notifikasi sudah sesuai dan proses penyimpanan notifikasi berjalan dengan benar.

**Status:** Perbaikan belum dikonfirmasi dalam laporan yang diberikan.

### 8.6 Jumlah Mahasiswa dan Data Pengguna Masih Kurang

**Kendala:**

Setelah aplikasi berhasil dijalankan, jumlah mahasiswa yang tersedia hanya dua orang. Data pengguna di database juga masih kurang sehingga belum sepenuhnya mencerminkan kebutuhan pengujian aplikasi.

**Penanganan dan tindak lanjut:**

Perlu dilakukan pemeriksaan terhadap data awal yang dimasukkan melalui seeder maupun data pengguna yang tersedia pada database produksi.

Data pengguna dapat dilengkapi melalui proses pengelolaan data sesuai peran dan kebutuhan pengujian. Penambahan data harus dilakukan dengan tetap memperhatikan validitas dan keamanan data.


### 8.7 Ringkasan Kendala Deployment

| No. | Kendala | Penanganan atau tindak lanjut |
|---|---|---|
| 1 | Deployment gagal dan perubahan Dockerfile tidak diterapkan setelah redeploy | Menyusun ulang proses deployment dari awal dan memeriksa kembali konfigurasi |
| 2 | Database belum dibuat ketika deployment dilakukan | Mengulang konfigurasi dari awal dengan memastikan database dibuat terlebih dahulu |
| 3 | Tampilan aplikasi tidak rapi dan UI tidak muncul sesuai proyek | Memeriksa konfigurasi frontend serta proses build aset |
| 4 | Notifikasi tugas dari dosen tidak masuk ke mahasiswa | Memeriksa proses pembuatan dan penyimpanan notifikasi |
| 5 | Notifikasi pendaftaran mandiri mahasiswa tidak masuk | Memeriksa proses pendaftaran dan pemanggilan notifikasi |
| 6 | Jumlah mahasiswa hanya dua orang dan data pengguna kurang | Memeriksa seeder dan melengkapi data pengguna sesuai kebutuhan |

## 9. Pemeriksaan Keamanan Produksi

Pemeriksaan keamanan dilakukan oleh Sarah pada tanggal **11 November 2026** terhadap server produksi KampusLMS.

### 9.1 Pemeriksaan Path Berkas Sensitif

Pemeriksaan dilakukan menggunakan perintah `curl -I` melalui Git Bash.

| No. | Path | Hasil |
|---|---|---|
| 1 | `/.env` | HTTP 404 Not Found |
| 2 | `/storage/logs/laravel.log` | HTTP 404 Not Found |
| 3 | `/composer.json` | HTTP 404 Not Found |

#### Hasil pemeriksaan `/.env`

```text
HTTP/1.1 404 Not Found
Cache-Control: no-cache, private
Content-Type: text/html; charset=utf-8
Date: Wed, 11 Nov 2026 14:43:39 GMT
Server: railway-hikari
x-powered-by: PHP/8.4.27
x-railway-request-id: DNfQdQUKRuSFnqI9lt7tkg
x-hikari-trace: sin1.nzn2
x-railway-edge: sin1
Connection: keep-alive
```

#### Hasil pemeriksaan `/storage/logs/laravel.log`

```text
HTTP/1.1 404 Not Found
Cache-Control: no-cache, private
Content-Type: text/html; charset=utf-8
Date: Wed, 11 Nov 2026 14:43:55 GMT
Server: railway-hikari
x-powered-by: PHP/8.4.27
x-railway-request-id: B1u2eDPkQbOQdp_OnpoFkQ
x-hikari-trace: sin1.462z
x-railway-edge: sin1
Connection: keep-alive
```

#### Hasil pemeriksaan `/composer.json`

```text
HTTP/1.1 404 Not Found
Cache-Control: no-cache, private
Content-Type: text/html; charset=utf-8
Date: Wed, 11 Nov 2026 14:45:38 GMT
Server: railway-hikari
x-powered-by: PHP/8.4.27
x-railway-request-id: vIILKc07RiaMiw8T9I3ezw
x-hikari-trace: sin1.d1nj
x-railway-edge: sin1
Connection: keep-alive
```
### Keluaran Perintah curl -I Sesungguhnya

$ for path in .env storage/logs/laravel.log composer.json; do
  echo "=== Cek /$path ==="
  curl -I -s "[https://kampuslms-kelompok-a08-production-66912567.up.railway.app/$path]" | head -n 5
done
```
=== Cek /.env ===
HTTP/1.1 404 Not Found
Cache-Control: no-cache, private
Content-Type: text/html; charset=utf-8
Date: Thu, 12 Nov 2026 03:46:35 GMT
Server: railway-hikari
=== Cek /storage/logs/laravel.log ===
HTTP/1.1 404 Not Found
Cache-Control: no-cache, private
Content-Type: text/html; charset=utf-8
Date: Thu, 12 Nov 2026 03:46:35 GMT
Server: railway-hikari
=== Cek /composer.json ===
HTTP/1.1 404 Not Found
Cache-Control: no-cache, private
Content-Type: text/html; charset=utf-8
Date: Thu, 12 Nov 2026 03:46:36 GMT
Server: railway-hikari
```

### 9.2 Pengujian Otorisasi API Lokal


```bash
scripts/test-authz.sh
```

Target API:

http://localhost:8000/api/v1

Hasil pengujian menunjukkan seluruh skenario dalam skrip memperoleh status **PASS**.

Skenario yang berhasil diuji antara lain:

- Mahasiswa yang tidak terdaftar ditolak ketika mencoba mengakses course.
- Dosen ditolak ketika mencoba mengakses course milik dosen lain.

Hasil tersebut menunjukkan bahwa skenario otorisasi yang diuji berhasil pada lingkungan lokal.

### 9.3 Pengujian Otorisasi Produksi

Pengujian otorisasi pada server produksi awalnya belum berhasil dilakukan karena percobaan login menggunakan akun admin@kampuslms.test menghasilkan respons HTTP 422 (Email atau kata sandi yang Anda masukkan salah.).

Setelah penyesuaian seeder akun demo dan data relasi pendaftaran mata kuliah (course_user) pada server produksi Railway, pengujian otorisasi API dijalankan ulang langsung terhadap endpoint produksi menggunakan perintah:

API_BASE_URL="[https://kampuslms-kelompok-a08-production-66912567.up.railway.app/api/v1]" bash scripts/test-authz.sh

### Keluaran sesungguhnya pada terminal produksi:

```
$ API_BASE_URL="[https://kampuslms-kelompok-a08-production-66912567.up.railway.app/api/v1](https://kampuslms-kelompok-a08-production-66912567.up.railway.app/api/v1)" bash scripts/test-authz.sh
PASS Daftar course tanpa token ditolak: HTTP 401
PASS Admin dapat melihat daftar seluruh course: HTTP 200
PASS Admin dapat membuat tugas melalui API: HTTP 201
PASS Admin dapat menghapus tugas melalui API: HTTP 204
PASS Admin dapat membuat materi melalui API: HTTP 201
PASS Admin dapat menghapus materi melalui API: HTTP 204
PASS Dosen pengampu dapat melihat course sendiri: HTTP 200
PASS Dosen lain tidak dapat melihat course dosen pengampu: HTTP 403
PASS Mahasiswa terdaftar dapat melihat course: HTTP 200
PASS Mahasiswa yang tidak terdaftar ditolak: HTTP 403
PASS Mahasiswa tidak dapat membuat tugas: HTTP 403
PASS Admin dapat melihat tugas draft: HTTP 200
PASS Dosen pengampu dapat melihat tugas draft: HTTP 200
PASS tugas draft tersedia untuk dosen pengampu
PASS Mahasiswa terdaftar hanya menerima tugas published: HTTP 200
PASS daftar mahasiswa tidak membocorkan tugas draft
PASS Mahasiswa tetap hanya menerima tugas published pada filter draft: HTTP 200
PASS filter draft tidak membocorkan tugas draft kepada mahasiswa
PASS Dosen lain tidak dapat melihat tugas draft course asing: HTTP 403
PASS Dosen lain tidak dapat membuka submission course asing: HTTP 403
Semua skenario uji otorisasi API lulus.
```

Kesimpulan: Seluruh 20 pengecekan dalam skrip uji otorisasi API (test-authz.sh) lulus 100% langsung di server produksi Railway.


### 9.4 Endpoint Submission yang Perlu Ditindaklanjuti

Berdasarkan dokumentasi `keamanan.md`, terdapat dua endpoint yang masih perlu diperiksa terkait validasi status publikasi tugas:

POST /api/v1/assignments/{id}/submissions
POST /api/v1/submissions

Kedua endpoint tersebut perlu diperiksa untuk memastikan mahasiswa tidak dapat mengirimkan submission secara langsung pada tugas yang masih berstatus `draft`.

Perilaku endpoint tersebut pada server produksi belum diverifikasi.

### 9.5 Kesimpulan Pemeriksaan Keamanan

Berdasarkan pemeriksaan keamanan produksi:

Tiga path berkas sensitif (/.env, /storage/logs/laravel.log, /composer.json) menghasilkan HTTP 404 Not Found.

Pengujian otorisasi API berhasil pada lingkungan lokal maupun server produksi Railway (seluruh 20 pengecekan berstatus PASS).

Endpoint submission terkait tugas berstatus draft masih perlu ditindaklanjuti sesuai catatan pada keamanan.md.

## 10. Pengujian Akun Demo pada Server Live

Pengujian akun demo dilakukan oleh Siti pada server live Railway untuk memastikan fitur utama dapat digunakan oleh setiap peran pengguna.

### 10.1 Pengujian Admin

**Akun:** `admin@kampus.id`

**Status: LULUS**

Hasil pengujian:

- Login menggunakan akun Admin berhasil.
- Admin dapat membuka dashboard.
- Navigasi dashboard dan menu yang diuji berjalan normal.
- Fitur pengelolaan data yang diuji dapat diakses dan berfungsi normal.
- Halaman ditampilkan sebagaimana mestinya untuk peran Admin.

**Kesimpulan:** Akun Admin berhasil digunakan untuk mengakses dashboard dan fitur pengelolaan data yang diuji.

### 10.2 Pengujian Dosen

**Akun:** `dosen@kampus.id`

**Status: LULUS**

Hasil pengujian:

- Login menggunakan akun Dosen berhasil.
- Dosen dapat membuka bagian pengelolaan mata kuliah.
- Fitur pengelolaan mata kuliah yang diuji berjalan normal.
- Dosen dapat membuka dan mengelola bagian materi.
- Halaman dan fitur yang diuji dapat digunakan sebagaimana mestinya.

**Kesimpulan:** Akun Dosen berhasil digunakan untuk mengakses dan mengelola mata kuliah serta materi yang diuji.

### 10.3 Pengujian Mahasiswa

**Akun:** `mahasiswa@kampus.id`

**Status: LULUS**

Hasil pengujian:

- Login menggunakan akun Mahasiswa berhasil.
- Mahasiswa dapat membuka dan melihat katalog mata kuliah.
- Mahasiswa dapat mengakses materi yang diuji.
- Halaman dan fitur yang diuji berjalan normal.

**Kesimpulan:** Akun Mahasiswa berhasil digunakan untuk melihat katalog mata kuliah dan mengakses materi.

### 10.4 Rekapitulasi Pengujian

| No. | Peran | Akun | Hasil |
|---|---|---|---|
| 1 | Admin | `admin@kampus.id` | LULUS |
| 2 | Dosen | `dosen@kampus.id` | LULUS |
| 3 | Mahasiswa | `mahasiswa@kampus.id` | LULUS |

Ketiga akun demo berhasil digunakan dan fitur-fitur yang diuji berjalan normal pada server live Railway.

Catatan: Kata sandi akun demo tidak dicantumkan dalam dokumentasi.

## 11. Pengujian Aplikasi Melalui Perangkat HP

Pengujian melalui perangkat HP dilakukan oleh Syarifah untuk memastikan aplikasi dapat diakses melalui perangkat mobile.

### 11.1 Identitas Pengujian

| Informasi | Keterangan |
|---|---|
| URL pengujian | https://kampuslms-kelompok-a08-production-66912567.up.railway.app |

### 11.2 Skenario Pengujian

| No. | Skenario | Hasil |
|---|---|---|
| 1 | Membuka URL produksi melalui HP | [Berhasil] |
| 2 | Memastikan halaman utama dapat ditampilkan | [Berhasil] |
| 3 | Melakukan login menggunakan akun demo | [Berhasil] |
| 4 | Membuka menu dan fitur aplikasi | [Berhasil] |
| 5 | Memastikan tampilan dapat digunakan melalui HP | [Berhasil] |

### 11.3 Bukti Pengujian

Dosen
![alt text](<WhatsApp Image 2026-11-12 at 07.27.14 (2).jpeg>)
![alt text](<WhatsApp Image 2026-11-12 at 07.27.14 (1)-1.jpeg>)
![alt text](<WhatsApp Image 2026-11-12 at 07.27.15.jpeg>)

Mahasiswa
![alt text](<WhatsApp Image 2026-11-12 at 11.41.32.jpeg>)
![alt text](<WhatsApp Image 2026-11-12 at 11.46.21.jpeg>)
![alt text](<WhatsApp Image 2026-11-12 at 11.46.21 (1).jpeg>)

Admin
![alt text](<WhatsApp Image 2026-11-12 at 11.43.23.jpeg>)
![alt text](<WhatsApp Image 2026-11-12 at 11.43.24.jpeg>)


## 12. Kesimpulan Akhir

Berdasarkan proses deployment dan pengujian yang telah dilakukan oleh anggota kelompok, aplikasi KampusLMS berhasil di-deploy pada platform Railway dan dapat diakses menggunakan HTTPS.

Hasil pemeriksaan menunjukkan bahwa:

1. Aplikasi berhasil berjalan pada lingkungan Railway menggunakan Docker, PHP 8.4, Apache, dan MySQL Managed Database.
2. Apache dan Laravel Queue Worker berhasil berjalan secara bersamaan melalui Supervisor.
3. Pemeriksaan tiga path berkas sensitif menghasilkan HTTP 404 Not Found.
4. Pengujian otorisasi API berhasil pada lingkungan lokal, tetapi belum berhasil diverifikasi pada server produksi.
5. Pengujian akun demo Admin, Dosen, dan Mahasiswa berhasil dilakukan pada server live.
6. Selama deployment ditemukan beberapa kendala, seperti kegagalan build Docker, urutan pembuatan database, tampilan aplikasi yang belum sesuai, notifikasi yang belum berjalan pada beberapa proses, serta data pengguna yang masih kurang.
7. Pengujian melalui perangkat HP masih perlu dilengkapi dengan hasil aktual dan bukti tangkapan layar.

Secara keseluruhan, aplikasi KampusLMS telah berhasil dipublikasikan dan akun demo untuk ketiga peran dapat digunakan. Namun, pemeriksaan keamanan produksi, perbaikan notifikasi, penyesuaian data pengguna, tampilan aplikasi, serta dokumentasi pengujian mobile masih perlu ditindaklanjuti.