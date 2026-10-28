<?php

namespace App\Notifications;

use App\Models\Assignment;
use Illuminate\Notifications\Notification;

class NewAssignmentNotification extends Notification
{
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
            'course_name' => $this->assignment->course->name,
            'title' => $this->assignment->title,
            'due_at' => $this->assignment->due_at?->toIso8601String(),
            'url' => route('mahasiswa.assignments.show', $this->assignment, false),
        ];
    }
}
