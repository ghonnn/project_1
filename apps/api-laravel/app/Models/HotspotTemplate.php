<?php

namespace App\Models;

use App\Models\Concerns\NexModel;
use App\Models\Traits\BelongsToTenant;

class HotspotTemplate extends NexModel
{
    use BelongsToTenant;
}
