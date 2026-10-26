<?php

namespace App\Notifications;

use App\Models\Submission;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class SubmissionGradedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Notifikasi bahwa submission mahasiswa telah dinilai.
     *
     * Implements ShouldQueue + use Queueable agar pengiriman
     * berjalan sebagai job individual di antrean.
     */
    public function __construct(public Submission $submission)
    {
    }

    /**
     * Saluran pengiriman: hanya database.
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Payload yang disimpan ke tabel notifications.
     *
     * KEAMANAN: Tidak menyertakan nilai (score) atau isi feedback.
     * Hanya menyimpan ID, judul tugas, nama mata kuliah, dan URL.
     * Mahasiswa harus membuka halaman tugas untuk melihat nilainya.
     */
    public function toDatabase(object $notifiable): array
    {
        return [
            'submission_id'    => $this->submission->id,
            'assignment_id'    => $this->submission->assignment_id,
            'assignment_title' => $this->submission->assignment->title,
            'course_name'      => $this->submission->assignment->course->name,
            'url'              => route('assignments.show', $this->submission->assignment_id),
        ];
    }
}
