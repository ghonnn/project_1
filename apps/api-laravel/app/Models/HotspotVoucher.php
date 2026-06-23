<?php

namespace App\Models;

use App\Models\Concerns\NexModel;
use App\Models\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HotspotVoucher extends NexModel
{
    use BelongsToTenant;

    protected $hidden = ['password'];

    protected function casts(): array
    {
        return [
            'hpp' => 'decimal:2',
            'commission' => 'decimal:2',
            'price' => 'decimal:2',
            'activated_at' => 'datetime',
            'expires_at' => 'datetime',
            'synced_at' => 'datetime',
        ];
    }

    public function profile(): BelongsTo
    {
        return $this->belongsTo(RadiusProfile::class, 'profile_id');
    }

    public function router(): BelongsTo
    {
        return $this->belongsTo(Router::class);
    }

    public function radiusServer(): BelongsTo
    {
        return $this->belongsTo(RadiusServer::class);
    }
}
