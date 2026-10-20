<?php

use App\Http\Controllers\AssignmentController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MaterialController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WebAuthController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Halaman Statis & Autentikasi Web
|--------------------------------------------------------------------------
*/

Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
Route::view('/tentang', 'tentang')->name('tentang');

Route::get('/login', [WebAuthController::class, 'create'])->name('login');
Route::post('/login', [WebAuthController::class, 'store'])->name('login.store');
Route::get('/forgot-password', [WebAuthController::class, 'requestPasswordReset'])
    ->name('password.request');
Route::post('/forgot-password', [WebAuthController::class, 'sendPasswordResetLink'])
    ->name('password.email');
Route::get('/reset-password/{token}', [WebAuthController::class, 'showPasswordResetForm'])
    ->name('password.reset');
Route::post('/reset-password', [WebAuthController::class, 'resetPassword'])
    ->name('password.update');
Route::post('/logout', [WebAuthController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

/*
|--------------------------------------------------------------------------
| ADMIN
|--------------------------------------------------------------------------
*/

Route::prefix('admin')
    ->name('admin.')
    ->middleware(['auth', 'role:admin'])
    ->group(function () {

        Route::resource('users', UserController::class);
        Route::resource('courses', CourseController::class);

        Route::scopeBindings()->group(function () {

            Route::resource('courses.materials', MaterialController::class)
                ->only([
                    'index',
                    'show',
                    'create',
                    'store',
                    'edit',
                    'update',
                    'destroy',
                ])
                ->shallow();

            Route::get(
                'courses/{course}/materials/{material}',
                [MaterialController::class, 'showInCourse']
            )->name('courses.materials.scoped-show');

            Route::resource('courses.assignments', AssignmentController::class)
                ->only([
                    'index',
                    'show',
                    'create',
                    'store',
                    'edit',
                    'update',
                    'destroy',
                ])
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
    ->middleware(['auth', 'role:dosen'])
    ->group(function () {

        Route::resource('courses', CourseController::class)
            ->only(['index', 'show', 'edit', 'update']);

        Route::scopeBindings()->group(function () {

            Route::resource('courses.materials', MaterialController::class)
                ->only([
                    'index',
                    'show',
                    'create',
                    'store',
                    'edit',
                    'update',
                    'destroy',
                ])
                ->shallow();

            Route::get(
                'courses/{course}/materials/{material}',
                [MaterialController::class, 'showInCourse']
            )->name('courses.materials.scoped-show');

            Route::resource('courses.assignments', AssignmentController::class)
                ->only([
                    'index',
                    'show',
                    'create',
                    'store',
                    'edit',
                    'update',
                    'destroy',
                ])
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
    ->middleware(['auth', 'role:mahasiswa'])
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
| Rute Unduh Materi Privat (Minggu 9)
|--------------------------------------------------------------------------
*/

Route::get('materials/{material}/download', [MaterialController::class, 'download'])
    ->middleware('auth')
    ->name('materials.download');