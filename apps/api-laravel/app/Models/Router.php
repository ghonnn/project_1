<?php

namespace App\Models;

use App\Models\Concerns\NexModel;
use App\Models\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Router extends NexModel
{
    use BelongsToTenant;

    protected function casts(): array
    {
        return [
            'online_sessions' => 'integer',
            'snmp_profile' => 'array',
        ];
    }

    public function interfaces(): HasMany
    {
        return $this->hasMany(RouterInterface::class);
    }

    public function radiusUsers(): HasMany
    {
        return $this->hasMany(RadiusUser::class);
    }
}
