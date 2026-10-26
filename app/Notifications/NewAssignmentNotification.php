<?php

namespace App\Notifications;

use App\Models\Assignment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class NewAssignmentNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Notifikasi tugas baru untuk mahasiswa.
     *
     * Implements ShouldQueue + use Queueable agar setiap pengiriman
     * ke masing-masing mahasiswa menjadi job individual di antrean
     * (1 job per mahasiswa). Jika satu gagal, 39 lainnya tetap terkirim.
     */
    public function __construct(public Assignment $assignment)
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
     * KEAMANAN: Tidak menyertakan data sensitif (nilai, feedback).
     * Hanya menyimpan ID, judul, nama mata kuliah, deadline, dan URL.
     */
    public function toDatabase(object $notifiable): array
    {
        return [
            'assignment_id' => $this->assignment->id,
            'course_name'   => $this->assignment->course->name,
            'title'         => $this->assignment->title,
            'due_at'        => $this->assignment->due_at?->toIso8601String(),
            'url'           => route('assignments.show', $this->assignment),
        ];
    }
}
