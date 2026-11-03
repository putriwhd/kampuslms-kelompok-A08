<?php

namespace App\Listeners;

use App\Events\AssignmentPublished;
use App\Notifications\NewAssignmentNotification;
use App\Notifications\AdminActivityNotification;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Throwable;

class SendAssignmentNotification implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Jumlah percobaan sebelum job dianggap gagal permanen.
     */
    public int $tries = 3;

    /**
     * Jeda detik antar percobaan ulang.
     */
    public int $backoff = 10;

    /**
     * Tangani event AssignmentPublished.
     *
     * Type-hint AssignmentPublished pada parameter handle() wajib tepat
     * agar Laravel 12 Event Discovery otomatis menghubungkan listener ini
     * ke event yang benar (tanpa perlu registrasi manual).
     *
     * Anti N+1: loadMissing() memastikan relasi course dan students
     * hanya di-query sekali, bukan per-iterasi.
     */
    public function handle(AssignmentPublished $event): void
    {
        // Eager load relasi yang dibutuhkan (Anti N+1)
        $assignment = $event->assignment->loadMissing(['course.students', 'creator']);


        // Ambil seluruh mahasiswa yang terdaftar di mata kuliah
        $students = $assignment->course->students;

        // Kirim notifikasi ke seluruh mahasiswa sekaligus
        if ($students->isNotEmpty()) {
            Notification::send($students, new NewAssignmentNotification($assignment));
        }

        $admins = User::where('role', 'admin')->get();
        if ($admins->isNotEmpty() && (! $assignment->creator || $assignment->creator->role === 'dosen')) {
            Notification::send($admins, new AdminActivityNotification(
                'Tugas baru dipublikasikan',
                'Dosen telah memublikasikan tugas baru pada mata kuliah ' . $assignment->course->name . '.',
                route('admin.courses.assignments.index', [
                    'course' => $assignment->course,
                    'as' => 'admin',
                ], false)
            ));
        }
    }

    /**
     * Tangani kegagalan setelah seluruh percobaan ($tries) habis.
     *
     * Job yang gagal 3 kali akan masuk tabel failed_jobs dan
     * dicatat ke storage/logs/laravel.log untuk keperluan debugging.
     */
    public function failed(AssignmentPublished $event, Throwable $exception): void
    {
        Log::error('SendAssignmentNotification gagal setelah seluruh percobaan.', [
            'assignment_id' => $event->assignment->id,
            'error'         => $exception->getMessage(),
            'trace'         => $exception->getTraceAsString(),
        ]);
    }
}

