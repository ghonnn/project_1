<?php

namespace App\Filament\Support;

use App\Models\HotspotTemplate;
use App\Models\HotspotVoucher;
use App\Models\RadiusProfile;
use App\Models\RadiusServer;
use App\Models\Router;
use App\Models\Tenant;
use App\Models\Mitra;
use App\Models\HotspotOutlet;
use App\Services\HotspotVoucherService;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\HtmlString;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\DB;

abstract class VoucherPage extends Page
{
    use WithFileUploads;

    protected static ?string $navigationGroup = 'Voucher';

    protected static ?string $navigationIcon = 'heroicon-o-wifi';

    protected static string $view = 'filament.pages.voucher-module';

    protected string $pageType = 'profile';

    public array $profileForm = [];

    public array $voucherForm = [];

    public array $templateForm = [];

    public array $userForm = [];

    public array $outletForm = [];

    public array $importForm = [];

    public array $exportForm = [];

    public $importFile = null;

    public $hotspotLogo = null;

    public array $selectedVouchers = [];

    public bool $selectAllVouchers = false;

    public ?string $targetRouterId = null;

    public ?string $editingProfileId = null;

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
            'commission' => 0,
            'dpp' => 5000,
            'status' => 'active',
            'color' => '#059669',
        ];

        $this->voucherForm = [
            'tenant_id' => $tenantId,
            'profile_id' => null,
            'router_id' => null,
            'radius_server_id' => null,
            'outlet_id' => null,
            'qty' => 10,
            'prefix' => 'NEX',
            'password_length' => 6,
            'batch_code' => '',
            'partner_name' => '',
            'outlet_name' => '',
            'commission' => 0,
            'dpp' => 5000,
        ];

        $template = HotspotTemplate::where('tenant_id', $tenantId)->latest()->first();
        $this->templateForm = [
            'tenant_id' => $tenantId,
            'name' => $template ? $template->name : 'NEX Default Login',
            'hotspot_name' => $template ? $template->hotspot_name : 'NEX ISP Hotspot',
            'dns_name' => $template ? $template->dns_name : 'wifi.nex.local',
            'support_phone' => $template ? $template->support_phone : '082170000000',
            'status' => $template ? $template->status : 'active',
            'html_body' => $template ? $template->html_body : app(HotspotVoucherService::class)->defaultTemplate(),
        ];

        $this->userForm = [
            'tenant_id' => $tenantId,
            'profile_id' => null,
            'router_id' => null,
            'radius_server_id' => null,
            'outlet_id' => null,
            'partner_id' => null,
            'potong_saldo' => 'no',
            'username' => '',
            'password' => '',
            'lock_mac' => 'no',
            'mac_address' => '',
            'hpp' => 0,
            'price' => 0,
            'commission' => 0,
        ];

        $this->outletForm = [
            'tenant_id' => $tenantId,
            'mitra_id' => null,
            'name' => '',
            'owner_name' => '',
            'phone' => '',
            'address' => '',
            'joined_at' => now()->format('Y-m-d'),
            'status' => 'active',
        ];

        $this->importForm = [
            'tenant_id' => $tenantId,
            'partner_id' => null,
            'potong_saldo' => 'no',
            'router_id' => null,
            'radius_server_id' => null,
            'outlet_id' => null,
            'lock_mac' => 'no',
            'profile_id' => null,
            'hpp' => 0,
            'price' => 0,
            'commission' => 0,
        ];

        $this->exportForm = [
            'tenant_id' => $tenantId,
            'partner_id' => null,
            'outlet_id' => null,
            'profile_id' => null,
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
                ['label' => 'Total Stok Voucher', 'value' => (string) $stock->count(), 'icon' => 'heroicon-o-ticket', 'color' => '#0ea5e9'],
                ['label' => 'Total HPP', 'value' => $this->rupiah((float) $stock->sum('hpp')), 'icon' => 'heroicon-o-calculator', 'color' => '#f59e0b'],
                ['label' => 'Total Komisi', 'value' => $this->rupiah((float) $stock->sum('commission')), 'icon' => 'heroicon-o-banknotes', 'color' => '#22c55e'],
                ['label' => 'Total Harga', 'value' => $this->rupiah((float) $stock->sum('price')), 'icon' => 'heroicon-o-circle-stack', 'color' => '#06b6d4'],
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
        $commission = $this->parseMoney($data['commission'] ?? 0);
        $dpp = $this->parseMoney($data['dpp'] ?? 0);
        $tax = $this->taxBreakdown($dpp);
        $isCreating = blank($this->editingProfileId);

        if ($isCreating && $this->voucherProfileQuery()->count() >= 100) {
            Notification::make()
                ->title('Maksimal 100 profile voucher per tenant')
                ->warning()
                ->send();

            return;
        }

        $payload = [
            'tenant_id' => $data['tenant_id'],
            'name' => substr((string) $data['name'], 0, 32),
            'attributes' => [
                'Profile-Type' => 'hotspot_voucher',
                'Mikrotik-Group' => substr((string) $data['group'], 0, 32),
                'Mikrotik-Address-List' => substr((string) $data['address_list'], 0, 32),
                'Mikrotik-Rate-Limit' => substr((string) $data['rate_limit'], 0, 32),
                'Shared-Users' => $data['shared_users'],
                'Quota-MB' => $data['quota_mb'],
                'Duration-Minutes' => $data['duration_minutes'],
                'Active-Days' => $data['active_days'],
                'Commission' => $commission,
                'Price' => $tax['total'],
                'Price-Includes-PPN' => 'yes',
                'DPP' => $tax['dpp'],
                'PPN' => $tax['ppn'],
                'Status' => $data['status'],
                'Color' => $data['color'] ?? '#059669',
            ],
        ];

        $profile = $isCreating
            ? RadiusProfile::create($payload)
            : tap($this->voucherProfileQuery()->findOrFail($this->editingProfileId))->update($payload);

        app(HotspotVoucherService::class)->syncProfile($profile);

        Notification::make()->title('Profile voucher tersimpan dan disync ke FreeRadius')->success()->send();
        $this->resetProfileForm();
    }

    public function editProfile(string $profileId): void
    {
        $profile = $this->voucherProfileQuery()->findOrFail($profileId);
        $attributes = $profile->attributes ?? [];

        $this->editingProfileId = $profile->id;
        $this->profileForm = [
            'tenant_id' => $profile->tenant_id,
            'name' => $profile->name,
            'group' => $attributes['Mikrotik-Group'] ?? '',
            'address_list' => $attributes['Mikrotik-Address-List'] ?? '',
            'rate_limit' => $attributes['Mikrotik-Rate-Limit'] ?? '',
            'shared_users' => $attributes['Shared-Users'] ?? 1,
            'quota_mb' => $attributes['Quota-MB'] ?? 0,
            'duration_minutes' => $attributes['Duration-Minutes'] ?? 60,
            'active_days' => $attributes['Active-Days'] ?? 1,
            'commission' => $this->rupiahInput((float) ($attributes['Commission'] ?? 0)),
            'dpp' => $this->rupiahInput((float) ($attributes['DPP'] ?? 0)),
            'status' => $attributes['Status'] ?? 'active',
            'color' => $attributes['Color'] ?? '#059669',
        ];
    }

    public function resetProfileForm(): void
    {
        $tenantId = $this->selectedTenantId();
        $this->editingProfileId = null;
        $this->profileForm = [
            'tenant_id' => $tenantId,
            'name' => '',
            'group' => '',
            'address_list' => 'HOTSPOT-ACTIVE',
            'rate_limit' => '5M/5M',
            'shared_users' => 1,
            'quota_mb' => 0,
            'duration_minutes' => 60,
            'active_days' => 1,
            'commission' => 0,
            'dpp' => 0,
            'status' => 'active',
            'color' => '#059669',
        ];
    }

    public function generateVouchers(): void
    {
        $data = $this->voucherForm;
        $data['batch_code'] = $data['batch_code'] ?: now('Asia/Jakarta')->format('YmdHis');
        $data['hpp'] = 0;
        $data['commission'] = $this->parseMoney($data['commission'] ?? 0);
        $data['dpp'] = $this->parseMoney($data['dpp'] ?? 0);
        $data['price'] = $this->taxBreakdown($data['dpp'])['total'];

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
            ->selectRaw('date(activated_at) as sale_date, partner_name, outlet_name, profile_id, count(*) as qty, sum(commission) as commission, sum(price) as price')
            ->groupByRaw('date(activated_at), partner_name, outlet_name, profile_id')
            ->latest('sale_date')
            ->limit(100)
            ->get()
            ->all();
    }

    public function columns(): array
    {
        return match ($this->pageType) {
            'profile' => ['Nama Profile', 'Group', 'Rate Limit', 'Shared', 'Kuota', 'Durasi', 'Harga DPP', 'PPN 11%', 'Harga Jual', 'Status'],
            'stock' => ['Username', 'Password', 'Profile', 'Router', 'Server', 'Partner', 'Outlet', 'Harga Jual', 'Status', 'Sync'],
            'sold' => ['Username', 'Profile', 'Partner', 'Outlet', 'Harga Jual', 'Aktif', 'Expired', 'MAC'],
            'online' => ['Username', 'IP Address', 'MAC Address', 'Uptime', 'Profile'],
            'recap' => ['Tanggal', 'Partner', 'Outlet', 'Profile', 'Qty', 'Komisi', 'Harga Jual'],
            default => ['Nama Template', 'Hotspot', 'DNS', 'Phone', 'Status'],
        };
    }

    public function rupiah(float $value): string
    {
        return 'Rp '.number_format($value, 0, ',', '.');
    }

    /**
     * @return array{dpp: float, ppn: float, total: float}
     */
    public function taxBreakdown(float $dpp): array
    {
        $dpp = max(0, $dpp);
        $ppn = round($dpp * 0.11, 2);

        return ['dpp' => $dpp, 'ppn' => $ppn, 'total' => round($dpp + $ppn, 2)];
    }

    public function parseMoney(mixed $value): float
    {
        if (is_int($value) || is_float($value)) {
            return (float) $value;
        }

        $value = trim((string) $value);

        if ($value === '') {
            return 0;
        }

        $hasDecimalComma = preg_match('/,\d{1,2}$/', $value) === 1;
        $normalized = preg_replace('/[^\d,.]/', '', $value) ?: '0';

        if ($hasDecimalComma) {
            $normalized = str_replace('.', '', $normalized);
            $normalized = str_replace(',', '.', $normalized);
        } else {
            $normalized = str_replace(['.', ','], '', $normalized);
        }

        return (float) $normalized;
    }

    public function rupiahInput(float $value): string
    {
        return number_format($value, 0, ',', '.');
    }

    public function activateProfile(string $profileId): void
    {
        $profile = RadiusProfile::findOrFail($profileId);
        $attributes = $profile->attributes;
        $attributes['Status'] = 'active';
        $profile->update(['attributes' => $attributes]);
        app(HotspotVoucherService::class)->syncProfile($profile);
        Notification::make()->title('Profile diaktifkan')->success()->send();
    }

    public function deactivateProfile(string $profileId): void
    {
        $profile = RadiusProfile::findOrFail($profileId);
        $attributes = $profile->attributes;
        $attributes['Status'] = 'inactive';
        $profile->update(['attributes' => $attributes]);
        app(HotspotVoucherService::class)->syncProfile($profile);
        Notification::make()->title('Profile dinonaktifkan')->success()->send();
    }

    public function deleteProfile(string $profileId): void
    {
        if (HotspotVoucher::where('profile_id', $profileId)->exists()) {
            Notification::make()->title('Profile tidak dapat dihapus karena masih digunakan oleh voucher')->danger()->send();
            return;
        }

        $profile = RadiusProfile::findOrFail($profileId);
        if (Schema::hasTable('radgroupreply')) {
            $groupName = $profile->attributes['Mikrotik-Group'] ?? $profile->name;
            DB::table('radgroupreply')->where('groupname', $groupName)->delete();
        }
        $profile->delete();
        Notification::make()->title('Profile dihapus')->success()->send();
    }

    public function updatedSelectAllVouchers($value): void
    {
        if ($value) {
            $this->selectedVouchers = $this->voucherRows()->pluck('id')->map(fn($id) => (string)$id)->toArray();
        } else {
            $this->selectedVouchers = [];
        }
    }

    public function lockMacForSelected(): void
    {
        if (empty($this->selectedVouchers)) {
            Notification::make()->title('Pilih voucher terlebih dahulu')->warning()->send();
            return;
        }

        $count = 0;
        foreach ($this->selectedVouchers as $id) {
            $voucher = HotspotVoucher::find($id);
            if ($voucher) {
                $lastMac = null;
                if (Schema::hasTable('radacct')) {
                    $lastMac = DB::table('radacct')
                        ->where('username', $voucher->username)
                        ->whereNotNull('callingstationid')
                        ->latest('acctstarttime')
                        ->value('callingstationid');
                }

                $macToLock = $lastMac ?: 'LOCK-PENDING';
                $voucher->update(['mac_address' => $macToLock]);
                app(HotspotVoucherService::class)->syncVoucher($voucher);
                $count++;
            }
        }

        Notification::make()->title("$count voucher berhasil di-lock MAC address-nya")->success()->send();
        $this->selectedVouchers = [];
        $this->selectAllVouchers = false;
    }

    public function unlockMacForSelected(): void
    {
        if (empty($this->selectedVouchers)) {
            Notification::make()->title('Pilih voucher terlebih dahulu')->warning()->send();
            return;
        }

        foreach ($this->selectedVouchers as $id) {
            $voucher = HotspotVoucher::find($id);
            if ($voucher) {
                $voucher->update(['mac_address' => null]);
                app(HotspotVoucherService::class)->syncVoucher($voucher);
            }
        }

        Notification::make()->title("MAC address terpilih berhasil di-unlock")->success()->send();
        $this->selectedVouchers = [];
        $this->selectAllVouchers = false;
    }

    public function setActiveForSelected(): void
    {
        if (empty($this->selectedVouchers)) {
            Notification::make()->title('Pilih voucher terlebih dahulu')->warning()->send();
            return;
        }

        foreach ($this->selectedVouchers as $id) {
            $voucher = HotspotVoucher::find($id);
            if ($voucher) {
                $voucher->update(['status' => 'stock']);
                app(HotspotVoucherService::class)->syncVoucher($voucher);
            }
        }

        Notification::make()->title("Voucher terpilih diaktifkan")->success()->send();
        $this->selectedVouchers = [];
        $this->selectAllVouchers = false;
    }

    public function setInactiveForSelected(): void
    {
        if (empty($this->selectedVouchers)) {
            Notification::make()->title('Pilih voucher terlebih dahulu')->warning()->send();
            return;
        }

        foreach ($this->selectedVouchers as $id) {
            $voucher = HotspotVoucher::find($id);
            if ($voucher) {
                $voucher->update(['status' => 'inactive']);
                app(HotspotVoucherService::class)->syncVoucher($voucher);
            }
        }

        Notification::make()->title("Voucher terpilih dinonaktifkan")->success()->send();
        $this->selectedVouchers = [];
        $this->selectAllVouchers = false;
    }

    public function changeRouterForSelected(): void
    {
        if (empty($this->selectedVouchers)) {
            Notification::make()->title('Pilih voucher terlebih dahulu')->warning()->send();
            return;
        }
        if (!$this->targetRouterId) {
            Notification::make()->title('Pilih router tujuan terlebih dahulu')->warning()->send();
            return;
        }

        foreach ($this->selectedVouchers as $id) {
            $voucher = HotspotVoucher::find($id);
            if ($voucher) {
                $voucher->update(['router_id' => $this->targetRouterId]);
                app(HotspotVoucherService::class)->syncVoucher($voucher);
            }
        }

        Notification::make()->title("Router terpilih berhasil diubah")->success()->send();
        $this->selectedVouchers = [];
        $this->selectAllVouchers = false;
        $this->targetRouterId = null;
    }

    public function deleteSelectedVouchers(): void
    {
        if (empty($this->selectedVouchers)) {
            Notification::make()->title('Pilih voucher terlebih dahulu')->warning()->send();
            return;
        }

        foreach ($this->selectedVouchers as $id) {
            $voucher = HotspotVoucher::find($id);
            if ($voucher) {
                if (Schema::hasTable('radcheck')) {
                    DB::table('radcheck')->where('username', $voucher->username)->delete();
                    DB::table('radreply')->where('username', $voucher->username)->delete();
                    DB::table('radusergroup')->where('username', $voucher->username)->delete();
                }
                $voucher->delete();
            }
        }

        Notification::make()->title("Voucher terpilih berhasil dihapus")->success()->send();
        $this->selectedVouchers = [];
        $this->selectAllVouchers = false;
    }

    public function updatedUserFormProfileId($value): void
    {
        if ($value) {
            $profile = RadiusProfile::find($value);
            if ($profile) {
                $attributes = $profile->attributes ?? [];
                $this->userForm['hpp'] = $attributes['HPP'] ?? 0;
                $this->userForm['price'] = $attributes['Price'] ?? 0;
                $this->userForm['commission'] = $attributes['Commission'] ?? 0;
            }
        }
    }

    public function updatedImportFormProfileId($value): void
    {
        if ($value) {
            $profile = RadiusProfile::find($value);
            if ($profile) {
                $attributes = $profile->attributes ?? [];
                $this->importForm['hpp'] = $attributes['HPP'] ?? 0;
                $this->importForm['price'] = $attributes['Price'] ?? 0;
                $this->importForm['commission'] = $attributes['Commission'] ?? 0;
            }
        }
    }

    public function createUser(): void
    {
        $data = $this->userForm;
        $data['tenant_id'] = $this->selectedTenantId();

        if (empty($data['username']) || empty($data['password'])) {
            Notification::make()->title('Username dan password wajib diisi')->danger()->send();
            return;
        }

        if (($data['lock_mac'] ?? 'no') === 'yes' && empty($data['mac_address'])) {
            $data['mac_address'] = 'LOCK-ON-LOGIN';
        }

        // Format money
        $data['hpp'] = $this->parseMoney($data['hpp'] ?? 0);
        $data['commission'] = $this->parseMoney($data['commission'] ?? 0);
        $data['price'] = $this->parseMoney($data['price'] ?? 0);

        try {
            app(HotspotVoucherService::class)->generate($data);
            Notification::make()->title('User hotspot berhasil dibuat')->success()->send();
            $this->resetUserForm();
        } catch (\Exception $e) {
            Notification::make()->title($e->getMessage())->danger()->send();
        }
    }

    public function resetUserForm(): void
    {
        $this->userForm = [
            'tenant_id' => $this->selectedTenantId(),
            'profile_id' => null,
            'router_id' => null,
            'radius_server_id' => null,
            'outlet_id' => null,
            'partner_id' => null,
            'potong_saldo' => 'no',
            'username' => '',
            'password' => '',
            'lock_mac' => 'no',
            'mac_address' => '',
            'hpp' => 0,
            'price' => 0,
            'commission' => 0,
        ];
    }

    public function saveOutlet(): void
    {
        $data = $this->outletForm;
        $data['tenant_id'] = $this->selectedTenantId();
        $data['joined_at'] = $data['joined_at'] ?: now()->format('Y-m-d');

        HotspotOutlet::create($data);
        Notification::make()->title('Outlet berhasil ditambahkan')->success()->send();
        $this->resetOutletForm();
    }

    public function resetOutletForm(): void
    {
        $this->outletForm = [
            'tenant_id' => $this->selectedTenantId(),
            'mitra_id' => null,
            'name' => '',
            'owner_name' => '',
            'phone' => '',
            'address' => '',
            'joined_at' => now()->format('Y-m-d'),
            'status' => 'active',
        ];
    }

    public function activateOutlet(string $id): void
    {
        HotspotOutlet::findOrFail($id)->update(['status' => 'active']);
        Notification::make()->title('Outlet diaktifkan')->success()->send();
    }

    public function deactivateOutlet(string $id): void
    {
        HotspotOutlet::findOrFail($id)->update(['status' => 'inactive']);
        Notification::make()->title('Outlet dinonaktifkan')->success()->send();
    }

    public function deleteOutlet(string $id): void
    {
        HotspotOutlet::findOrFail($id)->delete();
        Notification::make()->title('Outlet dihapus')->success()->send();
    }

    public function saveHotspotSetting(): void
    {
        $data = $this->templateForm;
        $data['tenant_id'] = $this->selectedTenantId();

        if ($this->hotspotLogo) {
            $this->validate([
                'hotspotLogo' => 'image|max:100',
            ]);
            $logoPath = $this->hotspotLogo->store('hotspot-logos', 'public');
            $data['logo_path'] = $logoPath;
        }

        HotspotTemplate::updateOrCreate(
            ['tenant_id' => $data['tenant_id'], 'name' => $data['name']],
            $data
        );

        Notification::make()->title('Setting hotspot berhasil disimpan')->success()->send();
    }

    public function importVouchers(): void
    {
        if (!$this->importFile) {
            Notification::make()->title('Pilih file terlebih dahulu')->danger()->send();
            return;
        }

        $this->validate([
            'importFile' => 'file|mimes:csv,txt,json|max:2048',
        ]);

        $path = $this->importFile->getRealPath();
        $content = file_get_contents($path);
        $vouchersData = [];

        $extension = $this->importFile->getClientOriginalExtension();
        if ($extension === 'json') {
            $json = json_decode($content, true);
            if (is_array($json)) {
                $vouchersData = $json;
            }
        } else {
            $lines = explode("\n", $content);
            $header = null;
            foreach ($lines as $line) {
                $line = trim($line);
                if (empty($line)) continue;

                $cols = str_getcsv($line, ',');
                if (!$header) {
                    $header = array_map('strtolower', $cols);
                    continue;
                }

                if (count($cols) >= 2) {
                    $row = array_combine(array_slice($header, 0, count($cols)), $cols);
                    $vouchersData[] = [
                        'username' => $row['username'] ?? $cols[0],
                        'password' => $row['password'] ?? $cols[1],
                    ];
                }
            }
        }

        if (empty($vouchersData)) {
            Notification::make()->title('Format file tidak didukung atau kosong')->danger()->send();
            return;
        }

        $successCount = 0;
        $errorMessages = [];

        foreach ($vouchersData as $vData) {
            if (empty($vData['username']) || empty($vData['password'])) {
                continue;
            }

            $payload = array_merge($this->importForm, [
                'tenant_id' => $this->selectedTenantId(),
                'username' => $vData['username'],
                'password' => $vData['password'],
                'mac_address' => ($this->importForm['lock_mac'] === 'yes' ? 'PENDING' : null),
                'hpp' => $this->parseMoney($this->importForm['hpp'] ?? 0),
                'price' => $this->parseMoney($this->importForm['price'] ?? 0),
                'commission' => $this->parseMoney($this->importForm['commission'] ?? 0),
            ]);

            try {
                app(HotspotVoucherService::class)->generate($payload);
                $successCount++;
            } catch (\Exception $e) {
                $errorMessages[] = $vData['username'] . ': ' . $e->getMessage();
            }
        }

        if ($successCount > 0) {
            Notification::make()->title("$successCount voucher berhasil di-import")->success()->send();
        }

        if (!empty($errorMessages)) {
            Notification::make()
                ->title("Beberapa voucher gagal di-import")
                ->body(implode("\n", array_slice($errorMessages, 0, 5)))
                ->danger()
                ->send();
        }

        $this->importFile = null;
    }

    public function downloadImportFormat(): StreamedResponse
    {
        $filename = 'nex-voucher-import-format.csv';
        return response()->streamDownload(function (): void {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['username', 'password']);
            fputcsv($handle, ['NEX123456', '889922']);
            fputcsv($handle, ['NEX789012', '771133']);
            fclose($handle);
        }, $filename);
    }

    public function exportVouchers(): StreamedResponse
    {
        $partner = $this->exportForm['partner_id'] ? Mitra::find($this->exportForm['partner_id'])?->name : null;
        $outletId = $this->exportForm['outlet_id'];
        $profileId = $this->exportForm['profile_id'];

        $query = HotspotVoucher::with(['profile', 'router', 'radiusServer'])
            ->where('tenant_id', $this->selectedTenantId());

        if ($partner) {
            $query->where('partner_name', $partner);
        }
        if ($outletId) {
            $query->where('outlet_id', $outletId);
        }
        if ($profileId) {
            $query->where('profile_id', $profileId);
        }

        $rows = $query->get();
        $filename = 'nex-hotspot-vouchers-export-'.now('Asia/Jakarta')->format('Ymd-His').'.csv';

        return response()->streamDownload(function () use ($rows): void {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($handle, ['Username', 'Password', 'Profile', 'Router', 'Server', 'Partner', 'Outlet', 'Harga', 'Status', 'MAC Address']);

            foreach ($rows as $row) {
                fputcsv($handle, [
                    $row->username,
                    $row->password,
                    $row->profile?->name,
                    $row->router?->router_name,
                    $row->radiusServer?->name,
                    $row->partner_name,
                    $row->outlet_name ?: ($row->outlet?->name),
                    $row->price,
                    $row->status,
                    $row->mac_address,
                ]);
            }

            fclose($handle);
        }, $filename);
    }

    public function outletRows()
    {
        return HotspotOutlet::with('mitra')
            ->where('tenant_id', $this->selectedTenantId())
            ->latest()
            ->get();
    }

    public function partnerOptions(): array
    {
        return Mitra::where('tenant_id', $this->selectedTenantId())
            ->where('status', 'active')
            ->orderBy('name')
            ->pluck('name', 'id')
            ->all();
    }

    public function outletOptions(): array
    {
        return HotspotOutlet::where('tenant_id', $this->selectedTenantId())
            ->where('status', 'active')
            ->orderBy('name')
            ->pluck('name', 'id')
            ->all();
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
