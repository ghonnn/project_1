<?php

namespace App\Models;

use App\Models\Concerns\NexModel;
use App\Models\Traits\BelongsToTenant;

class RouterLink extends NexModel
{
    use BelongsToTenant;
}
