<?php

namespace App\Filament\Resources\ServiceResource\Pages;

use App\Filament\Resources\ServiceResource;
use App\Filament\Resources\ServiceResource\Widgets\ServiceOverview;
use App\Filament\Support\AdminOptions;
use App\Models\Tenant;
use Filament\Actions;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListServices extends ListRecords
{
    protected static string $resource = ServiceResource::class;

    protected function getHeaderWidgets(): array
    {
        return [
            ServiceOverview::class,
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\ActionGroup::make([
                Actions\Action::make('delete_hint')
                    ->label('Hapus')
                    ->icon('heroicon-o-minus-circle')
                    ->color('danger')
                    ->action(fn () => Notification::make()->title('Pilih data pada tabel untuk aksi hapus.')->warning()->send()),
                Actions\Action::make('offline_hint')
                    ->label('Offline')
                    ->icon('heroicon-o-wrench-screwdriver')
                    ->color('warning')
                    ->action(fn () => Notification::make()->title('Gunakan status layanan untuk menandai pelanggan offline/nonaktif.')->info()->send()),
            ])
                ->label('Menu')
                ->icon('heroicon-o-bars-3')
                ->color('info')
                ->button(),
            Actions\Action::make('import')
                ->label('Import')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('info')
                ->action(fn () => Notification::make()->title('Import data langganan akan disambungkan pada modul berikutnya.')->info()->send()),
            Actions\Action::make('set_billing')
                ->label('Set Billing')
                ->icon('heroicon-o-cog-6-tooth')
                ->color('danger')
                ->modalHeading('Setting Billing Langganan')
                ->modalWidth('6xl')
                ->fillForm(function (): array {
                    $tenant = Tenant::query()->orderBy('name')->first();
                    $settings = $tenant?->billing_settings ?? [];

                    return [
                        'tenant_id' => $tenant?->id,
                        'billing_type_rule' => $settings['billing_type_rule'] ?? 'all',
                        'billing_cycle_rule' => $settings['billing_cycle_rule'] ?? 'monthly',
                        'monthly_isolation_day' => $settings['monthly_isolation_day'] ?? 15,
                        'invoice_publish_day' => $settings['invoice_publish_day'] ?? 10,
                        'prorate_enabled' => $settings['prorate_enabled'] ?? true,
                        'ppn_rule' => $settings['ppn_rule'] ?? 'all_taxed',
                        'ppn_rate' => $settings['ppn_rate'] ?? 11,
                        'suspended_invoice_extension' => $settings['suspended_invoice_extension'] ?? 'from_paid_date',
                        'isolation_time' => $settings['isolation_time'] ?? '23:59:00',
                        'internet_username_suffix' => $settings['internet_username_suffix'] ?? '@nex',
                        'deduct_recurring_invoice_balance' => $settings['deduct_recurring_invoice_balance'] ?? true,
                        'deduct_one_time_invoice_balance' => $settings['deduct_one_time_invoice_balance'] ?? true,
                    ];
                })
                ->form([
                    Forms\Components\Select::make('tenant_id')
                        ->label('Tenant')
                        ->options(fn () => AdminOptions::tenants())
                        ->searchable()
                        ->required(),
                    Forms\Components\Select::make('billing_type_rule')
                        ->label('Jenis tagihan yg digunakan')
                        ->options(['prepaid' => 'PRABAYAR', 'postpaid' => 'PASCABAYAR', 'all' => 'SEMUA JENIS'])
                        ->default('all')
                        ->required()
                        ->helperText('Prabayar dibayar di awal sebelum layanan digunakan. Pascabayar dibayar di akhir setelah layanan digunakan.'),
                    Forms\Components\Select::make('billing_cycle_rule')
                        ->label('Siklus tagihan yg digunakan')
                        ->options(['profile' => 'SIKLUS PROFILE', 'fixed' => 'SIKLUS TETAP', 'monthly' => 'SIKLUS BULAN', 'all' => 'SEMUA SIKLUS'])
                        ->default('monthly')
                        ->required(),
                    Forms\Components\TextInput::make('monthly_isolation_day')
                        ->label('Tanggal Isolir SIKLUS BULAN')
                        ->numeric()
                        ->minValue(1)
                        ->maxValue(31)
                        ->default(15)
                        ->required()
                        ->helperText('Penetapan tanggal isolir khusus siklus bulan.'),
                    Forms\Components\TextInput::make('invoice_publish_day')
                        ->label('Terbit invoice')
                        ->numeric()
                        ->minValue(0)
                        ->maxValue(31)
                        ->default(10)
                        ->required()
                        ->helperText('Invoice siklus tetap dan profile diterbitkan beberapa hari sebelum terisolir.'),
                    Forms\Components\Toggle::make('prorate_enabled')
                        ->label('Hitung prorata')
                        ->default(true)
                        ->helperText('Berlaku untuk registrasi baru, upgrade, downgrade, dan stop berlangganan.'),
                    Forms\Components\Select::make('ppn_rule')
                        ->label('Penetapan PPN')
                        ->options(['all_taxed' => 'SEMUA WAJIB PPN', 'all_untaxed' => 'SEMUA TANPA PPN', 'optional' => 'PILIHAN YA/TIDAK'])
                        ->default('all_taxed')
                        ->required(),
                    Forms\Components\TextInput::make('ppn_rate')
                        ->label('Tarif PPN')
                        ->numeric()
                        ->suffix('%')
                        ->default(11)
                        ->required(),
                    Forms\Components\Select::make('suspended_invoice_extension')
                        ->label('Perpanjangan inv suspended')
                        ->options(['from_paid_date' => 'SAMBUNG DARI TGL BAYAR', 'from_isolation_date' => 'SAMBUNG DARI TGL ISOLIR'])
                        ->default('from_paid_date')
                        ->required()
                        ->helperText('Jika sambung dari tanggal bayar, masa isolir diabaikan dan dihitung kembali mulai dari pembayaran.'),
                    Forms\Components\TextInput::make('isolation_time')
                        ->label('Jam isolir')
                        ->default('23:59:00')
                        ->required()
                        ->helperText('Berlaku untuk sisi MikroTik; timezone WIB.'),
                    Forms\Components\TextInput::make('internet_username_suffix')
                        ->label('Suffix Username internet')
                        ->default('@nex'),
                    Forms\Components\Toggle::make('deduct_recurring_invoice_balance')
                        ->label('Potong saldo INV RECURRING')
                        ->default(true),
                    Forms\Components\Toggle::make('deduct_one_time_invoice_balance')
                        ->label('Potong saldo INV ONE-TIME')
                        ->default(true),
                ])
                ->action(function (array $data): void {
                    $tenant = Tenant::query()->findOrFail($data['tenant_id']);
                    unset($data['tenant_id']);

                    $tenant->update(['billing_settings' => $data]);

                    Notification::make()->title('Setting billing langganan berhasil disimpan')->success()->send();
                }),
            Actions\Action::make('secret')
                ->label('Secret')
                ->icon('heroicon-o-code-bracket')
                ->color('info')
                ->action(fn () => Notification::make()->title('Secret pelanggan akan mengikuti konfigurasi Radius.')->info()->send()),
            Actions\Action::make('customers')
                ->label('Pelanggan')
                ->icon('heroicon-o-user-group')
                ->color('success')
                ->url(fn () => route('filament.admin.resources.customers.index')),
            Actions\Action::make('unique_code')
                ->label('Kode Unik')
                ->icon('heroicon-o-circle-stack')
                ->color('warning')
                ->action(fn () => Notification::make()->title('Kode unik dapat diisi pada data berlangganan.')->info()->send()),
            Actions\Action::make('offline')
                ->label('Offline')
                ->icon('heroicon-o-wrench-screwdriver')
                ->color('danger')
                ->action(fn () => Notification::make()->title('Gunakan status layanan untuk proses offline.')->info()->send()),
            Actions\CreateAction::make()
                ->label('Tambah')
                ->icon('heroicon-o-plus-circle')
                ->color('primary'),
        ];
    }
}
