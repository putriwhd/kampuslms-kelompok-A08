<?php

namespace App\Listeners;

use App\Events\SubmissionGraded;
use App\Notifications\SubmissionGradedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Throwable;

class SendSubmissionGradedNotification implements ShouldQueue
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
     * Tangani event SubmissionGraded.
     *
     * Type-hint SubmissionGraded pada parameter handle() wajib tepat
     * agar Laravel 12 Event Discovery otomatis menghubungkan listener ini
     * ke event yang benar (tanpa perlu registrasi manual).
     *
     * Anti N+1: loadMissing() memastikan relasi student dan
     * assignment.course hanya di-query sekali.
     */
    public function handle(SubmissionGraded $event): void
    {
        // Eager load relasi yang dibutuhkan (Anti N+1)
        $submission = $event->submission->loadMissing(['student', 'assignment.course']);

        // Ambil mahasiswa pemilik submission
        $student = $submission->student;

        // Kirim notifikasi hanya ke mahasiswa pemilik submission
        // Karena SubmissionGradedNotification implements ShouldQueue,
        // pengiriman ini akan menjadi 1 job individual di antrean
        if ($student) {
            $student->notify(new SubmissionGradedNotification($submission));
        }
    }

    /**
     * Tangani kegagalan setelah seluruh percobaan ($tries) habis.
     *
     * Job yang gagal 3 kali akan masuk tabel failed_jobs dan
     * dicatat ke storage/logs/laravel.log untuk keperluan debugging.
     */
    public function failed(SubmissionGraded $event, Throwable $exception): void
    {
        Log::error('SendSubmissionGradedNotification gagal setelah seluruh percobaan.', [
            'submission_id' => $event->submission->id,
            'error'         => $exception->getMessage(),
            'trace'         => $exception->getTraceAsString(),
        ]);
    }
}
