<?php

namespace App\Notifications;

use App\Models\EnrollmentRequest;
use Illuminate\Notifications\Notification;

class EnrollmentRequestedNotification extends Notification
{
    public function __construct(public EnrollmentRequest $enrollmentRequest)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'request_id'   => $this->enrollmentRequest->id,
            'course_id'    => $this->enrollmentRequest->course_id,
            'course_name'  => $this->enrollmentRequest->course->name,
            'student_id'   => $this->enrollmentRequest->user_id,
            'student_name' => $this->enrollmentRequest->student->name,
            'title'        => 'Permintaan Pendaftaran Kelas',
            'message'      => 'Mahasiswa ' . $this->enrollmentRequest->student->name . ' mengajukan pendaftaran ke mata kuliah ' . $this->enrollmentRequest->course->name . '.',
            'url'          => route('dosen.courses.show', ['course' => $this->enrollmentRequest->course_id, 'as' => 'dosen']),
        ];
    }
}
