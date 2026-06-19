<?php

namespace App\Models;

use App\Models\Concerns\NexModel;
use App\Models\Traits\BelongsToTenant;
use App\Services\FreeRadiusService;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServicePlanChange extends NexModel
{
    use BelongsToTenant;

    protected function casts(): array
    {
        return ['change_date' => 'date'];
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function oldProduct(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'old_product_id');
    }

    public function newProduct(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'new_product_id');
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_user_id');
    }

    protected static function booted(): void
    {
        static::saved(function (ServicePlanChange $change): void {
            $service = $change->service()->with('radiusUsers')->first();
            $newProduct = $change->newProduct()->first();

            if (! $service || ! $newProduct) {
                return;
            }

            $radiusProfile = RadiusProfile::updateOrCreate(
                [
                    'tenant_id' => $newProduct->tenant_id,
                    'name' => $newProduct->name,
                ],
                [
                    'attributes' => [
                        'Mikrotik-Group' => $newProduct->mikrotik_group,
                        'Mikrotik-Rate-Limit' => $newProduct->mikrotik_rate_limit,
                        'Shared-Users' => $newProduct->shared_users,
                        'Active-Days' => $newProduct->active_days,
                        'Profile-Price' => $newProduct->price,
                        'Profile-HPP' => $newProduct->hpp,
                        'Profile-PPN-Enabled' => $newProduct->ppn_enabled,
                        'Profile-Commission' => $newProduct->commission,
                    ],
                ]
            );

            $service->update([
                'product_id' => $newProduct->id,
                'service_category_id' => $newProduct->service_category_id,
                'billing_profile_name' => $newProduct->name,
                'billing_cycle' => $newProduct->billing_cycle,
                'profile_price' => $newProduct->price,
                'ppn_enabled' => $newProduct->ppn_enabled,
            ]);

            $freeRadius = app(FreeRadiusService::class);

            $service->radiusUsers()->each(function (RadiusUser $user) use ($radiusProfile, $freeRadius): void {
                $user->update(['profile_id' => $radiusProfile->id]);
                $freeRadius->syncUser($user->fresh());
            });
        });
    }
}
