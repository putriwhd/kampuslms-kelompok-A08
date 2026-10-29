# KampusLMS — Kelompok A08

Platform *Learning Management System* (LMS) berbasis web yang dikembangkan menggunakan framework Laravel 12. KampusLMS dirancang untuk memfasilitasi proses pembelajaran digital di lingkungan perguruan tinggi dengan mendukung alur pengelolaan mata kuliah, distribusi materi perkuliahan, penugasan, pengumpulan tugas (*submissions*), penilaian, dan sistem notifikasi akademik bagi tiga peran utama: **Admin**, **Dosen**, dan **Mahasiswa**.

---

---

## Tim Pengembang & Pembagian Tugas (Kelompok A-08)

Proyek ini dikembangkan oleh **Kelompok A-08** (Kelas Pemrograman Web):

| No. | Nama Anggota | NIM | Peran / Anggota | Tugas |
| :---: | :--- | :---: | :---: | :--- |
| 1 | **Putri Nurwahid** | 10241063 | Anggota 1 | Backend |
| 2 | **Sarah Adelia W** | 10241065 | Anggota 2 | Backend |
| 3 | **Siti Fatimah** | 10241067 | Anggota 3 | Frontend |
| 4 | **Syarifah Nazwa Aulia H** | 10241069 | Anggota 4 | Frontend |


---

## Persyaratan Instalasi (Prerequisites)

Sebelum menjalankan aplikasi, pastikan perangkat Anda telah terpasang:

- **PHP:** Versi 8.3 atau lebih baru (dengan ekstensi yang diwajibkan Laravel: `openssl`, `pdo`, `mbstring`, `tokenizer`, `xml`, `ctype`, `json`, `bcmath`, `curl`)
- **Composer:** Versi 2.x
- **Node.js & NPM:** Node.js versi LTS (v18+) dan NPM
- **Database Server:** MySQL / MariaDB (versi 8.0+) atau SQLite
- **Git:** Untuk manajemen repositori

---

## Langkah Instalasi Proyek

Ikuti langkah-langkah instalasi berikut secara berurutan:

### 1. Klon Repositori
```bash
git clone https://github.com/putriwhd/kampuslms-kelompok-A08.git
cd kampuslms-kelompok-A08
```

### 2. Instal Dependensi Backend (PHP)
```bash
composer install
```

### 3. Instal Dependensi Frontend & Kompilasi Aset
```bash
npm install
npm run build
```
*(Untuk keperluan pengembangan aktif dengan auto-reload aset, jalankan `npm run dev` pada terminal terpisah).*

### 4. Konfigurasi Environment (`.env`)
Salin file `.env.example` menjadi `.env`:
- **Linux / macOS:**
  ```bash
  cp .env.example .env
  ```
- **Windows (PowerShell / Command Prompt):**
  ```bash
  copy .env.example .env
  ```

Buka file `.env` dan sesuaikan pengaturan koneksi database Anda, misalnya:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=kampuslms
DB_USERNAME=root
DB_PASSWORD=
```
*(Pastikan database `kampuslms` sudah dibuat pada server MySQL Anda, atau sesuaikan dengan nama database lokal Anda).*

### 5. Buat Application Key
```bash
php artisan key:generate
```

### 6. Jalankan Migrasi Database dan Seeder
Eksekusi migrasi tabel dan pengisian data demo:
```bash
php artisan migrate --seed
```
*(Gunakan `php artisan migrate:fresh --seed` jika ingin mereset ulang seluruh tabel dari awal).*

### 7. Jalankan Server Pengembangan
Jalankan server aplikasi Laravel:
```bash
php artisan serve
```

Aplikasi sekarang dapat diakses melalui browser pada alamat:
 **[http://127.0.0.1:8000](http://127.0.0.1:8000)**

---

## Menjalankan Queue Worker (Wajib untuk Notifikasi)

KampusLMS menggunakan **antrean database** (`QUEUE_CONNECTION=database`) untuk memproses notifikasi secara asinkron. Tanpa worker yang aktif, notifikasi **tidak akan pernah terkirim** — dan tidak ada pesan error yang muncul (silent failure).

### Menyalakan Worker

Buka terminal **terpisah** (jangan ditutup) dan jalankan:

```bash
php artisan queue:work
```

> **Penting:** Worker harus tetap berjalan selama aplikasi digunakan. Jika terminal ditutup atau worker mati, job hanya menumpuk di tabel `jobs` dan tidak ada notifikasi yang dikirim. Ini bukan bug — ini memang cara kerja queue.

### Memeriksa dan Mengelola Job Gagal (Failed Jobs)

Job yang gagal setelah seluruh percobaan (3 kali) otomatis tercatat di tabel `failed_jobs`.

**Melihat daftar job yang gagal:**
```bash
php artisan queue:failed
```

**Menjalankan ulang seluruh job yang gagal:**
```bash
php artisan queue:retry all
```

**Menghapus seluruh job yang gagal:**
```bash
php artisan queue:flush
```

### Catatan Migrasi

Tabel `jobs`, `job_batches`, `failed_jobs`, dan `notifications` sudah tersedia di folder `database/migrations/`. Cukup jalankan:

```bash
php artisan migrate
```

>**Jangan** jalankan `php artisan make:queue-table`, `php artisan make:queue-failed-table`, atau `php artisan make:notifications-table`. Perintah-perintah tersebut akan membuat migrasi duplikat dan menyebabkan error saat `migrate`.

---

## 🌐 Tautan Proyek

- **URL Repositori GitHub:** [https://github.com/putriwhd/kampuslms-kelompok-A08.git](https://github.com/putriwhd/kampuslms-kelompok-A08.git)
- **URL Aplikasi Live:** `[Belum Tersedia — Aplikasi masih dalam tahap pengembangan lokal]`
