<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\CustomerRouterMapping;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Payment;
use App\Models\RadiusProfile;
use App\Models\RadiusServer;
use App\Models\RadiusUser;
use App\Models\Router;
use App\Models\RouterInterface;
use App\Models\Service;
use App\Models\ServiceRouterMapping;
use App\Services\AuditService;
use App\Services\FreeRadiusService;
use App\Support\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class MvpController extends Controller
{
    use ApiResponse;

    public function __construct(private readonly AuditService $audit, private readonly FreeRadiusService $freeRadius) {}

    public function health()
    {
        return $this->ok([
            'app' => 'nex-oss-bss-api',
            'status' => 'ok',
            'freeradius_host' => config('services.freeradius.host'),
            'mikrotik_test_public_ip' => config('services.mikrotik.test_public_ip'),
        ]);
    }

    public function customers(Request $request, string $tenantId) { return $this->ok(Customer::where('tenant_id', $tenantId)->paginate($request->integer('limit', 20))); }

    public function storeCustomer(Request $request, string $tenantId)
    {
        $data = $request->validate(['type' => ['required', 'in:individual,business'], 'name' => ['required'], 'email' => ['nullable', 'email'], 'phone' => ['nullable'], 'billing_contact' => ['nullable', 'array']]);
        $customer = Customer::create(['tenant_id' => $tenantId] + $data);
        $this->audit->log('customer.created', 'customers', $customer->id, [], $customer->toArray(), $request);
        return $this->ok($customer, 'Created', [], 201);
    }

    public function showCustomer(string $tenantId, string $id) { return $this->ok(Customer::where('tenant_id', $tenantId)->findOrFail($id)); }

    public function updateCustomer(Request $request, string $tenantId, string $id)
    {
        $customer = Customer::where('tenant_id', $tenantId)->findOrFail($id);
        $old = $customer->toArray();
        $customer->update($request->only(['type', 'name', 'email', 'phone', 'status', 'billing_contact']));
        $this->audit->log('customer.updated', 'customers', $customer->id, $old, $customer->fresh()->toArray(), $request);
        return $this->ok($customer->fresh());
    }

    public function services(Request $request, string $tenantId) { return $this->ok(Service::with(['serviceCategory', 'routerMappings', 'radiusUsers'])->where('tenant_id', $tenantId)->paginate($request->integer('limit', 20))); }

    public function storeService(Request $request, string $tenantId)
    {
        $data = $request->validate(['customer_id' => ['required', 'uuid'], 'product_id' => ['nullable', 'uuid'], 'service_category_id' => ['nullable', 'uuid'], 'cid' => ['nullable'], 'metadata' => ['nullable', 'array']]);
        return $this->ok(Service::create(['tenant_id' => $tenantId] + $data), 'Created', [], 201);
    }

    public function showService(string $tenantId, string $id) { return $this->ok(Service::with(['serviceCategory', 'routerMappings', 'radiusUsers'])->where('tenant_id', $tenantId)->findOrFail($id)); }

    public function patchService(Request $request, string $tenantId, string $id)
    {
        $service = Service::with(['serviceCategory', 'routerMappings', 'radiusUsers'])->where('tenant_id', $tenantId)->findOrFail($id);
        return match ($request->input('action')) {
            'activate' => $this->activateService($request, $service),
            'suspend' => $this->suspendService($request, $service),
            'unsuspend' => $this->unsuspendService($request, $service),
            default => $this->updateService($request, $service),
        };
    }

    private function updateService(Request $request, Service $service)
    {
        $service->update($request->only(['cid', 'metadata', 'status']));
        return $this->ok($service->fresh());
    }

    private function activateService(Request $request, Service $service)
    {
        if ($service->serviceCategory?->requires_router_mapping && $service->routerMappings()->count() === 0) {
            return $this->fail('Router mapping required.', ['router_mapping' => ['Internet/network service requires router mapping.']], 409);
        }
        if ($service->serviceCategory?->requires_radius && $service->radiusUsers()->count() === 0) {
            return $this->fail('Radius user required.', ['radius_user' => ['Service requires Radius user.']], 409);
        }
        $service->update(['status' => 'active', 'activated_at' => Carbon::now()]);
        $service->radiusUsers()->update(['status' => 'active']);
        $this->audit->log('service.activated', 'services', $service->id, [], $service->fresh()->toArray(), $request);
        return $this->ok($service->fresh(['radiusUsers', 'routerMappings']));
    }

    private function suspendService(Request $request, Service $service)
    {
        $service->update(['status' => 'suspended', 'suspended_at' => Carbon::now()]);
        $service->radiusUsers->each(fn (RadiusUser $user) => $this->freeRadius->suspendUser($user));
        $this->audit->log('service.suspended', 'services', $service->id, [], ['reason' => $request->input('reason')], $request);
        return $this->ok($service->fresh(['radiusUsers']));
    }

    private function unsuspendService(Request $request, Service $service)
    {
        $service->update(['status' => 'active', 'suspended_at' => null]);
        $service->radiusUsers->each(fn (RadiusUser $user) => $this->freeRadius->activateUser($user));
        $this->audit->log('service.unsuspended', 'services', $service->id, [], ['reason' => $request->input('reason')], $request);
        return $this->ok($service->fresh(['radiusUsers']));
    }

    public function routers(Request $request, string $tenantId) { return $this->ok(Router::where('tenant_id', $tenantId)->paginate($request->integer('limit', 20))); }

    public function storeRouter(Request $request, string $tenantId)
    {
        $data = $request->validate(['router_name' => ['required'], 'hostname' => ['required'], 'vendor' => ['nullable'], 'model' => ['nullable'], 'serial_number' => ['nullable'], 'router_role' => ['required', 'in:core_router,aggregation_router,edge_router,pppoe_router,bng,wireless_gateway,pop_router,bts_router'], 'site_name' => ['nullable'], 'management_ip' => ['required'], 'public_ip' => ['nullable'], 'status' => ['nullable'], 'snmp_status' => ['nullable'], 'snmp_profile' => ['nullable', 'array']]);
        $router = Router::create(['tenant_id' => $tenantId] + $data);
        $this->audit->log('router.created', 'routers', $router->id, [], $router->toArray(), $request);
        return $this->ok($router, 'Created', [], 201);
    }

    public function showRouter(string $tenantId, string $id) { return $this->ok(Router::with('interfaces')->where('tenant_id', $tenantId)->findOrFail($id)); }

    public function updateRouter(Request $request, string $tenantId, string $id)
    {
        $router = Router::where('tenant_id', $tenantId)->findOrFail($id);
        $old = $router->toArray();
        $router->update($request->only(['router_name', 'hostname', 'vendor', 'model', 'serial_number', 'router_role', 'site_name', 'management_ip', 'public_ip', 'status', 'snmp_status', 'snmp_profile']));
        $this->audit->log('router.updated', 'routers', $router->id, $old, $router->fresh()->toArray(), $request);
        return $this->ok($router->fresh());
    }

    public function routerInterfaces(string $tenantId) { return $this->ok(RouterInterface::where('tenant_id', $tenantId)->get()); }

    public function storeRouterInterface(Request $request, string $tenantId)
    {
        $data = $request->validate(['router_id' => ['required', 'uuid'], 'interface_name' => ['required'], 'interface_type' => ['nullable'], 'ip_address' => ['nullable'], 'vlan_id' => ['nullable', 'integer'], 'speed_mbps' => ['nullable', 'integer'], 'status' => ['nullable']]);
        Router::where('tenant_id', $tenantId)->findOrFail($data['router_id']);
        return $this->ok(RouterInterface::create(['tenant_id' => $tenantId] + $data), 'Created', [], 201);
    }

    public function mapServiceRouter(Request $request, string $tenantId, string $serviceId)
    {
        $service = Service::where('tenant_id', $tenantId)->findOrFail($serviceId);
        $data = $request->validate(['router_id' => ['required', 'uuid'], 'interface_id' => ['nullable', 'uuid'], 'vlan_id' => ['nullable', 'integer'], 'is_primary' => ['nullable', 'boolean']]);
        Router::where('tenant_id', $tenantId)->findOrFail($data['router_id']);
        if (! empty($data['interface_id'])) RouterInterface::where('tenant_id', $tenantId)->where('router_id', $data['router_id'])->findOrFail($data['interface_id']);
        $mapping = ServiceRouterMapping::create(['tenant_id' => $tenantId, 'service_id' => $service->id] + $data);
        CustomerRouterMapping::firstOrCreate(['tenant_id' => $tenantId, 'customer_id' => $service->customer_id, 'router_id' => $data['router_id']]);
        return $this->ok($mapping, 'Created', [], 201);
    }

    public function radiusServers(string $tenantId) { return $this->ok(RadiusServer::where('tenant_id', $tenantId)->get()); }
    public function showRadiusServer(string $tenantId, string $id) { return $this->ok(RadiusServer::where('tenant_id', $tenantId)->findOrFail($id)); }

    public function storeRadiusServer(Request $request, string $tenantId)
    {
        $data = $request->validate(['name' => ['required'], 'host' => ['required'], 'auth_port' => ['nullable', 'integer'], 'acct_port' => ['nullable', 'integer'], 'shared_secret' => ['required'], 'status' => ['nullable']]);
        return $this->ok(RadiusServer::create(['tenant_id' => $tenantId] + $data), 'Created', [], 201);
    }

    public function testRadiusServer(string $tenantId, string $id) { return $this->ok($this->freeRadius->testServerConnection(RadiusServer::where('tenant_id', $tenantId)->findOrFail($id))); }
    public function radiusProfiles(string $tenantId) { return $this->ok(RadiusProfile::where('tenant_id', $tenantId)->get()); }

    public function storeRadiusProfile(Request $request, string $tenantId)
    {
        $data = $request->validate(['name' => ['required'], 'attributes' => ['nullable', 'array']]);
        return $this->ok(RadiusProfile::create(['tenant_id' => $tenantId] + $data), 'Created', [], 201);
    }

    public function radiusUsers(string $tenantId) { return $this->ok(RadiusUser::where('tenant_id', $tenantId)->get()); }

    public function storeRadiusUser(Request $request, string $tenantId)
    {
        $data = $request->validate(['customer_id' => ['required', 'uuid'], 'service_id' => ['required', 'uuid'], 'router_id' => ['nullable', 'uuid'], 'profile_id' => ['nullable', 'uuid'], 'username' => ['required'], 'secret' => ['required'], 'status' => ['nullable']]);
        $service = Service::with('serviceCategory')->where('tenant_id', $tenantId)->findOrFail($data['service_id']);
        if ($service->serviceCategory?->requires_radius && empty($data['router_id'])) return $this->fail('Router required.', ['router_id' => ['Radius user for network service requires router.']], 409);
        return $this->ok(RadiusUser::create(['tenant_id' => $tenantId] + $data), 'Created', [], 201);
    }

    public function syncRadiusUser(string $tenantId, string $id) { return $this->ok($this->freeRadius->syncUser(RadiusUser::where('tenant_id', $tenantId)->findOrFail($id))); }
    public function suspendRadiusUser(string $tenantId, string $id) { return $this->ok($this->freeRadius->suspendUser(RadiusUser::where('tenant_id', $tenantId)->findOrFail($id))); }
    public function activateRadiusUser(string $tenantId, string $id) { return $this->ok($this->freeRadius->activateUser(RadiusUser::where('tenant_id', $tenantId)->findOrFail($id))); }

    public function generateRouterScript(Request $request, string $tenantId)
    {
        $data = $request->validate(['router_id' => ['required', 'uuid'], 'radius_server_id' => ['required', 'uuid'], 'os_version' => ['required', 'in:ROS6,ROS7'], 'script_type' => ['required', 'in:PPPoE,Hotspot'], 'service_profile' => ['nullable']]);
        $router = Router::where('tenant_id', $tenantId)->findOrFail($data['router_id']);
        $server = RadiusServer::where('tenant_id', $tenantId)->findOrFail($data['radius_server_id']);
        $service = strtolower($data['script_type']) === 'hotspot' ? 'hotspot' : 'ppp';
        $script = implode("\n", [
            sprintf('/radius add service=%s address=%s secret="%s" authentication-port=%d accounting-port=%d timeout=300ms', $service, $server->host, $server->shared_secret, $server->auth_port, $server->acct_port),
            $service === 'ppp' ? '/ppp aaa set use-radius=yes accounting=yes interim-update=5m' : '/ip hotspot profile set [ find default=yes ] use-radius=yes',
            sprintf('/system identity set name="%s"', $router->hostname),
        ]);
        $this->audit->log('router_script.generated', 'routers', $router->id, [], ['script_type' => $data['script_type']], $request);
        return $this->ok(['router_id' => $router->id, 'radius_server_id' => $server->id, 'os_version' => $data['os_version'], 'script_type' => $data['script_type'], 'service_profile' => $data['service_profile'] ?? null, 'script' => $script]);
    }

    public function invoices(Request $request, string $tenantId) { return $this->ok(Invoice::with('items')->where('tenant_id', $tenantId)->paginate($request->integer('limit', 20))); }

    public function storeInvoice(Request $request, string $tenantId)
    {
        $data = $request->validate(['customer_id' => ['required', 'uuid'], 'items' => ['required', 'array', 'min:1'], 'items.*.service_id' => ['required', 'uuid'], 'items.*.description' => ['required'], 'items.*.quantity' => ['required', 'numeric'], 'items.*.unit_amount' => ['required', 'numeric']]);
        return DB::transaction(function () use ($data, $tenantId) {
            $total = 0;
            $services = [];
            foreach ($data['items'] as $item) {
                $service = Service::with('addons')->where('tenant_id', $tenantId)->findOrFail($item['service_id']);
                if ($service->status !== 'active') return $this->fail('Invoice only allowed for active service.', ['service_id' => ['Service must be active.']], 409);
                $services[$service->id] = $service;
                $total += $item['quantity'] * $item['unit_amount'];
                foreach ($service->addons->where('status', 'active') as $addon) {
                    $total += (float) $addon->monthly_amount;
                }
            }
            $invoice = Invoice::create(['tenant_id' => $tenantId, 'customer_id' => $data['customer_id'], 'invoice_number' => 'INV-'.now()->format('YmdHis').'-'.random_int(100, 999), 'issue_date' => now()->toDateString(), 'due_date' => now()->addDays(14)->toDateString(), 'status' => 'issued', 'total_amount' => $total]);
            foreach ($data['items'] as $item) {
                InvoiceItem::create(['tenant_id' => $tenantId, 'invoice_id' => $invoice->id, 'service_id' => $item['service_id'], 'description' => $item['description'], 'quantity' => $item['quantity'], 'unit_amount' => $item['unit_amount'], 'total_amount' => $item['quantity'] * $item['unit_amount']]);
                foreach (($services[$item['service_id']]?->addons ?? collect())->where('status', 'active') as $addon) {
                    InvoiceItem::create(['tenant_id' => $tenantId, 'invoice_id' => $invoice->id, 'service_id' => $item['service_id'], 'description' => 'Addon: '.$addon->name, 'quantity' => $addon->quantity, 'unit_amount' => $addon->unit_price, 'total_amount' => $addon->monthly_amount]);
                }
            }
            return $this->ok($invoice->fresh('items'), 'Created', [], 201);
        });
    }

    public function storePayment(Request $request, string $tenantId)
    {
        $data = $request->validate(['invoice_id' => ['required', 'uuid'], 'amount' => ['required', 'numeric'], 'method' => ['nullable'], 'external_ref' => ['nullable']]);
        $invoice = Invoice::where('tenant_id', $tenantId)->findOrFail($data['invoice_id']);
        $payment = Payment::create(['tenant_id' => $tenantId, 'status' => 'reconciled', 'paid_at' => now()] + $data);
        $invoice->increment('paid_amount', $data['amount']);
        $invoice->update(['status' => $invoice->fresh()->paid_amount >= $invoice->total_amount ? 'paid' : 'partial_paid']);
        return $this->ok($payment, 'Created', [], 201);
    }
}
