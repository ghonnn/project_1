<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('router_script_templates')) {
            Schema::create('router_script_templates', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->uuid('tenant_id')->nullable();
                $table->string('vendor')->default('mikrotik');
                $table->string('os_version');
                $table->string('script_type');
                $table->text('template_body');
                $table->json('variables_schema')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
                $table->unique(['tenant_id', 'vendor', 'os_version', 'script_type'], 'router_script_templates_unique');
                $table->foreign('tenant_id')->references('id')->on('tenants')->nullOnDelete();
            });
        }

        Schema::table('router_interfaces', function (Blueprint $table) {
            $table->unique(['tenant_id', 'router_id', 'interface_name'], 'router_interfaces_tenant_router_name_unique');
        });

        Schema::table('service_router_mapping', function (Blueprint $table) {
            $table->index(['tenant_id', 'router_id'], 'service_router_mapping_tenant_router_idx');
            $table->index(['tenant_id', 'service_id'], 'service_router_mapping_tenant_service_idx');
        });

        Schema::table('radius_users', function (Blueprint $table) {
            $table->index(['tenant_id', 'router_id'], 'radius_users_tenant_router_idx');
            $table->index(['tenant_id', 'service_id'], 'radius_users_tenant_service_idx');
        });
    }

    public function down(): void
    {
        Schema::table('radius_users', function (Blueprint $table) {
            $table->dropIndex('radius_users_tenant_router_idx');
            $table->dropIndex('radius_users_tenant_service_idx');
        });

        Schema::table('service_router_mapping', function (Blueprint $table) {
            $table->dropIndex('service_router_mapping_tenant_router_idx');
            $table->dropIndex('service_router_mapping_tenant_service_idx');
        });

        Schema::table('router_interfaces', function (Blueprint $table) {
            $table->dropUnique('router_interfaces_tenant_router_name_unique');
        });

        Schema::dropIfExists('router_script_templates');
    }
};
