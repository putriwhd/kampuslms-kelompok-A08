# Dokumentasi Performa Kampus LMS

## 1. Tabel Pengukuran Performa

Tabel berikut membandingkan hasil pengukuran sebelum dan sesudah optimasi. Target anggaran adalah **kurang dari 15 query** untuk setiap halaman atau endpoint. Data waktu dan query diperoleh dari hasil Laravel Debugbar.

| Halaman / Endpoint | Sebelum: Query | Sesudah: Query | Sebelum: Waktu | Sesudah: Waktu | Konsumsi Memori | Status Sesudah |
|---|---:|---:|---:|---:|---|---|
| Dashboard mahasiswa | 7 | 7 | 9,9 ms | 112 ms | Tidak tersedia | Memenuhi |
| Rekap nilai mahasiswa | 9 | 8 | 222 ms | 12,01 ms | Tidak tersedia | Memenuhi |
| Dashboard dosen | 10 | 13 | 18,03 ms | 28,59 ms | Tidak tersedia | Memenuhi |
| Daftar mata kuliah | 10 | 8 | 19,63 ms | 27,07 ms | Tidak tersedia | Memenuhi |
| Detail mata kuliah | 10 | 10 | 20,54 ms | 113 ms | Tidak tersedia | Memenuhi |
| Detail materi | 10 | 8 | 39,08 ms | 23,89 ms | Tidak tersedia | Memenuhi |
| Detail tugas | 25 | 8 | 41,85 ms | 7,88 ms | Tidak tersedia | Memenuhi |
| Detail pengumpulan tugas | 22 | 11 | 27,19 ms | 15,04 ms | Tidak tersedia | Memenuhi |
| Rekap nilai dosen | 11 | 10 | 39,35 ms | 68,51 ms | Tidak tersedia | Memenuhi |

### Hasil Analisis Sebelum

Hasil Debugbar sebelum optimasi menunjukkan bahwa:

- Rekap nilai dosen sudah menggunakan jumlah query 11, sedangkan rekap nilai mahasiswa menggunakan 9 query.
- Dashboard mahasiswa dan dosen, daftar mata kuliah, detail mata kuliah, serta detail materi masih berada di bawah batas 15 query.
- Detail tugas menggunakan 25 query dan detail pengumpulan tugas menggunakan 22 query.
- Detail tugas memiliki banyak query policy dan loading course yang berulang untuk setiap item tugas.
- Detail pengumpulan tugas memuat submission, mahasiswa, grade, jumlah data, serta beberapa query policy secara terpisah.
- Kedua endpoint tersebut belum memenuhi target sebelum optimasi, tetapi hasil terbaru sesudah optimasi sudah memenuhi target.

### Hasil Analisis Sesudah

Hasil Debugbar sesudah optimasi menunjukkan bahwa:

- Keseluruhan sembilan pengukuran telah memenuhi batas kurang dari 15 query.
- Rekap nilai mahasiswa turun dari 9 menjadi 8 query dan waktu turun dari 222 ms menjadi 12,01 ms.
- Detail materi turun dari 10 menjadi 8 query dan waktu turun dari 39,08 ms menjadi 23,89 ms.
- Rekap nilai dosen turun dari 11 menjadi 10 query.
- Detail tugas turun dari 25 menjadi 8 query dan waktu turun dari 41,85 ms menjadi 7,88 ms.
- Detail pengumpulan tugas turun dari 22 menjadi 11 query dan waktu turun dari 27,19 ms menjadi 15,04 ms.
- Detail tugas dan detail pengumpulan tugas kini memenuhi target kurang dari 15 query.

Konsumsi memori tidak tercantum pada lampiran Debugbar, sehingga angka memori tidak tersedia dan tidak dibuat estimasi.

## 2. Penjelasan N+1 Guard dan Eager Loading

### N+1 Guard dengan `preventLazyLoading()`

[AppServiceProvider.php](/C:/Proweb/kampuslms-kelompok-A08/app/Providers/AppServiceProvider.php) mengaktifkan guard berikut:

```php
Model::preventLazyLoading(! $this->app->isProduction());
```

`preventLazyLoading()` aktif pada environment non-produksi. Guard ini akan mengidentifikasi akses relasi yang seharusnya sudah dimuat menggunakan eager loading tetapi justru dipanggil secara lazy di dalam proses aplikasi. Dengan cara ini, query tambahan dapat ditemukan saat pengembangan sebelum diterapkan ke production.

Contoh pola N+1 yang dicegah adalah:

```text
1 query mengambil daftar assignment
+ 1 query course untuk assignment pertama
+ 1 query course untuk assignment kedua
+ 1 query course untuk setiap assignment berikutnya
```

Guard ini hanya pengaman saat pengembangan dan tidak otomatis mengurangi query.

### Penerapan Eager Loading `with()`

Eager loading digunakan agar relasi diambil dalam satu atau beberapa query batch, bukan satu query per baris.

Pada [GradeRecapPageController.php](/C:/Proweb/kampuslms-kelompok-A08/app/Http/Controllers/GradeRecapPageController.php), data rekap nilai dosen dimuat dengan:

```php
->with([
    'students',
    'assignments.submissions.student',
    'assignments.submissions.grade',
])
```

Dengan eager loading tersebut, siswa, tugas, submission, dan grade dapat diproses pada koleksi yang sama tanpa melakukan query relasi untuk setiap item.

Rekap nilai mahasiswa juga membatasi dan memuat relasi tugas, submission milik pengguna, serta grade secara bertingkat:

```php
->with([
    'assignments' => fn ($query) => $query
        ->where('status', 'published')
        ->with([
            'submissions' => fn ($submissions) => $submissions
                ->where('user_id', $userId)
                ->with('grade'),
        ]),
])
```

Pada [AssignmentController.php](/C:/Proweb/kampuslms-kelompok-A08/app/Http/Controllers/AssignmentController.php), pengumpulan tugas memuat mahasiswa beserta submission tugas yang ditentukan:

```php
$course->students()->with([
    'submissions' => fn ($query) => $query
        ->where('assignment_id', $assignment->getKey()),
]);
```

Hasil Debugbar terbaru membuktikan detail tugas mencapai 8 query dan detail pengumpulan tugas mencapai 11 query. Eager loading, penghitungan agregat, pembatasan kolom, serta pengurangan query policy mampu menurunkan kedua endpoint di bawah target 15 query.

## 3. Penjelasan Indeks Database

Indeks tambahan didefinisikan pada [2026_11_02_000000_add_performance_indexes_table.php](/C:/Proweb/kampuslms-kelompok-A08/database/migrations/2026_11_02_000000_add_performance_indexes_table.php).

| Tabel | Kolom Indeks | Query Pemicu |
|---|---|---|
| `courses` | `(lecturer_id, status, created_at)` | Dashboard dosen dan daftar mata kuliah berdasarkan dosen, status, serta tanggal dibuat |
| `materials` | `(course_id, created_at)` | Daftar materi berdasarkan mata kuliah dan pengurutan terbaru |
| `assignments` | `(course_id, status, created_at)` | Daftar tugas berdasarkan mata kuliah, status, dan tanggal dibuat |
| `submissions` | `(user_id, submitted_at)` | Pengambilan submission milik mahasiswa dan pengurutan waktu pengumpulan |
| `submissions` | `(assignment_id, is_late, submitted_at)` | Rekap pengumpulan tugas, penghitungan submission terlambat, dan pengurutan pengumpulan |
| `grades` | `(graded_at)` | Pengambilan atau pengurutan nilai berdasarkan waktu dinilai |
| `course_user` | `(user_id)` | Join mahasiswa dengan mata kuliah dan pencarian seluruh enrollment pengguna |

Contoh query pemicu adalah:

```sql
SELECT *
FROM assignments
WHERE course_id = ?
  AND status = 'published'
ORDER BY created_at DESC;
```

Indeks `(course_id, status, created_at)` mempercepat pencarian tugas pada satu mata kuliah, filter status, dan pengurutan.

Contoh lain:

```sql
SELECT COUNT(*)
FROM submissions
WHERE assignment_id = ?
  AND is_late = 1;
```

Indeks `(assignment_id, is_late, submitted_at)` mempercepat pencarian submission berdasarkan tugas dan status keterlambatan.

Indeks mempercepat penyelesaian query database, tetapi tidak mengurangi jumlah query secara otomatis. Pengurangan query tetap harus dilakukan melalui eager loading, pagination, agregasi, dan guard N+1.

## 4. Analisis Strategi Caching

### Data yang Di-cache

[DashboardController.php](/C:/Proweb/kampuslms-kelompok-A08/app/Http/Controllers/DashboardController.php) memakai `Cache::remember()` untuk statistik dashboard:

```php
Cache::remember(
    "dashboard_stats:{$version}:{$scope}",
    now()->addMinutes(10),
    fn () => $stats()
);
```

Data dashboard yang disimpan dalam cache meliputi:

- Jumlah mata kuliah.
- Jumlah materi dan tugas.
- Jumlah submission.
- Jumlah nilai.
- Statistik yang dibedakan berdasarkan admin, dosen, atau mahasiswa.

Key cache dipisahkan berdasarkan versi dan pengguna atau scope, misalnya `dosen:{user_id}` dan `mahasiswa:{user_id}`. TTL cache adalah sepuluh menit.

[AppServiceProvider.php](/C:/Proweb/kampuslms-kelompok-A08/app/Providers/AppServiceProvider.php) meningkatkan versi dashboard ketika model `User`, `Course`, `EnrollmentRequest`, `Material`, `Assignment`, `Submission`, atau `Grade` disimpan atau dihapus. Perubahan versi membuat key cache lama tidak digunakan lagi, sehingga statistik dashboard tidak menunggu TTL sepuluh menit penuh setelah data berubah.

Data agregat dashboard cocok untuk cache karena perbedaan beberapa menit masih dapat diterima dan query agregat dapat memakan biaya lebih tinggi jika dijalankan setiap pembukaan halaman.

### Data yang Sengaja Tidak Di-cache

Data berikut tidak disimpan dalam cache aplikasi:

1. **Rekap nilai dosen dan mahasiswa**
   - Nilai dapat dibuat, diperbarui, atau dibatalkan sewaktu-waktu.
   - Cache dapat menampilkan nilai lama dan membuat hasil tidak sinkron dengan database.

2. **Detail pengumpulan tugas**
   - Status pengumpulan, status terlambat, nama berkas, ukuran berkas, dan waktu submit bersifat dinamis.
   - Data harus langsung mencerminkan submission terbaru mahasiswa.

3. **Notifikasi**
   - Notifikasi baru harus segera muncul.
   - Status sudah dibaca atau belum dibaca dapat berubah pada setiap interaksi.

4. **Data otorisasi dan sesi**
   - Pemeriksaan hak akses harus menggunakan data pengguna dan policy yang sedang aktif.
   - Cache tidak boleh dijadikan pengganti otorisasi real-time.

Dengan demikian, cache hanya diterapkan pada statistik agregat dashboard. Rekap nilai, pengumpulan tugas, notifikasi, dan data otorisasi sengaja dibaca langsung dari database agar tetap akurat dan real-time.
