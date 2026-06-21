<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('products') || ! Schema::hasColumn('products', 'mikrotik_group')) {
            return;
        }

        DB::table('products')
            ->whereNull('mikrotik_group')
            ->orWhere('mikrotik_group', 'RLRADIUS')
            ->orderBy('sku')
            ->get(['id', 'sku', 'name'])
            ->each(function (object $product): void {
                DB::table('products')
                    ->where('id', $product->id)
                    ->update([
                        'mikrotik_group' => $this->radiusGroupName((string) ($product->sku ?: $product->name)),
                        'updated_at' => now(),
                    ]);
            });
    }

    public function down(): void
    {
        //
    }

    private function radiusGroupName(string $value): string
    {
        $value = strtoupper(preg_replace('/[^A-Za-z0-9_-]+/', '-', trim($value)) ?: 'NEX-PROFILE');

        return substr($value, 0, 50);
    }
};
