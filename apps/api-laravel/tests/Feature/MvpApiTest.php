<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Product;
use App\Models\RadiusProfile;
use App\Models\RadiusServer;
use App\Models\RadiusSyncLog;
use App\Models\RadiusUser;
use App\Models\Router;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\ServicePlanChange;
use App\Models\ServiceRouterMapping;
use App\Models\Tenant;
use App\Models\User;
use App\Services\ServiceProvisioningService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MvpApiTest extends TestCase
{
    use RefreshDatabase;

    private string $token;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
        $this->token = $this->postJson('/api/v1/auth/login', [
            'email' => 'admin@nex.local',
            'password' => 'password',
        ])->json('data.access_token');
    }

    private function auth(): array
    {
        return ['Authorization' => 'Bearer '.$this->token];
    }

    private function tenant(): Tenant
    {
        return Tenant::where('slug', 'nex-demo-isp')->firstOrFail();
    }

    private function createCustomer(): Customer
    {
        return Customer::create([
            'tenant_id' => $this->tenant()->id,
            'type' => 'individual',
            'name' => 'Test Customer',
            'email' => 'customer@example.test',
        ]);
    }

    private function createService(string $categoryCode = 'BROADBAND'): Service
    {
        $tenant = $this->tenant();
        $category = ServiceCategory::where('tenant_id', $tenant->id)->where('code', $categoryCode)->firstOrFail();
        $product = Product::firstOrCreate(
            ['tenant_id' => $tenant->id, 'sku' => 'TEST-'.$categoryCode],
            [
                'service_category_id' => $category->id,
                'name' => 'Test '.$category->name,
                'mikrotik_group' => 'RLRADIUS',
                'mikrotik_rate_limit' => $categoryCode === 'BROADBAND' ? '100M/100M' : null,
                'shared_users' => 1,
                'active_days' => 30,
                'hpp' => 100000,
                'commission' => 0,
                'ppn_enabled' => false,
                'price' => 100000,
                'billing_cycle' => 'monthly',
                'status' => 'active',
            ]
        );

        return Service::create([
            'tenant_id' => $tenant->id,
            'customer_id' => $this->createCustomer()->id,
            'product_id' => $product->id,
            'service_category_id' => $category->id,
            'cid' => 'CID-'.fake()->unique()->numberBetween(1000, 9999),
        ]);
    }

    public function test_health_endpoint_works(): void
    {
        $this->getJson('/api/v1/health')
            ->assertOk()
            ->assertJsonPath('data.status', 'ok');
    }

    public function test_login_admin_works(): void
    {
        $this->assertNotEmpty($this->token);
    }

    public function test_tenant_isolation(): void
    {
        $otherTenant = Tenant::create(['name' => 'Other ISP', 'slug' => 'other-isp']);
        $tenantUser = User::create([
            'tenant_id' => $this->tenant()->id,
            'name' => 'Tenant User',
            'email' => 'tenant@example.test',
            'password' => 'password',
        ]);
        $token = $tenantUser->createToken('api')->plainTextToken;

        $this->getJson('/api/v1/tenants/'.$otherTenant->id.'/customers', [
            'Authorization' => 'Bearer '.$token,
        ])->assertForbidden();
    }

    public function test_customer_crud(): void
    {
        $tenant = $this->tenant();
        $created = $this->postJson('/api/v1/tenants/'.$tenant->id.'/customers', [
            'type' => 'business',
            'name' => 'ACME ISP Customer',
            'email' => 'acme@example.test',
        ], $this->auth())->assertCreated()->json('data');

        $this->putJson('/api/v1/tenants/'.$tenant->id.'/customers/'.$created['id'], [
            'name' => 'ACME Updated',
        ], $this->auth())->assertOk()->assertJsonPath('data.name', 'ACME Updated');
    }

    public function test_activation_without_router_mapping_rejected(): void
    {
        $service = $this->createService('BROADBAND');

        $this->patchJson('/api/v1/tenants/'.$this->tenant()->id.'/services/'.$service->id, [
            'action' => 'activate',
        ], $this->auth())->assertStatus(409);
    }

    public function test_activation_with_router_mapping_and_radius_user_succeeds(): void
    {
        $tenant = $this->tenant();
        $service = $this->createService('BROADBAND');
        $router = Router::where('tenant_id', $tenant->id)->firstOrFail();
        $profile = RadiusProfile::where('tenant_id', $tenant->id)->firstOrFail();

        $this->postJson('/api/v1/tenants/'.$tenant->id.'/services/'.$service->id.'/router-mapping', [
            'router_id' => $router->id,
        ], $this->auth())->assertCreated();

        RadiusUser::create([
            'tenant_id' => $tenant->id,
            'customer_id' => $service->customer_id,
            'service_id' => $service->id,
            'router_id' => $router->id,
            'profile_id' => $profile->id,
            'username' => 'cust-'.$service->id,
            'secret' => 'secret',
        ]);

        $this->patchJson('/api/v1/tenants/'.$tenant->id.'/services/'.$service->id, [
            'action' => 'activate',
        ], $this->auth())->assertOk()->assertJsonPath('data.status', 'active');
    }

    public function test_suspend_and_unsuspend_update_radius_user(): void
    {
        $tenant = $this->tenant();
        $service = $this->createService('BROADBAND');
        $router = Router::where('tenant_id', $tenant->id)->firstOrFail();
        $profile = RadiusProfile::where('tenant_id', $tenant->id)->firstOrFail();
        $radiusUser = RadiusUser::create([
            'tenant_id' => $tenant->id,
            'customer_id' => $service->customer_id,
            'service_id' => $service->id,
            'router_id' => $router->id,
            'profile_id' => $profile->id,
            'username' => 'suspend-test',
            'secret' => 'secret',
            'status' => 'active',
        ]);

        $service->update(['status' => 'active']);

        $this->patchJson('/api/v1/tenants/'.$tenant->id.'/services/'.$service->id, [
            'action' => 'suspend',
            'reason' => 'overdue',
        ], $this->auth())->assertOk();
        $this->assertSame('suspended', $radiusUser->fresh()->status);

        $this->patchJson('/api/v1/tenants/'.$tenant->id.'/services/'.$service->id, [
            'action' => 'unsuspend',
            'reason' => 'paid',
        ], $this->auth())->assertOk();
        $this->assertSame('active', $radiusUser->fresh()->status);
    }

    public function test_plan_change_updates_service_and_syncs_radius_profile(): void
    {
        $tenant = $this->tenant();
        $service = $this->createService('BROADBAND');
        $oldProfile = RadiusProfile::where('tenant_id', $tenant->id)->firstOrFail();
        $newProduct = Product::create([
            'tenant_id' => $tenant->id,
            'service_category_id' => $service->service_category_id,
            'name' => 'Upgrade 200M',
            'sku' => 'UPGRADE-200M',
            'mikrotik_group' => 'RLRADIUS',
            'mikrotik_rate_limit' => '200M/200M',
            'shared_users' => 1,
            'active_days' => 30,
            'hpp' => 200000,
            'commission' => 0,
            'ppn_enabled' => true,
            'price' => 222000,
            'billing_cycle' => 'monthly',
            'status' => 'active',
        ]);

        $radiusUser = RadiusUser::create([
            'tenant_id' => $tenant->id,
            'customer_id' => $service->customer_id,
            'service_id' => $service->id,
            'profile_id' => $oldProfile->id,
            'username' => 'plan-change-test',
            'secret' => 'secret',
            'status' => 'active',
        ]);

        ServicePlanChange::create([
            'tenant_id' => $tenant->id,
            'service_id' => $service->id,
            'old_product_id' => $service->product_id,
            'new_product_id' => $newProduct->id,
            'change_date' => now()->toDateString(),
            'change_type' => 'upgrade',
        ]);

        $newProfile = RadiusProfile::where('tenant_id', $tenant->id)->where('name', 'Upgrade 200M')->firstOrFail();

        $this->assertSame($newProduct->id, $service->fresh()->product_id);
        $this->assertSame($newProfile->id, $radiusUser->fresh()->profile_id);
        $this->assertDatabaseHas(RadiusSyncLog::class, [
            'tenant_id' => $tenant->id,
            'radius_user_id' => $radiusUser->id,
            'action' => 'sync',
        ]);
    }

    public function test_service_provisioning_connects_radius_router_and_billing(): void
    {
        $tenant = $this->tenant();
        $service = $this->createService('BROADBAND');
        $router = Router::where('tenant_id', $tenant->id)->firstOrFail();

        $result = app(ServiceProvisioningService::class)->provision($service, [
            'router_id' => $router->id,
            'username' => 'cust-provision',
            'password' => 'secret123',
            'create_invoice' => true,
        ]);

        $this->assertSame('active', $service->fresh()->status);
        $this->assertDatabaseHas(ServiceRouterMapping::class, [
            'tenant_id' => $tenant->id,
            'service_id' => $service->id,
            'router_id' => $router->id,
        ]);
        $this->assertDatabaseHas(RadiusUser::class, [
            'tenant_id' => $tenant->id,
            'service_id' => $service->id,
            'router_id' => $router->id,
            'username' => 'cust-provision',
            'status' => 'active',
        ]);
        $this->assertDatabaseHas(RadiusSyncLog::class, [
            'tenant_id' => $tenant->id,
            'action' => 'sync',
        ]);
        $this->assertNotNull($result['invoice']);
        $this->assertDatabaseHas(Invoice::class, [
            'tenant_id' => $tenant->id,
            'customer_id' => $service->customer_id,
            'status' => 'issued',
        ]);
    }

    public function test_invoice_only_for_active_service(): void
    {
        $tenant = $this->tenant();
        $service = $this->createService('CLOUD');

        $this->postJson('/api/v1/tenants/'.$tenant->id.'/invoices', [
            'customer_id' => $service->customer_id,
            'items' => [['service_id' => $service->id, 'description' => 'Cloud', 'quantity' => 1, 'unit_amount' => 100000]],
        ], $this->auth())->assertStatus(409);

        $service->update(['status' => 'active']);
        $this->postJson('/api/v1/tenants/'.$tenant->id.'/invoices', [
            'customer_id' => $service->customer_id,
            'items' => [['service_id' => $service->id, 'description' => 'Cloud', 'quantity' => 1, 'unit_amount' => 100000]],
        ], $this->auth())->assertCreated();
    }

    public function test_router_script_generator_contains_freeradius_host(): void
    {
        $tenant = $this->tenant();
        $router = Router::where('tenant_id', $tenant->id)->firstOrFail();
        $server = RadiusServer::where('tenant_id', $tenant->id)->firstOrFail();

        $this->postJson('/api/v1/tenants/'.$tenant->id.'/router-script-generator', [
            'router_id' => $router->id,
            'radius_server_id' => $server->id,
            'os_version' => 'ROS7',
            'script_type' => 'PPPoE',
            'service_profile' => '100M',
        ], $this->auth())->assertOk()->assertSee('10.20.1.19');
    }

    public function test_router_seed_contains_testing_public_ip(): void
    {
        $router = Router::where('hostname', 'mt-test-public-01')->firstOrFail();

        $this->assertSame('103.142.202.226', $router->public_ip);
    }

    public function test_radius_test_endpoint_returns_warning_safe(): void
    {
        $tenant = $this->tenant();
        $server = RadiusServer::where('tenant_id', $tenant->id)->firstOrFail();

        $this->postJson('/api/v1/tenants/'.$tenant->id.'/radius/servers/'.$server->id.'/test', [], $this->auth())
            ->assertOk()
            ->assertJsonPath('data.status', 'warning');
    }
}
