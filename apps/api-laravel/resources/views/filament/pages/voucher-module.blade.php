<x-filament-panels::page>
    @once
        <style>
            .nex-voucher-toolbar {
                display: flex;
                flex-wrap: wrap;
                align-items: center;
                gap: 8px;
            }

            .nex-voucher-control {
                display: inline-flex !important;
                align-items: center;
                justify-content: center;
                gap: 8px;
                min-height: 36px;
                min-width: max-content;
                padding: 0 12px;
                border: 1px solid transparent;
                border-radius: 6px;
                color: #ffffff !important;
                -webkit-text-fill-color: #ffffff;
                font-size: 13px;
                font-weight: 700;
                line-height: 1.1;
                white-space: nowrap;
                box-shadow: 0 1px 2px rgba(15, 23, 42, 0.12);
                transition: background-color 120ms ease, box-shadow 120ms ease, transform 120ms ease;
            }

            .nex-voucher-control:hover {
                box-shadow: 0 3px 8px rgba(15, 23, 42, 0.16);
                transform: translateY(-1px);
            }

            .nex-voucher-control:focus-visible {
                outline: 2px solid #10b981;
                outline-offset: 2px;
            }

            .nex-voucher-control svg {
                width: 16px;
                height: 16px;
                flex: none;
                color: currentColor !important;
                stroke: currentColor !important;
            }

            .nex-voucher-control--sky { background: #0284c7 !important; }
            .nex-voucher-control--sky:hover { background: #0369a1 !important; }
            .nex-voucher-control--emerald { background: #059669 !important; }
            .nex-voucher-control--emerald:hover { background: #047857 !important; }
            .nex-voucher-control--blue { background: #2563eb !important; }
            .nex-voucher-control--blue:hover { background: #1d4ed8 !important; }
            .nex-voucher-control--slate { background: #1e293b !important; }
            .nex-voucher-control--slate:hover { background: #0f172a !important; }
            .nex-voucher-control--rose { background: #e11d48 !important; }
            .nex-voucher-control--rose:hover { background: #be123c !important; }
            .nex-voucher-control--indigo { background: #4f46e5 !important; }
            .nex-voucher-control--indigo:hover { background: #4338ca !important; }
            .nex-voucher-control--violet { background: #7c3aed !important; }
            .nex-voucher-control--violet:hover { background: #6d28d9 !important; }
        </style>
    @endonce

    <div class="space-y-6">
        {{-- Top Statistics Panels --}}
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
                {{-- Profile Voucher Tab Form --}}
                @if ($pageType === 'profile')
                    <form wire:submit="saveProfile" class="grid gap-4 rounded-lg bg-gray-50 p-4 ring-1 ring-gray-950/10 sm:grid-cols-2">
                        <x-voucher-select label="Tenant" model="profileForm.tenant_id" :options="$this->tenantOptions()" />
                        <x-voucher-input label="Nama Profile" model="profileForm.name" />
                        <x-voucher-input label="MikroTik Group" model="profileForm.group" />
                        <x-voucher-input label="Address List" model="profileForm.address_list" />
                        <x-voucher-input label="Rate Limit" model="profileForm.rate_limit" placeholder="5M/5M" />
                        <x-voucher-input label="Shared" model="profileForm.shared_users" type="number" />
                        <x-voucher-input label="Kuota MB" model="profileForm.quota_mb" type="number" />
                        <x-voucher-input label="Durasi Menit" model="profileForm.duration_minutes" type="number" />
                        <x-voucher-input label="Masa Aktif Hari" model="profileForm.active_days" type="number" />
                        <x-voucher-input label="Warna Voucher" model="profileForm.color" type="color" />
                        <x-voucher-money-input label="Komisi" model="profileForm.commission" />
                        <x-voucher-money-input label="Harga DPP" model="profileForm.dpp" />
                        
                        @php
                            $profileTax = $this->taxBreakdown(
                                $this->parseMoney($profileForm['dpp'] ?? 0)
                            );
                        @endphp
                        <div class="grid gap-3 rounded-lg border border-gray-200 bg-white p-3 sm:grid-cols-3 sm:col-span-2">
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
                        <div class="flex justify-end gap-2 sm:col-span-2">
                            @if ($editingProfileId)
                                <x-filament::button type="button" wire:click="resetProfileForm" color="gray">Batal Edit</x-filament::button>
                            @endif
                            <x-filament::button type="submit" icon="heroicon-m-plus" color="success">{{ $editingProfileId ? 'Update Profile' : 'Simpan Profile' }}</x-filament::button>
                        </div>
                    </form>
                @endif

                {{-- Stok Voucher Tab Interface --}}
                @if ($pageType === 'stock')
                    <div x-data="{ stockPanel: null }" class="space-y-4">
                        <div class="rounded-lg bg-gray-100 px-4 py-3 text-sm font-bold uppercase text-gray-700 ring-1 ring-gray-950/10">Stok Voucher</div>

                        {{-- Control Panel Navigation Bar --}}
                        <div class="nex-voucher-toolbar">
                            <button type="button" class="nex-voucher-control nex-voucher-control--sky" x-on:click="stockPanel = stockPanel === 'menu' ? null : 'menu'">
                                <x-filament::icon icon="heroicon-m-bars-3" class="h-4 w-4" /> Aksi Terpilih ({{ count($selectedVouchers) }})
                            </button>
                            <button type="button" class="nex-voucher-control nex-voucher-control--emerald" x-on:click="stockPanel = stockPanel === 'create-user' ? null : 'create-user'">
                                <x-filament::icon icon="heroicon-m-plus-circle" class="h-4 w-4" /> Buat User
                            </button>
                            <button type="button" class="nex-voucher-control nex-voucher-control--blue" x-on:click="stockPanel = stockPanel === 'create-voucher' ? null : 'create-voucher'">
                                <x-filament::icon icon="heroicon-m-ticket" class="h-4 w-4" /> Buat Voucher (Bulk)
                            </button>
                            <button type="button" class="nex-voucher-control nex-voucher-control--slate" x-on:click="stockPanel = stockPanel === 'outlet' ? null : 'outlet'">
                                <x-filament::icon icon="heroicon-m-users" class="h-4 w-4" /> Data Outlet
                            </button>
                            <button type="button" class="nex-voucher-control nex-voucher-control--rose" x-on:click="stockPanel = stockPanel === 'setting' ? null : 'setting'">
                                <x-filament::icon icon="heroicon-m-cog-6-tooth" class="h-4 w-4" /> Setting Hotspot
                            </button>
                            <button type="button" class="nex-voucher-control nex-voucher-control--indigo" x-on:click="stockPanel = stockPanel === 'import' ? null : 'import'">
                                <x-filament::icon icon="heroicon-m-document-arrow-up" class="h-4 w-4" /> Import
                            </button>
                            <button type="button" class="nex-voucher-control nex-voucher-control--violet" x-on:click="stockPanel = stockPanel === 'export-panel' ? null : 'export-panel'">
                                <x-filament::icon icon="heroicon-m-document-arrow-down" class="h-4 w-4" /> Export
                            </button>
                        </div>

                        {{-- Aksi Terpilih Bulk Submenu --}}
                        <div x-show="stockPanel === 'menu'" class="rounded-lg border border-sky-100 bg-sky-50/50 p-4 space-y-3" x-cloak>
                            <div class="text-xs font-bold uppercase text-sky-800">Aksi Massal Voucher Terpilih ({{ count($selectedVouchers) }} item)</div>
                            <div class="flex flex-wrap items-center gap-2">
                                <button type="button" wire:click="downloadPrintHtml" class="inline-flex items-center gap-1 rounded bg-white px-3 py-1.5 text-xs font-semibold text-gray-700 shadow-sm ring-1 ring-gray-900/5 hover:bg-gray-50"><x-filament::icon icon="heroicon-m-printer" class="h-4 w-4 text-sky-600" /> Cetak Terpilih</button>
                                <button type="button" wire:click="lockMacForSelected" class="inline-flex items-center gap-1 rounded bg-white px-3 py-1.5 text-xs font-semibold text-gray-700 shadow-sm ring-1 ring-gray-900/5 hover:bg-gray-50"><x-filament::icon icon="heroicon-m-lock-closed" class="h-4 w-4 text-gray-500" /> Lock MAC</button>
                                <button type="button" wire:click="unlockMacForSelected" class="inline-flex items-center gap-1 rounded bg-white px-3 py-1.5 text-xs font-semibold text-gray-700 shadow-sm ring-1 ring-gray-900/5 hover:bg-gray-50"><x-filament::icon icon="heroicon-m-lock-open" class="h-4 w-4 text-gray-500" /> Unlock MAC</button>
                                <button type="button" wire:click="setActiveForSelected" class="inline-flex items-center gap-1 rounded bg-emerald-600 px-3 py-1.5 text-xs font-semibold text-white shadow-sm hover:bg-emerald-700"><x-filament::icon icon="heroicon-m-check-circle" class="h-4 w-4" /> Set Aktif</button>
                                <button type="button" wire:click="setInactiveForSelected" class="inline-flex items-center gap-1 rounded bg-amber-600 px-3 py-1.5 text-xs font-semibold text-white shadow-sm hover:bg-amber-700"><x-filament::icon icon="heroicon-m-no-symbol" class="h-4 w-4" /> Non Aktif</button>
                                <button type="button" wire:click="deleteSelectedVouchers" wire:confirm="Apakah Anda yakin ingin menghapus voucher terpilih?" class="inline-flex items-center gap-1 rounded bg-rose-600 px-3 py-1.5 text-xs font-semibold text-white shadow-sm hover:bg-rose-700"><x-filament::icon icon="heroicon-m-trash" class="h-4 w-4" /> Hapus</button>
                                
                                <div class="flex items-center gap-1 ml-auto">
                                    <select wire:model.live="targetRouterId" class="rounded border-gray-300 text-xs shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                                        <option value="">Pilih Router</option>
                                        @foreach ($this->routerOptions() as $id => $name)
                                            <option value="{{ $id }}">{{ $name }}</option>
                                        @endforeach
                                    </select>
                                    <button type="button" wire:click="changeRouterForSelected" class="inline-flex items-center gap-1 rounded bg-sky-600 px-3 py-1.5 text-xs font-semibold text-white shadow-sm hover:bg-sky-700">Ganti Router</button>
                                </div>
                            </div>
                        </div>

                        {{-- Buat User (Individual Voucher Form) --}}
                        <form x-show="stockPanel === 'create-user'" wire:submit="createUser" class="grid gap-4 rounded-lg bg-gray-50 p-4 ring-1 ring-gray-950/10 sm:grid-cols-2" x-cloak>
                            <div class="text-base font-bold text-gray-800 sm:col-span-2 flex items-center justify-between border-b pb-2">
                                <span>Buat User Baru (Individu)</span>
                                <button type="button" x-on:click="stockPanel = null" class="text-gray-400 hover:text-gray-600">&times;</button>
                            </div>
                            <x-voucher-select label="Partner" model="userForm.partner_id" :options="$this->partnerOptions()" />
                            <x-voucher-select label="Potong Saldo Partner" model="userForm.potong_saldo" :options="['yes' => 'YES', 'no' => 'NO']" />
                            <x-voucher-input label="Username" model="userForm.username" />
                            <x-voucher-input label="Password" model="userForm.password" />
                            <x-voucher-select label="Router" model="userForm.router_id" :options="$this->routerOptions()" />
                            <x-voucher-select label="Radius Server" model="userForm.radius_server_id" :options="$this->radiusServerOptions()" />
                            <x-voucher-select label="Kunci MAC Address" model="userForm.lock_mac" :options="['yes' => 'YES', 'no' => 'NO']" />
                            <x-voucher-input label="MAC Address (Kunci langsung / kosongkan)" model="userForm.mac_address" placeholder="Contoh: AA:BB:CC:DD:EE:FF" />
                            <x-voucher-select label="Outlet Voucher" model="userForm.outlet_id" :options="$this->outletOptions()" />
                            <x-voucher-select label="Profile Voucher" model="userForm.profile_id" :options="$this->profileOptions()" />
                            <x-voucher-money-input label="Harga Pokok Penjualan (HPP)" model="userForm.hpp" />
                            <x-voucher-money-input label="Harga Jual (Sudah inc PPN 11%)" model="userForm.price" />
                            <x-voucher-money-input label="Komisi Partner" model="userForm.commission" />

                            <div class="flex justify-end gap-2 sm:col-span-2 border-t pt-3">
                                <x-filament::button type="button" color="gray" x-on:click="stockPanel = null">Close</x-filament::button>
                                <x-filament::button type="submit" icon="heroicon-m-plus" color="success">Buat User</x-filament::button>
                            </div>
                        </form>

                        {{-- Buat Voucher (Bulk Generation Form) --}}
                        <form x-show="stockPanel === 'create-voucher'" wire:submit="generateVouchers" class="grid gap-4 rounded-lg bg-gray-50 p-4 ring-1 ring-gray-950/10 sm:grid-cols-2" x-cloak>
                            <div class="text-base font-bold text-gray-800 sm:col-span-2 flex items-center justify-between border-b pb-2">
                                <span>Generate Voucher (Massal)</span>
                                <button type="button" x-on:click="stockPanel = null" class="text-gray-400 hover:text-gray-600">&times;</button>
                            </div>
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
                            <div class="grid gap-3 rounded-lg border border-gray-200 bg-white p-3 sm:grid-cols-3 sm:col-span-2">
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
                            <div class="flex flex-wrap justify-end gap-2 sm:col-span-2 border-t pt-3">
                                <x-filament::button type="button" color="gray" x-on:click="stockPanel = null">Close</x-filament::button>
                                <x-filament::button type="submit" icon="heroicon-m-ticket" color="success">Generate Voucher</x-filament::button>
                            </div>
                        </form>

                        {{-- Data Outlet (Form & Table Panels) --}}
                        <div x-show="stockPanel === 'outlet'" class="grid gap-6 rounded-lg border border-gray-200 bg-white p-5 ring-1 ring-gray-950/5" x-cloak>
                            <div class="grid gap-5 lg:grid-cols-3">
                                {{-- Tambah Outlet Voucher Form --}}
                                <form wire:submit="saveOutlet" class="space-y-4 rounded-lg bg-gray-50 p-4 ring-1 ring-gray-950/5 lg:col-span-1">
                                    <div class="text-sm font-bold uppercase text-gray-700 border-b pb-2">Tambah Outlet Voucher</div>
                                    <x-voucher-select label="Cari Partner" model="outletForm.mitra_id" :options="$this->partnerOptions()" />
                                    <x-voucher-input label="Nama Outlet" model="outletForm.name" />
                                    <x-voucher-input label="Nama Pemilik" model="outletForm.owner_name" />
                                    <x-voucher-input label="Phone" model="outletForm.phone" />
                                    <x-voucher-input label="Alamat" model="outletForm.address" />
                                    <x-voucher-input label="Tanggal Bergabung" model="outletForm.joined_at" type="date" />
                                    
                                    <div class="flex justify-end pt-2">
                                        <x-filament::button type="submit" size="sm" color="success" icon="heroicon-m-plus">Simpan Outlet</x-filament::button>
                                    </div>
                                </form>

                                {{-- Table List of Outlets --}}
                                <div class="lg:col-span-2 space-y-3">
                                    <div class="text-sm font-bold uppercase text-gray-700 border-b pb-2">Daftar Outlet Terdaftar</div>
                                    <div class="overflow-x-auto rounded-lg ring-1 ring-gray-950/5">
                                        <table class="w-full min-w-[650px] text-left text-xs">
                                            <thead class="bg-gray-50 text-gray-600 font-bold uppercase">
                                                <tr>
                                                    <th class="px-3 py-2">Outlet</th>
                                                    <th class="px-3 py-2">Pemilik</th>
                                                    <th class="px-3 py-2">Phone</th>
                                                    <th class="px-3 py-2">Partner</th>
                                                    <th class="px-3 py-2">Alamat</th>
                                                    <th class="px-3 py-2">Gabung</th>
                                                    <th class="px-3 py-2">Status</th>
                                                    <th class="px-3 py-2">Aksi</th>
                                                </tr>
                                            </thead>
                                            <tbody class="divide-y divide-gray-100">
                                                @forelse ($this->outletRows() as $outlet)
                                                    <tr>
                                                        <td class="px-3 py-2 font-semibold text-gray-900">{{ $outlet->name }}</td>
                                                        <td class="px-3 py-2">{{ $outlet->owner_name ?: '-' }}</td>
                                                        <td class="px-3 py-2">{{ $outlet->phone ?: '-' }}</td>
                                                        <td class="px-3 py-2 font-medium text-sky-700">{{ $outlet->mitra?->name ?? 'SYSTEM' }}</td>
                                                        <td class="px-3 py-2 truncate max-w-[120px]">{{ $outlet->address ?: '-' }}</td>
                                                        <td class="px-3 py-2">{{ $outlet->joined_at?->format('d/m/Y') ?: '-' }}</td>
                                                        <td class="px-3 py-2">
                                                            <span class="inline-block rounded px-1.5 py-0.5 font-bold {{ $outlet->status === 'active' ? 'bg-emerald-50 text-emerald-700' : 'bg-gray-100 text-gray-600' }}">
                                                                {{ strtoupper($outlet->status) }}
                                                            </span>
                                                        </td>
                                                        <td class="px-3 py-2">
                                                            <div class="flex items-center gap-1">
                                                                @if ($outlet->status === 'active')
                                                                    <button type="button" wire:click="deactivateOutlet('{{ $outlet->id }}')" class="rounded bg-amber-50 px-2 py-1 text-[10px] font-bold text-amber-700 hover:bg-amber-100">Nonaktif</button>
                                                                @else
                                                                    <button type="button" wire:click="activateOutlet('{{ $outlet->id }}')" class="rounded bg-emerald-50 px-2 py-1 text-[10px] font-bold text-emerald-700 hover:bg-emerald-100">Aktifkan</button>
                                                                @endif
                                                                <button type="button" wire:click="deleteOutlet('{{ $outlet->id }}')" wire:confirm="Apakah Anda yakin ingin menghapus outlet ini?" class="rounded bg-rose-50 px-2 py-1 text-[10px] font-bold text-rose-700 hover:bg-rose-100">Hapus</button>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="8" class="px-3 py-8 text-center text-gray-500">Belum ada outlet voucher terdaftar.</td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Setting Hotspot & Logo Settings --}}
                        <div x-show="stockPanel === 'setting'" class="rounded-lg bg-white p-5 ring-1 ring-gray-950/10 space-y-4" x-cloak>
                            <div class="text-sm font-bold uppercase text-gray-700 border-b pb-2">Setting Hotspot & Branding</div>
                            <form wire:submit="saveHotspotSetting" class="grid gap-4 md:grid-cols-2">
                                <x-voucher-input label="Nama Hotspot" model="templateForm.hotspot_name" />
                                <x-voucher-input label="DNS Name" model="templateForm.dns_name" />
                                <x-voucher-input label="CS Phone" model="templateForm.support_phone" />
                                
                                <div>
                                    <label class="block">
                                        <span class="mb-1 block text-sm font-semibold text-gray-700">Logo Voucher & Invoice (JPG/PNG max 100KB)</span>
                                        <input type="file" wire:model="hotspotLogo" class="w-full text-xs text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100" />
                                        @error('hotspotLogo') <span class="text-xs text-rose-600">{{ $message }}</span> @enderror
                                    </label>
                                    
                                    {{-- Logo Preview --}}
                                    <div class="mt-2 flex items-center gap-3">
                                        @if ($hotspotLogo)
                                            <div class="relative h-12 w-24 border rounded overflow-hidden">
                                                <img src="{{ $hotspotLogo->temporaryUrl() }}" class="h-full w-full object-contain">
                                            </div>
                                            <span class="text-[10px] text-emerald-600 font-medium">Preview Logo Baru</span>
                                        @elseif (!empty($templateForm['logo_path']))
                                            <div class="relative h-12 w-24 border rounded overflow-hidden">
                                                <img src="{{ asset('storage/' . $templateForm['logo_path']) }}" class="h-full w-full object-contain">
                                            </div>
                                            <span class="text-[10px] text-gray-500 font-medium">Logo Aktif</span>
                                        @endif
                                    </div>
                                </div>

                                <div class="md:col-span-2 flex justify-end">
                                    <x-filament::button type="submit" color="success" icon="heroicon-m-check">Simpan Setting</x-filament::button>
                                </div>
                            </form>
                        </div>

                        {{-- Import Voucher --}}
                        <div x-show="stockPanel === 'import'" class="rounded-lg bg-white p-5 ring-1 ring-gray-950/10 space-y-4" x-cloak>
                            <div class="text-sm font-bold uppercase text-gray-700 border-b pb-2 flex items-center justify-between">
                                <span>Import Voucher dari File</span>
                                <button type="button" wire:click="downloadImportFormat" class="text-xs font-semibold text-emerald-600 hover:underline">Unduh Format Data Import (CSV/Excel)</button>
                            </div>
                            <form wire:submit="importVouchers" class="grid gap-4 md:grid-cols-2">
                                <x-voucher-select label="Cari Partner" model="importForm.partner_id" :options="$this->partnerOptions()" />
                                <x-voucher-select label="Potong Saldo Partner" model="importForm.potong_saldo" :options="['yes' => 'YES', 'no' => 'NO']" />
                                <x-voucher-select label="Router" model="importForm.router_id" :options="$this->routerOptions()" />
                                <x-voucher-select label="Server RADIUS" model="importForm.radius_server_id" :options="$this->radiusServerOptions()" />
                                <x-voucher-select label="Outlet" model="importForm.outlet_id" :options="$this->outletOptions()" />
                                <x-voucher-select label="Kunci MAC Address" model="importForm.lock_mac" :options="['yes' => 'YES', 'no' => 'NO']" />
                                <x-voucher-select label="Profile Voucher (Tarif)" model="importForm.profile_id" :options="$this->profileOptions()" />
                                
                                {{-- Automatically Shown Fields --}}
                                <div class="grid grid-cols-3 gap-2 bg-gray-50 p-3 rounded-lg border border-gray-200">
                                    <div>
                                        <span class="block text-[10px] font-semibold text-gray-500 uppercase">HPP</span>
                                        <span class="text-sm font-bold text-gray-800">{{ $this->rupiah($importForm['hpp'] ?? 0) }}</span>
                                    </div>
                                    <div>
                                        <span class="block text-[10px] font-semibold text-gray-500 uppercase">Komisi</span>
                                        <span class="text-sm font-bold text-gray-800">{{ $this->rupiah($importForm['commission'] ?? 0) }}</span>
                                    </div>
                                    <div>
                                        <span class="block text-[10px] font-semibold text-gray-500 uppercase">Harga Jual</span>
                                        <span class="text-sm font-bold text-emerald-700">{{ $this->rupiah($importForm['price'] ?? 0) }}</span>
                                    </div>
                                </div>

                                <div class="md:col-span-2">
                                    <label class="block">
                                        <span class="mb-1 block text-sm font-semibold text-gray-700">Pilih File (Excel/CSV, TXT, JSON)</span>
                                        <input type="file" wire:model="importFile" class="w-full text-xs text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100" />
                                        @error('importFile') <span class="text-xs text-rose-600">{{ $message }}</span> @enderror
                                    </label>
                                </div>

                                <div class="md:col-span-2 flex justify-end">
                                    <x-filament::button type="submit" color="success" icon="heroicon-m-document-arrow-up">Tombol Import</x-filament::button>
                                </div>
                            </form>
                        </div>

                        {{-- Export Voucher --}}
                        <div x-show="stockPanel === 'export-panel'" class="rounded-lg bg-white p-5 ring-1 ring-gray-950/10 space-y-4" x-cloak>
                            <div class="text-sm font-bold uppercase text-gray-700 border-b pb-2">Export Data Voucher ke Excel</div>
                            <form wire:submit="exportVouchers" class="grid gap-4 md:grid-cols-3">
                                <x-voucher-select label="Pilih Partner" model="exportForm.partner_id" :options="$this->partnerOptions()" />
                                <x-voucher-select label="Pilih Outlet" model="exportForm.outlet_id" :options="$this->outletOptions()" />
                                <x-voucher-select label="Pilih Profile" model="exportForm.profile_id" :options="$this->profileOptions()" />

                                <div class="md:col-span-3 flex justify-end">
                                    <x-filament::button type="submit" color="success" icon="heroicon-m-document-arrow-down">Export File Excel / CSV</x-filament::button>
                                </div>
                            </form>
                        </div>

                        {{-- Basic Table Filter --}}
                        <div class="grid gap-2 md:grid-cols-[80px_1fr_1fr_1fr_1fr_2fr]">
                            <select class="h-9 rounded-md border-gray-300 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                                <option>10</option>
                                <option>25</option>
                                <option>50</option>
                            </select>
                            <input type="search" class="h-9 rounded-md border-gray-300 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500" placeholder="Cari partner">
                            <select class="h-9 rounded-md border-gray-300 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                                <option>All Router</option>
                                @foreach ($this->routerOptions() as $routerName)
                                    <option>{{ $routerName }}</option>
                                @endforeach
                            </select>
                            <select class="h-9 rounded-md border-gray-300 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                                <option>All Profile</option>
                                @foreach ($this->profileOptions() as $profileName)
                                    <option>{{ $profileName }}</option>
                                @endforeach
                            </select>
                            <input type="text" class="h-9 rounded-md border-gray-300 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500" placeholder="Tgl pembuatan">
                            <input type="search" class="h-9 rounded-md border-gray-300 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500" placeholder="Cari voucher...">
                        </div>
                    </div>
                @endif

                {{-- Template HTML Editor Tab --}}
                @if ($pageType === 'template')
                    <form wire:submit="saveTemplate" class="grid gap-4 rounded-lg bg-gray-50 p-4 ring-1 ring-gray-950/10 md:grid-cols-2">
                        <x-voucher-select label="Tenant" model="templateForm.tenant_id" :options="$this->tenantOptions()" />
                        <x-voucher-input label="Nama Template" model="templateForm.name" />
                        <x-voucher-input label="Nama Hotspot" model="templateForm.hotspot_name" />
                        <x-voucher-input label="DNS Name" model="templateForm.dns_name" />
                        <x-voucher-input label="CS Phone" model="templateForm.support_phone" />
                        <div class="md:col-span-2">
                            <label class="mb-1 block text-sm font-semibold text-gray-700">HTML Login MikroTik</label>
                            <textarea wire:model="templateForm.html_body" rows="14" class="w-full rounded-lg border-gray-300 font-mono text-xs shadow-sm focus:border-emerald-500 focus:ring-emerald-500"></textarea>
                        </div>
                        <div class="md:col-span-2 flex justify-end gap-2">
                            <x-filament::button type="submit" icon="heroicon-m-check" color="success">Simpan Template</x-filament::button>
                            <x-filament::button type="button" wire:click="downloadTemplateHtml" icon="heroicon-m-arrow-down-tray" color="info">Download login.html</x-filament::button>
                        </div>
                    </form>
                @endif

                {{-- Main Data Tables --}}
                <div class="overflow-x-auto rounded-lg bg-white ring-1 ring-gray-950/10">
                    <table class="w-full min-w-[1050px] text-left text-sm">
                        <thead>
                            <tr class="border-b border-gray-200 bg-gray-50">
                                {{-- Checkbox column for selection on Stok Voucher --}}
                                @if ($pageType === 'stock')
                                    <th class="w-10 px-4 py-3"><input type="checkbox" wire:model.live="selectAllVouchers" class="rounded border-gray-300 text-emerald-600 focus:ring-emerald-500"></th>
                                @endif
                                
                                @foreach ($this->columns() as $column)
                                    <th class="whitespace-nowrap px-4 py-3 text-xs font-bold uppercase text-gray-600">{{ $column }}</th>
                                @endforeach
                                @if (in_array($pageType, ['profile', 'stock'], true))
                                    <th class="px-4 py-3 text-xs font-bold uppercase text-gray-600">Action</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            {{-- Profile rows rendering --}}
                            @if ($pageType === 'profile')
                                @forelse ($this->profileRows() as $profile)
                                    <tr>
                                        <td class="px-4 py-3 font-semibold">
                                            <span class="inline-block px-2.5 py-0.5 rounded-full text-xs font-bold" style="background-color: {{ $profile->attributes['Color'] ?? '#059669' }}20; color: {{ $profile->attributes['Color'] ?? '#059669' }}">
                                                {{ $profile->name }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3">{{ $profile->attributes['Mikrotik-Group'] ?? '-' }}</td>
                                        <td class="px-4 py-3">{{ $profile->attributes['Mikrotik-Rate-Limit'] ?? '-' }}</td>
                                        <td class="px-4 py-3">{{ $profile->attributes['Shared-Users'] ?? '-' }}</td>
                                        <td class="px-4 py-3">{{ $profile->attributes['Quota-MB'] ?? 'Unlimited' }}</td>
                                        <td class="px-4 py-3">{{ $profile->attributes['Duration-Minutes'] ?? '-' }} menit</td>
                                        <td class="px-4 py-3 text-right tabular-nums">{{ $this->rupiah((float) ($profile->attributes['DPP'] ?? 0)) }}</td>
                                        <td class="px-4 py-3 text-right tabular-nums">{{ $this->rupiah((float) ($profile->attributes['PPN'] ?? 0)) }}</td>
                                        <td class="px-4 py-3 text-right tabular-nums">{{ $this->rupiah((float) ($profile->attributes['Price'] ?? 0)) }}</td>
                                        <td class="px-4 py-3">
                                            <span class="inline-block rounded-full px-2 py-0.5 text-xs font-bold {{ ($profile->attributes['Status'] ?? 'active') === 'active' ? 'bg-emerald-50 text-emerald-700' : 'bg-gray-100 text-gray-600' }}">
                                                {{ $profile->attributes['Status'] ?? 'active' }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 text-right">
                                            <div class="flex items-center gap-1.5">
                                                <x-filament::button type="button" size="xs" color="info" wire:click="editProfile('{{ $profile->id }}')">Edit</x-filament::button>
                                                @if (($profile->attributes['Status'] ?? 'active') === 'active')
                                                    <x-filament::button type="button" size="xs" color="warning" wire:click="deactivateProfile('{{ $profile->id }}')">Nonaktif</x-filament::button>
                                                @else
                                                    <x-filament::button type="button" size="xs" color="success" wire:click="activateProfile('{{ $profile->id }}')">Aktifkan</x-filament::button>
                                                @endif
                                                <x-filament::button type="button" size="xs" color="danger" wire:click="deleteProfile('{{ $profile->id }}')" wire:confirm="Apakah Anda yakin ingin menghapus profile ini?">Hapus</x-filament::button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="11" class="px-4 py-10 text-center text-gray-500">Belum ada profile voucher.</td></tr>
                                @endforelse
                            @elseif (in_array($pageType, ['stock', 'sold'], true))
                                {{-- Stock & Sold Voucher rows rendering --}}
                                @forelse ($this->voucherRows() as $voucher)
                                    <tr>
                                        @if ($pageType === 'stock')
                                            <td class="px-4 py-3"><input type="checkbox" wire:model.live="selectedVouchers" value="{{ $voucher->id }}" class="rounded border-gray-300 text-emerald-600 focus:ring-emerald-500"></td>
                                        @endif
                                        <td class="px-4 py-3 font-semibold">{{ $voucher->username }}</td>
                                        @if ($pageType === 'stock')
                                            <td class="px-4 py-3 font-mono">{{ $voucher->password }}</td>
                                        @endif
                                        <td class="px-4 py-3">
                                            @if ($voucher->profile)
                                                <span class="inline-block px-2 py-0.5 rounded text-xs font-bold" style="background-color: {{ $voucher->profile->attributes['Color'] ?? '#059669' }}20; color: {{ $voucher->profile->attributes['Color'] ?? '#059669' }}">
                                                    {{ $voucher->profile->name }}
                                                </span>
                                            @else
                                                -
                                            @endif
                                        </td>
                                        @if ($pageType === 'stock')
                                            <td class="px-4 py-3">{{ $voucher->router?->router_name ?? '-' }}</td>
                                            <td class="px-4 py-3">{{ $voucher->radiusServer?->name ?? '-' }}</td>
                                        @endif
                                        <td class="px-4 py-3 text-sky-700 font-medium">{{ $voucher->partner_name ?? '-' }}</td>
                                        <td class="px-4 py-3">{{ $voucher->outlet_name ?: ($voucher->outlet?->name ?? '-') }}</td>
                                        <td class="px-4 py-3 text-right tabular-nums font-semibold">{{ $this->rupiah((float) $voucher->price) }}</td>
                                        @if ($pageType === 'stock')
                                            <td class="px-4 py-3">
                                                <span class="inline-block rounded-full px-2 py-0.5 text-xs font-bold {{ $voucher->status === 'stock' ? 'bg-emerald-50 text-emerald-700' : 'bg-gray-100 text-gray-600' }}">
                                                    {{ strtoupper($voucher->status) }}
                                                </span>
                                            </td>
                                            <td class="px-4 py-3">
                                                <div class="flex items-center gap-1.5">
                                                    <span class="text-xs {{ $voucher->synced_at ? 'text-emerald-600 font-medium' : 'text-amber-600 font-medium' }}">
                                                        {{ $voucher->synced_at ? 'Synced' : 'Pending' }}
                                                    </span>
                                                    @if ($voucher->mac_address)
                                                        <span class="inline-block bg-sky-55 text-sky-700 font-bold px-1 py-0.5 rounded text-[9px] uppercase border border-sky-200">Locked</span>
                                                    @endif
                                                </div>
                                            </td>
                                            <td class="px-4 py-3">
                                                <div class="flex items-center gap-1.5">
                                                    <x-filament::button size="xs" color="success" wire:click="markSold('{{ $voucher->id }}')">Terjual</x-filament::button>
                                                </div>
                                            </td>
                                        @else
                                            <td class="px-4 py-3">{{ $voucher->activated_at?->format('d/m/Y H:i') ?? '-' }}</td>
                                            <td class="px-4 py-3">{{ $voucher->expires_at?->format('d/m/Y H:i') ?? '-' }}</td>
                                            <td class="px-4 py-3 font-mono text-xs">{{ $voucher->mac_address ?? '-' }}</td>
                                        @endif
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="{{ $pageType === 'stock' ? 12 : 9 }}" class="px-4 py-10 text-center text-gray-500">Belum ada data voucher.</td>
                                    </tr>
                                @endforelse
                            @elseif ($pageType === 'online')
                                {{-- Online sessions rows rendering --}}
                                @forelse ($this->onlineRows() as $row)
                                    <tr>
                                        <td class="px-4 py-3 font-semibold">{{ $row->username }}</td>
                                        <td class="px-4 py-3">{{ $row->framedipaddress ?: '-' }}</td>
                                        <td class="px-4 py-3 font-mono text-xs">{{ $row->callingstationid ?: '-' }}</td>
                                        <td class="px-4 py-3">{{ $row->acctstarttime ?: '-' }}</td>
                                        <td class="px-4 py-3">
                                            @if ($row->profile)
                                                <span class="inline-block px-2 py-0.5 rounded text-xs font-bold" style="background-color: {{ $row->profile->attributes['Color'] ?? '#059669' }}20; color: {{ $row->profile->attributes['Color'] ?? '#059669' }}">
                                                    {{ $row->profile->name }}
                                                </span>
                                            @else
                                                -
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="5" class="px-4 py-10 text-center text-gray-500">Belum ada voucher online.</td></tr>
                                @endforelse
                            @elseif ($pageType === 'recap')
                                {{-- Recap rows rendering --}}
                                @forelse ($this->recapRows() as $row)
                                    <tr>
                                        <td class="px-4 py-3">{{ $row->sale_date ?: '-' }}</td>
                                        <td class="px-4 py-3 text-sky-700 font-medium">{{ $row->partner_name ?: '-' }}</td>
                                        <td class="px-4 py-3">{{ $row->outlet_name ?: '-' }}</td>
                                        <td class="px-4 py-3">
                                            @php $pRecap = App\Models\RadiusProfile::find($row->profile_id); @endphp
                                            @if ($pRecap)
                                                <span class="inline-block px-2 py-0.5 rounded text-xs font-bold" style="background-color: {{ $pRecap->attributes['Color'] ?? '#059669' }}20; color: {{ $pRecap->attributes['Color'] ?? '#059669' }}">
                                                    {{ $pRecap->name }}
                                                </span>
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 font-bold">{{ $row->qty }}</td>
                                        <td class="px-4 py-3 text-right tabular-nums font-semibold">{{ $this->rupiah((float) $row->commission) }}</td>
                                        <td class="px-4 py-3 text-right tabular-nums font-bold text-emerald-700">{{ $this->rupiah((float) $row->price) }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="7" class="px-4 py-10 text-center text-gray-500">Belum ada rekap voucher.</td></tr>
                                @endforelse
                            @else
                                {{-- Template rows rendering --}}
                                @forelse ($this->templateRows() as $template)
                                    <tr>
                                        <td class="px-4 py-3 font-semibold">{{ $template->name }}</td>
                                        <td class="px-4 py-3">{{ $template->hotspot_name }}</td>
                                        <td class="px-4 py-3">{{ $template->dns_name ?: '-' }}</td>
                                        <td class="px-4 py-3">{{ $template->support_phone ?: '-' }}</td>
                                        <td class="px-4 py-3">
                                            <span class="inline-block rounded px-1.5 py-0.5 text-xs font-bold bg-emerald-50 text-emerald-700">
                                                {{ strtoupper($template->status) }}
                                            </span>
                                        </td>
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
