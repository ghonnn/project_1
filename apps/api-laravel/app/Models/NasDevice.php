<?php

namespace App\Models;

use App\Models\Concerns\NexModel;
use App\Models\Traits\BelongsToTenant;

class NasDevice extends NexModel
{
    use BelongsToTenant;
}
