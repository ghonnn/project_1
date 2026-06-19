<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->unsignedInteger('license_max_sessions')->default(250)->after('logo_path');
            $table->unsignedInteger('license_max_vouchers')->default(5000)->after('license_max_sessions');
            $table->unsignedInteger('license_max_subscriptions')->default(200)->after('license_max_vouchers');
            $table->unsignedInteger('license_max_routers')->default(2)->after('license_max_subscriptions');
        });

        DB::table('tenants')
            ->whereNull('plan')
            ->orWhere('plan', '')
            ->orWhere('plan', 'rnd')
            ->update(['plan' => 'NEX BASIC']);
    }

    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn([
                'license_max_sessions',
                'license_max_vouchers',
                'license_max_subscriptions',
                'license_max_routers',
            ]);
        });
    }
};
