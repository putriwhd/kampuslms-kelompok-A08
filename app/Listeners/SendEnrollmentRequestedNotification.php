<?php

namespace App\Listeners;

use App\Events\EnrollmentRequested;
use App\Notifications\EnrollmentRequestedNotification;

class SendEnrollmentRequestedNotification
{
    public function handle(EnrollmentRequested $event): void
    {
        $enrollmentRequest = $event->enrollmentRequest->loadMissing(['course.lecturer', 'student']);
        $lecturer = $enrollmentRequest->course?->lecturer;

        if ($lecturer) {
            $lecturer->notify(new EnrollmentRequestedNotification($enrollmentRequest));
        }
    }
}
