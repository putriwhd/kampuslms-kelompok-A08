<?php

use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\AssignmentController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\GradeRecapController;
use App\Http\Controllers\GradeRecapPageController;
use App\Http\Controllers\MaterialController;
use App\Http\Controllers\NotificationPageController;
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
Route::middleware('guest')->group(function () {
    Route::get('/register', [WebAuthController::class, 'createAccount'])->name('register');
    Route::post('/register', [WebAuthController::class, 'register'])
        ->middleware('throttle:5,1')
        ->name('register.store');
});
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
        Route::put('courses/{course}/students', [CourseController::class, 'updateStudents'])
            ->name('courses.students.update');

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

            Route::get(
                'courses/{course}/assignments/{assignment}/submissions',
                [AssignmentController::class, 'submissionsRecap']
            )->name('courses.assignments.submissions.index');

            Route::get(
                'assignments/{assignment}/submissions',
                [AssignmentController::class, 'submissionsRecapShallow']
            )->name('assignments.submissions');

            Route::get(
                'courses/{course}/assignments/{assignment}/submissions/{submission}/download',
                [AssignmentController::class, 'downloadSubmission']
            )->name('courses.assignments.submissions.download');

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

        Route::get('/grade-recap', [GradeRecapController::class, 'lecturer'])
            ->name('grade-recap.index');
        Route::get('/grade-recap/overview', [GradeRecapPageController::class, 'lecturer'])
            ->name('grade-recap.page');

        Route::resource('courses', CourseController::class)
            ->only(['index', 'show', 'edit', 'update']);

        Route::put(
            'courses/{course}/students',
            [CourseController::class, 'updateStudents']
        )->name('courses.students.update');

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

            Route::post(
                'courses/{course}/assignments/{assignment}/submissions/{submission}/grade',
                [AssignmentController::class, 'gradeSubmission']
            )->name('courses.assignments.submissions.grade');

            Route::get(
                'courses/{course}/assignments/{assignment}/submissions/{submission}/download',
                [AssignmentController::class, 'downloadSubmission']
            )->name('courses.assignments.submissions.download');

            Route::get(
                'courses/{course}/assignments/{assignment}',
                [AssignmentController::class, 'showInCourse']
            )->name('courses.assignments.scoped-show');

            Route::get(
                'courses/{course}/assignments/{assignment}/submissions',
                [AssignmentController::class, 'submissionsRecap']
            )->name('courses.assignments.submissions.index');

            Route::get(
                'assignments/{assignment}/submissions',
                [AssignmentController::class, 'submissionsRecapShallow']
            )->name('assignments.submissions');

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

        Route::get('/grade-recap', [GradeRecapController::class, 'student'])
            ->name('grade-recap.index');
        Route::get('/grade-recap/overview', [GradeRecapPageController::class, 'student'])
            ->name('grade-recap.page');

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

            Route::get(
                'courses/{course}/assignments/{assignment}/submissions/{submission}/download',
                [AssignmentController::class, 'downloadSubmission']
            )->name('courses.assignments.submissions.download');

            Route::post(
                'courses/{course}/assignments/{assignment}/submissions',
                [AssignmentController::class, 'submitWeb']
            )->name('courses.assignments.submissions.store');

            Route::post(
                'assignments/{assignment}/submit',
                [AssignmentController::class, 'submitWeb']
            )->name('assignments.submit');

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

Route::prefix('notifications')
    ->name('notifications.')
    ->middleware(['auth', 'role:admin,dosen,mahasiswa'])
    ->group(function () {
        Route::get('/', [NotificationPageController::class, 'index'])->name('index');
        Route::get('/preview', [NotificationPageController::class, 'preview'])
            ->name('preview');
        Route::get('/unread-count', [NotificationController::class, 'unreadCount'])
            ->name('unread-count');
        Route::match(['patch', 'post'], '/{id}/read', [NotificationController::class, 'markAsRead'])
            ->name('read');
        Route::post('/read-all', [NotificationController::class, 'markAllAsRead'])
            ->name('read-all');
    });
