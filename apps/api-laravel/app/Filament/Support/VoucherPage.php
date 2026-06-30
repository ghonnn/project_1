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
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;

abstract class VoucherPage extends Page
{
    use WithFileUploads, WithPagination;

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

    public int|string $stockPerPage = 10;

    public string $stockSearch = '';

    public ?string $stockDate = null;

    public ?string $stockProfileId = null;

    public ?string $stockRouterId = null;

    public ?string $stockPartnerId = null;

    public string $stockSort = 'created_at';

    public string $stockSortDirection = 'desc';

    public int|string $recapMonth;

    public int|string $recapYear;

    public ?string $recapActionModal = null;

    public array $recapActionForm = [];

    public ?string $editingTemplateId = null;

    public function mount(): void
    {
        $tenantId = $this->defaultTenantId();
        $this->recapMonth = (int) now('Asia/Jakarta')->format('n');
        $this->recapYear = (int) now('Asia/Jakarta')->format('Y');
        $this->ensureDefaultVoucherTemplates($tenantId);

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
            'partner_id' => null,
            'potong_saldo' => 'no',
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
            'logo_path' => $template ? $template->logo_path : null,
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

        $this->recapActionForm = [
            'partner_id' => null,
            'date_from' => now('Asia/Jakarta')->startOfMonth()->format('Y-m-d'),
            'date_until' => now('Asia/Jakarta')->format('Y-m-d'),
        ];
    }

    public function updated(string $property, mixed $value = null): void
    {
        if (str_starts_with($property, 'stock') || str_starts_with($property, 'recap')) {
            $this->resetPage('vouchersPage');
            $this->resetPage('onlinePage');
            $this->resetPage('offlinePage');
            $this->selectAllVouchers = false;
            $this->selectedVouchers = [];
        }
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
            'offline' => 'Voucher Offline',
            'recap' => 'Rekap Voucher',
            'template' => 'Template Hotspot',
            default => 'Profil Voucher',
        };
    }

    /** @return array<int, array{label: string, value: string, description: string, icon: string, color: string}> */
    public function stats(): array
    {
        $tenantId = $this->selectedTenantId();

        $stock = HotspotVoucher::query()->where('tenant_id', $tenantId)->where('status', 'stock');
        $sold = HotspotVoucher::query()->where('tenant_id', $tenantId)->where('status', 'sold');
        $expired = HotspotVoucher::query()->where('tenant_id', $tenantId)->where('status', 'expired');
        $soldThisMonth = HotspotVoucher::query()
            ->where('tenant_id', $tenantId)
            ->where('status', 'sold')
            ->whereBetween('activated_at', [now()->startOfMonth(), now()->endOfMonth()]);

        return match ($this->pageType) {
            'stock' => [
                ['label' => 'Total Stok', 'value' => (string) $stock->count(), 'description' => 'Jumlah voucher aktif', 'icon' => 'heroicon-o-wifi', 'color' => '#0d82ff'],
                ['label' => 'Total HPP', 'value' => $this->rupiah((float) $stock->sum('hpp')), 'description' => 'Harga pokok penjualan', 'icon' => 'heroicon-o-banknotes', 'color' => '#16a34a'],
                ['label' => 'Total Komisi', 'value' => $this->rupiah((float) $stock->sum('commission')), 'description' => 'Komisi partner / outlet', 'icon' => 'heroicon-o-currency-dollar', 'color' => '#f59e0b'],
                ['label' => 'Total Harga', 'value' => $this->rupiah((float) $stock->sum('price')), 'description' => 'Nominal jual voucher', 'icon' => 'heroicon-o-circle-stack', 'color' => '#0891b2'],
            ],
            'sold' => [
                ['label' => 'Jumlah', 'value' => (string) $sold->count(), 'description' => 'Total voucher terjual', 'icon' => 'heroicon-o-shopping-bag', 'color' => '#0d82ff'],
                ['label' => 'Total Penjualan', 'value' => $this->rupiah((float) $sold->sum('price')), 'description' => 'Nominal voucher terjual', 'icon' => 'heroicon-o-banknotes', 'color' => '#22c55e'],
                ['label' => 'Total '.$this->monthLabel(), 'value' => $this->rupiah((float) $soldThisMonth->sum('price')), 'description' => 'Penjualan bulan ini', 'icon' => 'heroicon-o-calendar-days', 'color' => '#0891b2'],
                ['label' => 'Jumlah Expired', 'value' => (string) $expired->count(), 'description' => 'Voucher expired', 'icon' => 'heroicon-o-calendar-date-range', 'color' => '#ef4444'],
            ],
            'online' => [
                ['label' => 'Voucher Online', 'value' => (string) $this->onlineRows()->total(), 'description' => 'Sesi aktif saat ini', 'icon' => 'heroicon-o-wifi', 'color' => '#22c55e'],
                ['label' => 'FreeRadius', 'value' => '10.20.1.19 / 103.142.202.19', 'description' => 'Server autentikasi', 'icon' => 'heroicon-o-server', 'color' => '#0ea5e9'],
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

        if (empty($data['profile_id'])) {
            Notification::make()->title('Profile voucher wajib dipilih')->danger()->send();

            return;
        }

        $data['batch_code'] = $data['batch_code'] ?: now('Asia/Jakarta')->format('YmdHis');
        $data['commission'] = $this->parseMoney($data['commission'] ?? 0);
        $data['dpp'] = $this->parseMoney($data['dpp'] ?? 0);
        $data['hpp'] = $data['dpp'];
        $data['price'] = $this->taxBreakdown($data['dpp'])['total'];
        $data['admin_user_id'] = auth()->id();

        try {
            $vouchers = app(HotspotVoucherService::class)->generate($data);
            $this->lastBatchCode = $data['batch_code'];

            Notification::make()
                ->title(count($vouchers).' voucher berhasil dibuat')
                ->body('User/password sudah disimpan dan disync ke FreeRadius SQL.')
                ->success()
                ->send();
        } catch (\Throwable $e) {
            Notification::make()->title($e->getMessage())->danger()->send();
        }
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
        $template = $this->editingTemplateId
            ? tap(HotspotTemplate::where('tenant_id', $this->selectedTenantId())->findOrFail($this->editingTemplateId))->update($this->templateForm)
            : HotspotTemplate::updateOrCreate(
                ['tenant_id' => $this->templateForm['tenant_id'], 'name' => $this->templateForm['name']],
                $this->templateForm
            );

        $this->editingTemplateId = $template->id;
        Notification::make()->title('Template hotspot tersimpan')->success()->send();
    }

    public function loadTemplate(?string $templateId = null): void
    {
        if (! $templateId) {
            return;
        }

        $template = HotspotTemplate::where('tenant_id', $this->selectedTenantId())->findOrFail($templateId);
        $this->editingTemplateId = $template->id;
        $this->templateForm = [
            'tenant_id' => $template->tenant_id,
            'name' => $template->name,
            'hotspot_name' => $template->hotspot_name,
            'dns_name' => $template->dns_name,
            'support_phone' => $template->support_phone,
            'logo_path' => $template->logo_path,
            'status' => $template->status,
            'html_body' => $template->html_body,
        ];
    }

    public function addTemplate(): void
    {
        $this->editingTemplateId = null;
        $this->templateForm = [
            'tenant_id' => $this->selectedTenantId(),
            'name' => 'Template Baru',
            'hotspot_name' => 'NEX ISP Hotspot',
            'dns_name' => 'wifi.nex.local',
            'support_phone' => '082170000000',
            'logo_path' => null,
            'status' => 'active',
            'html_body' => app(HotspotVoucherService::class)->defaultTemplate(),
        ];
    }

    public function deleteTemplate(): void
    {
        if (! $this->editingTemplateId) {
            Notification::make()->title('Pilih template terlebih dahulu')->warning()->send();

            return;
        }

        HotspotTemplate::where('tenant_id', $this->selectedTenantId())->findOrFail($this->editingTemplateId)->delete();
        $this->addTemplate();
        Notification::make()->title('Template voucher dihapus')->success()->send();
    }

    public function exportCsv(): StreamedResponse
    {
        $rows = $this->filteredVoucherQuery()->limit(1000)->get();
        $filename = 'nex-hotspot-vouchers-'.now('Asia/Jakarta')->format('Ymd-His').'.csv';

        return response()->streamDownload(function () use ($rows): void {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Kode', 'Username', 'Password', 'Profile', 'Router', 'Server', 'Partner', 'Outlet', 'HPP', 'Komisi', 'Harga', 'Saldo', 'Admin', 'Tgl Pembuatan']);

            foreach ($rows as $row) {
                fputcsv($handle, [
                    $row->batch_code,
                    $row->username,
                    $row->password,
                    $row->profile?->name,
                    $row->router?->router_name,
                    $row->radiusServer?->name,
                    $row->partner_name,
                    $row->outlet_name ?: $row->outlet?->name,
                    $row->hpp,
                    $row->commission,
                    $row->price,
                    $row->balance_deducted ? 'Yes' : 'No',
                    $row->admin?->name ?? 'SYSTEM',
                    $row->created_at?->format('d/m/Y H:i:s'),
                ]);
            }

            fclose($handle);
        }, $filename);
    }

    public function openPrintTab(): void
    {
        $rows = $this->printableVoucherRows();

        if ($rows->isEmpty()) {
            $this->dispatch('voucher-print-empty');

            Notification::make()->title('Tidak ada voucher untuk dicetak')->warning()->send();

            return;
        }

        $token = Str::random(40);

        Cache::put('voucher-print:'.$token, [
            'tenant_id' => $this->selectedTenantId(),
            'voucher_ids' => $rows->pluck('id')->all(),
            'template_id' => $this->editingTemplateId ?: HotspotTemplate::where('tenant_id', $this->selectedTenantId())->where('status', 'active')->orderBy('name')->value('id'),
        ], now()->addMinutes(10));

        $this->dispatch('voucher-print-ready', url: route('voucher.print', ['token' => $token]));
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

    public function previewTemplate(): void
    {
        $template = new HotspotTemplate($this->templateForm);
        $html = app(HotspotVoucherService::class)->renderTemplate($template);
        $token = Str::random(40);

        Cache::put('voucher-template-preview:'.$token, [
            'html' => $html,
        ], now()->addMinutes(10));

        $this->dispatch('voucher-print-ready', url: route('voucher.template.preview', ['token' => $token]));
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
        $query = $this->filteredVoucherQuery();

        if (in_array($this->pageType, ['stock', 'sold'], true)) {
            return $query->paginate(
                max(5, min(100, (int) $this->stockPerPage)),
                ['hotspot_vouchers.*'],
                'vouchersPage',
            );
        }

        return $query->latest('hotspot_vouchers.created_at')->limit(200)->get();
    }

    protected function filteredVoucherQuery(): Builder
    {
        $query = HotspotVoucher::with(['profile', 'router', 'radiusServer', 'outlet', 'mitra', 'admin'])
            ->where('hotspot_vouchers.tenant_id', $this->selectedTenantId())
            ->when($this->pageType === 'stock', fn (Builder $query) => $query->where('hotspot_vouchers.status', 'stock'))
            ->when($this->pageType === 'sold', fn (Builder $query) => $query->whereIn('hotspot_vouchers.status', ['sold', 'expired']));

        if (! in_array($this->pageType, ['stock', 'sold'], true)) {
            return $query;
        }

        $query
            ->when($this->stockDate, fn (Builder $query, string $date) => $query->whereDate($this->pageType === 'sold' ? 'hotspot_vouchers.activated_at' : 'hotspot_vouchers.created_at', $date))
            ->when($this->stockProfileId, fn (Builder $query, string $profileId) => $query->where('hotspot_vouchers.profile_id', $profileId))
            ->when($this->stockRouterId, fn (Builder $query, string $routerId) => $query->where('hotspot_vouchers.router_id', $routerId));

        if ($this->stockPartnerId) {
            $partner = Mitra::query()->find($this->stockPartnerId);

            $query->where(function (Builder $query) use ($partner): void {
                $query->where('hotspot_vouchers.mitra_id', $this->stockPartnerId);

                if ($partner) {
                    $query->orWhere('hotspot_vouchers.partner_name', $partner->name);
                }
            });
        }

        $search = trim($this->stockSearch);
        if ($search !== '') {
            $query->where(function (Builder $query) use ($search): void {
                $query
                    ->where('hotspot_vouchers.batch_code', 'like', "%{$search}%")
                    ->orWhere('hotspot_vouchers.username', 'like', "%{$search}%")
                    ->orWhere('hotspot_vouchers.password', 'like', "%{$search}%")
                    ->orWhere('hotspot_vouchers.partner_name', 'like', "%{$search}%")
                    ->orWhere('hotspot_vouchers.outlet_name', 'like', "%{$search}%")
                    ->orWhereHas('profile', fn (Builder $query) => $query->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('router', fn (Builder $query) => $query->where('router_name', 'like', "%{$search}%"))
                    ->orWhereHas('radiusServer', fn (Builder $query) => $query->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('outlet', fn (Builder $query) => $query->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('admin', fn (Builder $query) => $query->where('name', 'like', "%{$search}%"));
            });
        }

        return $this->sortVoucherQuery($query);
    }

    protected function sortVoucherQuery(Builder $query): Builder
    {
        $direction = $this->stockSortDirection === 'asc' ? 'asc' : 'desc';

        return match ($this->stockSort) {
            'profile' => $query
                ->leftJoin('radius_profiles as voucher_sort_profiles', 'voucher_sort_profiles.id', '=', 'hotspot_vouchers.profile_id')
                ->select('hotspot_vouchers.*')
                ->orderBy('voucher_sort_profiles.name', $direction),
            'router' => $query
                ->leftJoin('routers as voucher_sort_routers', 'voucher_sort_routers.id', '=', 'hotspot_vouchers.router_id')
                ->select('hotspot_vouchers.*')
                ->orderBy('voucher_sort_routers.router_name', $direction),
            'server' => $query
                ->leftJoin('radius_servers as voucher_sort_servers', 'voucher_sort_servers.id', '=', 'hotspot_vouchers.radius_server_id')
                ->select('hotspot_vouchers.*')
                ->orderBy('voucher_sort_servers.name', $direction),
            'admin' => $query
                ->leftJoin('users as voucher_sort_admins', 'voucher_sort_admins.id', '=', 'hotspot_vouchers.admin_user_id')
                ->select('hotspot_vouchers.*')
                ->orderBy('voucher_sort_admins.name', $direction),
            'saldo' => $query->orderBy('hotspot_vouchers.balance_deducted', $direction),
            'kode' => $query->orderBy('hotspot_vouchers.batch_code', $direction),
            'username', 'password', 'mitra', 'outlet', 'hpp', 'commission', 'price', 'created_at', 'activated_at', 'expires_at', 'mac_address' => $query->orderBy('hotspot_vouchers.'.$this->stockSortColumn(), $direction),
            default => $query->latest('hotspot_vouchers.created_at'),
        };
    }

    protected function stockSortColumn(): string
    {
        return match ($this->stockSort) {
            'mitra' => 'partner_name',
            'outlet' => 'outlet_name',
            default => $this->stockSort,
        };
    }

    public function sortVouchers(string $field): void
    {
        $allowed = ['kode', 'username', 'password', 'profile', 'router', 'server', 'mitra', 'outlet', 'hpp', 'commission', 'price', 'saldo', 'admin', 'created_at', 'activated_at', 'expires_at', 'mac_address'];

        if (! in_array($field, $allowed, true)) {
            return;
        }

        if ($this->stockSort === $field) {
            $this->stockSortDirection = $this->stockSortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->stockSort = $field;
            $this->stockSortDirection = 'asc';
        }

        $this->resetPage('vouchersPage');
    }

    public function exportSoldVouchers(): StreamedResponse
    {
        $rows = $this->filteredVoucherQuery()->limit(5000)->get();
        $filename = 'nex-voucher-terjual-'.now('Asia/Jakarta')->format('Ymd-His').'.csv';

        return response()->streamDownload(function () use ($rows): void {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($handle, ['Username', 'Password', 'Profile', 'Router', 'Server', 'Partner', 'Outlet', 'HPP', 'Komisi', 'Harga', 'Saldo', 'Admin', 'Kode', 'Durasi', 'Kuota', 'Tgl Aktif', 'Tgl Expired', 'MAC Address']);

            foreach ($rows as $row) {
                fputcsv($handle, [
                    $row->username,
                    $row->password,
                    $row->profile?->name,
                    $row->router?->router_name,
                    $row->radiusServer?->name,
                    $row->partner_name ?: ($row->mitra?->name ?? 'SYSTEM'),
                    $row->outlet_name ?: ($row->outlet?->name),
                    $row->hpp,
                    $row->commission,
                    $row->price,
                    $row->balance_deducted ? 'Yes' : 'No',
                    $row->admin?->name ?? 'SYSTEM',
                    $row->batch_code,
                    $this->voucherDurationLabel($row),
                    $this->voucherQuotaLabel($row),
                    $row->activated_at?->format('d/m/Y H:i:s'),
                    $row->expires_at?->format('d/m/Y H:i:s'),
                    $row->mac_address,
                ]);
            }

            fclose($handle);
        }, $filename);
    }

    public function exportSoldRecap(): StreamedResponse
    {
        $rows = collect($this->soldRecapRows());
        $filename = 'nex-rekap-voucher-terjual-'.now('Asia/Jakarta')->format('Ymd-His').'.csv';

        return response()->streamDownload(function () use ($rows): void {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($handle, ['Tanggal', 'Partner', 'Outlet', 'Profile', 'Qty', 'HPP', 'Komisi', 'Penjualan']);

            foreach ($rows as $row) {
                fputcsv($handle, [
                    $row->sale_date ?: '-',
                    $row->partner_name ?: 'SYSTEM',
                    $row->outlet_name ?: '-',
                    $row->profile?->name ?: '-',
                    $row->qty,
                    $row->hpp,
                    $row->commission,
                    $row->price,
                ]);
            }

            fclose($handle);
        }, $filename);
    }

    public function deleteExpiredVouchers(): void
    {
        $rows = HotspotVoucher::query()
            ->where('tenant_id', $this->selectedTenantId())
            ->where('status', 'expired')
            ->get();

        if ($rows->isEmpty()) {
            Notification::make()->title('Tidak ada voucher expired untuk dihapus')->warning()->send();

            return;
        }

        foreach ($rows as $voucher) {
            if (Schema::hasTable('radcheck')) {
                DB::table('radcheck')->where('username', $voucher->username)->delete();
                DB::table('radreply')->where('username', $voucher->username)->delete();
                DB::table('radusergroup')->where('username', $voucher->username)->delete();
            }

            $voucher->delete();
        }

        Notification::make()->title($rows->count().' voucher expired berhasil dihapus')->success()->send();
        $this->resetPage('vouchersPage');
    }

    public function kickSelectedOnlineUsers(): void
    {
        if (empty($this->selectedVouchers)) {
            Notification::make()->title('Pilih voucher online terlebih dahulu')->warning()->send();

            return;
        }

        $usernames = HotspotVoucher::query()
            ->where('tenant_id', $this->selectedTenantId())
            ->whereIn('id', $this->selectedVouchers)
            ->pluck('username');

        if (Schema::hasTable('radacct')) {
            DB::table('radacct')
                ->whereIn('username', $usernames)
                ->whereNull('acctstoptime')
                ->update(['acctstoptime' => now()]);
        }

        Notification::make()->title($usernames->count().' user online berhasil di-kick')->success()->send();
        $this->selectedVouchers = [];
        $this->selectAllVouchers = false;
        $this->resetPage('onlinePage');
    }

    public function deleteSelectedOnlineSessions(): void
    {
        if (empty($this->selectedVouchers)) {
            Notification::make()->title('Pilih voucher online terlebih dahulu')->warning()->send();

            return;
        }

        $usernames = HotspotVoucher::query()
            ->where('tenant_id', $this->selectedTenantId())
            ->whereIn('id', $this->selectedVouchers)
            ->pluck('username');

        if (Schema::hasTable('radacct')) {
            DB::table('radacct')
                ->whereIn('username', $usernames)
                ->whereNull('acctstoptime')
                ->delete();
        }

        Notification::make()->title('Session online terpilih berhasil dihapus')->success()->send();
        $this->selectedVouchers = [];
        $this->selectAllVouchers = false;
        $this->resetPage('onlinePage');
    }

    public function syncOnlineSessions(): void
    {
        Notification::make()->title('Data voucher online disinkronkan dari tabel RADIUS accounting')->success()->send();
        $this->resetPage('onlinePage');
    }

    public function soldRecapRows(): array
    {
        return HotspotVoucher::query()
            ->with('profile')
            ->where('tenant_id', $this->selectedTenantId())
            ->whereIn('status', ['sold', 'expired'])
            ->selectRaw('date(activated_at) as sale_date, partner_name, outlet_name, profile_id, count(*) as qty, sum(hpp) as hpp, sum(commission) as commission, sum(price) as price')
            ->groupByRaw('date(activated_at), partner_name, outlet_name, profile_id')
            ->latest('sale_date')
            ->limit(100)
            ->get()
            ->all();
    }

    public function soldChartRows(): array
    {
        $rows = HotspotVoucher::query()
            ->where('tenant_id', $this->selectedTenantId())
            ->where('status', 'sold')
            ->whereNotNull('activated_at')
            ->where('activated_at', '>=', now()->subDays(13)->startOfDay())
            ->selectRaw('date(activated_at) as sale_date, count(*) as qty, sum(price) as price')
            ->groupByRaw('date(activated_at)')
            ->orderBy('sale_date')
            ->get();

        $max = max(1, (int) $rows->max('qty'));

        return $rows->map(fn ($row): array => [
            'date' => $row->sale_date,
            'qty' => (int) $row->qty,
            'price' => (float) $row->price,
            'height' => max(8, (int) round(((int) $row->qty / $max) * 96)),
        ])->all();
    }

    protected function printableVoucherRows()
    {
        if (! empty($this->selectedVouchers)) {
            return HotspotVoucher::with(['profile', 'router', 'radiusServer', 'outlet', 'mitra', 'admin'])
                ->where('tenant_id', $this->selectedTenantId())
                ->whereIn('id', $this->selectedVouchers)
                ->latest()
                ->get();
        }

        return $this->filteredVoucherQuery()->limit(120)->get();
    }

    public function templateRows()
    {
        return HotspotTemplate::where('tenant_id', $this->selectedTenantId())->latest()->limit(20)->get();
    }

    public function templateOptions(): array
    {
        $this->ensureDefaultVoucherTemplates($this->selectedTenantId());

        return HotspotTemplate::where('tenant_id', $this->selectedTenantId())
            ->orderBy('name')
            ->pluck('name', 'id')
            ->all();
    }

    protected function ensureDefaultVoucherTemplates(?string $tenantId): void
    {
        if (! $tenantId) {
            return;
        }

        foreach (app(HotspotVoucherService::class)->defaultPrintTemplates() as $template) {
            HotspotTemplate::firstOrCreate(
                ['tenant_id' => $tenantId, 'name' => $template['name']],
                $template + ['tenant_id' => $tenantId],
            );
        }
    }

    public function monthOptions(): array
    {
        return [
            1 => 'Januari',
            2 => 'Februari',
            3 => 'Maret',
            4 => 'April',
            5 => 'Mei',
            6 => 'Juni',
            7 => 'Juli',
            8 => 'Agustus',
            9 => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember',
        ];
    }

    public function yearOptions(): array
    {
        $year = (int) now('Asia/Jakarta')->format('Y');

        return array_combine(range($year - 3, $year + 1), range($year - 3, $year + 1));
    }

    public function onlineRows()
    {
        if (! Schema::hasTable('radacct')) {
            return $this->emptyPaginator('onlinePage');
        }

        return $this->sessionQuery(false)->paginate(
            max(5, min(100, (int) $this->stockPerPage)),
            ['*'],
            'onlinePage',
        );
    }

    public function offlineRows()
    {
        if (! Schema::hasTable('radacct')) {
            return $this->emptyPaginator('offlinePage');
        }

        return $this->sessionQuery(true)->paginate(
            max(5, min(100, (int) $this->stockPerPage)),
            ['*'],
            'offlinePage',
        );
    }

    protected function sessionQuery(bool $offline): Builder
    {
        $query = HotspotVoucher::query()
            ->with(['profile', 'router', 'radiusServer', 'outlet', 'mitra', 'admin'])
            ->select(
                'hotspot_vouchers.*',
                'radacct.acctsessionid',
                'radacct.framedipaddress',
                'radacct.callingstationid',
                'radacct.acctstarttime',
                'radacct.acctstoptime',
                'radacct.acctinputoctets',
                'radacct.acctoutputoctets',
                'radacct.nasipaddress',
            )
            ->join('radacct', 'radacct.username', '=', 'hotspot_vouchers.username')
            ->where('hotspot_vouchers.tenant_id', $this->selectedTenantId())
            ->when($offline, fn (Builder $query) => $query->whereNotNull('radacct.acctstoptime'))
            ->when(! $offline, fn (Builder $query) => $query->whereNull('radacct.acctstoptime'))
            ->when($this->stockProfileId, fn (Builder $query, string $profileId) => $query->where('hotspot_vouchers.profile_id', $profileId))
            ->when($this->stockRouterId, fn (Builder $query, string $routerId) => $query->where('hotspot_vouchers.router_id', $routerId));

        if ($this->stockPartnerId) {
            $partner = Mitra::query()->find($this->stockPartnerId);

            $query->where(function (Builder $query) use ($partner): void {
                $query->where('hotspot_vouchers.mitra_id', $this->stockPartnerId);

                if ($partner) {
                    $query->orWhere('hotspot_vouchers.partner_name', $partner->name);
                }
            });
        }

        $search = trim($this->stockSearch);
        if ($search !== '') {
            $query->where(function (Builder $query) use ($search): void {
                $query
                    ->where('hotspot_vouchers.username', 'like', "%{$search}%")
                    ->orWhere('hotspot_vouchers.partner_name', 'like', "%{$search}%")
                    ->orWhere('hotspot_vouchers.outlet_name', 'like', "%{$search}%")
                    ->orWhere('radacct.framedipaddress', 'like', "%{$search}%")
                    ->orWhere('radacct.callingstationid', 'like', "%{$search}%")
                    ->orWhereHas('profile', fn (Builder $query) => $query->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('router', fn (Builder $query) => $query->where('router_name', 'like', "%{$search}%"))
                    ->orWhereHas('radiusServer', fn (Builder $query) => $query->where('name', 'like', "%{$search}%"));
            });
        }

        return $query->latest($offline ? 'radacct.acctstoptime' : 'radacct.acctstarttime');
    }

    protected function emptyPaginator(string $pageName): LengthAwarePaginator
    {
        return new LengthAwarePaginator([], 0, max(5, min(100, (int) $this->stockPerPage)), 1, [
            'path' => request()->url(),
            'pageName' => $pageName,
        ]);
    }

    public function recapRows(): array
    {
        return $this->creationRecapQuery()
            ->whereMonth('created_at', (int) $this->recapMonth)
            ->whereYear('created_at', (int) $this->recapYear)
            ->when(trim($this->stockSearch) !== '', function (Builder $query): void {
                $search = trim($this->stockSearch);
                $query->where(function (Builder $query) use ($search): void {
                    $query
                        ->where('batch_code', 'like', "%{$search}%")
                        ->orWhere('partner_name', 'like', "%{$search}%")
                        ->orWhere('outlet_name', 'like', "%{$search}%")
                        ->orWhereHas('profile', fn (Builder $query) => $query->where('name', 'like', "%{$search}%"));
                });
            })
            ->selectRaw('batch_code, date(created_at) as created_date, partner_name, outlet_name, profile_id, count(*) as qty, sum(case when status = \'stock\' then 1 else 0 end) as stock_qty, sum(case when status in (\'sold\', \'expired\') then 1 else 0 end) as sold_qty, sum(hpp) as hpp, sum(commission) as commission, sum(price) as price')
            ->groupByRaw('batch_code, date(created_at), partner_name, outlet_name, profile_id')
            ->latest('created_date')
            ->limit(max(5, min(100, (int) $this->stockPerPage)))
            ->get()
            ->all();
    }

    public function openRecapExportModal(): void
    {
        $this->openRecapActionModal('export');
    }

    public function openRecapPrintModal(): void
    {
        $this->openRecapActionModal('print');
    }

    public function closeRecapActionModal(): void
    {
        $this->recapActionModal = null;
    }

    public function openRecapPrintTab(): void
    {
        $rows = collect($this->recapRowsForAction());

        if ($rows->isEmpty()) {
            Notification::make()->title('Tidak ada data rekap untuk dicetak')->warning()->send();

            return;
        }

        $token = Str::random(40);
        Cache::put('voucher-recap-print:'.$token, [
            'tenant_id' => $this->selectedTenantId(),
            'partner_id' => $this->recapActionForm['partner_id'] ?? null,
            'date_from' => $this->recapActionForm['date_from'] ?? now('Asia/Jakarta')->startOfMonth()->format('Y-m-d'),
            'date_until' => $this->recapActionForm['date_until'] ?? now('Asia/Jakarta')->format('Y-m-d'),
        ], now()->addMinutes(10));

        $this->recapActionModal = null;
        $this->dispatch('voucher-print-ready', url: route('voucher.recap.print', ['token' => $token]));
    }

    public function exportCreationRecap(): StreamedResponse
    {
        $rows = collect($this->recapRowsForAction());
        $filename = 'nex-rekap-pembuatan-voucher-'.now('Asia/Jakarta')->format('Ymd-His').'.csv';

        return response()->streamDownload(function () use ($rows): void {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($handle, ['Kode', 'Tgl Pembuatan', 'Partner', 'Outlet', 'Profile', 'Qty', 'Sisa Stok', 'Terjual', 'Total HPP', 'Total Komisi', 'Total Harga']);

            foreach ($rows as $row) {
                fputcsv($handle, [
                    $row->batch_code ?: '-',
                    $row->created_date ?: '-',
                    $row->partner_name ?: 'SYSTEM',
                    $row->outlet_name ?: '-',
                    $row->profile?->name ?: '-',
                    $row->qty,
                    $row->stock_qty,
                    $row->sold_qty,
                    $row->hpp,
                    $row->commission,
                    $row->price,
                ]);
            }

            fclose($handle);
        }, $filename);
    }

    /** @return array<int, object> */
    protected function recapRowsForAction(): array
    {
        $dateFrom = $this->recapActionForm['date_from'] ?? now('Asia/Jakarta')->startOfMonth()->format('Y-m-d');
        $dateUntil = $this->recapActionForm['date_until'] ?? now('Asia/Jakarta')->format('Y-m-d');
        $partnerId = $this->recapActionForm['partner_id'] ?? null;
        $partner = $partnerId ? Mitra::query()->find($partnerId) : null;

        return $this->creationRecapQuery()
            ->whereDate('created_at', '>=', $dateFrom)
            ->whereDate('created_at', '<=', $dateUntil)
            ->when($partner, function (Builder $query) use ($partner): void {
                $query->where(function (Builder $query) use ($partner): void {
                    $query->where('mitra_id', $partner->id)->orWhere('partner_name', $partner->name);
                });
            })
            ->latest('created_date')
            ->limit(1000)
            ->get()
            ->all();
    }

    protected function creationRecapQuery(): Builder
    {
        return HotspotVoucher::query()
            ->with('profile')
            ->where('tenant_id', $this->selectedTenantId())
            ->selectRaw('batch_code, date(created_at) as created_date, partner_name, outlet_name, profile_id, count(*) as qty, sum(case when status = \'stock\' then 1 else 0 end) as stock_qty, sum(case when status in (\'sold\', \'expired\') then 1 else 0 end) as sold_qty, sum(hpp) as hpp, sum(commission) as commission, sum(price) as price')
            ->groupByRaw('batch_code, date(created_at), partner_name, outlet_name, profile_id');
    }

    protected function openRecapActionModal(string $action): void
    {
        $this->recapActionModal = $action;
        $this->recapActionForm = array_merge([
            'partner_id' => null,
            'date_from' => now('Asia/Jakarta')->startOfMonth()->format('Y-m-d'),
            'date_until' => now('Asia/Jakarta')->format('Y-m-d'),
        ], $this->recapActionForm);
    }

    public function columns(): array
    {
        return match ($this->pageType) {
            'profile' => ['Nama Profile', 'Group', 'Rate Limit', 'Shared', 'Kuota', 'Durasi', 'Harga DPP', 'PPN 11%', 'Harga Jual', 'Status'],
            'stock' => ['Kode', 'Username', 'Password', 'Profile', 'Router', 'Server', 'Partner', 'Outlet', 'HPP', 'Komisi', 'Harga', 'Saldo', 'Admin', 'Tgl Pembuatan'],
            'sold' => ['Username', 'Password', 'Profile', 'Router', 'Server', 'Partner', 'Outlet', 'HPP', 'Komisi', 'Harga', 'Saldo', 'Admin', 'Kode', 'Durasi', 'Kuota', 'Tgl Aktif', 'Tgl Expired', 'MAC AC'],
            'online' => ['Username', 'Profile', 'Uptime', 'Upload', 'Download', 'Router', 'Interface', 'Server', 'IP Address', 'MAC Addr', 'Partner', 'Outlet', 'Last Connected', 'Last Update'],
            'offline' => ['Username', 'Router', 'Interface', 'Server', 'IP Address', 'Download', 'Upload', 'Last Connected', 'Last Offline', 'Reason'],
            'recap' => ['#', 'Kode', 'Tgl Pembuatan', 'Partner', 'Outlet', 'Profile', 'Qty', 'Sisa Stok', 'Terjual', 'Total HPP', 'Total Komisi', 'Total Harga'],
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

    public function monthLabel(): string
    {
        $months = [
            1 => 'Januari',
            2 => 'Februari',
            3 => 'Maret',
            4 => 'April',
            5 => 'Mei',
            6 => 'Juni',
            7 => 'Juli',
            8 => 'Agustus',
            9 => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember',
        ];

        return ($months[(int) now('Asia/Jakarta')->format('n')] ?? now('Asia/Jakarta')->format('F')).' '.now('Asia/Jakarta')->format('Y');
    }

    public function voucherDurationLabel(HotspotVoucher $voucher): string
    {
        $minutes = (int) ($voucher->profile?->attributes['Duration-Minutes'] ?? 0);

        if ($minutes <= 0) {
            return '00:00:00/UNLIMITED';
        }

        $hours = intdiv($minutes, 60);
        $remainingMinutes = $minutes % 60;

        return sprintf('%02d:%02d:00/UNLIMITED', $hours, $remainingMinutes);
    }

    public function voucherQuotaLabel(HotspotVoucher $voucher): string
    {
        $quotaMb = (int) ($voucher->profile?->attributes['Quota-MB'] ?? 0);

        return $quotaMb > 0 ? number_format($quotaMb, 0, ',', '.').'MB/UNLIMITED' : '0/UNLIMITED';
    }

    public function bytesLabel(mixed $bytes): string
    {
        $bytes = (float) ($bytes ?: 0);

        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 2, ',', '.').' GB';
        }

        if ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2, ',', '.').' MB';
        }

        if ($bytes >= 1024) {
            return number_format($bytes / 1024, 2, ',', '.').' KB';
        }

        return number_format($bytes, 0, ',', '.').' B';
    }

    public function uptimeLabel(mixed $start, mixed $stop = null): string
    {
        if (! $start) {
            return '-';
        }

        $start = \Illuminate\Support\Carbon::parse($start);
        $end = $stop ? \Illuminate\Support\Carbon::parse($stop) : now();
        $seconds = max(0, $start->diffInSeconds($end));
        $days = intdiv($seconds, 86400);
        $seconds %= 86400;
        $hours = intdiv($seconds, 3600);
        $seconds %= 3600;
        $minutes = intdiv($seconds, 60);

        return ($days > 0 ? $days.'d ' : '').sprintf('%02d:%02d', $hours, $minutes);
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
            $rows = match ($this->pageType) {
                'online' => $this->onlineRows(),
                default => $this->voucherRows(),
            };
            $collection = method_exists($rows, 'getCollection') ? $rows->getCollection() : $rows;
            $this->selectedVouchers = $collection->pluck('id')->map(fn($id) => (string)$id)->toArray();
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
                $this->userForm['hpp'] = $attributes['HPP'] ?? ($attributes['DPP'] ?? 0);
                $this->userForm['price'] = $attributes['Price'] ?? 0;
                $this->userForm['commission'] = $attributes['Commission'] ?? 0;
            }
        }
    }

    public function updatedVoucherFormProfileId($value): void
    {
        if ($value) {
            $profile = RadiusProfile::find($value);
            if ($profile) {
                $attributes = $profile->attributes ?? [];
                $this->voucherForm['dpp'] = $attributes['DPP'] ?? 0;
                $this->voucherForm['commission'] = $attributes['Commission'] ?? 0;
            }
        }
    }

    public function updatedImportFormProfileId($value): void
    {
        if ($value) {
            $profile = RadiusProfile::find($value);
            if ($profile) {
                $attributes = $profile->attributes ?? [];
                $this->importForm['hpp'] = $attributes['HPP'] ?? ($attributes['DPP'] ?? 0);
                $this->importForm['price'] = $attributes['Price'] ?? 0;
                $this->importForm['commission'] = $attributes['Commission'] ?? 0;
            }
        }
    }

    public function updatedEditingTemplateId($value): void
    {
        if (blank($value)) {
            $this->addTemplate();

            return;
        }

        $this->loadTemplate($value);
    }

    public function createUser(): void
    {
        $data = $this->userForm;
        $data['tenant_id'] = $this->selectedTenantId();

        if (empty($data['username']) || empty($data['password'])) {
            Notification::make()->title('Username dan password wajib diisi')->danger()->send();
            return;
        }

        if (empty($data['profile_id'])) {
            Notification::make()->title('Profile voucher wajib dipilih')->danger()->send();
            return;
        }

        if (($data['lock_mac'] ?? 'no') === 'yes' && empty($data['mac_address'])) {
            $data['mac_address'] = 'LOCK-ON-LOGIN';
        }

        // Format money
        $data['hpp'] = $this->parseMoney($data['hpp'] ?? 0);
        $data['commission'] = $this->parseMoney($data['commission'] ?? 0);
        $data['price'] = $this->parseMoney($data['price'] ?? 0);
        $data['admin_user_id'] = auth()->id();

        try {
            $vouchers = app(HotspotVoucherService::class)->generate($data);

            if (empty($vouchers)) {
                Notification::make()->title('Username sudah ada, user tidak dibuat')->warning()->send();

                return;
            }

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

        if (empty($this->importForm['profile_id'])) {
            Notification::make()->title('Profile voucher wajib dipilih')->danger()->send();
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
                'admin_user_id' => auth()->id(),
            ]);

            try {
                $created = app(HotspotVoucherService::class)->generate($payload);
                $successCount += count($created);
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
        $partner = $this->exportForm['partner_id'] ? Mitra::find($this->exportForm['partner_id']) : null;
        $outletId = $this->exportForm['outlet_id'];
        $profileId = $this->exportForm['profile_id'];

        $query = HotspotVoucher::with(['profile', 'router', 'radiusServer', 'outlet', 'mitra', 'admin'])
            ->where('tenant_id', $this->selectedTenantId());

        if ($partner) {
            $query->where(function (Builder $query) use ($partner): void {
                $query->where('mitra_id', $partner->id)->orWhere('partner_name', $partner->name);
            });
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
            fputcsv($handle, ['Kode', 'Username', 'Password', 'Profile', 'Router', 'Server', 'Partner', 'Outlet', 'HPP', 'Komisi', 'Harga', 'Saldo', 'Admin', 'Tgl Pembuatan', 'MAC Address']);

            foreach ($rows as $row) {
                fputcsv($handle, [
                    $row->batch_code,
                    $row->username,
                    $row->password,
                    $row->profile?->name,
                    $row->router?->router_name,
                    $row->radiusServer?->name,
                    $row->partner_name,
                    $row->outlet_name ?: ($row->outlet?->name),
                    $row->hpp,
                    $row->commission,
                    $row->price,
                    $row->balance_deducted ? 'Yes' : 'No',
                    $row->admin?->name ?? 'SYSTEM',
                    $row->created_at?->format('d/m/Y H:i:s'),
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
        return new HtmlString(view('exports.hotspot-vouchers-print', ['vouchers' => $this->filteredVoucherQuery()->limit(12)->get()])->render());
    }
}
