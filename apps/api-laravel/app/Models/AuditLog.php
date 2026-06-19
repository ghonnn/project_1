<?php

namespace App\Models;

use App\Models\Concerns\NexModel;

class AuditLog extends NexModel
{
    public $timestamps = false;

    protected function casts(): array
    {
        return ['old_values' => 'array', 'new_values' => 'array', 'created_at' => 'datetime'];
    }
}
