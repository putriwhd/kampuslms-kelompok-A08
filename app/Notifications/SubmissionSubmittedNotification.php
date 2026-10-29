<?php

namespace App\Notifications;

use App\Models\Submission;
use Illuminate\Notifications\Notification;

class SubmissionSubmittedNotification extends Notification
{
    public function __construct(public Submission $submission)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        $submission = $this->submission->loadMissing(['student', 'assignment.course']);
        $student = $submission->student;
        $assignment = $submission->assignment;
        $course = $assignment->course;

        return [
            'title' => 'Tugas baru dikumpulkan',
            'message' => ($student?->name ?? 'Mahasiswa') . ' mengumpulkan tugas "' . $assignment->title . '" pada mata kuliah ' . $course->name . '.',
            'submission_id' => $submission->id,
            'assignment_id' => $assignment->id,
            'assignment_title' => $assignment->title,
            'course_name' => $course->name,
            'student_name' => $student?->name,
            'url' => route('dosen.assignments.show', $assignment, false),
        ];
    }
}
