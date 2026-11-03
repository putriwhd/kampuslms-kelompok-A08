<?php

namespace App\Listeners;

use App\Events\EnrollmentApproved;
use App\Notifications\EnrollmentApprovedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Throwable;

class SendEnrollmentApprovedNotification implements ShouldQueue
{
    use InteractsWithQueue;

    public int $tries = 3;
    public int $backoff = 10;

    public function handle(EnrollmentApproved $event): void
    {
        $enrollmentRequest = $event->enrollmentRequest->loadMissing(['course', 'student']);
        $student = $enrollmentRequest->student;

        if ($student) {
            $student->notify(new EnrollmentApprovedNotification($enrollmentRequest));
        }
    }

    public function failed(EnrollmentApproved $event, Throwable $exception): void
    {
        Log::error('SendEnrollmentApprovedNotification gagal setelah seluruh percobaan.', [
            'request_id' => $event->enrollmentRequest->id,
            'error'      => $exception->getMessage(),
            'trace'      => $exception->getTraceAsString(),
        ]);
    }
}

