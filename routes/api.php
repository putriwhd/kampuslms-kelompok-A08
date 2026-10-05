<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;

/*
|--------------------------------------------------------------------------
| API Routes — KampusLMS (Laravel 12 / Sanctum)
| Prefix Global: /api/v1
|--------------------------------------------------------------------------
|
| Konfigurasi Orang 1:
| - Task 1: Sanctum Authentication
| - Task 5: Rate Limiting (throttle:5,1 untuk login, throttle:60,1 untuk rute umum)
|
*/

Route::prefix('v1')->group(function () {

    // --- 1. AUTENTIKASI PUBLIK (Rate limit: 5 permintaan per menit) ---
    Route::post('/auth/login', [AuthController::class, 'login'])
        ->middleware('throttle:5,1');

    // --- 2. RUTE TERPROTEKSI SANCTUM (Rate limit: 60 permintaan per menit) ---
    Route::middleware(['auth:sanctum', 'throttle:60,1'])->group(function () {

        // Autentikasi Pengguna
        Route::post('/auth/logout', [AuthController::class, 'logout']);
        Route::get('/me', [AuthController::class, 'me']);

        // [SLOT UNTUK ORANG 3: Endpoint Courses, Assignments, Submissions & Grading]
        // Contoh rute yang akan diisi oleh Orang 3:
        // Route::get('/courses', [CourseController::class, 'index']);
        // Route::get('/courses/{id}', [CourseController::class, 'show']);
        // Route::get('/courses/{id}/materials', [CourseController::class, 'materials']);
        // Route::get('/courses/{id}/assignments', [CourseController::class, 'assignments']);
        // Route::post('/assignments', [AssignmentController::class, 'store']);
        // Route::match(['put', 'patch'], '/assignments/{id}', [AssignmentController::class, 'update']);
        // Route::delete('/assignments/{id}', [AssignmentController::class, 'destroy']);
        // Route::get('/assignments/{id}/submissions', [AssignmentController::class, 'submissions']);
        // Route::post('/assignments/{id}/submissions', [SubmissionController::class, 'store']);
        // Route::put('/submissions/{id}/grade', [SubmissionController::class, 'grade']);
    });
});
