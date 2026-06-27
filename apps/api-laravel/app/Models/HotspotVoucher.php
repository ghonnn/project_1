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
            'balance_deducted' => 'boolean',
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

    public function outlet(): BelongsTo
    {
        return $this->belongsTo(HotspotOutlet::class, 'outlet_id');
    }

    public function mitra(): BelongsTo
    {
        return $this->belongsTo(Mitra::class, 'mitra_id');
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_user_id');
    }
}
