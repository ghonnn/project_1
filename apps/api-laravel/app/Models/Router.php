<?php

namespace App\Models;

use App\Models\Concerns\NexModel;
use App\Models\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Router extends NexModel
{
    use BelongsToTenant;

    public const PPP_CONNECTION_TYPES = ['PPP', 'PPPOE'];

    public const HOTSPOT_CONNECTION_TYPES = ['HOTSPOT', 'WIFI'];

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

    public function pppoeOnlineCount(): int
    {
        return $this->onlineRadiusUsersQuery(self::PPP_CONNECTION_TYPES)->count();
    }

    public function hotspotOnlineCount(): int
    {
        return $this->onlineRadiusUsersQuery(self::HOTSPOT_CONNECTION_TYPES)->count();
    }

    /**
     * @param array<int, string> $connectionTypes
     */
    private function onlineRadiusUsersQuery(array $connectionTypes): \Illuminate\Database\Eloquent\Builder
    {
        return $this->radiusUsers()
            ->where('status', 'active')
            ->whereHas('service', fn ($query) => $query->whereIn('connection_type', $connectionTypes));
    }
}
