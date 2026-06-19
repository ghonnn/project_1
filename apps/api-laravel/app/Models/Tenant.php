<?php

namespace App\Models;

use App\Models\Concerns\NexModel;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tenant extends NexModel
{
    protected function casts(): array
    {
        return [
            'billing_settings' => 'array',
            'license_max_routers' => 'integer',
            'license_max_sessions' => 'integer',
            'license_max_subscriptions' => 'integer',
            'license_max_vouchers' => 'integer',
        ];
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
}
