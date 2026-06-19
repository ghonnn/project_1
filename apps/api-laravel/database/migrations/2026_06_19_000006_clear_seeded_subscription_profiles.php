<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('products')) {
            DB::table('products')
                ->whereIn('sku', [
                    'MD-70M',
                    'MD-60M',
                    'MD-35M',
                    'MD-25M',
                    'MD-15M',
                    'MD-10M',
                    'YD-70M',
                    'YD-60M',
                    'YD-35M',
                    'YD-25M',
                    'YD-15M',
                    'YD-10M',
                    'VPS-BASIC',
                    'DOMAIN-HOSTING',
                ])
                ->delete();
        }

        if (Schema::hasTable('radius_profiles')) {
            DB::table('radius_profiles')
                ->whereIn('name', [
                    '100M',
                    'MD 70 Mbps',
                    'MD 60 Mbps',
                    'MD 35 Mbps',
                    'MD 25 Mbps',
                    'MD 15 Mbps',
                    'MD 10 Mbps',
                    'YD 70 Mbps',
                    'YD 60 Mbps',
                    'YD 35 Mbps',
                    'YD 25 Mbps',
                    'YD 15 Mbps',
                    'YD 10 Mbps',
                    'Cloud VPS Basic',
                    'Domain Hosting',
                ])
                ->delete();
        }
    }

    public function down(): void
    {
        //
    }
};
