<?php

namespace App\Models;

use App\Models\Concerns\NexModel;
use App\Models\Traits\BelongsToTenant;

class RadiusServer extends NexModel
{
    use BelongsToTenant;

    protected function casts(): array
    {
        return ['last_tested_at' => 'datetime'];
    }
}
