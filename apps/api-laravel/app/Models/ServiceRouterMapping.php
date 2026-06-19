<?php

namespace App\Models;

use App\Models\Concerns\NexModel;
use App\Models\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServiceRouterMapping extends NexModel
{
    use BelongsToTenant;

    protected $table = 'service_router_mapping';

    protected function casts(): array
    {
        return ['is_primary' => 'boolean'];
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function router(): BelongsTo
    {
        return $this->belongsTo(Router::class);
    }

    public function interface(): BelongsTo
    {
        return $this->belongsTo(RouterInterface::class, 'interface_id');
    }

    protected static function booted(): void
    {
        static::saved(function (ServiceRouterMapping $mapping): void {
            $service = $mapping->service()->first();

            if (! $service) {
                return;
            }

            CustomerRouterMapping::firstOrCreate([
                'tenant_id' => $mapping->tenant_id,
                'customer_id' => $service->customer_id,
                'router_id' => $mapping->router_id,
            ]);
        });
    }
}
