<?php

namespace App\Events;

use App\Models\Assignment;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AssignmentPublished
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Event yang dilempar saat dosen mempublikasikan tugas baru.
     *
     * Data yang dibawa: objek Assignment lengkap (relasi course
     * akan di-eager-load oleh Listener, bukan di sini).
     */
    public function __construct(public Assignment $assignment)
    {
    }
}
