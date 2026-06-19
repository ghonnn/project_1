<?php

namespace App\Models;

use App\Models\Concerns\NexModel;
use App\Models\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends NexModel
{
    use BelongsToTenant;

    protected function casts(): array
    {
        return [
            'active_days' => 'integer',
            'commission' => 'decimal:2',
            'hpp' => 'decimal:2',
            'price' => 'decimal:2',
            'pricing' => 'array',
            'shared_users' => 'integer',
        ];
    }

    public function serviceCategory(): BelongsTo
    {
        return $this->belongsTo(ServiceCategory::class);
    }

    public function services(): HasMany
    {
        return $this->hasMany(Service::class);
    }
}
