<?php

use App\Http\Controllers\CourseController;
use App\Http\Controllers\AssignmentController;
use App\Http\Controllers\MaterialController;
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
| TEST SECURITY — DATA LEAK
|--------------------------------------------------------------------------
| Route ini sengaja dibuat untuk menguji apakah data User
| dapat diakses tanpa autentikasi.
|
| JIKA endpoint ini mengembalikan data User dalam JSON,
| berarti terjadi kebocoran data.
*/

Route::get('/test-leak', function () {
    return response()->json(\App\Models\User::all());
});


/*
|--------------------------------------------------------------------------
| ADMIN
|--------------------------------------------------------------------------
*/

Route::prefix('admin')
    ->name('admin.')
    ->middleware('role:admin')
    ->group(function () {

        Route::resource('users', UserController::class);

        Route::resource('courses', CourseController::class);

        Route::scopeBindings()->group(function () {

            Route::resource('courses.materials', MaterialController::class)
                ->only(['index', 'show'])
                ->shallow();

            Route::get(
                'courses/{course}/materials/{material}',
                [MaterialController::class, 'showInCourse']
            )->name('courses.materials.scoped-show');


            Route::resource('courses.assignments', AssignmentController::class)
                ->only(['index', 'show'])
                ->shallow();

            Route::get(
                'courses/{course}/assignments/{assignment}',
                [AssignmentController::class, 'showInCourse']
            )->name('courses.assignments.scoped-show');

        });
    });


/*
|--------------------------------------------------------------------------
| DOSEN
|--------------------------------------------------------------------------
*/

Route::prefix('dosen')
    ->name('dosen.')
    ->middleware('role:dosen')
    ->group(function () {

        Route::resource('courses', CourseController::class)
            ->only(['index', 'show']);

        Route::scopeBindings()->group(function () {

            Route::resource('courses.materials', MaterialController::class)
                ->only(['index', 'show'])
                ->shallow();

            Route::get(
                'courses/{course}/materials/{material}',
                [MaterialController::class, 'showInCourse']
            )->name('courses.materials.scoped-show');


            Route::resource('courses.assignments', AssignmentController::class)
                ->only(['index', 'show'])
                ->shallow();

            Route::get(
                'courses/{course}/assignments/{assignment}',
                [AssignmentController::class, 'showInCourse']
            )->name('courses.assignments.scoped-show');

        });
    });


/*
|--------------------------------------------------------------------------
| MAHASISWA
|--------------------------------------------------------------------------
*/

Route::prefix('mahasiswa')
    ->name('mahasiswa.')
    ->middleware('role:mahasiswa')
    ->group(function () {

        Route::resource('courses', CourseController::class)
            ->only(['index', 'show']);

        Route::scopeBindings()->group(function () {

            Route::resource('courses.materials', MaterialController::class)
                ->only(['index', 'show'])
                ->shallow();

            Route::get(
                'courses/{course}/materials/{material}',
                [MaterialController::class, 'showInCourse']
            )->name('courses.materials.scoped-show');


            Route::resource('courses.assignments', AssignmentController::class)
                ->only(['index', 'show'])
                ->shallow();

            Route::get(
                'courses/{course}/assignments/{assignment}',
                [AssignmentController::class, 'showInCourse']
            )->name('courses.assignments.scoped-show');

        });
    });