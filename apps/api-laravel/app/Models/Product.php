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

    protected static function booted(): void
    {
        static::saved(function (Product $product): void {
            RadiusProfile::updateOrCreate(
                [
                    'tenant_id' => $product->tenant_id,
                    'name' => $product->name,
                ],
                [
                    'attributes' => [
                        'Mikrotik-Group' => $product->mikrotik_group,
                        'Mikrotik-Rate-Limit' => $product->mikrotik_rate_limit,
                        'Shared-Users' => $product->shared_users,
                        'Active-Days' => $product->active_days,
                        'Profile-Price' => $product->price,
                        'Profile-HPP' => $product->hpp,
                        'Profile-Commission' => $product->commission,
                    ],
                ]
            );
        });
    }
}
