<?php

use App\Http\Controllers\CourseController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;



Route::view('/', 'dashboard')->name('dashboard');


Route::view('/tentang', 'tentang')->name('tentang');


Route::get('/courses/create', [CourseController::class, 'index'])->name('courses.index');
Route::get('/courses/{course}', [CourseController::class, 'show'])->name('courses.show');

/*
|--------------------------------------------------------------------------
| Halaman Statis
|--------------------------------------------------------------------------
*/

Route::view('/', 'dashboard')->name('dashboard');

Route::view('/tentang', 'tentang')->name('tentang');



/*
|--------------------------------------------------------------------------
| Resource Routes
|--------------------------------------------------------------------------
| Mata Kuliah dan Pengguna menggunakan CRUD lengkap
| sesuai kebutuhan Minggu 3.
|--------------------------------------------------------------------------
*/

Route::resource('users', UserController::class);

Route::resource('courses', CourseController::class);


Route::get('/courses', [CourseController::class, 'index'])->name('courses.index');
Route::get('/courses/{course}', [CourseController::class, 'show'])->name('courses.show');