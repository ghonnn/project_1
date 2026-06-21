<?php

namespace App\Models;

use App\Models\Concerns\NexModel;
use App\Models\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

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
        return $this->onlineSessionCount(self::PPP_CONNECTION_TYPES);
    }

    public function hotspotOnlineCount(): int
    {
        return $this->onlineSessionCount(self::HOTSPOT_CONNECTION_TYPES);
    }

    /**
     * @param array<int, string> $connectionTypes
     */
    private function onlineRadiusUsersQuery(array $connectionTypes): HasMany
    {
        return $this->radiusUsers()
            ->where('status', 'active')
            ->whereHas('service', fn ($query) => $query->whereIn('connection_type', $connectionTypes));
    }

    /**
     * @param array<int, string> $connectionTypes
     */
    private function onlineSessionCount(array $connectionTypes): int
    {
        if (! Schema::hasTable('radacct')) {
            return 0;
        }

        $usernames = $this->onlineRadiusUsersQuery($connectionTypes)
            ->pluck('username')
            ->filter()
            ->unique()
            ->values();

        if ($usernames->isEmpty()) {
            return 0;
        }

        return DB::table('radacct')
            ->whereNull('acctstoptime')
            ->whereIn('username', $usernames)
            ->distinct('username')
            ->count('username');
    }
}
