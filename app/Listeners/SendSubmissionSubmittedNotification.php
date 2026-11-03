<?php

namespace App\Listeners;

use App\Events\SubmissionSubmitted;
use App\Notifications\SubmissionSubmittedNotification;

class SendSubmissionSubmittedNotification
{
    public function handle(SubmissionSubmitted $event): void
    {
        $submission = $event->submission->loadMissing(['student', 'assignment.course.lecturer']);
        $lecturer = $submission->assignment->course->lecturer;

        if ($lecturer) {
            $lecturer->notify(new SubmissionSubmittedNotification($submission));
        }
    }
}
