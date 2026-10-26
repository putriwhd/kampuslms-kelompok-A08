<?php

namespace App\Events;

use App\Models\Submission;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SubmissionGraded
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Event yang dilempar saat dosen memberi nilai pada submission.
     *
     * Data yang dibawa: objek Submission lengkap (relasi student
     * dan assignment.course akan di-eager-load oleh Listener).
     */
    public function __construct(public Submission $submission)
    {
    }
}
