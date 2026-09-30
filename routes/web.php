<?php

use App\Http\Controllers\AssignmentController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\MaterialController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Halaman Umum
|--------------------------------------------------------------------------
*/

Route::view('/', 'dashboard')->name('dashboard');

Route::view('/tentang', 'tentang')->name('tentang');


/*
|--------------------------------------------------------------------------
| Admin
|--------------------------------------------------------------------------
*/

Route::middleware(['role:admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {

        Route::resource('users', UserController::class);

        Route::resource('courses', CourseController::class);

        Route::scopeBindings()->group(function () {

            Route::resource('courses.materials', MaterialController::class)
                ->shallow();

            Route::resource('courses.assignments', AssignmentController::class)
                ->shallow();

        });
    });


/*
|--------------------------------------------------------------------------
| Dosen
|--------------------------------------------------------------------------
*/

Route::middleware(['role:dosen'])
    ->prefix('dosen')
    ->name('dosen.')
    ->group(function () {

        Route::resource('courses', CourseController::class)
            ->only(['index', 'show']);

        Route::scopeBindings()->group(function () {

            Route::resource('courses.materials', MaterialController::class)
                ->shallow();

            Route::resource('courses.assignments', AssignmentController::class)
                ->shallow();

        });
    });


/*
|--------------------------------------------------------------------------
| Mahasiswa
|--------------------------------------------------------------------------
*/

Route::middleware(['role:mahasiswa'])
    ->prefix('mahasiswa')
    ->name('mahasiswa.')
    ->group(function () {

        Route::resource('courses', CourseController::class)
            ->only(['index', 'show']);

        Route::scopeBindings()->group(function () {

            Route::resource('courses.materials', MaterialController::class)
                ->only(['index', 'show'])
                ->shallow();

            Route::resource('courses.assignments', AssignmentController::class)
                ->only(['index', 'show'])
                ->shallow();

        });
    });