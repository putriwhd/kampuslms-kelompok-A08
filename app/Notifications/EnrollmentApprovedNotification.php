<?php

namespace App\Notifications;

use App\Models\EnrollmentRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class EnrollmentApprovedNotification extends Notification implements ShouldQueue
{
    use Queueable;

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
            'course_id'   => $this->enrollmentRequest->course_id,
            'course_name' => $this->enrollmentRequest->course->name,
            'title'       => 'Pendaftaran Kelas Disetujui',
            'message'     => 'Pendaftaran Anda pada mata kuliah ' . $this->enrollmentRequest->course->name . ' telah disetujui dosen.',
            'url'         => route('mahasiswa.courses.show', ['course' => $this->enrollmentRequest->course_id, 'as' => 'mahasiswa']),
        ];
    }
}

