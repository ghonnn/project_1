<?php

namespace App\Models;

use App\Models\Concerns\NexModel;
use App\Models\Traits\BelongsToTenant;

class RadiusSyncLog extends NexModel
{
    use BelongsToTenant;

    protected function casts(): array
    {
        return ['payload' => 'array'];
    }
}
