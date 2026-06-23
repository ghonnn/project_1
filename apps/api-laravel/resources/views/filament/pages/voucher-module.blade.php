<x-filament-panels::page>
    <div class="space-y-6">
        @if (count($this->stats()))
            <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                @foreach ($this->stats() as $stat)
                    <div class="flex items-center gap-4 rounded-lg bg-white p-4 shadow-sm ring-1 ring-gray-950/10">
                        <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-lg text-white" style="background: {{ $stat['color'] }}">
                            <x-dynamic-component :component="$stat['icon']" class="h-5 w-5" />
                        </div>
                        <div class="min-w-0">
                            <div class="truncate text-xs font-semibold uppercase text-gray-500">{{ $stat['label'] }}</div>
                            <div class="mt-1 text-xl font-bold text-gray-950">{{ $stat['value'] }}</div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        <x-filament::section>
            <x-slot name="heading">{{ $this->headingTitle() }}</x-slot>

            <div class="space-y-5">
                @if ($pageType === 'profile')
                    <form wire:submit="saveProfile" class="grid gap-4 rounded-lg bg-gray-50 p-4 ring-1 ring-gray-950/10 md:grid-cols-4">
                        <x-voucher-select label="Tenant" model="profileForm.tenant_id" :options="$this->tenantOptions()" />
                        <x-voucher-input label="Nama Profile" model="profileForm.name" />
                        <x-voucher-input label="MikroTik Group" model="profileForm.group" />
                        <x-voucher-input label="Address List" model="profileForm.address_list" />
                        <x-voucher-input label="Rate Limit" model="profileForm.rate_limit" placeholder="5M/5M" />
                        <x-voucher-input label="Shared" model="profileForm.shared_users" type="number" />
                        <x-voucher-input label="Kuota MB" model="profileForm.quota_mb" type="number" />
                        <x-voucher-input label="Durasi Menit" model="profileForm.duration_minutes" type="number" />
                        <x-voucher-input label="Masa Aktif Hari" model="profileForm.active_days" type="number" />
                        <x-voucher-input label="HPP" model="profileForm.hpp" type="number" />
                        <x-voucher-input label="Komisi" model="profileForm.commission" type="number" />
                        <x-voucher-input label="Harga" model="profileForm.price" type="number" />
                        <div class="md:col-span-4 flex justify-end">
                            <x-filament::button type="submit" icon="heroicon-m-plus" color="success">Simpan Profile</x-filament::button>
                        </div>
                    </form>
                @endif

                @if ($pageType === 'stock')
                    <form wire:submit="generateVouchers" class="grid gap-4 rounded-lg bg-gray-50 p-4 ring-1 ring-gray-950/10 md:grid-cols-4">
                        <x-voucher-select label="Tenant" model="voucherForm.tenant_id" :options="$this->tenantOptions()" />
                        <x-voucher-select label="Profile" model="voucherForm.profile_id" :options="$this->profileOptions()" />
                        <x-voucher-select label="Router" model="voucherForm.router_id" :options="$this->routerOptions()" />
                        <x-voucher-select label="Radius Server" model="voucherForm.radius_server_id" :options="$this->radiusServerOptions()" />
                        <x-voucher-input label="Jumlah" model="voucherForm.qty" type="number" />
                        <x-voucher-input label="Prefix Username" model="voucherForm.prefix" />
                        <x-voucher-input label="Panjang Password" model="voucherForm.password_length" type="number" />
                        <x-voucher-input label="Batch Code" model="voucherForm.batch_code" placeholder="Auto" />
                        <x-voucher-input label="Partner" model="voucherForm.partner_name" />
                        <x-voucher-input label="Outlet/Hotel Area" model="voucherForm.outlet_name" />
                        <x-voucher-input label="HPP" model="voucherForm.hpp" type="number" />
                        <x-voucher-input label="Harga" model="voucherForm.price" type="number" />
                        <x-voucher-input label="Komisi" model="voucherForm.commission" type="number" />
                        <div class="md:col-span-3 flex flex-wrap justify-end gap-2">
                            <x-filament::button type="submit" icon="heroicon-m-ticket" color="success">Generate Voucher</x-filament::button>
                            <x-filament::button type="button" wire:click="downloadPrintHtml" icon="heroicon-m-printer" color="gray">Print HTML</x-filament::button>
                            <x-filament::button type="button" wire:click="exportCsv" icon="heroicon-m-arrow-down-tray" color="info">Export CSV</x-filament::button>
                        </div>
                    </form>
                @endif

                @if ($pageType === 'template')
                    <form wire:submit="saveTemplate" class="grid gap-4 rounded-lg bg-gray-50 p-4 ring-1 ring-gray-950/10 md:grid-cols-2">
                        <x-voucher-select label="Tenant" model="templateForm.tenant_id" :options="$this->tenantOptions()" />
                        <x-voucher-input label="Nama Template" model="templateForm.name" />
                        <x-voucher-input label="Nama Hotspot" model="templateForm.hotspot_name" />
                        <x-voucher-input label="DNS Name" model="templateForm.dns_name" />
                        <x-voucher-input label="CS Phone" model="templateForm.support_phone" />
                        <div class="md:col-span-2">
                            <label class="mb-1 block text-sm font-semibold text-gray-700">HTML Login MikroTik</label>
                            <textarea wire:model="templateForm.html_body" rows="14" class="w-full rounded-lg border-gray-300 font-mono text-xs shadow-sm"></textarea>
                        </div>
                        <div class="md:col-span-2 flex justify-end gap-2">
                            <x-filament::button type="submit" icon="heroicon-m-check" color="success">Simpan Template</x-filament::button>
                            <x-filament::button type="button" wire:click="downloadTemplateHtml" icon="heroicon-m-arrow-down-tray" color="info">Download login.html</x-filament::button>
                        </div>
                    </form>
                @endif

                <div class="overflow-x-auto rounded-lg bg-white ring-1 ring-gray-950/10">
                    <table class="w-full min-w-[1050px] text-left text-sm">
                        <thead>
                            <tr class="border-b border-gray-200 bg-gray-50">
                                @foreach ($this->columns() as $column)
                                    <th class="whitespace-nowrap px-4 py-3 text-xs font-bold uppercase text-gray-600">{{ $column }}</th>
                                @endforeach
                                @if (in_array($pageType, ['stock'], true))
                                    <th class="px-4 py-3 text-xs font-bold uppercase text-gray-600">Action</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @if ($pageType === 'profile')
                                @forelse ($this->profileRows() as $profile)
                                    <tr>
                                        <td class="px-4 py-3 font-semibold">{{ $profile->name }}</td>
                                        <td class="px-4 py-3">{{ $profile->attributes['Mikrotik-Group'] ?? '-' }}</td>
                                        <td class="px-4 py-3">{{ $profile->attributes['Mikrotik-Rate-Limit'] ?? '-' }}</td>
                                        <td class="px-4 py-3">{{ $profile->attributes['Shared-Users'] ?? '-' }}</td>
                                        <td class="px-4 py-3">{{ $profile->attributes['Quota-MB'] ?? 'Unlimited' }}</td>
                                        <td class="px-4 py-3">{{ $profile->attributes['Duration-Minutes'] ?? '-' }} menit</td>
                                        <td class="px-4 py-3">{{ $this->rupiah((float) ($profile->attributes['Price'] ?? 0)) }}</td>
                                        <td class="px-4 py-3"><span class="rounded-full bg-emerald-50 px-2 py-1 text-xs font-bold text-emerald-700">{{ $profile->attributes['Status'] ?? 'active' }}</span></td>
                                    </tr>
                                @empty
                                    <tr><td colspan="8" class="px-4 py-10 text-center text-gray-500">Belum ada profile voucher.</td></tr>
                                @endforelse
                            @elseif (in_array($pageType, ['stock', 'sold'], true))
                                @forelse ($this->voucherRows() as $voucher)
                                    <tr>
                                        <td class="px-4 py-3 font-semibold">{{ $voucher->username }}</td>
                                        @if ($pageType === 'stock')
                                            <td class="px-4 py-3 font-mono">{{ $voucher->password }}</td>
                                        @endif
                                        <td class="px-4 py-3">{{ $voucher->profile?->name ?? '-' }}</td>
                                        @if ($pageType === 'stock')
                                            <td class="px-4 py-3">{{ $voucher->router?->router_name ?? '-' }}</td>
                                            <td class="px-4 py-3">{{ $voucher->radiusServer?->name ?? '-' }}</td>
                                        @endif
                                        <td class="px-4 py-3">{{ $voucher->partner_name ?? '-' }}</td>
                                        <td class="px-4 py-3">{{ $voucher->outlet_name ?? '-' }}</td>
                                        <td class="px-4 py-3">{{ $this->rupiah((float) $voucher->price) }}</td>
                                        @if ($pageType === 'stock')
                                            <td class="px-4 py-3">{{ $voucher->status }}</td>
                                            <td class="px-4 py-3">{{ $voucher->synced_at ? 'Synced' : 'Pending' }}</td>
                                            <td class="px-4 py-3">
                                                <x-filament::button size="xs" color="success" wire:click="markSold('{{ $voucher->id }}')">Terjual</x-filament::button>
                                            </td>
                                        @else
                                            <td class="px-4 py-3">{{ $voucher->activated_at?->format('d/m/Y H:i') ?? '-' }}</td>
                                            <td class="px-4 py-3">{{ $voucher->expires_at?->format('d/m/Y H:i') ?? '-' }}</td>
                                            <td class="px-4 py-3">{{ $voucher->mac_address ?? '-' }}</td>
                                        @endif
                                    </tr>
                                @empty
                                    <tr><td colspan="11" class="px-4 py-10 text-center text-gray-500">Belum ada data voucher.</td></tr>
                                @endforelse
                            @elseif ($pageType === 'online')
                                @forelse ($this->onlineRows() as $row)
                                    <tr>
                                        <td class="px-4 py-3 font-semibold">{{ $row->username }}</td>
                                        <td class="px-4 py-3">{{ $row->framedipaddress ?: '-' }}</td>
                                        <td class="px-4 py-3">{{ $row->callingstationid ?: '-' }}</td>
                                        <td class="px-4 py-3">{{ $row->acctstarttime ?: '-' }}</td>
                                        <td class="px-4 py-3">{{ $row->profile?->name ?? '-' }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="5" class="px-4 py-10 text-center text-gray-500">Belum ada voucher online.</td></tr>
                                @endforelse
                            @elseif ($pageType === 'recap')
                                @forelse ($this->recapRows() as $row)
                                    <tr>
                                        <td class="px-4 py-3">{{ $row->sale_date ?: '-' }}</td>
                                        <td class="px-4 py-3">{{ $row->partner_name ?: '-' }}</td>
                                        <td class="px-4 py-3">{{ $row->outlet_name ?: '-' }}</td>
                                        <td class="px-4 py-3">{{ $row->profile_id ?: '-' }}</td>
                                        <td class="px-4 py-3">{{ $row->qty }}</td>
                                        <td class="px-4 py-3">{{ $this->rupiah((float) $row->hpp) }}</td>
                                        <td class="px-4 py-3">{{ $this->rupiah((float) $row->commission) }}</td>
                                        <td class="px-4 py-3">{{ $this->rupiah((float) $row->price) }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="8" class="px-4 py-10 text-center text-gray-500">Belum ada rekap voucher.</td></tr>
                                @endforelse
                            @else
                                @forelse ($this->templateRows() as $template)
                                    <tr>
                                        <td class="px-4 py-3 font-semibold">{{ $template->name }}</td>
                                        <td class="px-4 py-3">{{ $template->hotspot_name }}</td>
                                        <td class="px-4 py-3">{{ $template->dns_name ?: '-' }}</td>
                                        <td class="px-4 py-3">{{ $template->support_phone ?: '-' }}</td>
                                        <td class="px-4 py-3">{{ $template->status }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="5" class="px-4 py-10 text-center text-gray-500">Belum ada template hotspot.</td></tr>
                                @endforelse
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </x-filament::section>
    </div>
</x-filament-panels::page>
