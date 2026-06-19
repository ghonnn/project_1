<?php

namespace App\Models;

use App\Models\Concerns\NexModel;
use App\Models\Traits\BelongsToTenant;

class RadiusProfile extends NexModel
{
    use BelongsToTenant;

    protected function casts(): array
    {
        return ['attributes' => 'array'];
    }
}
