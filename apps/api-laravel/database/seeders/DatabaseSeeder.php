<?php

namespace Database\Seeders;

use App\Models\NasDevice;
use App\Models\Permission;
use App\Models\RadiusServer;
use App\Models\Role;
use App\Models\Router;
use App\Models\ServiceCategory;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $tenant = Tenant::updateOrCreate(
            ['slug' => 'nex-demo-isp'],
            ['name' => 'NEX Demo ISP', 'plan' => 'rnd', 'status' => 'active']
        );

        $roles = collect([
            ['name' => 'Platform Owner', 'code' => 'platform_owner', 'scope' => 'platform', 'tenant_id' => null],
            ['name' => 'Tenant Owner', 'code' => 'tenant_owner', 'scope' => 'tenant', 'tenant_id' => $tenant->id],
            ['name' => 'Tenant Admin', 'code' => 'tenant_admin', 'scope' => 'tenant', 'tenant_id' => $tenant->id],
            ['name' => 'Finance', 'code' => 'finance', 'scope' => 'tenant', 'tenant_id' => $tenant->id],
            ['name' => 'NOC', 'code' => 'noc', 'scope' => 'tenant', 'tenant_id' => $tenant->id],
            ['name' => 'Sales', 'code' => 'sales', 'scope' => 'tenant', 'tenant_id' => $tenant->id],
            ['name' => 'Technician', 'code' => 'technician', 'scope' => 'tenant', 'tenant_id' => $tenant->id],
            ['name' => 'Partner', 'code' => 'partner', 'scope' => 'tenant', 'tenant_id' => $tenant->id],
            ['name' => 'Customer', 'code' => 'customer', 'scope' => 'tenant', 'tenant_id' => $tenant->id],
        ])->map(fn ($role) => Role::updateOrCreate(
            ['tenant_id' => $role['tenant_id'], 'code' => $role['code']],
            $role
        ));

        foreach (['tenant.manage', 'customer.manage', 'service.manage', 'router.manage', 'radius.manage', 'billing.manage', 'payment.manage', 'mitra.manage', 'ticket.manage', 'audit.read'] as $code) {
            [$module, $action] = explode('.', $code);
            Permission::updateOrCreate(['code' => $code], compact('module', 'action'));
        }

        $allPermissionCodes = Permission::pluck('code')->all();
        $permissionMatrix = [
            'platform_owner' => $allPermissionCodes,
            'tenant_owner' => $allPermissionCodes,
            'tenant_admin' => ['customer.manage', 'service.manage', 'router.manage', 'radius.manage', 'mitra.manage', 'ticket.manage', 'audit.read'],
            'finance' => ['billing.manage', 'payment.manage'],
            'noc' => ['router.manage', 'radius.manage', 'ticket.manage'],
            'sales' => ['customer.manage', 'service.manage', 'ticket.manage'],
            'technician' => ['ticket.manage'],
            'partner' => [],
            'customer' => [],
        ];

        $roles->each(function (Role $role) use ($permissionMatrix): void {
            $codes = $permissionMatrix[$role->code] ?? [];
            $role->permissions()->sync(Permission::whereIn('code', $codes)->pluck('id'));
        });

        $admin = User::updateOrCreate(
            ['email' => 'admin@nex.local'],
            ['name' => 'NEX Platform Admin', 'tenant_id' => null, 'password' => Hash::make('password'), 'status' => 'active']
        );
        $admin->roles()->sync([$roles->firstWhere('code', 'platform_owner')->id]);

        $categories = collect([
            ['code' => 'BROADBAND', 'name' => 'Broadband', 'requires_router_mapping' => true, 'requires_radius' => true, 'requires_ip_assignment' => true, 'requires_vlan' => true],
            ['code' => 'DEDICATED', 'name' => 'Dedicated', 'requires_router_mapping' => true, 'requires_radius' => false, 'requires_ip_assignment' => true, 'requires_vlan' => true],
            ['code' => 'GPON', 'name' => 'GPON', 'requires_router_mapping' => true, 'requires_radius' => true, 'requires_ip_assignment' => true, 'requires_vlan' => true],
            ['code' => 'WIRELESS', 'name' => 'Wireless', 'requires_router_mapping' => true, 'requires_radius' => true, 'requires_ip_assignment' => true, 'requires_vlan' => false],
            ['code' => 'CLOUD', 'name' => 'Cloud', 'requires_router_mapping' => false, 'requires_radius' => false, 'requires_ip_assignment' => false, 'requires_vlan' => false],
            ['code' => 'DOMAIN', 'name' => 'Domain', 'requires_router_mapping' => false, 'requires_radius' => false, 'requires_ip_assignment' => false, 'requires_vlan' => false],
        ])->map(fn ($category) => ServiceCategory::updateOrCreate(
            ['tenant_id' => $tenant->id, 'code' => $category['code']],
            ['tenant_id' => $tenant->id] + $category
        ));

        $radiusServer = RadiusServer::updateOrCreate(
            ['tenant_id' => $tenant->id, 'name' => 'FreeRadius RND'],
            [
                'host' => '10.20.1.19',
                'auth_port' => 1812,
                'acct_port' => 1813,
                'shared_secret' => 'testing123',
                'status' => 'active',
            ]
        );

        $router = Router::updateOrCreate(
            ['tenant_id' => $tenant->id, 'hostname' => 'mt-test-public-01'],
            [
                'router_name' => 'MikroTik Public Test',
                'vendor' => 'MikroTik',
                'router_role' => 'pppoe_router',
                'management_ip' => '103.142.202.226',
                'public_ip' => '103.142.202.226',
                'status' => 'active',
                'snmp_status' => 'not_configured',
            ]
        );

        NasDevice::updateOrCreate(
            ['tenant_id' => $tenant->id, 'nas_ip_address' => '103.142.202.226'],
            [
                'radius_server_id' => $radiusServer->id,
                'router_id' => $router->id,
                'hostname' => 'mt-test-public-01',
                'vendor_type' => 'mikrotik',
                'secret' => 'testing123',
                'status' => 'active',
            ]
        );
    }
}
