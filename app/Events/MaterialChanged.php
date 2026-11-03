<?php

namespace App\Events;

use App\Models\Material;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MaterialChanged
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public Material $material,
        public string $action
    ) {
    }
}
