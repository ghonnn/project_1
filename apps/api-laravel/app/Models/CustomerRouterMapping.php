<?php

namespace App\Models;

use App\Models\Concerns\NexModel;
use App\Models\Traits\BelongsToTenant;

class CustomerRouterMapping extends NexModel
{
    use BelongsToTenant;

    protected $table = 'customer_router_mapping';
}
