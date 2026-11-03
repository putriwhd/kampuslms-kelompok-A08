<?php

namespace App\Events;

use App\Models\EnrollmentRequest;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class EnrollmentApproved
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Event yang dipicu saat dosen menyetujui pendaftaran mahasiswa.
     */
    public function __construct(public EnrollmentRequest $enrollmentRequest)
    {
    }
}

