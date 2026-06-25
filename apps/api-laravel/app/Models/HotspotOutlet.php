<?php

namespace App\Models;

use App\Models\Concerns\NexModel;
use App\Models\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HotspotOutlet extends NexModel
{
    use BelongsToTenant;

    protected function casts(): array
    {
        return [
            'joined_at' => 'date',
        ];
    }

    public function mitra(): BelongsTo
    {
        return $this->belongsTo(Mitra::class, 'mitra_id');
    }

    public function vouchers(): HasMany
    {
        return $this->hasMany(HotspotVoucher::class, 'outlet_id');
    }
}
