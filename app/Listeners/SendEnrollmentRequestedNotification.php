<?php

namespace App\Listeners;

use App\Events\EnrollmentRequested;
use App\Notifications\EnrollmentRequestedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Throwable;

class SendEnrollmentRequestedNotification implements ShouldQueue
{
    use InteractsWithQueue;

    public int $tries = 3;
    public int $backoff = 10;

    public function handle(EnrollmentRequested $event): void
    {
        $enrollmentRequest = $event->enrollmentRequest->loadMissing(['course.lecturer', 'student']);
        $lecturer = $enrollmentRequest->course?->lecturer;

        if ($lecturer) {
            $lecturer->notify(new EnrollmentRequestedNotification($enrollmentRequest));
        }
    }

    public function failed(EnrollmentRequested $event, Throwable $exception): void
    {
        Log::error('SendEnrollmentRequestedNotification gagal setelah seluruh percobaan.', [
            'request_id' => $event->enrollmentRequest->id,
            'error'      => $exception->getMessage(),
            'trace'      => $exception->getTraceAsString(),
        ]);
    }
}

