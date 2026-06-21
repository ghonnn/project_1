<?php

namespace App\Models;

use App\Models\Concerns\NexModel;
use App\Models\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

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

    public function nasDevices(): HasMany
    {
        return $this->hasMany(NasDevice::class);
    }

    public function primaryNasDevice(): HasOne
    {
        return $this->hasOne(NasDevice::class)
            ->where('status', 'active')
            ->orderByDesc('created_at');
    }
}
