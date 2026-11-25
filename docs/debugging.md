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
* **Regression Test:** Menggunakan `Notification::fake()` serta `Event::fake()` pada `AssignmentPublicationNotificationTest::test_publishing_an_assignment_notifies_only_enrolled_students_and_links_to_its_detail`, `CriticalAccessControlTest::test_notification_sent_to_enrolled_students_when_new_assignment_published`, dan `WeekTenBackendTest::test_web_assignment_publication_and_grading_dispatch_events` untuk memastikan *event* dan notifikasi benar-benar ter-dispatch.

---

## Bug 2: Form Kumpul Ulang Muncul Padahal Sudah Dinilai (Minggu 10)

### 1. Gejala
* Mahasiswa yang sudah mengumpulkan tugas — bahkan yang status *submission*-nya sudah dinilai oleh dosen — tetap melihat formulir kirim ulang tugas (*resubmit form*) pada halaman detail tugas (`resources/views/assignments/show.blade.php`).
* Kondisi ini membingungkan mahasiswa dan berisiko menimpa berkas tugas yang sudah dinilai.

### 2. Langkah Investigasi & Penyebab
1. Memeriksa tampilan DOM pada `resources/views/assignments/show.blade.php` menggunakan akun mahasiswa yang sudah memiliki data *submission* dan nilai.
2. Menelusuri logika kondisional Blade yang merender komponen formulir pengumpulan tugas.
3. **Penyebab Utama:** Blok rendering komponen formulir pengumpulan tugas pada `resources/views/assignments/show.blade.php` belum memeriksa keberadaan data `$submission` maupun status penilaian (`grade`), sehingga formulir unggah tetap dirender selama tugas berstatus `published`.

### 3. Perbaikan & Regression Test
* **Perbaikan:** Memperbarui kondisi Blade pada `resources/views/assignments/show.blade.php` agar menyembunyikan formulir pengumpulan ulang apabila *submission* sudah dinilai atau tidak lagi memenuhi syarat pengumpulan ulang, serta menampilkan ringkasan *submission* dan nilai mahasiswa.
* **Regression Test:** Diverifikasi melalui *feature test* pada `SubmissionBackendTest::test_web_submission_and_resubmission_for_mahasiswa` dan `WeekSevenDeliveryTest::test_lecturer_can_grade_submissions_and_student_can_only_see_own_grade` yang memastikan halaman detail tugas merender status *submission* dan nilai sesuai kondisi pengumpulan mahasiswa.

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