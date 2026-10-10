# KampusLMS — Kelompok A08

Platform *Learning Management System* (LMS) berbasis web yang dikembangkan menggunakan framework Laravel 12. KampusLMS dirancang untuk memfasilitasi proses pembelajaran digital di lingkungan perguruan tinggi dengan mendukung alur pengelolaan mata kuliah, distribusi materi perkuliahan, penugasan, pengumpulan tugas (*submissions*), penilaian, dan sistem notifikasi akademik bagi tiga peran utama: **Admin**, **Dosen**, dan **Mahasiswa**.

---

## 🛠️ Teknologi yang Digunakan

- **Framework Backend:** Laravel 12 (`laravel/framework: ^12.0`)
- **Bahasa Pemrograman:** PHP `^8.3`
- **Database:** MySQL / MariaDB (mendukung SQLite untuk testing)
- **Autentikasi & API Token:** Laravel Sanctum (`^4.3`)
- **Frontend & Templating:** Blade Templating Engine, Vanilla CSS, JavaScript
- **Asset Bundler:** Vite
- **Package Manager:** Composer & NPM

---

## 👥 Tim Pengembang & Pembagian Tugas (Kelompok A-08)

Proyek ini dikembangkan oleh **Kelompok A-08** (Kelas Pemrograman Web):

| No. | Nama Anggota | NIM | Peran / Anggota | Cakupan Tugas (Minggu 1–2 & Berjalan) |
| :---: | :--- | :---: | :---: | :--- |
| 1 | **Putri Nurwahid** | 10241063 | Anggota 1 | Konfigurasi Environment (`composer.json`), Dokumentasi Proyek (`README.md`), dan Kerangka Navigasi Layout (`resources/views/components/layout.blade.php`). |
| 2 | **Sarah Adelia W** | 10241065 | Anggota 2 | Perancangan Controller & Routing Mata Kuliah (`CourseController`), Pengelolaan Halaman Mata Kuliah, dan Pengujian Route. |
| 3 | **Siti Fatimah** | 10241067 | Anggota 3 | Pengembangan Antarmuka Frontend, Desain Tampilan Blade (`resources/views/tentang.blade.php`), dan Penataan Aset Vite/CSS. |
| 4 | **Syarifah Nazwa Aulia H** | 10241069 | Anggota 4 | Struktur Database & Migrasi, Definisi Model Eloquent & Relasi Data, serta Data Seeder Awal. |

> *Catatan: Rincian pembagian tugas anggota 2–4 dapat disesuaikan lebih lanjut oleh masing-masing anggota kelompok.*

---

## 📋 Persyaratan Instalasi (Prerequisites)

Sebelum menjalankan aplikasi, pastikan perangkat Anda telah terpasang:

- **PHP:** Versi 8.3 atau lebih baru (dengan ekstensi yang diwajibkan Laravel: `openssl`, `pdo`, `mbstring`, `tokenizer`, `xml`, `ctype`, `json`, `bcmath`, `curl`)
- **Composer:** Versi 2.x
- **Node.js & NPM:** Node.js versi LTS (v18+) dan NPM
- **Database Server:** MySQL / MariaDB (versi 8.0+) atau SQLite
- **Git:** Untuk manajemen repositori

---

## 🚀 Langkah Instalasi Proyek

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
👉 **[http://127.0.0.1:8000](http://127.0.0.1:8000)**

---

## 🔑 Akun Demo Aplikasi

Database seeder (`DemoAccountSeeder.php` & `DatabaseSeeder.php`) telah menyediakan akun demo bawaan untuk setiap peran pengguna. Semua akun demo menggunakan kata sandi (*password*) yang sama:

> **Password Akun Demo:** `password`

| Peran (*Role*) | Nama Pengguna | Alamat Email | Password | Identitas (NIM/NIP) |
| :--- | :--- | :--- | :--- | :--- |
| **Admin** | Administrator KampusLMS | `admin@kampuslms.test` | `password` | ADM001 |
| **Dosen** | Dosen Demo | `dosen@kampuslms.test` | `password` | NIP001 |
| **Mahasiswa** | Mahasiswa Demo | `mahasiswa@kampuslms.test` | `password` | MHS0001 |

*Akun demo tambahan yang tersedia di seeder:*
- **Dosen Kedua:** `dosen2@kampuslms.test` | Password: `password` | NIP: `NIP002`
- **Mahasiswa Kedua:** `mahasiswa2@kampuslms.test` | Password: `password` | NIM: `MHS0002`

---

## 🌐 Tautan Proyek

- **URL Repositori GitHub:** [https://github.com/putriwhd/kampuslms-kelompok-A08.git](https://github.com/putriwhd/kampuslms-kelompok-A08.git)
- **URL Aplikasi Live:** `[Belum Tersedia — Aplikasi masih dalam tahap pengembangan lokal]`
