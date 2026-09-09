<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CourseController;

/*
|--------------------------------------------------------------------------
| Dashboard
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return view('dashboard');
})->name('dashboard');


/*
|--------------------------------------------------------------------------
| Mata Kuliah
|--------------------------------------------------------------------------
*/

// Menampilkan daftar mata kuliah
Route::get('/courses', [CourseController::class, 'index'])
    ->name('courses.index');

// Menampilkan detail mata kuliah
Route::get('/courses/{id}', [CourseController::class, 'show'])
    ->name('courses.show');


/*
|--------------------------------------------------------------------------
| Tentang
|--------------------------------------------------------------------------
*/

Route::get('/tentang', function () {
    return view('tentang');
})->name('tentang');