<?php

use App\Http\Controllers\CourseController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;



Route::view('/', 'dashboard')->name('dashboard');


Route::view('/tentang', 'tentang')->name('tentang');


Route::get('/courses/craete', [CourseController::class, 'index'])->name('courses.index');
Route::get('/courses/{course}', [CourseController::class, 'show'])->name('courses.show');

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
| Route yang BELUM dibuat (sengaja, sesuai batas Minggu 1-2)
|--------------------------------------------------------------------------
| - courses.create / courses.store / courses.edit / courses.update /
|   courses.destroy -> baru Minggu 3 (CRUD + database).
| - Middleware 'auth' / pembatasan role admin-dosen-mahasiswa -> baru
|   Minggu 4-7 (login & Policy).
| Menambahkannya sekarang berarti mendahului materi dan tidak sesuai
| instruksi soal.
*/

// Halaman Statis
Route::view('/', 'dashboard')->name('dashboard');
Route::view('/tentang', 'tentang')->name('tentang');

// Resource Routes (Mendukung CRUD Lengkap & Simulasi Role)
Route::resource('users', UserController::class);
Route::resource('courses', CourseController::class);

Route::get('/courses', [CourseController::class, 'index'])->name('courses.index');
Route::get('/courses/{course}', [CourseController::class, 'show'])->name('courses.show');

