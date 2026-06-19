<?php

namespace App\Models;

use App\Models\Concerns\NexModel;
use App\Models\Traits\BelongsToTenant;

class ServiceCategory extends NexModel
{
    use BelongsToTenant;

    protected function casts(): array
    {
        return [
            'requires_router_mapping' => 'boolean',
            'requires_radius' => 'boolean',
            'requires_ip_assignment' => 'boolean',
            'requires_vlan' => 'boolean',
        ];
    }
}
