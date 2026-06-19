<?php

namespace Database\Seeders;

use App\Models\NasDevice;
use App\Models\Permission;
use App\Models\Product;
use App\Models\RadiusProfile;
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

        foreach (['tenant.manage', 'customer.manage', 'service.manage', 'router.manage', 'radius.manage', 'billing.manage', 'audit.read'] as $code) {
            [$module, $action] = explode('.', $code);
            Permission::updateOrCreate(['code' => $code], compact('module', 'action'));
        }

        $roles->each(fn (Role $role) => $role->permissions()->sync(Permission::pluck('id')));

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

        foreach ([
            ['BROADBAND', 'MD-70M', 'MD 70 Mbps', 450450, '70M/70M 85M/85M 55M/55M 10/10 1 35M/35M', 450450],
            ['BROADBAND', 'MD-60M', 'MD 60 Mbps', 382883, '60M/60M 72M/72M 48M/48M 10/10 1 30M/30M', 382883],
            ['BROADBAND', 'MD-35M', 'MD 35 Mbps', 292793, '35M/35M 42M/42M 20M/20M 10/10 1 18M/18M', 292793],
            ['BROADBAND', 'MD-25M', 'MD 25 Mbps', 238739, '25M/25M 30M/30M 20M/20M 10/10 1 12M/12M', 238739],
            ['BROADBAND', 'MD-15M', 'MD 15 Mbps', 180180, '15M/15M 18M/18M 12M/12M 10/10 1 8M/8M', 180180],
            ['BROADBAND', 'MD-10M', 'MD 10 Mbps', 148649, '10M/10M 12M/12M 8M/8M 10/10 1 5M/5M', 148649],
            ['BROADBAND', 'YD-70M', 'YD 70 Mbps', 450450, '70M/70M', 450450],
            ['BROADBAND', 'YD-60M', 'YD 60 Mbps', 382883, '60M/60M', 382883],
            ['BROADBAND', 'YD-35M', 'YD 35 Mbps', 292793, '35M/35M', 292793],
            ['BROADBAND', 'YD-25M', 'YD 25 Mbps', 238739, '25M/25M', 238739],
            ['BROADBAND', 'YD-15M', 'YD 15 Mbps', 180180, '15M/15M', 180180],
            ['BROADBAND', 'YD-10M', 'YD 10 Mbps', 148649, '10M/10M', 148649],
            ['CLOUD', 'VPS-BASIC', 'Cloud VPS Basic', 150000, null, 100000],
            ['DOMAIN', 'DOMAIN-HOSTING', 'Domain Hosting', 100000, null, 75000],
        ] as [$categoryCode, $sku, $name, $price, $rateLimit, $hpp]) {
            Product::updateOrCreate(
                ['tenant_id' => $tenant->id, 'sku' => $sku],
                [
                    'tenant_id' => $tenant->id,
                    'service_category_id' => $categories->firstWhere('code', $categoryCode)->id,
                    'name' => $name,
                    'mikrotik_group' => 'RLRADIUS',
                    'mikrotik_rate_limit' => $rateLimit,
                    'shared_users' => 1,
                    'active_days' => 30,
                    'hpp' => $hpp,
                    'commission' => 0,
                    'price' => $price,
                    'billing_cycle' => 'monthly',
                    'status' => 'active',
                    'pricing' => ['recurring' => ['amount' => $price, 'period' => 'month']],
                ]
            );
        }

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

        RadiusProfile::updateOrCreate(
            ['tenant_id' => $tenant->id, 'name' => '100M'],
            ['attributes' => ['Mikrotik-Rate-Limit' => '100M/100M']]
        );
    }
}
