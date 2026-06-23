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
                    <form wire:submit="saveProfile" class="grid gap-4 rounded-lg bg-gray-50 p-4 ring-1 ring-gray-950/10" style="grid-template-columns: repeat(2, minmax(0, 1fr));">
                        <x-voucher-select label="Tenant" model="profileForm.tenant_id" :options="$this->tenantOptions()" />
                        <x-voucher-input label="Nama Profile" model="profileForm.name" />
                        <x-voucher-input label="MikroTik Group" model="profileForm.group" />
                        <x-voucher-input label="Address List" model="profileForm.address_list" />
                        <x-voucher-input label="Rate Limit" model="profileForm.rate_limit" placeholder="5M/5M" />
                        <x-voucher-input label="Shared" model="profileForm.shared_users" type="number" />
                        <x-voucher-input label="Kuota MB" model="profileForm.quota_mb" type="number" />
                        <x-voucher-input label="Durasi Menit" model="profileForm.duration_minutes" type="number" />
                        <x-voucher-input label="Masa Aktif Hari" model="profileForm.active_days" type="number" />
                        <x-voucher-money-input label="Komisi" model="profileForm.commission" />
                        <x-voucher-money-input label="Harga DPP" model="profileForm.dpp" />
                        @php
                            $profileTax = $this->taxBreakdown(
                                $this->parseMoney($profileForm['dpp'] ?? 0)
                            );
                        @endphp
                        <div class="grid gap-3 rounded-lg border border-gray-200 bg-white p-3 sm:grid-cols-3" style="grid-column: 1 / -1;">
                            <div class="flex items-center justify-between gap-3">
                                <span class="text-xs font-semibold uppercase text-gray-500">Harga DPP</span>
                                <span class="text-sm font-bold tabular-nums text-gray-950">{{ $this->rupiah($profileTax['dpp']) }}</span>
                            </div>
                            <div class="flex items-center justify-between gap-3">
                                <span class="text-xs font-semibold uppercase text-gray-500">PPN 11%</span>
                                <span class="text-sm font-bold tabular-nums text-gray-950">{{ $this->rupiah($profileTax['ppn']) }}</span>
                            </div>
                            <div class="flex items-center justify-between gap-3">
                                <span class="text-xs font-semibold uppercase text-gray-500">Harga Jual</span>
                                <span class="text-sm font-bold tabular-nums text-emerald-700">{{ $this->rupiah($profileTax['total']) }}</span>
                            </div>
                        </div>
                        <div class="flex justify-end gap-2" style="grid-column: 1 / -1;">
                            @if ($editingProfileId)
                                <x-filament::button type="button" wire:click="resetProfileForm" color="gray">Batal Edit</x-filament::button>
                            @endif
                            <x-filament::button type="submit" icon="heroicon-m-plus" color="success">{{ $editingProfileId ? 'Update Profile' : 'Simpan Profile' }}</x-filament::button>
                        </div>
                    </form>
                @endif

                @if ($pageType === 'stock')
                    <div x-data="{ stockPanel: null }" class="space-y-4">
                        <div class="rounded-lg bg-gray-100 px-4 py-3 text-sm font-bold uppercase text-gray-700 ring-1 ring-gray-950/10">Stok Voucher</div>

                        <div class="flex flex-wrap items-center gap-2">
                            <button type="button" class="inline-flex h-9 items-center gap-2 rounded-md bg-sky-600 px-3 text-sm font-bold text-white shadow-sm hover:bg-sky-700" x-on:click="stockPanel = stockPanel === 'menu' ? null : 'menu'">
                                <x-filament::icon icon="heroicon-m-bars-3" class="h-4 w-4" /> Menu
                            </button>
                            <button type="button" wire:click="downloadPrintHtml" class="inline-flex h-9 items-center gap-2 rounded-md bg-slate-600 px-3 text-sm font-bold text-white shadow-sm hover:bg-slate-700">
                                <x-filament::icon icon="heroicon-m-printer" class="h-4 w-4" /> Print
                            </button>
                            <button type="button" class="inline-flex h-9 items-center gap-2 rounded-md bg-emerald-600 px-3 text-sm font-bold text-white shadow-sm hover:bg-emerald-700" x-on:click="stockPanel = 'create-user'">
                                <x-filament::icon icon="heroicon-m-plus-circle" class="h-4 w-4" /> Buat User
                            </button>
                            <button type="button" class="inline-flex h-9 items-center gap-2 rounded-md bg-blue-600 px-3 text-sm font-bold text-white shadow-sm hover:bg-blue-700" x-on:click="stockPanel = 'create-voucher'">
                                <x-filament::icon icon="heroicon-m-ticket" class="h-4 w-4" /> Buat Voucher
                            </button>
                            <button type="button" class="inline-flex h-9 items-center gap-2 rounded-md bg-slate-800 px-3 text-sm font-bold text-white shadow-sm hover:bg-slate-900" x-on:click="stockPanel = 'outlet'">
                                <x-filament::icon icon="heroicon-m-users" class="h-4 w-4" /> Outlet
                            </button>
                            <button type="button" class="inline-flex h-9 items-center gap-2 rounded-md bg-rose-500 px-3 text-sm font-bold text-white shadow-sm hover:bg-rose-600" x-on:click="stockPanel = 'setting'">
                                <x-filament::icon icon="heroicon-m-cog-6-tooth" class="h-4 w-4" /> Setting
                            </button>
                            <button type="button" class="inline-flex h-9 items-center gap-2 rounded-md bg-sky-600 px-3 text-sm font-bold text-white shadow-sm hover:bg-sky-700" x-on:click="stockPanel = 'import'">
                                <x-filament::icon icon="heroicon-m-document-arrow-up" class="h-4 w-4" /> Import
                            </button>
                            <button type="button" wire:click="exportCsv" class="inline-flex h-9 items-center gap-2 rounded-md bg-emerald-600 px-3 text-sm font-bold text-white shadow-sm hover:bg-emerald-700">
                                <x-filament::icon icon="heroicon-m-document-arrow-down" class="h-4 w-4" /> Export
                            </button>
                        </div>

                        <div x-show="stockPanel === 'menu'" class="w-56 rounded-lg bg-white p-2 shadow-lg ring-1 ring-gray-950/10">
                            <button type="button" wire:click="downloadPrintHtml" class="flex w-full items-center gap-2 rounded-md px-3 py-2 text-left text-sm font-semibold text-gray-700 hover:bg-gray-50"><x-filament::icon icon="heroicon-m-printer" class="h-4 w-4 text-sky-600" /> Cetak Terpilih</button>
                            <button type="button" class="flex w-full items-center gap-2 rounded-md px-3 py-2 text-left text-sm font-semibold text-gray-700 hover:bg-gray-50"><x-filament::icon icon="heroicon-m-lock-closed" class="h-4 w-4 text-gray-500" /> Lock MAC</button>
                            <button type="button" class="flex w-full items-center gap-2 rounded-md px-3 py-2 text-left text-sm font-semibold text-gray-700 hover:bg-gray-50"><x-filament::icon icon="heroicon-m-lock-open" class="h-4 w-4 text-gray-500" /> Unlock MAC</button>
                            <button type="button" class="flex w-full items-center gap-2 rounded-md px-3 py-2 text-left text-sm font-semibold text-emerald-700 hover:bg-emerald-50"><x-filament::icon icon="heroicon-m-check-circle" class="h-4 w-4" /> Set Aktif</button>
                            <button type="button" class="flex w-full items-center gap-2 rounded-md px-3 py-2 text-left text-sm font-semibold text-amber-700 hover:bg-amber-50"><x-filament::icon icon="heroicon-m-no-symbol" class="h-4 w-4" /> Non Aktif</button>
                            <button type="button" class="flex w-full items-center gap-2 rounded-md px-3 py-2 text-left text-sm font-semibold text-gray-700 hover:bg-gray-50"><x-filament::icon icon="heroicon-m-server-stack" class="h-4 w-4 text-sky-600" /> Ganti Router</button>
                            <button type="button" class="flex w-full items-center gap-2 rounded-md px-3 py-2 text-left text-sm font-semibold text-rose-700 hover:bg-rose-50"><x-filament::icon icon="heroicon-m-trash" class="h-4 w-4" /> Hapus</button>
                        </div>

                        <div class="grid gap-2 md:grid-cols-[80px_1fr_1fr_1fr_1fr_2fr]">
                            <select class="h-9 rounded-md border-gray-300 text-sm shadow-sm">
                                <option>10</option>
                                <option>25</option>
                                <option>50</option>
                            </select>
                            <input type="search" class="h-9 rounded-md border-gray-300 text-sm shadow-sm" placeholder="Cari partner">
                            <select class="h-9 rounded-md border-gray-300 text-sm shadow-sm">
                                <option>All Router</option>
                                @foreach ($this->routerOptions() as $routerName)
                                    <option>{{ $routerName }}</option>
                                @endforeach
                            </select>
                            <select class="h-9 rounded-md border-gray-300 text-sm shadow-sm">
                                <option>All Profile</option>
                                @foreach ($this->profileOptions() as $profileName)
                                    <option>{{ $profileName }}</option>
                                @endforeach
                            </select>
                            <input type="text" class="h-9 rounded-md border-gray-300 text-sm shadow-sm" placeholder="Tgl pembuatan">
                            <input type="search" class="h-9 rounded-md border-gray-300 text-sm shadow-sm" placeholder="Cari voucher...">
                        </div>

                        <form x-show="stockPanel === 'create-user' || stockPanel === 'create-voucher'" wire:submit="generateVouchers" class="grid gap-4 rounded-lg bg-gray-50 p-4 ring-1 ring-gray-950/10" style="grid-template-columns: repeat(2, minmax(0, 1fr));">
                            <div class="text-base font-bold text-gray-800" style="grid-column: 1 / -1;" x-text="stockPanel === 'create-user' ? 'Buat User' : 'Buat Voucher'"></div>
                            <x-voucher-select label="Tenant" model="voucherForm.tenant_id" :options="$this->tenantOptions()" />
                            <x-voucher-input label="Partner" model="voucherForm.partner_name" placeholder="Cari partner" />
                            <x-voucher-select label="Profile" model="voucherForm.profile_id" :options="$this->profileOptions()" />
                            <x-voucher-input label="Outlet/Hotel Area" model="voucherForm.outlet_name" />
                            <x-voucher-select label="Router" model="voucherForm.router_id" :options="$this->routerOptions()" />
                            <x-voucher-select label="Radius Server" model="voucherForm.radius_server_id" :options="$this->radiusServerOptions()" />
                            <x-voucher-input label="Jumlah" model="voucherForm.qty" type="number" />
                            <x-voucher-input label="Prefix Username" model="voucherForm.prefix" />
                            <x-voucher-input label="Panjang Password" model="voucherForm.password_length" type="number" />
                            <x-voucher-input label="Batch Code" model="voucherForm.batch_code" placeholder="Auto" />
                            <x-voucher-money-input label="Komisi" model="voucherForm.commission" />
                            <x-voucher-money-input label="Harga DPP" model="voucherForm.dpp" />
                            @php
                                $voucherTax = $this->taxBreakdown(
                                    $this->parseMoney($voucherForm['dpp'] ?? 0)
                                );
                            @endphp
                            <div class="grid gap-3 rounded-lg border border-gray-200 bg-white p-3 sm:grid-cols-3" style="grid-column: 1 / -1;">
                                <div class="flex items-center justify-between gap-3">
                                    <span class="text-xs font-semibold uppercase text-gray-500">Harga DPP</span>
                                    <span class="text-sm font-bold tabular-nums text-gray-950">{{ $this->rupiah($voucherTax['dpp']) }}</span>
                                </div>
                                <div class="flex items-center justify-between gap-3">
                                    <span class="text-xs font-semibold uppercase text-gray-500">PPN 11%</span>
                                    <span class="text-sm font-bold tabular-nums text-gray-950">{{ $this->rupiah($voucherTax['ppn']) }}</span>
                                </div>
                                <div class="flex items-center justify-between gap-3">
                                    <span class="text-xs font-semibold uppercase text-gray-500">Harga Jual</span>
                                    <span class="text-sm font-bold tabular-nums text-emerald-700">{{ $this->rupiah($voucherTax['total']) }}</span>
                                </div>
                            </div>
                            <div class="flex flex-wrap justify-end gap-2" style="grid-column: 1 / -1;">
                                <x-filament::button type="button" color="gray" x-on:click="stockPanel = null">Close</x-filament::button>
                                <x-filament::button type="submit" icon="heroicon-m-ticket" color="success">Generate Voucher</x-filament::button>
                            </div>
                        </form>

                        <div x-show="stockPanel === 'outlet'" class="rounded-lg bg-white p-4 ring-1 ring-gray-950/10">
                            <div class="mb-3 rounded-md bg-emerald-50 px-3 py-2 text-sm font-medium text-emerald-800">Outlet adalah lokasi penjualan voucher seperti public area, hotel, kafe, atau reseller partner.</div>
                            <div class="overflow-x-auto">
                                <table class="w-full min-w-[720px] text-left text-sm">
                                    <thead class="bg-gray-50 text-xs font-bold uppercase text-gray-600">
                                        <tr>
                                            <th class="px-3 py-2">Outlet</th>
                                            <th class="px-3 py-2">Pemilik</th>
                                            <th class="px-3 py-2">Phone</th>
                                            <th class="px-3 py-2">Stok VC</th>
                                            <th class="px-3 py-2">Partner</th>
                                            <th class="px-3 py-2">Alamat</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100">
                                        <tr>
                                            <td class="px-3 py-2 font-semibold">SYSTEM</td>
                                            <td class="px-3 py-2">Internal</td>
                                            <td class="px-3 py-2">-</td>
                                            <td class="px-3 py-2">{{ $this->voucherRows()->count() }}</td>
                                            <td class="px-3 py-2">SYSTEM</td>
                                            <td class="px-3 py-2">Default outlet</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div x-show="stockPanel === 'setting'" class="rounded-lg bg-white p-4 ring-1 ring-gray-950/10">
                            <div class="grid gap-4 md:grid-cols-2">
                                <x-voucher-input label="Nama Hotspot" model="templateForm.hotspot_name" />
                                <x-voucher-input label="DNS Name" model="templateForm.dns_name" />
                                <x-voucher-input label="CS Phone" model="templateForm.support_phone" />
                                <div class="flex items-end justify-end">
                                    <x-filament::button type="button" wire:click="saveTemplate" color="success">Simpan Setting</x-filament::button>
                                </div>
                            </div>
                        </div>

                        <div x-show="stockPanel === 'import'" class="rounded-lg bg-white p-4 ring-1 ring-gray-950/10">
                            <div class="grid gap-4 md:grid-cols-2">
                                <x-voucher-select label="Profile" model="voucherForm.profile_id" :options="$this->profileOptions()" />
                                <x-voucher-select label="Router" model="voucherForm.router_id" :options="$this->routerOptions()" />
                                <input type="file" class="rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm shadow-sm md:col-span-2">
                            </div>
                        </div>
                    </div>
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
                                @if (in_array($pageType, ['profile', 'stock'], true))
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
                                        <td class="px-4 py-3 text-right tabular-nums">{{ $this->rupiah((float) ($profile->attributes['DPP'] ?? 0)) }}</td>
                                        <td class="px-4 py-3 text-right tabular-nums">{{ $this->rupiah((float) ($profile->attributes['PPN'] ?? 0)) }}</td>
                                        <td class="px-4 py-3 text-right tabular-nums">{{ $this->rupiah((float) ($profile->attributes['Price'] ?? 0)) }}</td>
                                        <td class="px-4 py-3"><span class="rounded-full bg-emerald-50 px-2 py-1 text-xs font-bold text-emerald-700">{{ $profile->attributes['Status'] ?? 'active' }}</span></td>
                                        <td class="px-4 py-3 text-right">
                                            <x-filament::button type="button" size="xs" color="info" wire:click="editProfile('{{ $profile->id }}')">Edit</x-filament::button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="11" class="px-4 py-10 text-center text-gray-500">Belum ada profile voucher.</td></tr>
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
                                        <td class="px-4 py-3 text-right tabular-nums">{{ $this->rupiah((float) $voucher->price) }}</td>
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
                                        <td class="px-4 py-3 text-right tabular-nums">{{ $this->rupiah((float) $row->commission) }}</td>
                                        <td class="px-4 py-3 text-right tabular-nums">{{ $this->rupiah((float) $row->price) }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="7" class="px-4 py-10 text-center text-gray-500">Belum ada rekap voucher.</td></tr>
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
