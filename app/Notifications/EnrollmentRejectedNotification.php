<?php

namespace App\Notifications;

use App\Models\EnrollmentRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class EnrollmentRejectedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public EnrollmentRequest $enrollmentRequest) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        $courseName = $this->enrollmentRequest->course->name;
        $reason = $this->enrollmentRequest->rejection_reason;

        return [
            'course_id' => $this->enrollmentRequest->course_id,
            'course_name' => $courseName,
            'title' => 'Pendaftaran Kelas Ditolak',
            'message' => 'Pengajuan mata kuliah '.$courseName.' ditolak. Alasan: '.$reason,
            'rejection_reason' => $reason,
            'url' => route('mahasiswa.catalog.index'),
        ];
    }
}
