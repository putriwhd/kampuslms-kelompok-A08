# Dokumentasi Debugging & Analisis Bug — KampusLMS

Dokumen ini merangkum 3 *bug* nyata yang pernah ditemui selama pengembangan KampusLMS sepanjang semester, beserta gejala, langkah investigasi, perbaikan, dan *regression test* yang menguncinya.

---

## Bug 1: Queue Worker Mati Sehingga Notifikasi Tidak Terkirim (Minggu 10)

### 1. Gejala
* Ketika dosen memublikasikan tugas baru atau memberi nilai pada *submission* mahasiswa, respons antarmuka web berhasil menyimpan data tanpa menampilkan pesan *error*.
* Namun, notifikasi tidak muncul di pusat notifikasi maupun terkirim ke akun mahasiswa terkait.

### 2. Langkah Investigasi & Penyebab
1. Memeriksa berkas log pada `storage/logs/laravel.log`, tetapi tidak ditemukan catatan *error* atau *exception* terkait pengiriman notifikasi.
2. Mengecek konfigurasi `.env` dan mendapati `QUEUE_CONNECTION=database`.
3. Memeriksa isi tabel `jobs` pada basis data dan menemukan antrean *job* notifikasi menumpuk dengan kolom `attempts = 0`.
4. **Penyebab Utama:** Listener/notifikasi mengimplementasikan `ShouldQueue`, sedangkan proses *queue worker* (`php artisan queue:work`) tidak sedang berjalan di lingkungan lokal sehingga seluruh *job* tertahan di tabel `jobs`.

### 3. Perbaikan & Regression Test
* **Perbaikan:** Menjalankan proses `php artisan queue:work` pada sesi pengembangan dan mendokumentasikan kebutuhan *worker* pada alur eksekusi aplikasi.
* **Regression Test:** `AssignmentPublicationNotificationTest::test_publishing_an_assignment_notifies_only_enrolled_students_and_links_to_its_detail` serta `CriticalAccessControlTest::test_notification_sent_to_enrolled_students_when_new_assignment_published` menggunakan `Notification::fake()` untuk memeriksa penerima notifikasi. `WeekTenBackendTest::test_web_assignment_publication_and_grading_dispatch_events` menggunakan `Event::fake()` untuk memeriksa dispatch event. Pemeriksaan otomatis ini tidak menjalankan atau membuktikan bahwa proses queue worker aktif; implementasi notifikasi ber-queue juga perlu diverifikasi terpisah di lingkungan lokal terisolasi.

---

## Bug 2: Form Kumpul Ulang Muncul Padahal Sudah Dinilai (Minggu 10)

### 1. Gejala
* Mahasiswa yang sudah mengumpulkan tugas — bahkan yang status *submission*-nya sudah dinilai oleh dosen — tetap melihat formulir kirim ulang tugas (*resubmit form*) pada halaman detail tugas (`resources/views/assignments/show.blade.php`).
* Kondisi ini membingungkan mahasiswa dan berisiko menimpa berkas tugas yang sudah dinilai.

### 2. Langkah Investigasi & Penyebab
1. Memeriksa tampilan DOM pada `resources/views/assignments/show.blade.php` menggunakan akun mahasiswa yang sudah memiliki data *submission* dan nilai.
2. Menelusuri logika kondisional Blade yang merender komponen formulir pengumpulan tugas.
3. **Penyebab Utama:** Blok rendering pada `resources/views/assignments/show.blade.php` perlu membedakan mahasiswa yang sudah memiliki submission dari yang belum. Form hanya boleh ditampilkan jika mahasiswa belum mengumpulkan dan masih diizinkan membuat submission; status `published` saja tidak cukup.

### 3. Perbaikan & Regression Test
* **Perbaikan:** Tampilan saat ini merender ringkasan submission jika `$ownSubmission` tersedia, termasuk status penilaian; form hanya dirender jika belum ada submission dan Policy mengizinkan pengumpulan.
* **Regression Test:** `SubmissionBackendTest::test_web_submission_and_resubmission_for_mahasiswa` memastikan form tidak muncul setelah pengumpulan, baik saat menunggu penilaian maupun setelah nilai dan feedback tersimpan. `WeekSevenDeliveryTest::test_lecturer_can_grade_submissions_and_student_can_only_see_own_grade` menguji batas akses nilai.

---

## Bug 3: Selisih Waktu Server UTC vs WITA pada Perhitungan `is_late` (Minggu 7 & 10)

### 1. Gejala
* Tugas yang dikumpulkan mahasiswa sebelum tenggat waktu lokal (WITA) tercatat sebagai terlambat (`is_late = true`) atau langsung ditolak sistem saat `allow_late` bernilai `false`.

### 2. Langkah Investigasi & Penyebab
1. Membandingkan timestamp `submitted_at` pada tabel `submissions` dengan `due_at` pada tabel `assignments`.
2. Memeriksa konfigurasi zona waktu aplikasi di `config/app.php` dan fungsi `date_default_timezone_get()` pada runtime PHP.
3. **Penyebab Utama:** Waktu server berjalan menggunakan zona `UTC`, sementara jadwal tenggat tugas diinput dalam waktu lokal WITA (`Asia/Makassar`, `UTC+8`), menyebabkan selisih 8 jam pada perbandingan waktu pengumpulan tugas.

### 3. Perbaikan & Regression Test
* **Perbaikan:** Menyelaraskan zona waktu aplikasi pada `config/app.php` menjadi `'timezone' => 'Asia/Makassar'` dan memastikan perbandingan batas waktu menggunakan instance `Carbon` yang konsisten.
* **Regression Test:** Ditutup oleh *regression test* `SubmissionBackendTest::test_server_lateness_calculation_and_deadline_rejection` serta `CriticalAccessControlTest::test_student_submission_after_deadline_is_rejected_when_allow_late_is_false` yang menguji batas waktu pengumpulan tepat sebelum dan sesudah tenggat.