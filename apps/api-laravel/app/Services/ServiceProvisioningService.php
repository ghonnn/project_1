<?php

namespace App\Services;

use App\Models\CustomerRouterMapping;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\RadiusProfile;
use App\Models\RadiusServer;
use App\Models\RadiusUser;
use App\Models\Router;
use App\Models\RouterInterface;
use App\Models\Service;
use App\Models\ServiceRouterMapping;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ServiceProvisioningService
{
    public function __construct(
        private readonly FreeRadiusService $freeRadius,
        private readonly RouterProvisioningService $routerProvisioning,
    ) {}

    /**
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    public function provision(Service $service, array $data): array
    {
        $service->loadMissing(['customer', 'product', 'serviceCategory', 'addons']);

        if (! $service->product) {
            throw ValidationException::withMessages([
                'product_id' => ['Profile Langganan wajib dipilih sebelum provisioning.'],
            ]);
        }

        return DB::transaction(function () use ($service, $data): array {
            $router = Router::query()
                ->where('tenant_id', $service->tenant_id)
                ->findOrFail($data['router_id']);

            $radiusServer = ($data['radius_server_id'] ?? null)
                ? RadiusServer::query()
                    ->where('tenant_id', $service->tenant_id)
                    ->where('status', 'active')
                    ->findOrFail($data['radius_server_id'])
                : $this->routerProvisioning->radiusServerForRouter($router);

            if ($this->requiresRadius($service) && ! $radiusServer) {
                throw ValidationException::withMessages([
                    'provision_radius_server_id' => ['Radius server aktif wajib dipilih untuk layanan PPPoE/Hotspot/WiFi.'],
                ]);
            }

            $interfaceId = $data['interface_id'] ?? null;
            if ($interfaceId) {
                RouterInterface::query()
                    ->where('tenant_id', $service->tenant_id)
                    ->where('router_id', $router->id)
                    ->findOrFail($interfaceId);
            }

            $radiusProfile = $this->syncRadiusProfile($service);

            $mapping = ServiceRouterMapping::query()->updateOrCreate(
                [
                    'tenant_id' => $service->tenant_id,
                    'service_id' => $service->id,
                    'router_id' => $router->id,
                ],
                [
                    'interface_id' => $interfaceId,
                    'vlan_id' => $data['vlan_id'] ?? null,
                    'is_primary' => true,
                ]
            );

            CustomerRouterMapping::query()->firstOrCreate([
                'tenant_id' => $service->tenant_id,
                'customer_id' => $service->customer_id,
                'router_id' => $router->id,
            ]);

            $nasDevice = $radiusServer
                ? $this->routerProvisioning->ensureNasDevice($router, $radiusServer, $router->radius_secret)
                : null;

            $username = trim((string) ($data['username'] ?: $service->internet_username ?: $service->cid));
            $password = trim((string) ($data['password'] ?: $service->internet_password ?: $this->defaultPassword()));

            $radiusUser = RadiusUser::query()->updateOrCreate(
                [
                    'tenant_id' => $service->tenant_id,
                    'service_id' => $service->id,
                ],
                [
                    'customer_id' => $service->customer_id,
                    'router_id' => $router->id,
                    'profile_id' => $radiusProfile->id,
                    'username' => $username,
                    'secret' => $password,
                    'status' => 'active',
                ]
            );

            $dpp = (float) ($service->dpp_amount ?: $service->product->hpp ?: $service->product->price);
            $ppnRate = (float) ($service->ppn_rate ?: 11);
            $ppnEnabled = (bool) $service->ppn_enabled;
            $profilePrice = (float) ($service->profile_price ?: ($ppnEnabled ? round($dpp + ($dpp * ($ppnRate / 100))) : $dpp));

            $service->update([
                'status' => 'active',
                'activated_at' => Carbon::now(),
                'billing_active_date' => $service->billing_active_date ?: Carbon::now()->toDateString(),
                'internet_username' => $username,
                'internet_password' => $password,
                'billing_profile_name' => $service->product->name,
                'billing_cycle' => $service->product->billing_cycle,
                'dpp_amount' => $dpp,
                'ppn_rate' => $ppnRate,
                'profile_price' => $profilePrice,
                'ppn_enabled' => $ppnEnabled,
            ]);

            $syncLog = $this->freeRadius->syncUser($radiusUser->fresh());
            $invoice = ($data['create_invoice'] ?? true) ? $this->createInitialInvoice($service->fresh(['product', 'addons'])) : null;

            return [
                'service' => $service->fresh(['routerMappings', 'radiusUsers', 'product']),
                'mapping' => $mapping,
                'radius_user' => $radiusUser->fresh(),
                'radius_sync_log' => $syncLog,
                'nas_device' => $nasDevice,
                'invoice' => $invoice?->fresh('items'),
            ];
        });
    }

    private function syncRadiusProfile(Service $service): RadiusProfile
    {
        $product = $service->product;

        return RadiusProfile::query()->updateOrCreate(
            [
                'tenant_id' => $service->tenant_id,
                'name' => $product->name,
            ],
            [
                'attributes' => [
                    'Mikrotik-Group' => $product->mikrotik_group,
                    'Mikrotik-Rate-Limit' => $product->mikrotik_rate_limit,
                    'Shared-Users' => $product->shared_users,
                    'Active-Days' => $product->active_days,
                    'Profile-Price' => $product->price,
                    'Profile-HPP' => $product->hpp,
                    'Profile-PPN-Enabled' => $product->ppn_enabled,
                    'Profile-Commission' => $product->commission,
                ],
            ]
        );
    }

    private function createInitialInvoice(Service $service): Invoice
    {
        $total = (float) $service->profile_price;

        foreach ($service->addons->where('status', 'active') as $addon) {
            $total += (float) $addon->monthly_amount;
        }

        $invoice = Invoice::query()->create([
            'tenant_id' => $service->tenant_id,
            'customer_id' => $service->customer_id,
            'invoice_number' => 'INV-'.now()->format('YmdHis').'-'.random_int(100, 999),
            'issue_date' => $service->invoice_issue_date?->toDateString() ?? now()->toDateString(),
            'due_date' => $service->billing_isolation_date?->toDateString() ?? now()->addDays(14)->toDateString(),
            'status' => 'issued',
            'total_amount' => $total,
        ]);

        InvoiceItem::query()->create([
            'tenant_id' => $service->tenant_id,
            'invoice_id' => $invoice->id,
            'service_id' => $service->id,
            'description' => 'Langganan: '.$service->billing_profile_name,
            'quantity' => 1,
            'unit_amount' => $service->profile_price,
            'total_amount' => $service->profile_price,
        ]);

        foreach ($service->addons->where('status', 'active') as $addon) {
            InvoiceItem::query()->create([
                'tenant_id' => $service->tenant_id,
                'invoice_id' => $invoice->id,
                'service_id' => $service->id,
                'description' => 'Addon: '.$addon->name,
                'quantity' => $addon->quantity,
                'unit_amount' => $addon->unit_price,
                'total_amount' => $addon->monthly_amount,
            ]);
        }

        return $invoice;
    }

    private function defaultPassword(): string
    {
        return strtoupper(substr(bin2hex(random_bytes(4)), 0, 8));
    }

    private function requiresRadius(Service $service): bool
    {
        $connectionType = strtoupper((string) $service->connection_type);

        return (bool) $service->serviceCategory?->requires_radius
            || in_array($connectionType, ['PPP', 'PPPOE', 'HOTSPOT', 'WIFI'], true);
    }
}
