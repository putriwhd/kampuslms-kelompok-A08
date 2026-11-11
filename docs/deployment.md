# Laporan Deployment dan Pengujian KampusLMS

## 1. Informasi Deployment

Aplikasi KampusLMS telah berhasil di-deploy ke platform Railway dan dapat diakses secara online melalui koneksi HTTPS.

Berikut informasi aplikasi yang telah di-deploy:

- **Nama aplikasi:** KampusLMS
- **Platform deployment:** Railway
- **URL produksi:** https://kampuslms-kelompok-a08-production-66912567.up.railway.app
- **Status aplikasi:** Online
- **Protokol komunikasi:** HTTPS

### Bukti Keberhasilan Deployment

Berdasarkan pemeriksaan terhadap URL produksi, aplikasi memberikan respons sebagai berikut:

```text
HTTP/2 200
server: Railway
strict-transport-security: max-age=31536000; includeSubDomains
```

Respons HTTP 200 menunjukkan bahwa aplikasi berhasil diakses oleh pengguna. Sementara itu, header `strict-transport-security` menunjukkan bahwa server menerapkan kebijakan HTTPS.

## 2. Spesifikasi Server

Deployment KampusLMS menggunakan layanan Railway dengan lingkungan aplikasi berbasis Docker.

Spesifikasi server yang digunakan adalah:

| Komponen | Spesifikasi |
|---|---|
| Platform | Railway |
| Runtime | Docker |
| Bahasa pemrograman | PHP 8.4 |
| Web server | Apache |
| Database | MySQL Managed Database |
| Process manager | Supervisor |

### Konfigurasi Proses Aplikasi

Aplikasi menggunakan Supervisor melalui berkas `supervisord.conf` untuk mengelola dua proses utama secara bersamaan, yaitu:

1. **Apache (`apache2-foreground)**` sebagai web server yang menangani permintaan pengguna.
2. **Laravel Queue Worker (`php artisan queue:work`)** untuk menjalankan pekerjaan antrean di latar belakang.

Dengan konfigurasi tersebut, proses web server dan queue worker dapat berjalan secara bersamaan di lingkungan produksi.

## 3. Skrip Deployment

Proses deployment dibantu dengan berkas `deploy.sh` yang digunakan untuk menjalankan beberapa perintah persiapan aplikasi setelah kode tersedia di lingkungan server.

Berdasarkan laporan Putri, skrip tersebut menjalankan proses berikut:

1. Menjalankan migrasi database.
2. Membuat cache konfigurasi Laravel.
3. Membuat cache route.
4. Membuat cache tampilan atau view.
5. Meminta Laravel Queue Worker untuk melakukan restart agar dapat menggunakan perubahan terbaru.

Untuk menjalankan skrip deployment, gunakan perintah:

```bash
bash deploy.sh
```

Perintah tersebut dijalankan melalui terminal pada lingkungan deployment setelah konfigurasi database tersedia.

**Catatan:** Berkas `deploy.sh` yang digunakan dalam proyek tetap menjadi acuan untuk isi lengkap skrip deployment.

## 4. Pemantauan Proses dengan Supervisor

Supervisor digunakan untuk memastikan Apache dan Laravel Queue Worker berjalan dengan baik pada server produksi.

Pemeriksaan status proses dilakukan melalui console Railway menggunakan perintah:

```bash
supervisorctl status
```

Berikut hasil pemeriksaan pada server live Railway:

```text
root@b800351702ce:/var/www/html# supervisorctl status
apache2                          RUNNING   pid 9, uptime 0:18:12
laravel-worker                   RUNNING   pid 10, uptime 0:18:12
```

Berdasarkan hasil tersebut, proses Apache dan Laravel Queue Worker sama-sama menunjukkan status `RUNNING`.

Hal ini menunjukkan bahwa kedua proses utama aplikasi berhasil dijalankan melalui Supervisor.

## 5. Kendala Selama Deployment

Berdasarkan laporan deployment yang diberikan oleh Putri, aplikasi KampusLMS berhasil di-deploy hingga dapat diakses melalui server live Railway menggunakan HTTPS.

Konfigurasi server menggunakan Docker, PHP 8.4, Apache, MySQL Managed Database, dan Supervisor juga berhasil dijalankan. Hal tersebut dibuktikan melalui hasil pemeriksaan status Apache dan Laravel Queue Worker yang menunjukkan status `RUNNING`.

Dalam laporan yang diberikan, tidak terdapat kendala teknis khusus yang dilaporkan selama proses deployment.

## 6. Pemeriksaan Keamanan Produksi

Pemeriksaan keamanan produksi dilakukan oleh Sarah pada tanggal **11 November 2026**. Pengujian bertujuan untuk memastikan beberapa berkas sensitif tidak dapat diakses secara langsung melalui URL publik serta memeriksa fungsi otorisasi API.

### 6.1 Pemeriksaan Akses Berkas Sensitif

Pemeriksaan dilakukan menggunakan perintah `curl -I` melalui Git Bash terhadap tiga path berikut:

| No. | Path yang Diuji | Hasil Respons | Status |
|---|---|---|---|
| 1 | `/.env` | HTTP 404 Not Found | PASS |
| 2 | `/storage/logs/laravel.log` | HTTP 404 Not Found | PASS |
| 3 | `/composer.json` | HTTP 404 Not Found | PASS |

Ketiga path menghasilkan respons HTTP 404 Not Found.

**Kesimpulan:** Ketiga berkas tidak dapat diakses melalui URL publik yang diuji pada saat pemeriksaan.

### 6.2 Pengujian Otorisasi API

Pengujian otorisasi dilakukan menggunakan skrip:

```bash
scripts/test-authz.sh
```

Pengujian dijalankan pada lingkungan lokal dengan target API:

```text
http://localhost:8000/api/v1
```

Hasil pengujian menunjukkan seluruh skenario dalam skrip berhasil dijalankan dengan status **PASS**.

Beberapa skenario yang berhasil diuji meliputi:

- Mahasiswa yang tidak terdaftar ditolak ketika mencoba mengakses course.
- Dosen ditolak ketika mencoba mengakses course milik dosen lain.

Hasil tersebut menunjukkan bahwa skenario pembatasan akses yang diuji berjalan sesuai harapan pada lingkungan lokal.

### 6.3 Pengujian Otorisasi pada Server Produksi

Pengujian otorisasi pada server produksi belum berhasil diselesaikan karena proses login menggunakan akun pengujian berikut ditolak:

```text
admin@kampuslms.test
```

Server memberikan respons HTTP 422 dengan pesan:

```json
{
  "message": "Data yang diberikan tidak valid.",
  "errors": {
    "email": [
      "Email atau kata sandi yang Anda masukkan salah."
    ]
  }
}
```

Karena autentikasi gagal, skrip pengujian berhenti sebelum menjalankan skenario otorisasi berikutnya. Pengujian juga tidak sampai pada tahap pembuatan maupun penghapusan data.

**Kesimpulan:** Pengujian otorisasi berhasil pada lingkungan lokal, tetapi belum dapat diverifikasi pada server produksi karena kendala kredensial akun pengujian.

### 6.4 Pemeriksaan Endpoint Submission

Berdasarkan dokumentasi keamanan proyek, terdapat dua endpoint submission yang masih perlu ditindaklanjuti terkait pemeriksaan status publikasi tugas:

```text
POST /api/v1/assignments/{id}/submissions
POST /api/v1/submissions
```

Pemeriksaan diperlukan untuk memastikan mahasiswa tidak dapat mengirimkan submission secara langsung pada tugas yang masih berstatus `draft`.

Perilaku kedua endpoint tersebut pada server produksi belum diverifikasi melalui pengujian yang dilaporkan.

## 7. Pengujian Akun Demo pada Server Live

Pengujian akun demo dilakukan oleh Siti pada server live Railway. Pengujian mencakup tiga peran pengguna, yaitu Admin, Dosen, dan Mahasiswa.

Tujuannya adalah memastikan setiap peran dapat masuk ke aplikasi serta mengakses fitur sesuai dengan hak aksesnya.

### 7.1 Pengujian Akun Admin

**Akun:** `admin@kampus.id`

**Hasil: LULUS**

Pengujian yang dilakukan:

- Login menggunakan akun Admin berhasil.
- Dashboard berhasil dibuka setelah login.
- Navigasi dashboard dan menu yang diuji berjalan normal.
- Fitur pengelolaan data yang diuji dapat diakses dan berfungsi normal.
- Halaman ditampilkan sebagaimana mestinya untuk peran Admin.

**Kesimpulan:** Akun Admin berhasil digunakan untuk mengakses dashboard, melakukan navigasi, dan menggunakan fitur pengelolaan data yang diuji.

### 7.2 Pengujian Akun Dosen

**Akun:** `dosen@kampus.id`

**Hasil: LULUS**

Pengujian yang dilakukan:

- Login menggunakan akun Dosen berhasil.
- Bagian pengelolaan mata kuliah berhasil dibuka.
- Fitur pengelolaan mata kuliah yang diuji berjalan normal.
- Bagian materi berhasil dibuka dan dikelola.
- Halaman dan fitur yang diuji dapat digunakan sebagaimana mestinya.

**Kesimpulan:** Akun Dosen berhasil digunakan untuk mengakses dan mengelola mata kuliah serta materi yang diuji.

### 7.3 Pengujian Akun Mahasiswa

**Akun:** `mahasiswa@kampus.id`

**Hasil: LULUS**

Pengujian yang dilakukan:

- Login menggunakan akun Mahasiswa berhasil.
- Katalog mata kuliah berhasil dibuka.
- Mahasiswa dapat melihat katalog mata kuliah.
- Materi yang diuji berhasil diakses.
- Halaman dan fitur yang diuji berjalan normal.

**Kesimpulan:** Akun Mahasiswa berhasil digunakan untuk melihat katalog mata kuliah dan mengakses materi.

### 7.4 Rekapitulasi Hasil Pengujian Akun Demo

| No. | Peran | Akun | Hasil |
|---|---|---|---|
| 1 | Admin | `admin@kampus.id` | LULUS |
| 2 | Dosen | `dosen@kampus.id` | LULUS |
| 3 | Mahasiswa | `mahasiswa@kampus.id` | LULUS |

Berdasarkan hasil pengujian, ketiga akun demo berhasil digunakan dan fitur-fitur yang diuji berjalan normal pada server live Railway.

*Catatan: Kata sandi akun demo tidak dicantumkan dalam laporan.*

## 8. Pengujian Aplikasi Melalui Perangkat HP

Pengujian melalui perangkat HP dilakukan oleh Syarifah untuk memastikan aplikasi KampusLMS dapat diakses dan digunakan melalui perangkat mobile.

### 8.1 Identitas Pengujian

| Informasi | Keterangan |
|---|---|
| Tanggal pengujian | [Isi tanggal pengujian] |
| Merek dan tipe HP | [Isi merek dan tipe HP] |
| Browser | [Isi browser yang digunakan] |
| URL pengujian | https://kampuslms-kelompok-a08-production-66912567.up.railway.app |

### 8.2 Skenario dan Hasil Pengujian

| No. | Skenario Pengujian | Hasil |
|---|---|---|
| 1 | Membuka URL produksi melalui HP | [Berhasil/Gagal] |
| 2 | Memastikan halaman utama dapat ditampilkan | [Berhasil/Gagal] |
| 3 | Melakukan login menggunakan akun demo | [Berhasil/Gagal] |
| 4 | Membuka menu dan fitur aplikasi | [Berhasil/Gagal] |
| 5 | Memastikan tampilan aplikasi dapat digunakan melalui HP | [Berhasil/Gagal] |

### 8.3 Dokumentasi Pengujian

Tambahkan tangkapan layar sebagai bukti bahwa aplikasi KampusLMS berhasil dibuka dan diuji melalui perangkat HP.

**Bukti pengujian:**

[Tambahkan tangkapan layar pengujian melalui HP di bagian ini.]

### 8.4 Kesimpulan Pengujian Mobile

[Isi kesimpulan berdasarkan hasil pengujian langsung melalui perangkat HP.]

## 9. Kesimpulan Akhir

Berdasarkan hasil deployment dan pengujian yang telah dilakukan oleh anggota kelompok, aplikasi KampusLMS berhasil di-deploy pada platform Railway dan dapat diakses secara online menggunakan HTTPS.

Hasil pemeriksaan dan pengujian menunjukkan bahwa:

1. Aplikasi berhasil berjalan pada server Railway dengan lingkungan Docker, PHP 8.4, Apache, dan MySQL Managed Database.
2. Apache dan Laravel Queue Worker berhasil berjalan secara bersamaan melalui Supervisor.
3. Pemeriksaan terhadap tiga path berkas sensitif menghasilkan HTTP 404 Not Found.
4. Pengujian otorisasi API berhasil pada lingkungan lokal, tetapi belum berhasil diverifikasi pada server produksi karena proses login akun pengujian gagal.
5. Pengujian akun demo Admin, Dosen, dan Mahasiswa pada server live berhasil dilakukan.
6. Tidak terdapat kendala teknis khusus yang dilaporkan Putri selama proses deployment.
7. Pengujian melalui perangkat HP perlu dilengkapi dengan hasil aktual dan bukti tangkapan layar.

Secara keseluruhan, aplikasi KampusLMS telah berhasil dipublikasikan dan fitur-fitur yang diuji dapat digunakan pada server live Railway. Meskipun demikian, pengujian otorisasi produksi, pemeriksaan endpoint submission, dan dokumentasi pengujian mobile masih perlu ditindaklanjuti.

## 10. Pembagian Kontribusi

| Anggota | Kontribusi |
|---|---|
| Putri | Melakukan deployment aplikasi, menyiapkan spesifikasi server, skrip deployment, dan konfigurasi Supervisor. |
| Sarah | Melakukan pemeriksaan keamanan produksi dan pengujian otorisasi API. |
| Siti | Melakukan pengujian akun demo Admin, Dosen, dan Mahasiswa pada server live. |
| Syarifah | Memperbarui README, melakukan pengujian melalui perangkat HP, serta menyusun dokumentasi akhir deployment. |
