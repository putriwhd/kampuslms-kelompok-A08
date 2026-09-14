<?php

use App\Http\Controllers\CourseController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

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