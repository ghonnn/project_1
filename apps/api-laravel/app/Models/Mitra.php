<?php

namespace App\Models;

use App\Models\Concerns\NexModel;
use App\Models\Traits\BelongsToTenant;

class Mitra extends NexModel
{
    use BelongsToTenant;

    protected function casts(): array
    {
        return [
            'commission_value' => 'decimal:2',
            'balance' => 'decimal:2',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Mitra $mitra): void {
            if (! $mitra->status) {
                $mitra->status = 'active';
            }

            if (! $mitra->code) {
                $last = static::query()
                    ->where('tenant_id', $mitra->tenant_id)
                    ->whereNotNull('code')
                    ->orderByDesc('code')
                    ->value('code');

                $mitra->code = 'MTR-'.str_pad((string) (((int) str_replace('MTR-', '', (string) $last)) + 1), 4, '0', STR_PAD_LEFT);
            }
        });
    }
}
