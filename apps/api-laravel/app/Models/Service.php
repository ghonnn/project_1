<?php

namespace App\Models;

use App\Models\Concerns\NexModel;
use App\Models\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

class Service extends NexModel
{
    use BelongsToTenant;

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
            'activated_at' => 'datetime',
            'suspended_at' => 'datetime',
            'terminated_at' => 'datetime',
            'billing_active_date' => 'date',
            'billing_isolation_date' => 'date',
            'installed_at' => 'date',
            'ppn_enabled' => 'boolean',
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'profile_price' => 'decimal:2',
            'partner_commission' => 'decimal:2',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function serviceCategory(): BelongsTo
    {
        return $this->belongsTo(ServiceCategory::class);
    }

    public function routerMappings(): HasMany
    {
        return $this->hasMany(ServiceRouterMapping::class);
    }

    public function radiusUsers(): HasMany
    {
        return $this->hasMany(RadiusUser::class);
    }

    public function planChanges(): HasMany
    {
        return $this->hasMany(ServicePlanChange::class);
    }

    public function addons(): HasMany
    {
        return $this->hasMany(ServiceAddon::class);
    }

    protected static function booted(): void
    {
        static::creating(function (Service $service): void {
            if ($service->cid) {
                return;
            }

            $date = Carbon::now()->format('Y_m_d');
            $prefix = $date.'_';
            $lastCid = static::query()
                ->where('tenant_id', $service->tenant_id)
                ->where('cid', 'like', $prefix.'%')
                ->orderByDesc('cid')
                ->value('cid');

            $sequence = 1;
            if (is_string($lastCid)) {
                $sequence = ((int) substr($lastCid, -5)) + 1;
            }

            $service->cid = $prefix.str_pad((string) $sequence, 5, '0', STR_PAD_LEFT);
        });
    }
}
