<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hotspot_outlets', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->uuid('mitra_id')->nullable();
            $table->string('name');
            $table->string('owner_name')->nullable();
            $table->string('phone')->nullable();
            $table->text('address')->nullable();
            $table->date('joined_at')->nullable();
            $table->string('status')->default('active');
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreign('mitra_id')->references('id')->on('mitras')->nullOnDelete();
        });

        // Add outlet_id to hotspot_vouchers
        if (Schema::hasTable('hotspot_vouchers') && !Schema::hasColumn('hotspot_vouchers', 'outlet_id')) {
            Schema::table('hotspot_vouchers', function (Blueprint $table) {
                $table->uuid('outlet_id')->nullable()->after('radius_server_id');
                $table->foreign('outlet_id')->references('id')->on('hotspot_outlets')->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('hotspot_vouchers') && Schema::hasColumn('hotspot_vouchers', 'outlet_id')) {
            Schema::table('hotspot_vouchers', function (Blueprint $table) {
                $table->dropForeign(['outlet_id']);
                $table->dropColumn('outlet_id');
            });
        }

        Schema::dropIfExists('hotspot_outlets');
    }
};
