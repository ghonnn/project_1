<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tenants', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('plan')->nullable();
            $table->string('status')->default('active');
            $table->timestamps();
        });

        Schema::create('users', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->nullable();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->string('status')->default('active');
            $table->rememberToken();
            $table->timestamps();
            $table->foreign('tenant_id')->references('id')->on('tenants')->nullOnDelete();
        });

        Schema::create('roles', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->nullable();
            $table->string('name');
            $table->string('code');
            $table->string('scope')->default('tenant');
            $table->timestamps();
            $table->unique(['tenant_id', 'code']);
            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
        });

        Schema::create('permissions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('module');
            $table->string('action');
            $table->string('code')->unique();
            $table->timestamps();
        });

        Schema::create('role_permissions', function (Blueprint $table) {
            $table->uuid('role_id');
            $table->uuid('permission_id');
            $table->primary(['role_id', 'permission_id']);
            $table->foreign('role_id')->references('id')->on('roles')->cascadeOnDelete();
            $table->foreign('permission_id')->references('id')->on('permissions')->cascadeOnDelete();
        });

        Schema::create('user_roles', function (Blueprint $table) {
            $table->uuid('user_id');
            $table->uuid('role_id');
            $table->primary(['user_id', 'role_id']);
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('role_id')->references('id')->on('roles')->cascadeOnDelete();
        });

        Schema::create('customers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->string('type');
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('status')->default('prospect');
            $table->json('billing_contact')->nullable();
            $table->timestamps();
            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->index(['tenant_id', 'status']);
        });

        Schema::create('service_categories', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->string('code');
            $table->string('name');
            $table->boolean('requires_router_mapping')->default(false);
            $table->boolean('requires_radius')->default(false);
            $table->boolean('requires_ip_assignment')->default(false);
            $table->boolean('requires_vlan')->default(false);
            $table->timestamps();
            $table->unique(['tenant_id', 'code']);
            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
        });

        Schema::create('products', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->uuid('service_category_id')->nullable();
            $table->string('sku');
            $table->string('name');
            $table->decimal('price', 14, 2)->default(0);
            $table->string('billing_cycle')->default('monthly');
            $table->string('status')->default('active');
            $table->json('pricing')->nullable();
            $table->timestamps();
            $table->unique(['tenant_id', 'sku']);
            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreign('service_category_id')->references('id')->on('service_categories')->nullOnDelete();
        });

        Schema::create('services', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->uuid('customer_id');
            $table->uuid('product_id')->nullable();
            $table->uuid('service_category_id')->nullable();
            $table->string('cid')->nullable();
            $table->string('status')->default('requested');
            $table->timestamp('activated_at')->nullable();
            $table->timestamp('suspended_at')->nullable();
            $table->timestamp('terminated_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->unique(['tenant_id', 'cid']);
            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreign('customer_id')->references('id')->on('customers')->cascadeOnDelete();
            $table->foreign('product_id')->references('id')->on('products')->nullOnDelete();
            $table->foreign('service_category_id')->references('id')->on('service_categories')->nullOnDelete();
        });

        Schema::create('routers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->string('router_name');
            $table->string('hostname');
            $table->string('vendor')->nullable();
            $table->string('model')->nullable();
            $table->string('serial_number')->nullable();
            $table->string('router_role');
            $table->string('site_name')->nullable();
            $table->string('management_ip');
            $table->string('public_ip')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->string('status')->default('draft');
            $table->string('snmp_status')->default('not_configured');
            $table->json('snmp_profile')->nullable();
            $table->timestamps();
            $table->unique(['tenant_id', 'hostname']);
            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
        });

        Schema::create('router_interfaces', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->uuid('router_id');
            $table->string('interface_name');
            $table->string('interface_type')->nullable();
            $table->string('ip_address')->nullable();
            $table->integer('vlan_id')->nullable();
            $table->integer('speed_mbps')->nullable();
            $table->string('status')->default('provisioning');
            $table->timestamps();
            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreign('router_id')->references('id')->on('routers')->cascadeOnDelete();
        });

        Schema::create('router_links', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->uuid('router_a_id');
            $table->uuid('router_b_id');
            $table->uuid('interface_a_id')->nullable();
            $table->uuid('interface_b_id')->nullable();
            $table->string('link_type')->nullable();
            $table->string('status')->default('active');
            $table->integer('capacity_mbps')->nullable();
            $table->timestamps();
            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
        });

        Schema::create('service_router_mapping', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->uuid('service_id');
            $table->uuid('router_id');
            $table->uuid('interface_id')->nullable();
            $table->integer('vlan_id')->nullable();
            $table->boolean('is_primary')->default(true);
            $table->timestamps();
            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreign('service_id')->references('id')->on('services')->cascadeOnDelete();
            $table->foreign('router_id')->references('id')->on('routers')->cascadeOnDelete();
            $table->foreign('interface_id')->references('id')->on('router_interfaces')->nullOnDelete();
        });

        Schema::create('customer_router_mapping', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->uuid('customer_id');
            $table->uuid('router_id');
            $table->timestamps();
            $table->unique(['tenant_id', 'customer_id', 'router_id']);
            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreign('customer_id')->references('id')->on('customers')->cascadeOnDelete();
            $table->foreign('router_id')->references('id')->on('routers')->cascadeOnDelete();
        });

        Schema::create('radius_servers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->nullable();
            $table->string('name');
            $table->string('host');
            $table->integer('auth_port')->default(1812);
            $table->integer('acct_port')->default(1813);
            $table->string('shared_secret');
            $table->string('status')->default('active');
            $table->string('last_test_status')->nullable();
            $table->text('last_test_message')->nullable();
            $table->timestamp('last_tested_at')->nullable();
            $table->timestamps();
            $table->foreign('tenant_id')->references('id')->on('tenants')->nullOnDelete();
        });

        Schema::create('radius_profiles', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->string('name');
            $table->json('attributes')->nullable();
            $table->timestamps();
            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
        });

        Schema::create('nas_devices', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->uuid('radius_server_id')->nullable();
            $table->uuid('router_id');
            $table->string('hostname');
            $table->string('nas_ip_address');
            $table->string('vendor_type')->nullable();
            $table->string('secret');
            $table->string('status')->default('active');
            $table->timestamps();
            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreign('radius_server_id')->references('id')->on('radius_servers')->nullOnDelete();
            $table->foreign('router_id')->references('id')->on('routers')->cascadeOnDelete();
        });

        Schema::create('radius_users', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->uuid('customer_id');
            $table->uuid('service_id');
            $table->uuid('router_id')->nullable();
            $table->uuid('profile_id')->nullable();
            $table->string('username');
            $table->string('secret');
            $table->string('status')->default('pending');
            $table->timestamps();
            $table->unique(['tenant_id', 'username']);
            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreign('customer_id')->references('id')->on('customers')->cascadeOnDelete();
            $table->foreign('service_id')->references('id')->on('services')->cascadeOnDelete();
            $table->foreign('router_id')->references('id')->on('routers')->nullOnDelete();
            $table->foreign('profile_id')->references('id')->on('radius_profiles')->nullOnDelete();
        });

        Schema::create('radius_sync_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->uuid('radius_user_id')->nullable();
            $table->uuid('radius_server_id')->nullable();
            $table->string('action');
            $table->string('status');
            $table->text('message')->nullable();
            $table->json('payload')->nullable();
            $table->timestamps();
            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreign('radius_user_id')->references('id')->on('radius_users')->nullOnDelete();
            $table->foreign('radius_server_id')->references('id')->on('radius_servers')->nullOnDelete();
        });

        Schema::create('invoices', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->uuid('customer_id');
            $table->string('invoice_number');
            $table->date('issue_date');
            $table->date('due_date');
            $table->string('status')->default('issued');
            $table->decimal('total_amount', 14, 2)->default(0);
            $table->decimal('paid_amount', 14, 2)->default(0);
            $table->timestamps();
            $table->unique(['tenant_id', 'invoice_number']);
            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreign('customer_id')->references('id')->on('customers')->cascadeOnDelete();
        });

        Schema::create('invoice_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->uuid('invoice_id');
            $table->uuid('service_id')->nullable();
            $table->string('description');
            $table->decimal('quantity', 12, 2)->default(1);
            $table->decimal('unit_amount', 14, 2);
            $table->decimal('total_amount', 14, 2);
            $table->timestamps();
            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreign('invoice_id')->references('id')->on('invoices')->cascadeOnDelete();
            $table->foreign('service_id')->references('id')->on('services')->nullOnDelete();
        });

        Schema::create('payments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->uuid('invoice_id');
            $table->decimal('amount', 14, 2);
            $table->string('method')->default('manual');
            $table->string('status')->default('initiated');
            $table->string('external_ref')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreign('invoice_id')->references('id')->on('invoices')->cascadeOnDelete();
        });

        Schema::create('tickets', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->uuid('customer_id')->nullable();
            $table->uuid('service_id')->nullable();
            $table->uuid('router_id')->nullable();
            $table->string('subject');
            $table->text('description')->nullable();
            $table->string('status')->default('new');
            $table->timestamps();
        });

        Schema::create('work_orders', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->uuid('ticket_id')->nullable();
            $table->uuid('service_id')->nullable();
            $table->uuid('router_id')->nullable();
            $table->string('status')->default('created');
            $table->json('report')->nullable();
            $table->timestamps();
        });

        Schema::create('audit_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->nullable();
            $table->uuid('user_id')->nullable();
            $table->string('action');
            $table->string('entity_type');
            $table->uuid('entity_id')->nullable();
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->string('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });

        Schema::create('personal_access_tokens', function (Blueprint $table) {
            $table->id();
            $table->uuidMorphs('tokenable');
            $table->text('name');
            $table->string('token', 64)->unique();
            $table->text('abilities')->nullable();
            $table->timestamp('last_used_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        foreach ([
            'personal_access_tokens', 'audit_logs', 'work_orders', 'tickets', 'payments', 'invoice_items', 'invoices',
            'radius_sync_logs', 'radius_users', 'nas_devices', 'radius_profiles', 'radius_servers',
            'customer_router_mapping', 'service_router_mapping', 'router_links', 'router_interfaces',
            'routers', 'services', 'products', 'service_categories', 'customers', 'user_roles',
            'role_permissions', 'permissions', 'roles', 'users', 'tenants',
        ] as $table) {
            Schema::dropIfExists($table);
        }
    }
};
