<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hotspot_vouchers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->uuid('profile_id')->nullable();
            $table->uuid('router_id')->nullable();
            $table->uuid('radius_server_id')->nullable();
            $table->string('username');
            $table->string('password');
            $table->string('batch_code')->nullable();
            $table->string('partner_name')->nullable();
            $table->string('outlet_name')->nullable();
            $table->decimal('hpp', 14, 2)->default(0);
            $table->decimal('commission', 14, 2)->default(0);
            $table->decimal('price', 14, 2)->default(0);
            $table->string('status')->default('stock');
            $table->string('mac_address')->nullable();
            $table->timestamp('activated_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('synced_at')->nullable();
            $table->text('sync_message')->nullable();
            $table->timestamps();
            $table->unique(['tenant_id', 'username']);
            $table->index(['tenant_id', 'status']);
            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreign('profile_id')->references('id')->on('radius_profiles')->nullOnDelete();
            $table->foreign('router_id')->references('id')->on('routers')->nullOnDelete();
            $table->foreign('radius_server_id')->references('id')->on('radius_servers')->nullOnDelete();
        });

        Schema::create('hotspot_templates', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->string('name');
            $table->string('hotspot_name')->default('NEX ISP Hotspot');
            $table->string('dns_name')->nullable();
            $table->string('support_phone')->nullable();
            $table->string('status')->default('active');
            $table->longText('html_body');
            $table->timestamps();
            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hotspot_templates');
        Schema::dropIfExists('hotspot_vouchers');
    }
};
