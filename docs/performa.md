# Dokumentasi Performa Kampus LMS

Dokumentasi ini mencatat hasil implementasi optimasi query, pagination, cache, indeks database, serta cara mengukur ulang performa secara reproducible.

## 1. Tabel Pengukuran Sebelum dan Sesudah

Angka pada tabel berikut dibaca langsung dari pasangan screenshot berakhiran `1` untuk sebelum optimasi dan `2` untuk sesudah optimasi. Gambar `1` dan `2` pada masing-masing pasangan harus diuji pada halaman yang sama dan data yang sama agar perbandingan valid.

| Halaman | Sebelum: query/waktu | Sesudah: query/waktu | Perubahan | Analisis |
|---|---:|---:|---:|---|
| Daftar mata kuliah | 9 query / 125 ms | 8 query / 95,13 ms | -1 query; -29,87 ms (-23,90%) | Eager loading dosen dan pagination membatasi loading data; satu query berkurang pada screenshot. |
| Detail materi | 8 query / 116 ms | 8 query / 122 ms | 0 query; +6 ms (+5,17%) | Jumlah query tidak berubah pada pengukuran tunggal; materi/course sudah dimuat melalui route/model yang tepat, sedangkan cache/browser dapat memengaruhi waktu. |
| Detail mata kuliah | 10 query / 136 ms | 11 query / 195 ms | +1 query; +59 ms (+43,38%) | Screenshot sesudah menampilkan implementasi pagination/detail yang lebih lengkap. Satu pengukuran tunggal ini belum membuktikan peningkatan waktu; perlu minimal lima pengukuran rata-rata pada dataset identik. |
| Rekap nilai dosen | 51 query / 411 ms | 10 query / 176 ms | -41 query; -235 ms (-57,18%) | Eager loading bertingkat menggantikan pola N+1 dan membatasi hasil dengan pagination 10. Halaman memenuhi target kurang dari 15 query. |
| Rekap pengumpulan tugas dosen | 21 query / 192 ms | 18 query / 162 ms | -3 query; -30 ms (-15,63%) | Submission dan grade pada mahasiswa dimuat lebih terarah, serta daftar siswa menggunakan pagination 15. Query relasi berkurang tiga pada screenshot. |

### Cakupan Pengukuran Halaman Utama

Tabel di atas memuat seluruh pasangan screenshot yang saat ini memiliki angka. Matriks berikut mencatat halaman utama lain agar cakupannya terlihat; tanda "Belum diukur" berarti belum ada angka sebelum/sesudah yang dapat dibandingkan pada dataset dan kondisi yang sama. Jangan mengganti nilai tersebut dengan estimasi.

| Halaman utama | Sebelum: query/waktu | Sesudah: query/waktu | Bukti/status |
|---|---:|---:|---|
| Dashboard admin | Belum diukur | Belum diukur | Perlu benchmark terpisah untuk role admin. |
| Dashboard dosen | Belum diukur | Belum diukur | Perlu benchmark terpisah untuk role dosen. |
| Dashboard mahasiswa | Belum diukur | Belum diukur | Perlu benchmark terpisah untuk role mahasiswa. |
| Daftar mata kuliah | 9 query / 125 ms | 8 query / 95,13 ms | Angka screenshot tersedia; role dan ukuran dataset perlu dicatat saat pengukuran ulang. |
| Detail mata kuliah | 10 query / 136 ms | 11 query / 195 ms | Angka screenshot tersedia; hasil tunggal sesudah lebih tinggi dan perlu diulang. |
| Form tambah/edit mata kuliah | Belum diukur | Belum diukur | Termasuk alur daftar pilihan dosen. |
| Pengelolaan pendaftaran mahasiswa pada detail mata kuliah | Belum diukur | Belum diukur | Termasuk pencarian dan pagination mahasiswa. |
| Daftar materi | Belum diukur | Belum diukur | Belum ada pasangan hasil pada tabel bukti saat ini. |
| Detail materi | 8 query / 116 ms | 8 query / 122 ms | Angka screenshot tersedia. |
| Daftar tugas | Belum diukur | Belum diukur | Belum ada pasangan hasil pada tabel bukti saat ini. |
| Detail tugas | Belum diukur | Belum diukur | Pisahkan pengukuran halaman detail dari daftar pengumpulan. |
| Rekap pengumpulan tugas dosen | 21 query / 192 ms | 18 query / 162 ms | Angka screenshot tersedia. |
| Rekap nilai dosen (halaman) | 51 query / 411 ms | 10 query / 176 ms | Angka screenshot tersedia; test query budget juga memverifikasi `< 15`. |
| Rekap nilai mahasiswa (halaman) | Belum diukur | Belum diukur | Query budget teruji `< 15`, tetapi pasangan waktu/query sebelum-sesudah belum dicatat. |
| API rekap nilai dosen | Belum diukur | Belum diukur | Query budget teruji `< 15`; belum ada baseline screenshot/benchmark sebelum-sesudah. |
| API rekap nilai mahasiswa | Belum diukur | Belum diukur | Query budget teruji `< 15`; belum ada baseline screenshot/benchmark sebelum-sesudah. |
| Daftar pengguna admin | Belum diukur | Belum diukur | Belum ada pasangan hasil pada tabel bukti saat ini. |
| Katalog/enrollment mata kuliah | Belum diukur | Belum diukur | Belum ada pasangan hasil pada tabel bukti saat ini. |
| Daftar notifikasi | Belum diukur | Belum diukur | Belum ada pasangan hasil pada tabel bukti saat ini. |

Untuk melengkapi angka yang belum tersedia, ukur halaman sebelum dan sesudah pada database dengan isi identik, catat role, jumlah baris relevan, jumlah query, serta waktu respons. Gunakan sedikitnya lima pengukuran untuk waktu respons dan laporkan rata-ratanya; jangan membandingkan request pertama yang mengisi cache dengan request yang memakai cache.

## Bukti Screenshot dari Folder `Hasil Optimasi`

Aturan pembacaan bukti:

- File berakhiran `1` = **sebelum optimasi**.
- File berakhiran `2` = **sesudah optimasi**.
- Angka utama mengikuti panel Debugbar pada screenshot: `Queries` dan waktu respons pada panel kanan bawah.
- Daftar `Requests`, `Views`, dan `Models` membantu konteks, tetapi perbandingan utama yang digunakan adalah query dan waktu pada halaman serta dataset yang sama.

### Daftar mata kuliah

| Bukti | Query | Waktu | Analisis |
|---|---:|---:|---|
| `DaftarMatkul1.png` — sebelum | 9 | 125 ms | Hasil sebelum telah memuat relasi dosen dan data daftar tanpa optimasi target pada pasangan ini. |
| `DaftarMatku2.png` — sesudah | 8 | 95,13 ms | Penggunaan eager loading dosen dan pagination mengurangi satu query. Waktu turun 29,87 ms atau sekitar 23,90%. |

#### Sebelum optimasi — `DaftarMatkul1.png`

![Daftar mata kuliah sebelum optimasi](Hasil%20Optimasi/DaftarMatkul1.png)

#### Sesudah optimasi — `DaftarMatku2.png`

![Daftar mata kuliah sesudah optimasi](Hasil%20Optimasi/DaftarMatku2.png)

### Detail materi

| Bukti | Query | Waktu | Analisis |
|---|---:|---:|---|
| `DetailMateri1.png` — sebelum | 8 | 116 ms | Detail materi belum dipisahkan pada pengukuran visual dengan pasangan sesudah. |
| `DetailMateri2.png` — sesudah | 8 | 122 ms | Jumlah query tetap sama. Pengukuran tunggal ini lebih lambat 6 ms; hasil satu kali belum cukup untuk menyatakan peningkatan waktu, tetapi struktur route/pagination dan loading relasi sudah dipertahankan. |

#### Sebelum optimasi — `DetailMateri1.png`

![Detail materi sebelum optimasi](Hasil%20Optimasi/DetailMateri1.png)

#### Sesudah optimasi — `DetailMateri2.png`

![Detail materi sesudah optimasi](Hasil%20Optimasi/DetailMateri2.png)

### Detail mata kuliah

| Bukti | Query | Waktu | Analisis |
|---|---:|---:|---|
| `DetailMatkul1.png` — sebelum | 10 | 136 ms | Detail course pada screenshot awal memuat 10 query. |
| `DetailMatkul2.png` — sesudah | 11 | 195 ms | Pengukuran tunggal sesudah bertambah satu query dan 59 ms. Oleh karena itu jumlah query pada satu screenshot ini tidak menunjukkan perbaikan, dan hasilnya perlu diulang 5 kali pada dataset identik sebelum ditulis sebagai rata-rata. |

#### Sebelum optimasi — `DetailMatkul1.png`

![Detail mata kuliah sebelum optimasi](Hasil%20Optimasi/DetailMatkul1.png)

#### Sesudah optimasi — `DetailMatkul2.png`

![Detail mata kuliah sesudah optimasi](Hasil%20Optimasi/DetailMatkul2.png)

### Rekap nilai dosen

| Bukti | Query | Waktu | Analisis |
|---|---:|---:|---|
| `RecapNilai1.png` — sebelum | 51 | 411 ms | Pemrosesan tabel rekap memiliki pola N+1 dan melampaui target kurang dari 15 query. |
| `RecapNilai2.png` — sesudah | 10 | 176 ms | Eager loading bertingkat dan pagination 10 mengurangi 41 query atau sekitar 80,39%. Waktu turun 235 ms atau sekitar 57,18%. |

#### Sebelum optimasi — `RecapNilai1.png`

![Rekap nilai dosen sebelum optimasi](Hasil%20Optimasi/RecapNilai1.png)

#### Sesudah optimasi — `RecapNilai2.png`

![Rekap nilai dosen sesudah optimasi](Hasil%20Optimasi/RecapNilai2.png)

### Rekap pengumpulan tugas dosen

| Bukti | Query | Waktu | Analisis |
|---|---:|---:|---|
| `RecapPengumpulanTugas1.png` — sebelum | 21 | 192 ms | Daftar pengumpulan dan relasi submission masih menimbulkan jumlah query lebih besar. |
| `RecapPengumpulanTugas2.png` — sesudah | 18 | 162 ms | Daftar mahasiswa dipaginasi 15 dan submission/grade dimuat lebih terarah, sehingga query turun tiga dan waktu turun 30 ms atau sekitar 15,63%. |

#### Sebelum optimasi — `RecapPengumpulanTugas1.png`

![Rekap pengumpulan tugas dosen sebelum optimasi](Hasil%20Optimasi/RecapPengumpulanTugas1.png)

#### Sesudah optimasi — `RecapPengumpulanTugas2.png`

![Rekap pengumpulan tugas dosen sesudah optimasi](Hasil%20Optimasi/RecapPengumpulanTugas2.png)


### Hasil Aktual yang Sudah Terverifikasi

Jalankan:

```powershell
php artisan test --filter=QueryBudgetTest
```

Hasil saat dokumentasi ini dibuat:

- Rekap nilai dosen halaman Blade: **lulus**, jumlah query `< 15`.
- Rekap nilai mahasiswa halaman Blade: **lulus**, jumlah query `< 15`.
- Rekap nilai dosen API: **lulus**, jumlah query `< 15` dan `paginate(10)` dengan 10 data pada halaman pertama.
- Rekap nilai mahasiswa API: **lulus**, jumlah query `< 15` dan `paginate(10)` dengan 10 data pada halaman pertama.
- Seluruh pengujian QueryBudget: **4 pengujian lulus dan 16 assertion lulus** saat dijalankan pada repository ini.

Angka sebelum optimasi tidak dapat diganti dengan hasil palsu karena belum tersedia baseline commit/dataset lama yang tersimpan. Nilai sebelum dapat ditambahkan melalui prosedur benchmark pada bagian tutorial dengan screenshot Debugbar dan dataset yang sama; sel selama demonstrasi harus mengunci jumlah course, student, assignment, submission, dan grade.

Pengujian ada pada `tests/Feature/QueryBudgetTest.php`.

## 2. Halaman Terburuk Sebelum Optimisasi

Halaman terburuk diperkirakan adalah **Rekap Nilai Dosen**. Struktur datanya paling dalam:

```text
mata kuliah
  -> mahasiswa
  -> tugas
  -> submission
  -> grade
```

Tanpa `with()` atau dengan pengambilan koleksi menggunakan `get()`, setiap jumlah mahasiswa/tugas/submission dapat menambah satu atau lebih query. Kompleksitas mendekati pola:

```text
1 query awal + N query mahasiswa/tugas + M query submission + K query grade
```

Jumlahnya tumbuh seiring volume dan menyebabkan halaman melampaui batas 15 query. Halaman ini ditangani menggunakan eager loading serta pagination 10 di `GradeRecapPageController.php` dan API terkait.

## 3. Tutorial N+1 dan Debugbar

### A. Pasang Debugbar

Pastikan dependency development sudah terpasang:

```powershell
composer require barryvdh/laravel-debugbar --dev
php artisan vendor:publish --provider="Barryvdh\Debugbar\ServiceProvider"
```

Pada `.env`:

```dotenv
APP_DEBUG=true
DEBUGBAR_ENABLED=true
```

Kemudian jalankan:

```powershell
php artisan optimize:clear
php artisan serve
```

### B. Peragakan lonjakan N+1

Gunakan dataset yang memiliki sedikitnya 3 mata kuliah, 10 mahasiswa, dan beberapa tugas/submission.

1. Login sebagai dosen.
2. Buka `/dosen/grade-recap/page` atau halaman rekap nilai dosen pada aplikasi Anda.
3. Catat jumlah query pada panel Laravel Debugbar di browser.
4. Buat perubahan sementara pada `GradeRecapPageController.php`: komentar bagian `with([...])`, lalu buka kembali halaman yang sama.
5. Bandingkan jumlah query dan durasi sebelum/sesudah pada dataset yang sama.
6. Kembalikan eager loading dan refresh ulang dengan cache serta query log bersih.

Contoh pola N+1 yang tidak boleh ada pada loop:

```php
foreach ($courses as $course) {
    $course->students;      // Lazy loading
    $course->assignments;   // Lazy loading lagi
}
```

Polanya seharusnya:

```php
$courses = Course::query()
    ->with([
        'students',
        'assignments.submissions.grade',
    ])
    ->paginate(10);
```

Setiap query baru pada Debugbar perlu diberi tanda waktu. Jangan membandingkan dataset kosong atau hasil halaman berbeda.

## 4. Eager Loading, `preventLazyLoading`, dan `withCount`

### Penggunaan `with()`

`with()` memuat relasi dalam query terpisah yang dibatasi, bukan satu query untuk setiap parent.

Contoh di rekap dosen:

```php
$query = Course::query()->with([
    'students',
    'assignments.submissions.student',
    'assignments.submissions.grade',
]);
```

### N+1 Guard

`AppServiceProvider.php` mengaktifkan:

```php
Model::preventLazyLoading(! $this->app->isProduction());
```

Pada environment non-produksi, lazy loading yang tidak disengaja akan memunculkan exception daripada menyembunyikan pemborosan query.

### `withCount('students')` vs `count($course->students)`

`withCount('students')` menghasilkan kolom agregat `students_count` dalam query yang sama:

```php
$course = Course::query()
    ->withCount('students')
    ->findOrFail($courseId);

echo $course->students_count;
```

`count($course->students)` terlebih dahulu membutuhkan koleksi `$course->students`. Jika relasi belum di-load, setiap course dapat memicu query lazy loading, lalu PHP menghitung seluruh objek Student yang sebenarnya mungkin hanya diperlukan jumlahnya:

```php
count($course->students); // Boros: memuat objek tambahan lalu menghitung di PHP
```

Gunakan `withCount()` apabila hanya jumlah yang diperlukan.

## 5. Index Database dan Pembuktian `EXPLAIN`

Migrasi indeks ada pada:

- `database/migrations/2026_11_02_000000_add_performance_indexes_table.php`

Indeks mencakup kombinasi yang mendukung:

- filter dosen/status/tanggal pada `courses`;
- pencarian/pengurutan materi;
- pencarian tugas berdasarkan course dan status;
- submission berdasarkan assignment/user;
- grade berdasarkan submission/user;
- enrollment pada tabel `course_user`.

### Jalankan EXPLAIN

Masuk ke MySQL:

```powershell
mysql -u root -p kampuslms
```

Contoh:

```sql
EXPLAIN SELECT *
FROM courses
WHERE lecturer_id = 1
  AND status = 'active'
ORDER BY created_at DESC;
```

Hasil yang sudah diverifikasi pada database proyek:

| Field | Nilai |
|---|---|
| type | `ref` |
| key | `courses_lecturer_status_created_at_index` |
| rows | `1` |
| Extra | `Backward index scan` |

Artinya MySQL memilih indeks gabungan untuk pencarian berdasarkan dosen/status dan pemanfaatan urutan created_at.

Jika menggunakan database lain:

```sql
ANALYZE;
EXPLAIN QUERY PLAN SELECT ...;
```

Catat `key/possible_keys`, `rows`, dan waktu eksekusi bersama hasil screenshot terminal.

## 6. Cache Dashboard

Implementasi cache ada pada `DashboardController.php`.

- Cache dibungkus dengan `Cache::remember`.
- Masa berlaku contoh: 10 menit.
- Kunci dibuat berdasarkan role dan scope pengguna agar dosen/mahasiswa tidak menerima dashboard milik user lain.
- Version counter menjaga invalidasi tetap terisolasi dan dapat diperbarui.

Invalidasi otomatis didaftarkan pada event `saved()` dan `deleted()` model:

- `User`
- `Course`
- `EnrollmentRequest`
- `Material`
- `Assignment`
- `Submission`
- `Grade`

Perubahan salah satu model menaikkan versi cache dashboard sehingga kunci lama tidak dipakai pada request berikutnya.

Persetujuan enrollment juga memanggil `DashboardController::invalidateCache()` secara eksplisit karena perubahan `course_user` pada tabel pivot tidak selalu memunculkan event model `EnrollmentRequest`. Lokasi implementasi:

- daftar dan cache dashboard: `app/Http/Controllers/DashboardController.php`;
- invalidasi otomatis model: `app/Providers/AppServiceProvider.php`;
- invalidasi saat persetujuan enrollment: `app/Http/Controllers/EnrollmentController.php`;
- tautan pagination dashboard: `resources/views/dashboard.blade.php`.

### Data yang tidak boleh di-cache

Data berikut sengaja tidak disimpan dalam cache jangka panjang:

1. **Nilai dan feedback mahasiswa** — dosen dapat mengubah nilai kapan saja.
2. **Submission terbaru** — status tepat waktu/terlambat dapat berubah ketika berkas baru masuk.
3. **Permintaan enrollment pending** — status disetujui atau ditolak harus langsung terlihat.
4. **Notifikasi belum dibaca** — harus sinkron dengan queue dan tindakan user.
5. **Rekap nilai dosen dan mahasiswa** — data dinamis dan sangat sensitif terhadap perubahan assignment/submission/grade.

Data-data ini tetap dioptimalkan menggunakan eager loading, pagination, dan batas query, tetapi tidak disimpan sebagai snapshot lama.

## 7. Pagination Wajib

Daftar yang sudah dipaginasi meliputi:

- mata kuliah;
- tugas;
- materi;
- rekap nilai;
- pengguna;
- katalog mata kuliah;
- detail mahasiswa/pending enrollment pada halaman mata kuliah;
- daftar mata kuliah pada dashboard dosen dan mahasiswa, masing-masing `paginate(15)`;
- rekap pengumpulan tugas dosen.

Rekap pengumpulan menggunakan paginator 15 mahasiswa pada:

```text
AssignmentController::showAssignment
AssignmentController::submissionsRecap
```

Tampilan Blade menambahkan:

```blade
{{ $submissionRecap->links() }}
```

Paginator menggunakan query string sehingga filter/pencarian dapat dipertahankan ketika berpindah halaman.

## 8. Aturan `env()` dan `config:cache`

Tidak boleh memakai `env()` di controller, route, model, view, atau database migration. Panggilan environment hanya boleh berada pada berkas dalam folder `config/`.

Benar:

```php
// config/database.php
env('DB_CONNECTION', 'sqlite')

// controller atau model
config('database.default')
```

Salah:

```php
$connection = env('DB_CONNECTION'); // Jangan dilakukan di luar config
```

`php artisan config:cache` menggabungkan seluruh berkas konfigurasi menjadi cache PHP. Setelah konfigurasi dicache, Laravel tidak membaca `.env` langsung untuk setiap request. Pemanggilan `env()` di luar folder `config/` dapat mengembalikan nilai lama, `null`, atau nilai yang tidak konsisten.

### Tutorial verifikasi config cache

```powershell
php artisan optimize:clear
php artisan config:cache
php artisan tinker
```

Dalam `tinker`:

```php
config('database.default')
```

Untuk membersihkan kembali:

```powershell
php artisan optimize:clear
php artisan serve
```

Ubah `.env`, lalu jalankan `php artisan optimize:clear` sebelum menguji ulang. Jalankan ulang `config:cache` saat deploy production setelah perubahan environment selesai.

## 9. Tutorial Benchmark Lengkap di Lokal

1. Jalankan aplikasi pada dataset development yang stabil.
2. Aktikan Debugbar dan `APP_DEBUG=true`.
3. Catat jumlah mahasiswa, tugas, submission, dan grade dalam spreadsheet.
4. Gunakan URL yang sama untuk setiap pengukuran sebelum dan sesudah optimasi.
5. Catat minimal 5 kali setiap halaman, lalu ambil rata-rata waktu dan query.
6. Catat memory usage dari Debugbar/PHPUnit.
7. Jalankan query-budget otomatis:

```powershell
php artisan test --filter=QueryBudgetTest
```

8. Jalankan seluruh pengujian terkait fitur mingguan:

```powershell
php artisan test tests/Feature/WeekSevenDeliveryTest.php
php artisan test tests/Feature/ApiEndpointsTest.php
```

9. Jalankan pemeriksaan env:

```powershell
rg -n "env\\(" app routes resources database
```

10. Ulangi hasil `EXPLAIN`, screenshot terminal, dan masukkan angka aktual ke tabel ini.

Jangan mengisi angka hasil bukan pengukuran yang sama dengan angka hipotetis atau hasil dari dataset berbeda.

## 10. Verifikasi Automated Test Suite & Scaling Proof

Untuk memastikan optimasi N+1 dan pencegahan Lazy Loading terbukti secara empiris di seluruh aplikasi (bukan sekadar asumsi manual), telah ditambahkan dan dijalankan automated feature test suite berikut:

### A. Pembuktian Lazy Loading Guard (`LazyLoadingGuardCoverageTest.php`)
- **Tujuan:** Memastikan seluruh route utama aplikasi bebas dari *Lazy Loading Violation Exception* saat dipanggil oleh pengguna dengan peran yang sesuai.
- **Mekanisme:** 
  1. Mengaktifkan `Model::preventLazyLoading(true)` pada metode `setUp()`.
  2. Menyiapkan fixture data relasi komprehensif (User, Course, Material, Assignment, Submission, Notification).
  3. Menguji respons HTTP GET pada route utama untuk Admin, Dosen, dan Mahasiswa.
- **Hasil Pengujian:**
  ```powershell
  php artisan test --filter=LazyLoadingGuardCoverageTest
  # PASS: 1 passed (29 assertions)
