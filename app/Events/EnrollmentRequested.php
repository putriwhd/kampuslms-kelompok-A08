<?php

namespace App\Events;

use App\Models\EnrollmentRequest;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class EnrollmentRequested
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Event yang dipicu saat mahasiswa mengajukan pendaftaran mandiri.
     */
    public function __construct(public EnrollmentRequest $enrollmentRequest)
    {
    }
}

