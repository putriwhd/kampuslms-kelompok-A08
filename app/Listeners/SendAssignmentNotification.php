<?php

namespace App\Listeners;

use App\Events\AssignmentPublished;
use App\Notifications\NewAssignmentNotification;
use Illuminate\Support\Facades\Notification;

class SendAssignmentNotification
{
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
        $assignment = $event->assignment->loadMissing('course.students');
        $students = $assignment->course->students;

        if ($students->isNotEmpty()) {
            Notification::send($students, new NewAssignmentNotification($assignment));
        }
    }
}
