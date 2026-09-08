<?php

use App\Http\Controllers\CourseController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Route — cakupan Minggu 1 & 2 SAJA
|--------------------------------------------------------------------------
| Belum ada: auth, middleware role, validasi, CRUD penuh (create/store/
| edit/update/destroy). Semua itu masuk Minggu 4 ke atas sesuai modul.
| Data mata kuliah masih array statis di controller (database baru
| masuk Minggu 3), jadi route SENGAJA tidak pakai route model binding
| Eloquent (mis. {course} tidak otomatis di-resolve ke Model).
*/

// GET / -> Dashboard. Dipakai Route::view() (bukan controller) karena
// halaman ini murni tampilan statis tanpa data dinamis apa pun —
// controller kosong hanya menambah berkas tanpa manfaat (Minggu 1-2
// belum butuh logika apa-apa di sini).
Route::view('/', 'dashboard')->name('dashboard');

// GET /tentang -> halaman "Tentang" (dibuat sejak Minggu 1, item BUILD
// nomor 7). Sama seperti dashboard, masih statis sehingga cukup
// Route::view(), tidak perlu controller.
Route::view('/tentang', 'tentang')->name('tentang');

/*
|--------------------------------------------------------------------------
| Modul Mata Kuliah (courses)
|--------------------------------------------------------------------------
| Nama route & path folder view ('courses') mengikuti persis contoh di
| modul Minggu 2 (Bagian 2.1 & 2.2), supaya konsisten dengan materi.
|
| Urutan route di bawah SUDAH benar: '/courses' (index) didaftarkan
| sebelum '/courses/{course}' tidak jadi masalah di sini karena
| 'create' tidak dipakai pada tahap ini -- tapi urutan tetap ditulis
| index dulu baru show, sebagai kebiasaan yang benar (lihat Bagian 2.1:
| "Urutan menentukan").
*/
Route::get('/courses', [CourseController::class, 'index'])->name('courses.index');
Route::get('/courses/{course}', [CourseController::class, 'show'])->name('courses.show');

/*
|--------------------------------------------------------------------------
| Route yang BELUM dibuat (sengaja, sesuai batas Minggu 1-2)
|--------------------------------------------------------------------------
| - courses.create / courses.store / courses.edit / courses.update /
|   courses.destroy -> baru Minggu 3 (CRUD + database).
| - Middleware 'auth' / pembatasan role admin-dosen-mahasiswa -> baru
|   Minggu 4-7 (login & Policy).
| Menambahkannya sekarang berarti mendahului materi dan tidak sesuai
| instruksi soal.
*/