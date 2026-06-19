<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->string('region')->nullable()->after('cid');
            $table->decimal('latitude', 10, 7)->nullable()->after('region');
            $table->decimal('longitude', 10, 7)->nullable()->after('latitude');
            $table->text('installation_address')->nullable()->after('longitude');
            $table->string('partner_name')->nullable()->after('installation_address');
            $table->string('server_name')->nullable()->after('partner_name');
            $table->string('connection_type')->nullable()->after('server_name');
            $table->string('internet_username')->nullable()->after('connection_type');
            $table->string('internet_password')->nullable()->after('internet_username');
            $table->string('ip_address')->nullable()->after('internet_password');
            $table->string('device_ownership_status')->nullable()->after('ip_address');
            $table->string('device_brand')->nullable()->after('device_ownership_status');
            $table->string('device_serial_number')->nullable()->after('device_brand');
            $table->string('odp_number')->nullable()->after('device_serial_number');
            $table->string('odp_port')->nullable()->after('odp_number');
            $table->string('onu_slot')->nullable()->after('odp_port');
            $table->string('billing_profile_name')->nullable()->after('onu_slot');
            $table->string('billing_cycle')->nullable()->after('billing_profile_name');
            $table->string('billing_type')->nullable()->after('billing_cycle');
            $table->date('billing_active_date')->nullable()->after('billing_type');
            $table->date('billing_isolation_date')->nullable()->after('billing_active_date');
            $table->boolean('ppn_enabled')->default(false)->after('billing_isolation_date');
            $table->string('unit_code')->nullable()->after('ppn_enabled');
            $table->decimal('profile_price', 14, 2)->default(0)->after('unit_code');
            $table->decimal('partner_commission', 14, 2)->default(0)->after('profile_price');
            $table->date('installed_at')->nullable()->after('partner_commission');
            $table->text('notes')->nullable()->after('installed_at');
        });

        Schema::create('service_plan_changes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->uuid('service_id');
            $table->uuid('old_product_id')->nullable();
            $table->uuid('new_product_id')->nullable();
            $table->uuid('admin_user_id')->nullable();
            $table->date('change_date');
            $table->string('change_type');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreign('service_id')->references('id')->on('services')->cascadeOnDelete();
            $table->foreign('old_product_id')->references('id')->on('products')->nullOnDelete();
            $table->foreign('new_product_id')->references('id')->on('products')->nullOnDelete();
            $table->foreign('admin_user_id')->references('id')->on('users')->nullOnDelete();
        });

        Schema::create('service_addons', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->uuid('service_id');
            $table->string('name');
            $table->decimal('quantity', 12, 2)->default(1);
            $table->decimal('unit_price', 14, 2)->default(0);
            $table->decimal('monthly_amount', 14, 2)->default(0);
            $table->string('status')->default('active');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreign('service_id')->references('id')->on('services')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_addons');
        Schema::dropIfExists('service_plan_changes');

        Schema::table('services', function (Blueprint $table) {
            $table->dropColumn([
                'region',
                'latitude',
                'longitude',
                'installation_address',
                'partner_name',
                'server_name',
                'connection_type',
                'internet_username',
                'internet_password',
                'ip_address',
                'device_ownership_status',
                'device_brand',
                'device_serial_number',
                'odp_number',
                'odp_port',
                'onu_slot',
                'billing_profile_name',
                'billing_cycle',
                'billing_type',
                'billing_active_date',
                'billing_isolation_date',
                'ppn_enabled',
                'unit_code',
                'profile_price',
                'partner_commission',
                'installed_at',
                'notes',
            ]);
        });
    }
};
