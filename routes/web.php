<?php

use App\Http\Controllers\CourseController;
use Illuminate\Support\Facades\Route;


Route::view('/', 'dashboard')->name('dashboard');


Route::view('/tentang', 'tentang')->name('tentang');


Route::get('/courses/craete', [CourseController::class, 'index'])->name('courses.index');
Route::get('/courses/{course}', [CourseController::class, 'show'])->name('courses.show');
