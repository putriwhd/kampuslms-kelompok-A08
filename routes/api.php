<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CourseController;
use App\Http\Controllers\Api\AssignmentController;
use App\Http\Controllers\Api\SubmissionController;
use App\Http\Controllers\Api\NotificationController;

/*
|--------------------------------------------------------------------------
| API Routes — KampusLMS (Laravel 12 / Sanctum)
| Prefix Global: /api/v1
|--------------------------------------------------------------------------
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

        // --- RUTE ORANG 3: Courses ---
        Route::get('/courses', [CourseController::class, 'index']);
        Route::get('/courses/{id}', [CourseController::class, 'show']);
        Route::get('/courses/{id}/materials', [CourseController::class, 'materials']);
        Route::get('/courses/{id}/assignments', [CourseController::class, 'assignments']);

        // --- RUTE ORANG 3: Assignments ---
        Route::post('/assignments', [AssignmentController::class, 'store']);
        Route::match(['put', 'patch'], '/assignments/{id}', [AssignmentController::class, 'update']);
        Route::delete('/assignments/{id}', [AssignmentController::class, 'destroy']);
        Route::get('/assignments/{id}/submissions', [AssignmentController::class, 'submissions']);
        Route::post('/assignments/{id}/submissions', [SubmissionController::class, 'storeForAssignment']);

        // --- RUTE ORANG 3: Submissions ---
        Route::post('/submissions', [SubmissionController::class, 'store']);
        Route::put('/submissions/{id}/grade', [SubmissionController::class, 'grade']);

        // --- RUTE ORANG 3: Notifications ---
        Route::get('/notifications', [NotificationController::class, 'index']);
        Route::post('/notifications/{id}/read', [NotificationController::class, 'markRead']);
    });
});