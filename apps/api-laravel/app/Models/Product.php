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
            'ppn_enabled' => 'boolean',
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
        static::saving(function (Product $product): void {
            if (blank($product->mikrotik_group) || $product->mikrotik_group === 'RLRADIUS') {
                $product->mikrotik_group = static::radiusGroupName($product->sku ?: $product->name);
            }

            $hpp = (float) $product->hpp;

            $product->price = $product->ppn_enabled ? round($hpp * 1.11) : $hpp;
            $product->pricing = null;
        });

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
                        'Profile-PPN-Enabled' => $product->ppn_enabled,
                        'Profile-Commission' => $product->commission,
                    ],
                ]
            );
        });
    }

    private static function radiusGroupName(string $value): string
    {
        $value = strtoupper(preg_replace('/[^A-Za-z0-9_-]+/', '-', trim($value)) ?: 'NEX-PROFILE');

        return substr($value, 0, 50);
    }
}
