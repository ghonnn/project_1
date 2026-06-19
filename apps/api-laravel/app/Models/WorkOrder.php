<?php

namespace App\Models;

use App\Models\Concerns\NexModel;
use App\Models\Traits\BelongsToTenant;

class WorkOrder extends NexModel
{
    use BelongsToTenant;

    protected function casts(): array
    {
        return ['report' => 'array'];
    }
}
