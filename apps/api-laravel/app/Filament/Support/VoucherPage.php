<?php

namespace App\Filament\Support;

use App\Models\HotspotTemplate;
use App\Models\HotspotVoucher;
use App\Models\RadiusProfile;
use App\Models\RadiusServer;
use App\Models\Router;
use App\Models\Tenant;
use App\Services\HotspotVoucherService;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\HtmlString;
use Symfony\Component\HttpFoundation\StreamedResponse;

abstract class VoucherPage extends Page
{
    protected static ?string $navigationGroup = 'Voucher';

    protected static ?string $navigationIcon = 'heroicon-o-wifi';

    protected static string $view = 'filament.pages.voucher-module';

    protected string $pageType = 'profile';

    public array $profileForm = [];

    public array $voucherForm = [];

    public array $templateForm = [];

    public ?string $lastBatchCode = null;

    public function mount(): void
    {
        $tenantId = $this->defaultTenantId();

        $this->profileForm = [
            'tenant_id' => $tenantId,
            'name' => 'Voucher 1 Jam',
            'group' => 'NEX-HOTSPOT-1J',
            'address_list' => 'HOTSPOT-ACTIVE',
            'rate_limit' => '5M/5M',
            'shared_users' => 1,
            'quota_mb' => 0,
            'duration_minutes' => 60,
            'active_days' => 1,
            'hpp' => 0,
            'commission' => 0,
            'price' => 5000,
            'price_includes_ppn' => 'yes',
            'status' => 'active',
        ];

        $this->voucherForm = [
            'tenant_id' => $tenantId,
            'profile_id' => null,
            'router_id' => null,
            'radius_server_id' => null,
            'qty' => 10,
            'prefix' => 'NEX',
            'password_length' => 6,
            'batch_code' => '',
            'partner_name' => '',
            'outlet_name' => '',
            'hpp' => 0,
            'commission' => 0,
            'price' => 5000,
            'price_includes_ppn' => 'yes',
        ];

        $this->templateForm = [
            'tenant_id' => $tenantId,
            'name' => 'NEX Default Login',
            'hotspot_name' => 'NEX ISP Hotspot',
            'dns_name' => 'wifi.nex.local',
            'support_phone' => '082170000000',
            'status' => 'active',
            'html_body' => app(HotspotVoucherService::class)->defaultTemplate(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function getViewData(): array
    {
        return [
            'title' => static::getNavigationLabel(),
            'pageType' => $this->pageType,
        ];
    }

    public function headingTitle(): string
    {
        return match ($this->pageType) {
            'stock' => 'Stok Voucher',
            'sold' => 'Voucher Terjual',
            'online' => 'Voucher Online',
            'recap' => 'Rekap Voucher',
            'template' => 'Template Hotspot',
            default => 'Profil Voucher',
        };
    }

    /** @return array<int, array{label: string, value: string, icon: string, color: string}> */
    public function stats(): array
    {
        $tenantId = $this->selectedTenantId();

        $stock = HotspotVoucher::query()->where('tenant_id', $tenantId)->where('status', 'stock');
        $sold = HotspotVoucher::query()->where('tenant_id', $tenantId)->where('status', 'sold');

        return match ($this->pageType) {
            'stock' => [
                ['label' => 'Total Stok', 'value' => (string) $stock->count(), 'icon' => 'heroicon-o-ticket', 'color' => '#0ea5e9'],
                ['label' => 'Nilai Stok', 'value' => $this->rupiah((float) $stock->sum('price')), 'icon' => 'heroicon-o-banknotes', 'color' => '#22c55e'],
                ['label' => 'Batch Terakhir', 'value' => $this->lastBatchCode ?: '-', 'icon' => 'heroicon-o-queue-list', 'color' => '#f59e0b'],
                ['label' => 'Synced Radius', 'value' => (string) HotspotVoucher::where('tenant_id', $tenantId)->whereNotNull('synced_at')->count(), 'icon' => 'heroicon-o-signal', 'color' => '#06b6d4'],
            ],
            'sold' => [
                ['label' => 'Jumlah Terjual', 'value' => (string) $sold->count(), 'icon' => 'heroicon-o-shopping-cart', 'color' => '#0ea5e9'],
                ['label' => 'Total Penjualan', 'value' => $this->rupiah((float) $sold->sum('price')), 'icon' => 'heroicon-o-banknotes', 'color' => '#22c55e'],
                ['label' => 'Komisi', 'value' => $this->rupiah((float) $sold->sum('commission')), 'icon' => 'heroicon-o-currency-dollar', 'color' => '#06b6d4'],
                ['label' => 'Expired', 'value' => (string) HotspotVoucher::where('tenant_id', $tenantId)->where('status', 'expired')->count(), 'icon' => 'heroicon-o-calendar-date-range', 'color' => '#ef4444'],
            ],
            'online' => [
                ['label' => 'Voucher Online', 'value' => (string) $this->onlineRows()->count(), 'icon' => 'heroicon-o-wifi', 'color' => '#22c55e'],
                ['label' => 'FreeRadius', 'value' => '10.20.1.19 / 103.142.202.19', 'icon' => 'heroicon-o-server', 'color' => '#0ea5e9'],
            ],
            default => [],
        };
    }

    public function saveProfile(): void
    {
        $data = $this->profileForm;
        $hpp = $this->parseMoney($data['hpp'] ?? 0);
        $commission = $this->parseMoney($data['commission'] ?? 0);
        $price = $this->parseMoney($data['price'] ?? 0);
        $priceIncludesPpn = ($data['price_includes_ppn'] ?? 'yes') === 'yes';
        $tax = $this->taxBreakdown($price, $priceIncludesPpn);

        $profile = RadiusProfile::updateOrCreate(
            ['tenant_id' => $data['tenant_id'], 'name' => $data['name']],
            [
                'attributes' => [
                    'Profile-Type' => 'hotspot_voucher',
                    'Mikrotik-Group' => $data['group'],
                    'Mikrotik-Address-List' => $data['address_list'],
                    'Mikrotik-Rate-Limit' => $data['rate_limit'],
                    'Shared-Users' => $data['shared_users'],
                    'Quota-MB' => $data['quota_mb'],
                    'Duration-Minutes' => $data['duration_minutes'],
                    'Active-Days' => $data['active_days'],
                    'HPP' => $hpp,
                    'HPP-Includes-PPN' => 'no',
                    'Commission' => $commission,
                    'Price' => $price,
                    'Price-Includes-PPN' => $priceIncludesPpn ? 'yes' : 'no',
                    'DPP' => $tax['dpp'],
                    'PPN' => $tax['ppn'],
                    'Status' => $data['status'],
                ],
            ]
        );

        app(HotspotVoucherService::class)->syncProfile($profile);

        Notification::make()->title('Profile voucher tersimpan dan disync ke FreeRadius')->success()->send();
    }

    public function generateVouchers(): void
    {
        $data = $this->voucherForm;
        $data['batch_code'] = $data['batch_code'] ?: now('Asia/Jakarta')->format('YmdHis');
        $data['hpp'] = $this->parseMoney($data['hpp'] ?? 0);
        $data['commission'] = $this->parseMoney($data['commission'] ?? 0);
        $data['price'] = $this->parseMoney($data['price'] ?? 0);
        $data['price_includes_ppn'] = $data['price_includes_ppn'] ?? 'yes';

        $vouchers = app(HotspotVoucherService::class)->generate($data);
        $this->lastBatchCode = $data['batch_code'];

        Notification::make()
            ->title(count($vouchers).' voucher berhasil dibuat')
            ->body('User/password sudah disimpan dan disync ke FreeRadius SQL.')
            ->success()
            ->send();
    }

    public function markSold(string $voucherId): void
    {
        $voucher = HotspotVoucher::with('profile')->findOrFail($voucherId);
        $activeDays = (int) ($voucher->profile?->attributes['Active-Days'] ?? 0);

        $voucher->update([
            'status' => 'sold',
            'activated_at' => now(),
            'expires_at' => $activeDays > 0 ? now()->addDays($activeDays) : null,
        ]);

        Notification::make()->title('Voucher ditandai terjual')->success()->send();
    }

    public function saveTemplate(): void
    {
        HotspotTemplate::updateOrCreate(
            ['tenant_id' => $this->templateForm['tenant_id'], 'name' => $this->templateForm['name']],
            $this->templateForm
        );

        Notification::make()->title('Template hotspot tersimpan')->success()->send();
    }

    public function exportCsv(): StreamedResponse
    {
        $rows = $this->voucherRows();
        $filename = 'nex-hotspot-vouchers-'.now('Asia/Jakarta')->format('Ymd-His').'.csv';

        return response()->streamDownload(function () use ($rows): void {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Username', 'Password', 'Profile', 'Router', 'Server', 'Partner', 'Outlet', 'Harga', 'Status', 'Batch']);

            foreach ($rows as $row) {
                fputcsv($handle, [
                    $row->username,
                    $row->password,
                    $row->profile?->name,
                    $row->router?->router_name,
                    $row->radiusServer?->name,
                    $row->partner_name,
                    $row->outlet_name,
                    $row->price,
                    $row->status,
                    $row->batch_code,
                ]);
            }

            fclose($handle);
        }, $filename);
    }

    public function downloadPrintHtml(): StreamedResponse
    {
        $rows = $this->voucherRows()->take(120);
        $html = view('exports.hotspot-vouchers-print', ['vouchers' => $rows])->render();

        return response()->streamDownload(fn () => print($html), 'nex-voucher-print.html');
    }

    public function downloadTemplateHtml(): StreamedResponse
    {
        $template = HotspotTemplate::where('tenant_id', $this->selectedTenantId())->latest()->first();

        if (! $template) {
            $template = new HotspotTemplate($this->templateForm);
        }

        $html = app(HotspotVoucherService::class)->renderTemplate($template);

        return response()->streamDownload(fn () => print($html), 'login.html');
    }

    /** @return array<string, string> */
    public function tenantOptions(): array
    {
        return Tenant::orderBy('name')->pluck('name', 'id')->all();
    }

    /** @return array<string, string> */
    public function profileOptions(): array
    {
        return $this->voucherProfileQuery()->orderBy('name')->pluck('name', 'id')->all();
    }

    /** @return array<string, string> */
    public function routerOptions(): array
    {
        return Router::where('tenant_id', $this->selectedTenantId())->orderBy('router_name')->pluck('router_name', 'id')->all();
    }

    /** @return array<string, string> */
    public function radiusServerOptions(): array
    {
        return RadiusServer::where('tenant_id', $this->selectedTenantId())->orderBy('name')->pluck('name', 'id')->all();
    }

    public function profileRows()
    {
        return $this->voucherProfileQuery()->latest()->limit(50)->get();
    }

    public function voucherRows()
    {
        return HotspotVoucher::with(['profile', 'router', 'radiusServer'])
            ->where('tenant_id', $this->selectedTenantId())
            ->when($this->pageType === 'stock', fn ($query) => $query->where('status', 'stock'))
            ->when($this->pageType === 'sold', fn ($query) => $query->whereIn('status', ['sold', 'expired']))
            ->latest()
            ->limit(200)
            ->get();
    }

    public function templateRows()
    {
        return HotspotTemplate::where('tenant_id', $this->selectedTenantId())->latest()->limit(20)->get();
    }

    public function onlineRows()
    {
        if (! Schema::hasTable('radacct')) {
            return collect();
        }

        return HotspotVoucher::query()
            ->select('hotspot_vouchers.*', 'radacct.framedipaddress', 'radacct.callingstationid', 'radacct.acctstarttime')
            ->join('radacct', 'radacct.username', '=', 'hotspot_vouchers.username')
            ->where('hotspot_vouchers.tenant_id', $this->selectedTenantId())
            ->whereNull('radacct.acctstoptime')
            ->latest('radacct.acctstarttime')
            ->limit(100)
            ->get();
    }

    public function recapRows(): array
    {
        return HotspotVoucher::query()
            ->where('tenant_id', $this->selectedTenantId())
            ->where('status', 'sold')
            ->selectRaw('date(activated_at) as sale_date, partner_name, outlet_name, profile_id, count(*) as qty, sum(hpp) as hpp, sum(commission) as commission, sum(price) as price')
            ->groupByRaw('date(activated_at), partner_name, outlet_name, profile_id')
            ->latest('sale_date')
            ->limit(100)
            ->get()
            ->all();
    }

    public function columns(): array
    {
        return match ($this->pageType) {
            'profile' => ['Nama Profile', 'Group', 'Rate Limit', 'Shared', 'Kuota', 'Durasi', 'HPP', 'DPP', 'PPN', 'Harga', 'Status'],
            'stock' => ['Username', 'Password', 'Profile', 'Router', 'Server', 'Partner', 'Outlet', 'Harga', 'Status', 'Sync'],
            'sold' => ['Username', 'Profile', 'Partner', 'Outlet', 'Harga', 'Aktif', 'Expired', 'MAC'],
            'online' => ['Username', 'IP Address', 'MAC Address', 'Uptime', 'Profile'],
            'recap' => ['Tanggal', 'Partner', 'Outlet', 'Profile', 'Qty', 'HPP', 'Komisi', 'Harga'],
            default => ['Nama Template', 'Hotspot', 'DNS', 'Phone', 'Status'],
        };
    }

    public function rupiah(float $value): string
    {
        return 'Rp'.number_format($value, 0, ',', '.');
    }

    /**
     * @return array{dpp: float, ppn: float, total: float}
     */
    public function taxBreakdown(float $price, bool $priceIncludesPpn = true): array
    {
        if ($priceIncludesPpn) {
            $dpp = round($price / 1.11, 2);
            $ppn = round($price - $dpp, 2);

            return ['dpp' => $dpp, 'ppn' => $ppn, 'total' => $price];
        }

        $ppn = round($price * 0.11, 2);

        return ['dpp' => $price, 'ppn' => $ppn, 'total' => round($price + $ppn, 2)];
    }

    public function parseMoney(mixed $value): float
    {
        if (is_numeric($value)) {
            return (float) $value;
        }

        $normalized = preg_replace('/[^\d,]/', '', (string) $value) ?: '0';
        $normalized = str_replace(',', '.', str_replace('.', '', $normalized));

        return (float) $normalized;
    }

    private function voucherProfileQuery()
    {
        return RadiusProfile::query()
            ->where('tenant_id', $this->selectedTenantId())
            ->where('attributes->Profile-Type', 'hotspot_voucher');
    }

    protected function selectedTenantId(): ?string
    {
        return $this->voucherForm['tenant_id'] ?? $this->profileForm['tenant_id'] ?? $this->templateForm['tenant_id'] ?? $this->defaultTenantId();
    }

    protected function defaultTenantId(): ?string
    {
        return auth()->user()?->tenant_id ?: Tenant::query()->orderBy('name')->value('id');
    }

    public function printPreview(): HtmlString
    {
        return new HtmlString(view('exports.hotspot-vouchers-print', ['vouchers' => $this->voucherRows()->take(12)])->render());
    }
}
