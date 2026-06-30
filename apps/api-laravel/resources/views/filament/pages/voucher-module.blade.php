<x-filament-panels::page>
    @once
        <style>
            .nex-voucher-toolbar {
                display: flex;
                flex-wrap: wrap;
                align-items: center;
                gap: 6px;
            }

            .nex-voucher-control {
                display: inline-flex !important;
                align-items: center;
                justify-content: center;
                gap: 6px;
                min-height: 32px;
                min-width: max-content;
                width: auto !important;
                max-width: max-content;
                padding: 0 10px;
                border: 1px solid transparent;
                border-radius: 6px;
                color: #ffffff !important;
                -webkit-text-fill-color: #ffffff;
                font-size: 12px;
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
                width: 14px;
                height: 14px;
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

            .nex-template-toolbar {
                display: grid;
                grid-template-columns: minmax(0, 1fr) auto;
                gap: 8px;
                align-items: end;
                padding: 10px;
                border: 1px solid #e5e7eb;
                border-radius: 8px;
                background: #f8fafc;
            }

            .nex-template-actions {
                display: flex !important;
                flex-wrap: wrap;
                align-items: center;
                justify-content: flex-end;
                gap: 6px;
                width: auto;
                min-width: max-content;
            }

            .nex-template-actions > .nex-voucher-control {
                flex: 0 0 auto !important;
                width: auto !important;
                min-width: 86px;
                max-width: none;
                padding-inline: 12px;
            }

            .nex-template-select {
                display: grid;
                min-width: 0;
                gap: 4px;
            }

            .nex-voucher-stat-card {
                min-height: 86px;
                padding-right: 70px;
            }

            .nex-voucher-stat-icon {
                position: absolute;
                right: 20px;
                top: 50%;
                display: flex;
                width: 40px;
                height: 40px;
                transform: translateY(-50%);
                align-items: center;
                justify-content: center;
                color: var(--nex-stat-color);
                pointer-events: none;
            }

            .nex-voucher-stat-icon svg,
            .nex-voucher-stat-svg {
                width: 32px !important;
                min-width: 32px !important;
                max-width: 32px !important;
                height: 32px !important;
                min-height: 32px !important;
                max-height: 32px !important;
                display: block !important;
                flex: none !important;
                color: currentColor !important;
                stroke: currentColor !important;
            }

            .nex-voucher-filterbar {
                display: grid;
                grid-template-columns: minmax(148px, 176px) minmax(150px, 190px) minmax(170px, 220px) minmax(150px, 200px) 84px minmax(220px, 1fr);
                gap: 8px;
                align-items: center;
                padding: 10px;
                border: 1px solid #e5e7eb;
                border-radius: 8px;
                background: #f8fafc;
            }

            .nex-voucher-filterbar--simple {
                grid-template-columns: 84px minmax(220px, 1fr);
            }

            .nex-voucher-filterbar--recap {
                grid-template-columns: 82px 82px minmax(140px, 160px) 92px 84px minmax(220px, 1fr);
            }

            .nex-voucher-field {
                display: flex;
                height: 32px;
                min-width: 0;
                overflow: hidden;
                border: 1px solid #d1d5db;
                border-radius: 6px;
                background: #ffffff;
                box-shadow: 0 1px 1px rgba(15, 23, 42, 0.04);
            }

            .nex-voucher-field:focus-within {
                border-color: #10b981;
                box-shadow: 0 0 0 1px #10b981;
            }

            .nex-voucher-field > span {
                display: inline-flex;
                width: 32px;
                flex: none;
                align-items: center;
                justify-content: center;
                border-right: 1px solid #e5e7eb;
                background: #f8fafc;
                color: #64748b;
            }

            .nex-voucher-field svg {
                width: 14px;
                height: 14px;
            }

            .nex-voucher-field input,
            .nex-voucher-field select,
            .nex-voucher-select-compact {
                min-width: 0;
                height: 30px;
                flex: 1;
                border: 0;
                font-size: 12px;
                line-height: 1.2;
            }

            .nex-voucher-select-compact {
                height: 32px;
                border: 1px solid #d1d5db;
                border-radius: 6px;
                background-color: #ffffff;
                box-shadow: 0 1px 1px rgba(15, 23, 42, 0.04);
            }

            .nex-voucher-table th {
                padding-top: 8px !important;
                padding-bottom: 8px !important;
                font-size: 11px;
            }

            .nex-voucher-table td {
                padding-top: 8px !important;
                padding-bottom: 8px !important;
                font-size: 12px;
            }

            @media (max-width: 1279px) {
                .nex-voucher-filterbar,
                .nex-voucher-filterbar--recap {
                    grid-template-columns: repeat(2, minmax(0, 1fr));
                }
            }

            @media (max-width: 640px) {
                .nex-voucher-filterbar,
                .nex-voucher-filterbar--simple,
                .nex-voucher-filterbar--recap,
                .nex-template-toolbar {
                    grid-template-columns: 1fr;
                }

                .nex-template-actions {
                    justify-content: flex-start;
                }
            }

            .nex-voucher-code-editor {
                min-height: 360px;
                border-color: #1f2937 !important;
                background: #252836 !important;
                color: #e5e7eb !important;
                font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
                font-size: 12px;
                line-height: 1.55;
                tab-size: 4;
            }
        </style>
        <script>
            window.addEventListener('voucher-print-ready', (event) => {
                if (! event.detail?.url) {
                    return;
                }

                if (window.nexVoucherPrintWindow && ! window.nexVoucherPrintWindow.closed) {
                    window.nexVoucherPrintWindow.location.href = event.detail.url;
                    window.nexVoucherPrintWindow.focus();
                    window.nexVoucherPrintWindow = null;

                    return;
                }

                window.open(event.detail.url, '_blank', 'noopener,noreferrer');
            });

            window.addEventListener('voucher-export-ready', () => {
                // Reserved for Livewire streamed downloads.
            });

            window.addEventListener('voucher-print-empty', () => {
                if (window.nexVoucherPrintWindow && ! window.nexVoucherPrintWindow.closed) {
                    window.nexVoucherPrintWindow.close();
                    window.nexVoucherPrintWindow = null;
                }
            });
        </script>
    @endonce

    <div class="space-y-4">
        {{-- Top Statistics Panels --}}
        @if (count($this->stats()))
            <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
                @foreach ($this->stats() as $stat)
                    <div class="nex-voucher-stat-card relative overflow-hidden rounded-lg bg-white p-4 shadow-sm ring-1 ring-gray-950/5">
                        <div class="min-w-0">
                            <div class="truncate text-xs font-bold uppercase text-gray-700">{{ $stat['label'] }}</div>
                            <div class="mt-1.5 text-xl font-bold leading-none" style="color: {{ $stat['color'] }}">{{ $stat['value'] }}</div>
                            <div class="mt-1.5 truncate text-xs font-medium text-gray-500">{{ $stat['description'] }}</div>
                        </div>
                        <div class="nex-voucher-stat-icon" style="--nex-stat-color: {{ $stat['color'] }}">
                            <x-filament::icon :icon="$stat['icon']" class="nex-voucher-stat-svg" />
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        <x-filament::section>
            <x-slot name="heading">{{ $this->headingTitle() }}</x-slot>

            <div class="space-y-4">
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
                        {{-- Control Panel Navigation Bar --}}
                        <div class="nex-voucher-toolbar">
                            <button type="button" class="nex-voucher-control nex-voucher-control--sky" x-on:click="stockPanel = stockPanel === 'menu' ? null : 'menu'">
                                <x-filament::icon icon="heroicon-m-bars-3" class="h-4 w-4" /> MENU
                            </button>
                            <button type="button" class="nex-voucher-control nex-voucher-control--rose" x-on:click="window.nexVoucherPrintWindow = window.open('about:blank', '_blank')" wire:click="openPrintTab">
                                <x-filament::icon icon="heroicon-m-printer" class="h-4 w-4" /> QUICK PRINT
                            </button>
                            <button type="button" class="nex-voucher-control nex-voucher-control--emerald" x-on:click="stockPanel = stockPanel === 'create-user' ? null : 'create-user'">
                                <x-filament::icon icon="heroicon-m-plus-circle" class="h-4 w-4" /> BUAT USER
                            </button>
                            <button type="button" class="nex-voucher-control nex-voucher-control--blue" x-on:click="stockPanel = stockPanel === 'create-voucher' ? null : 'create-voucher'">
                                <x-filament::icon icon="heroicon-m-plus-circle" class="h-4 w-4" /> BUAT VOUCHER
                            </button>
                            <button type="button" class="nex-voucher-control nex-voucher-control--slate" x-on:click="stockPanel = stockPanel === 'outlet' ? null : 'outlet'">
                                <x-filament::icon icon="heroicon-m-users" class="h-4 w-4" /> OUTLET
                            </button>
                            <button type="button" class="nex-voucher-control nex-voucher-control--rose" x-on:click="stockPanel = stockPanel === 'setting' ? null : 'setting'">
                                <x-filament::icon icon="heroicon-m-cog-6-tooth" class="h-4 w-4" /> SETTING
                            </button>
                            <button type="button" class="nex-voucher-control nex-voucher-control--blue" x-on:click="stockPanel = stockPanel === 'import' ? null : 'import'">
                                <x-filament::icon icon="heroicon-m-document-arrow-up" class="h-4 w-4" /> IMPORT
                            </button>
                            <button type="button" class="nex-voucher-control nex-voucher-control--emerald" x-on:click="stockPanel = stockPanel === 'export-panel' ? null : 'export-panel'">
                                <x-filament::icon icon="heroicon-m-document-arrow-down" class="h-4 w-4" /> EXPORT
                            </button>
                        </div>

                        {{-- Aksi Terpilih Bulk Submenu --}}
                        <div x-show="stockPanel === 'menu'" class="rounded-lg border border-sky-100 bg-sky-50/50 p-4 space-y-3" x-cloak>
                            <div class="text-xs font-bold uppercase text-sky-800">Aksi Massal Voucher Terpilih ({{ count($selectedVouchers) }} item)</div>
                            <div class="flex flex-wrap items-center gap-2">
                                <button type="button" x-on:click="window.nexVoucherPrintWindow = window.open('about:blank', '_blank')" wire:click="openPrintTab" class="inline-flex items-center gap-1 rounded bg-white px-3 py-1.5 text-xs font-semibold text-gray-700 shadow-sm ring-1 ring-gray-900/5 hover:bg-gray-50"><x-filament::icon icon="heroicon-m-printer" class="h-4 w-4 text-sky-600" /> Cetak Terpilih</button>
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
                            <x-voucher-select label="Partner" model="voucherForm.partner_id" :options="$this->partnerOptions()" />
                            <x-voucher-select label="Potong Saldo Partner" model="voucherForm.potong_saldo" :options="['yes' => 'YES', 'no' => 'NO']" />
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
                                <button type="button" wire:click="downloadImportFormat" class="text-xs font-semibold text-emerald-600 hover:underline">Unduh Format Data Import (CSV)</button>
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
                                        <span class="mb-1 block text-sm font-semibold text-gray-700">Pilih File (CSV, TXT, JSON)</span>
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
                        <div class="nex-voucher-filterbar">
                            <label class="nex-voucher-field">
                                <span>
                                    <x-filament::icon icon="heroicon-m-calendar-days" class="h-4 w-4" />
                                </span>
                                <input type="date" wire:model.live="stockDate" class="min-w-0 flex-1 border-0 text-sm focus:ring-0" aria-label="Tanggal pembuatan">
                            </label>

                            <label class="nex-voucher-field">
                                <span>
                                    <x-filament::icon icon="heroicon-m-identification" class="h-4 w-4" />
                                </span>
                                <select wire:model.live="stockProfileId" class="min-w-0 flex-1 border-0 text-sm focus:ring-0" aria-label="Filter profile">
                                    <option value="">ALL PROFILE</option>
                                    @foreach ($this->profileOptions() as $id => $name)
                                        <option value="{{ $id }}">{{ $name }}</option>
                                    @endforeach
                                </select>
                            </label>

                            <label class="nex-voucher-field">
                                <span>
                                    <x-filament::icon icon="heroicon-m-server-stack" class="h-4 w-4" />
                                </span>
                                <select wire:model.live="stockRouterId" class="min-w-0 flex-1 border-0 text-sm focus:ring-0" aria-label="Filter router">
                                    <option value="">ALL ROUTER</option>
                                    @foreach ($this->routerOptions() as $id => $name)
                                        <option value="{{ $id }}">{{ $name }}</option>
                                    @endforeach
                                </select>
                            </label>

                            <label class="nex-voucher-field">
                                <span>
                                    <x-filament::icon icon="heroicon-m-user-group" class="h-4 w-4" />
                                </span>
                                <select wire:model.live="stockPartnerId" class="min-w-0 flex-1 border-0 text-sm focus:ring-0" aria-label="Filter partner">
                                    <option value="">Pilih partner</option>
                                    @foreach ($this->partnerOptions() as $id => $name)
                                        <option value="{{ $id }}">{{ $name }}</option>
                                    @endforeach
                                </select>
                            </label>

                            <label class="nex-voucher-field">
                                <span>
                                    <x-filament::icon icon="heroicon-m-list-bullet" class="h-4 w-4" />
                                </span>
                                <select wire:model.live="stockPerPage" class="min-w-0 flex-1 border-0 text-sm focus:ring-0" aria-label="Jumlah baris">
                                    <option value="10">10</option>
                                    <option value="25">25</option>
                                    <option value="50">50</option>
                                    <option value="100">100</option>
                                </select>
                            </label>

                            <label class="nex-voucher-field">
                                <span>
                                    <x-filament::icon icon="heroicon-m-magnifying-glass" class="h-4 w-4" />
                                </span>
                                <input type="search" wire:model.live.debounce.350ms="stockSearch" class="min-w-0 flex-1 border-0 text-sm focus:ring-0" placeholder="Cari voucher..." aria-label="Cari voucher">
                            </label>
                        </div>
                    </div>
                @endif

                {{-- Voucher Terjual Interface --}}
                @if ($pageType === 'sold')
                    <div x-data="{ soldPanel: null }" class="space-y-4">
                        <div class="nex-voucher-toolbar">
                            <button type="button" class="nex-voucher-control nex-voucher-control--sky" x-on:click="soldPanel = soldPanel === 'menu' ? null : 'menu'">
                                <x-filament::icon icon="heroicon-m-bars-3" class="h-4 w-4" /> MENU
                            </button>
                            <button type="button" class="nex-voucher-control nex-voucher-control--blue" wire:click="exportSoldVouchers">
                                <x-filament::icon icon="heroicon-m-document-arrow-down" class="h-4 w-4" /> EXPORT
                            </button>
                            <button type="button" class="nex-voucher-control nex-voucher-control--emerald" wire:click="exportSoldRecap">
                                <x-filament::icon icon="heroicon-m-document-text" class="h-4 w-4" /> REKAPITULASI
                            </button>
                            <button type="button" class="nex-voucher-control" style="background:#f59e0b !important" x-on:click="soldPanel = soldPanel === 'chart' ? null : 'chart'">
                                <x-filament::icon icon="heroicon-m-chart-bar" class="h-4 w-4" /> GRAFIK PENJUALAN
                            </button>
                            <button type="button" class="nex-voucher-control nex-voucher-control--rose" wire:click="deleteExpiredVouchers" wire:confirm="Apakah Anda yakin ingin menghapus semua voucher expired?">
                                <x-filament::icon icon="heroicon-m-archive-box-x-mark" class="h-4 w-4" /> HAPUS EXPIRED
                            </button>
                        </div>

                        <div x-show="soldPanel === 'menu'" class="rounded-lg border border-sky-100 bg-sky-50/50 p-4 space-y-3" x-cloak>
                            <div class="text-xs font-bold uppercase text-sky-800">Aksi Voucher Terjual ({{ count($selectedVouchers) }} item terpilih)</div>
                            <div class="flex flex-wrap items-center gap-2">
                                <button type="button" wire:click="exportSoldVouchers" class="inline-flex items-center gap-1 rounded bg-white px-3 py-1.5 text-xs font-semibold text-gray-700 shadow-sm ring-1 ring-gray-900/5 hover:bg-gray-50"><x-filament::icon icon="heroicon-m-document-arrow-down" class="h-4 w-4 text-sky-600" /> Export Terfilter</button>
                                <button type="button" wire:click="exportSoldRecap" class="inline-flex items-center gap-1 rounded bg-white px-3 py-1.5 text-xs font-semibold text-gray-700 shadow-sm ring-1 ring-gray-900/5 hover:bg-gray-50"><x-filament::icon icon="heroicon-m-document-text" class="h-4 w-4 text-emerald-600" /> Rekapitulasi</button>
                                <button type="button" wire:click="deleteExpiredVouchers" wire:confirm="Apakah Anda yakin ingin menghapus semua voucher expired?" class="inline-flex items-center gap-1 rounded bg-rose-600 px-3 py-1.5 text-xs font-semibold text-white shadow-sm hover:bg-rose-700"><x-filament::icon icon="heroicon-m-archive-box-x-mark" class="h-4 w-4" /> Hapus Expired</button>
                            </div>
                        </div>

                        <div x-show="soldPanel === 'chart'" class="rounded-lg border border-amber-100 bg-amber-50/40 p-4" x-cloak>
                            <div class="mb-3 text-xs font-bold uppercase text-amber-800">Grafik Penjualan 14 Hari Terakhir</div>
                            <div class="flex h-36 items-end gap-3 overflow-x-auto rounded-lg bg-white p-4 ring-1 ring-gray-950/5">
                                @forelse ($this->soldChartRows() as $bar)
                                    <div class="flex min-w-12 flex-col items-center gap-2">
                                        <div class="text-[10px] font-bold text-gray-600">{{ $bar['qty'] }}</div>
                                        <div class="w-8 rounded-t bg-amber-500" style="height: {{ $bar['height'] }}px"></div>
                                        <div class="text-[10px] text-gray-500">{{ \Illuminate\Support\Carbon::parse($bar['date'])->format('d/m') }}</div>
                                    </div>
                                @empty
                                    <div class="w-full text-center text-sm text-gray-500">Belum ada data grafik penjualan.</div>
                                @endforelse
                            </div>
                        </div>

                        <div class="nex-voucher-filterbar">
                            <label class="nex-voucher-field">
                                <span>
                                    <x-filament::icon icon="heroicon-m-calendar-days" class="h-4 w-4" />
                                </span>
                                <input type="date" wire:model.live="stockDate" class="min-w-0 flex-1 border-0 text-sm focus:ring-0" aria-label="Tanggal terjual">
                            </label>

                            <label class="nex-voucher-field">
                                <span>
                                    <x-filament::icon icon="heroicon-m-identification" class="h-4 w-4" />
                                </span>
                                <select wire:model.live="stockProfileId" class="min-w-0 flex-1 border-0 text-sm focus:ring-0" aria-label="Filter profile">
                                    <option value="">ALL PROFILE</option>
                                    @foreach ($this->profileOptions() as $id => $name)
                                        <option value="{{ $id }}">{{ $name }}</option>
                                    @endforeach
                                </select>
                            </label>

                            <label class="nex-voucher-field">
                                <span>
                                    <x-filament::icon icon="heroicon-m-server-stack" class="h-4 w-4" />
                                </span>
                                <select wire:model.live="stockRouterId" class="min-w-0 flex-1 border-0 text-sm focus:ring-0" aria-label="Filter router">
                                    <option value="">ALL ROUTER</option>
                                    @foreach ($this->routerOptions() as $id => $name)
                                        <option value="{{ $id }}">{{ $name }}</option>
                                    @endforeach
                                </select>
                            </label>

                            <label class="nex-voucher-field">
                                <span>
                                    <x-filament::icon icon="heroicon-m-user-group" class="h-4 w-4" />
                                </span>
                                <select wire:model.live="stockPartnerId" class="min-w-0 flex-1 border-0 text-sm focus:ring-0" aria-label="Filter partner">
                                    <option value="">Pilih partner</option>
                                    @foreach ($this->partnerOptions() as $id => $name)
                                        <option value="{{ $id }}">{{ $name }}</option>
                                    @endforeach
                                </select>
                            </label>

                            <label class="nex-voucher-field">
                                <span>
                                    <x-filament::icon icon="heroicon-m-list-bullet" class="h-4 w-4" />
                                </span>
                                <select wire:model.live="stockPerPage" class="min-w-0 flex-1 border-0 text-sm focus:ring-0" aria-label="Jumlah baris">
                                    <option value="10">10</option>
                                    <option value="25">25</option>
                                    <option value="50">50</option>
                                    <option value="100">100</option>
                                </select>
                            </label>

                            <label class="nex-voucher-field">
                                <span>
                                    <x-filament::icon icon="heroicon-m-magnifying-glass" class="h-4 w-4" />
                                </span>
                                <input type="search" wire:model.live.debounce.350ms="stockSearch" class="min-w-0 flex-1 border-0 text-sm focus:ring-0" placeholder="Cari voucher..." aria-label="Cari voucher">
                            </label>
                        </div>
                    </div>
                @endif

                {{-- Voucher Online Interface --}}
                @if ($pageType === 'online')
                    <div class="space-y-4">
                        <div class="nex-voucher-toolbar">
                            <button type="button" class="nex-voucher-control nex-voucher-control--slate" wire:click="kickSelectedOnlineUsers" wire:confirm="Kick user online terpilih?">
                                <x-filament::icon icon="heroicon-m-power" class="h-4 w-4" /> KICK USER
                            </button>
                            <button type="button" class="nex-voucher-control nex-voucher-control--rose" wire:click="deleteSelectedOnlineSessions" wire:confirm="Hapus session online terpilih?">
                                <x-filament::icon icon="heroicon-m-minus-circle" class="h-4 w-4" /> HAPUS
                            </button>
                            <button type="button" class="nex-voucher-control nex-voucher-control--emerald" wire:click="syncOnlineSessions">
                                <x-filament::icon icon="heroicon-m-arrow-path" class="h-4 w-4" /> SINKRONKAN
                            </button>
                        </div>

                        <div class="nex-voucher-filterbar nex-voucher-filterbar--simple">
                            <label class="nex-voucher-field">
                                <span>
                                    <x-filament::icon icon="heroicon-m-list-bullet" class="h-4 w-4" />
                                </span>
                                <select wire:model.live="stockPerPage" class="min-w-0 flex-1 border-0 text-sm focus:ring-0" aria-label="Jumlah baris">
                                    <option value="10">10</option>
                                    <option value="13">13</option>
                                    <option value="25">25</option>
                                    <option value="50">50</option>
                                </select>
                            </label>
                            <label class="nex-voucher-field">
                                <span>
                                    <x-filament::icon icon="heroicon-m-magnifying-glass" class="h-4 w-4" />
                                </span>
                                <input type="search" wire:model.live.debounce.350ms="stockSearch" class="min-w-0 flex-1 border-0 text-sm focus:ring-0" placeholder="Cari voucher online..." aria-label="Cari voucher online">
                            </label>
                        </div>
                    </div>
                @endif

                {{-- Voucher Offline Interface --}}
                @if ($pageType === 'offline')
                    <div class="nex-voucher-filterbar nex-voucher-filterbar--simple">
                        <label class="nex-voucher-field">
                            <span>
                                <x-filament::icon icon="heroicon-m-list-bullet" class="h-4 w-4" />
                            </span>
                            <select wire:model.live="stockPerPage" class="min-w-0 flex-1 border-0 text-sm focus:ring-0" aria-label="Jumlah baris">
                                <option value="10">10</option>
                                <option value="13">13</option>
                                <option value="25">25</option>
                                <option value="50">50</option>
                            </select>
                        </label>
                        <label class="nex-voucher-field">
                            <span>
                                <x-filament::icon icon="heroicon-m-magnifying-glass" class="h-4 w-4" />
                            </span>
                            <input type="search" wire:model.live.debounce.350ms="stockSearch" class="min-w-0 flex-1 border-0 text-sm focus:ring-0" placeholder="Cari voucher offline..." aria-label="Cari voucher offline">
                        </label>
                    </div>
                @endif

                {{-- Rekap Pembuatan Voucher Interface --}}
                @if ($pageType === 'recap')
                    <div class="space-y-4">
                        <div class="rounded-md border border-emerald-200 bg-emerald-50 px-3 py-2 text-xs text-emerald-800">
                            <x-filament::icon icon="heroicon-m-information-circle" class="mr-1 inline h-4 w-4" />
                            Table ini berisi informasi rekap voucher yang dikelompokkan berdasarkan kode dan tanggal pembuatan.
                        </div>
                        <div class="nex-voucher-filterbar nex-voucher-filterbar--recap">
                            <button type="button" wire:click="openRecapExportModal" class="nex-voucher-control nex-voucher-control--blue">
                                <x-filament::icon icon="heroicon-m-document-arrow-down" class="h-4 w-4" /> EXPORT
                            </button>
                            <button type="button" wire:click="openRecapPrintModal" class="nex-voucher-control nex-voucher-control--slate">
                                <x-filament::icon icon="heroicon-m-printer" class="h-4 w-4" /> PRINT
                            </button>
                            <label class="nex-voucher-field">
                                <span>
                                    <x-filament::icon icon="heroicon-m-calendar-days" class="h-4 w-4" />
                                </span>
                                <select wire:model.live="recapMonth" class="min-w-0 flex-1 border-0 text-sm focus:ring-0">
                                    @foreach ($this->monthOptions() as $id => $name)
                                        <option value="{{ $id }}">{{ $name }}</option>
                                    @endforeach
                                </select>
                            </label>
                            <select wire:model.live="recapYear" class="nex-voucher-select-compact focus:border-emerald-500 focus:ring-emerald-500">
                                @foreach ($this->yearOptions() as $id => $name)
                                    <option value="{{ $id }}">{{ $name }}</option>
                                @endforeach
                            </select>
                            <label class="nex-voucher-field">
                                <span>
                                    <x-filament::icon icon="heroicon-m-list-bullet" class="h-4 w-4" />
                                </span>
                                <select wire:model.live="stockPerPage" class="min-w-0 flex-1 border-0 text-sm focus:ring-0">
                                    <option value="10">10</option>
                                    <option value="25">25</option>
                                    <option value="50">50</option>
                                </select>
                            </label>
                            <label class="nex-voucher-field">
                                <span>
                                    <x-filament::icon icon="heroicon-m-magnifying-glass" class="h-4 w-4" />
                                </span>
                                <input type="search" wire:model.live.debounce.350ms="stockSearch" class="min-w-0 flex-1 border-0 text-sm focus:ring-0" placeholder="Cari voucher...">
                            </label>
                        </div>
                    </div>
                @endif

                {{-- Template HTML Editor Tab --}}
                @if ($pageType === 'template')
                    <form wire:submit="saveTemplate" class="grid gap-8 lg:grid-cols-[minmax(0,1fr)_360px]">
                        <div class="space-y-5">
                            <div class="nex-template-toolbar">
                                <label class="nex-template-select">
                                    <span class="text-xs font-semibold uppercase text-gray-600">Pilih</span>
                                    <select wire:model.live="editingTemplateId" class="h-9 rounded-md border-gray-300 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                                        <option value="">Template Baru</option>
                                        @foreach ($this->templateOptions() as $id => $name)
                                            <option value="{{ $id }}">{{ $name }}</option>
                                        @endforeach
                                    </select>
                                </label>

                                <div class="nex-template-actions w-auto">
                                    <button type="submit" class="nex-voucher-control nex-voucher-control--blue !w-auto">
                                        <x-filament::icon icon="heroicon-m-bookmark-square" class="h-4 w-4" /> UPDATE
                                    </button>
                                    <button type="button" x-on:click="window.nexVoucherPrintWindow = window.open('about:blank', '_blank')" wire:click="previewTemplate" class="nex-voucher-control !w-auto" style="background:#0891b2 !important">
                                        <x-filament::icon icon="heroicon-m-eye" class="h-4 w-4" /> PREVIEW
                                    </button>
                                    <button type="button" wire:click="deleteTemplate" wire:confirm="Hapus template voucher ini?" class="nex-voucher-control nex-voucher-control--rose !w-auto">
                                        <x-filament::icon icon="heroicon-m-trash" class="h-4 w-4" /> HAPUS
                                    </button>
                                    <button type="button" wire:click="addTemplate" class="nex-voucher-control nex-voucher-control--emerald !w-auto">
                                        <x-filament::icon icon="heroicon-m-plus" class="h-4 w-4" /> TAMBAH
                                    </button>
                                </div>
                            </div>

                            <div class="border-t border-gray-200 pt-4">
                                <label class="mb-1 block text-xs font-semibold uppercase text-gray-600">Nama Template</label>
                                <input type="text" wire:model="templateForm.name" class="h-9 w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                            </div>

                            <div class="grid gap-4 md:grid-cols-3">
                                <x-voucher-input label="Nama Hotspot" model="templateForm.hotspot_name" />
                                <x-voucher-input label="DNS Name" model="templateForm.dns_name" />
                                <x-voucher-input label="CS Phone" model="templateForm.support_phone" />
                            </div>

                            <div>
                                <h3 class="mb-2 flex items-center gap-2 text-xl font-semibold text-gray-800">
                                    <x-filament::icon icon="heroicon-m-code-bracket" class="h-5 w-5" /> HEADER
                                </h3>
                                <textarea wire:model="templateForm.html_body" rows="18" class="nex-voucher-code-editor w-full rounded-md shadow-sm focus:border-emerald-500 focus:ring-emerald-500"></textarea>
                            </div>

                            <div>
                                <h3 class="mb-2 flex items-center gap-2 text-xl font-semibold text-gray-800">
                                    <x-filament::icon icon="heroicon-m-code-bracket" class="h-5 w-5" /> FOOTER
                                </h3>
                                <textarea readonly rows="5" class="nex-voucher-code-editor w-full rounded-md opacity-90 shadow-sm">&lt;/body&gt;
&lt;/html&gt;</textarea>
                            </div>
                        </div>

                        <div class="space-y-5 rounded-lg bg-white p-5 ring-1 ring-gray-950/10">
                            <div>
                                <h3 class="text-2xl font-semibold text-gray-800"># DAFTAR PARAMETER</h3>
                                <p class="mt-2 text-sm text-gray-500">Daftar parameter yang bisa dimasukkan kedalam template voucher</p>
                            </div>
                            <div class="border-t border-sky-300 pt-4 text-sm text-gray-700">
                                <ul class="list-disc space-y-1 pl-5">
                                    @foreach ([
                                        '#username# : Username',
                                        '#password# : Password',
                                        '#profile# : Profile',
                                        '#harga# : Harga jual',
                                        '#aktif# : Masa aktif',
                                        '#durasi# : Durasi',
                                        '#kuota# : Kuota',
                                        '#color# : Warna voucher',
                                        '#dns# : DNS Name',
                                        '#hsname# : Nama hotspot',
                                        '#printdate# : Tanggal cetak',
                                        '#printtime# : Jam cetak',
                                        '#partner# / #mitra# : Nama partner',
                                        '#outlet# : Nama outlet',
                                        '#nomor# : Nomor urut',
                                        '#logo# : Logo voucher',
                                        '#kode# : Nomor Kode pembuatan',
                                        '#partnerphone# / #mitraphone# : Nomor HP partner',
                                        '#loginurl# : URL login otomatis QR',
                                        '#csphone# : Nomor HP CS',
                                    ] as $parameter)
                                        <li>{{ $parameter }}</li>
                                    @endforeach
                                </ul>
                            </div>
                            <p class="border-t border-sky-300 pt-4 text-sm text-gray-500">Untuk menampilkan model kode voucher, sintak harus di apit dengan hastag #kodevoucher# dalam 1 baris</p>
                            <p class="text-sm text-gray-500">Untuk menampilkan model username dan password, sintak harus di apit dengan hastag #usernamepassword# dalam 1 baris</p>
                        </div>
                    </form>
                @endif

                @if ($pageType !== 'template')
                    {{-- Main Data Tables --}}
                    @php
                        $voucherRows = in_array($pageType, ['stock', 'sold'], true) ? $this->voucherRows() : null;
                        $onlineRows = $pageType === 'online' ? $this->onlineRows() : null;
                        $offlineRows = $pageType === 'offline' ? $this->offlineRows() : null;
                        $recapRows = $pageType === 'recap' ? collect($this->recapRows()) : collect();
                    @endphp
                    <div class="overflow-x-auto rounded-lg bg-white ring-1 ring-gray-950/10">
                    <table @class([
                        'nex-voucher-table w-full text-left text-sm',
                        'min-w-[1700px]' => $pageType === 'stock',
                        'min-w-[2200px]' => $pageType === 'sold',
                        'min-w-[1900px]' => $pageType === 'online',
                        'min-w-[1500px]' => in_array($pageType, ['offline', 'recap'], true),
                        'min-w-[1050px]' => ! in_array($pageType, ['stock', 'sold', 'online', 'offline', 'recap'], true),
                    ])>
                        <thead>
                            <tr class="border-b border-gray-200 bg-gray-50">
                                {{-- Checkbox column for voucher selection --}}
                                @if (in_array($pageType, ['stock', 'sold', 'online'], true))
                                    <th class="w-10 px-4 py-3"><input type="checkbox" wire:model.live="selectAllVouchers" class="rounded border-gray-300 text-emerald-600 focus:ring-emerald-500"></th>
                                @endif
                                
                                @if ($pageType === 'stock')
                                    @foreach ([
                                        'kode' => 'Kode',
                                        'username' => 'Username',
                                        'password' => 'Password',
                                        'profile' => 'Profile',
                                        'router' => 'Router',
                                        'server' => 'Server',
                                        'mitra' => 'Partner',
                                        'outlet' => 'Outlet',
                                        'hpp' => 'HPP',
                                        'commission' => 'Komisi',
                                        'price' => 'Harga',
                                        'saldo' => 'Saldo',
                                        'admin' => 'Admin',
                                        'created_at' => 'Tgl Pembuatan',
                                    ] as $field => $label)
                                        <th class="whitespace-nowrap px-4 py-3 text-xs font-bold uppercase text-gray-600">
                                            <button type="button" wire:click="sortVouchers('{{ $field }}')" class="inline-flex items-center gap-2 uppercase">
                                                {{ $label }}
                                                <x-filament::icon :icon="$stockSort === $field ? ($stockSortDirection === 'asc' ? 'heroicon-m-arrow-up' : 'heroicon-m-arrow-down') : 'heroicon-m-arrows-up-down'" class="h-3.5 w-3.5 text-gray-400" />
                                            </button>
                                        </th>
                                    @endforeach
                                    <th class="w-10 px-4 py-3"></th>
                                @elseif ($pageType === 'sold')
                                    @foreach ([
                                        'username' => 'Username',
                                        'password' => 'Password',
                                        'profile' => 'Profile',
                                        'router' => 'Router',
                                        'server' => 'Server',
                                        'mitra' => 'Partner',
                                        'outlet' => 'Outlet',
                                        'hpp' => 'HPP',
                                        'commission' => 'Komisi',
                                        'price' => 'Harga',
                                        'saldo' => 'Saldo',
                                        'admin' => 'Admin',
                                        'kode' => 'Kode',
                                        'duration' => 'Durasi',
                                        'quota' => 'Kuota',
                                        'activated_at' => 'Tgl Aktif',
                                        'expires_at' => 'Tgl Expired',
                                        'mac_address' => 'MAC AC',
                                    ] as $field => $label)
                                        <th class="whitespace-nowrap px-4 py-3 text-xs font-bold uppercase text-gray-600">
                                            @if (in_array($field, ['duration', 'quota'], true))
                                                <span class="inline-flex items-center gap-2 uppercase">{{ $label }} <x-filament::icon icon="heroicon-m-arrows-up-down" class="h-3.5 w-3.5 text-gray-300" /></span>
                                            @else
                                                <button type="button" wire:click="sortVouchers('{{ $field }}')" class="inline-flex items-center gap-2 uppercase">
                                                    {{ $label }}
                                                    <x-filament::icon :icon="$stockSort === $field ? ($stockSortDirection === 'asc' ? 'heroicon-m-arrow-up' : 'heroicon-m-arrow-down') : 'heroicon-m-arrows-up-down'" class="h-3.5 w-3.5 text-gray-400" />
                                                </button>
                                            @endif
                                        </th>
                                    @endforeach
                                @else
                                    @foreach ($this->columns() as $column)
                                        <th class="whitespace-nowrap px-4 py-3 text-xs font-bold uppercase text-gray-600">{{ $column }}</th>
                                    @endforeach
                                @endif
                                @if ($pageType === 'profile')
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
                            @elseif ($pageType === 'stock')
                                {{-- Stock Voucher rows rendering --}}
                                @forelse ($voucherRows as $voucher)
                                    <tr>
                                        @php
                                            $passwordMask = str_repeat('*', max(8, min(10, strlen((string) $voucher->password))));
                                        @endphp
                                        <td class="px-4 py-3"><input type="checkbox" wire:model.live="selectedVouchers" value="{{ $voucher->id }}" class="rounded border-gray-300 text-emerald-600 focus:ring-emerald-500"></td>
                                        <td class="whitespace-nowrap px-4 py-3 font-medium text-gray-700">{{ $voucher->batch_code ?: '-' }}</td>
                                        <td class="whitespace-nowrap px-4 py-3 font-medium text-gray-700">{{ $voucher->username }}</td>
                                        <td class="whitespace-nowrap px-4 py-3 font-mono text-gray-700">{{ $passwordMask }}</td>
                                        <td class="whitespace-nowrap px-4 py-3 text-gray-700">{{ $voucher->profile?->name ?? '-' }}</td>
                                        <td class="whitespace-nowrap px-4 py-3 text-gray-700">{{ $voucher->router?->router_name ?? '-' }}</td>
                                        <td class="whitespace-nowrap px-4 py-3 text-gray-700">{{ $voucher->radiusServer?->name ?? '-' }}</td>
                                        <td class="whitespace-nowrap px-4 py-3 font-medium text-gray-700">{{ $voucher->partner_name ?: ($voucher->mitra?->name ?? 'SYSTEM') }}</td>
                                        <td class="whitespace-nowrap px-4 py-3 text-gray-700">{{ $voucher->outlet_name ?: ($voucher->outlet?->name ?? '-') }}</td>
                                        <td class="whitespace-nowrap px-4 py-3 tabular-nums text-gray-700">{{ number_format((float) $voucher->hpp, 0, ',', '.') }}</td>
                                        <td class="whitespace-nowrap px-4 py-3 tabular-nums text-gray-700">{{ number_format((float) $voucher->commission, 0, ',', '.') }}</td>
                                        <td class="whitespace-nowrap px-4 py-3 tabular-nums text-gray-700">{{ number_format((float) $voucher->price, 0, ',', '.') }}</td>
                                        <td class="whitespace-nowrap px-4 py-3">
                                            <span class="inline-flex rounded bg-amber-50 px-1.5 py-0.5 text-xs font-bold text-amber-500">
                                                {{ $voucher->balance_deducted ? 'Yes' : 'No' }}
                                            </span>
                                        </td>
                                        <td class="whitespace-nowrap px-4 py-3 font-medium text-gray-700">{{ $voucher->admin?->name ?? 'SYSTEM' }}</td>
                                        <td class="whitespace-nowrap px-4 py-3 tabular-nums text-gray-700">{{ $voucher->created_at?->format('d/m/Y H:i:s') ?? '-' }}</td>
                                        <td class="px-4 py-3 text-right">
                                            <span title="{{ $voucher->mac_address ? 'MAC terkunci' : 'MAC belum terkunci' }}" class="inline-flex text-gray-500">
                                                <x-filament::icon :icon="$voucher->mac_address ? 'heroicon-m-lock-closed' : 'heroicon-m-lock-open'" class="h-5 w-5" />
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="16" class="px-4 py-10 text-center text-gray-500">Belum ada data voucher.</td>
                                    </tr>
                                @endforelse
                            @elseif ($pageType === 'sold')
                                {{-- Sold Voucher rows rendering --}}
                                @forelse ($voucherRows as $voucher)
                                    <tr>
                                        @php
                                            $passwordMask = str_repeat('*', max(8, min(10, strlen((string) $voucher->password))));
                                        @endphp
                                        <td class="px-4 py-3"><input type="checkbox" wire:model.live="selectedVouchers" value="{{ $voucher->id }}" class="rounded border-gray-300 text-emerald-600 focus:ring-emerald-500"></td>
                                        <td class="whitespace-nowrap px-4 py-3 font-medium text-gray-700">{{ $voucher->username }}</td>
                                        <td class="whitespace-nowrap px-4 py-3 font-mono text-gray-700">{{ $passwordMask }}</td>
                                        <td class="whitespace-nowrap px-4 py-3 text-gray-700">{{ $voucher->profile?->name ?? '-' }}</td>
                                        <td class="whitespace-nowrap px-4 py-3 text-gray-700">{{ $voucher->router?->router_name ?? '-' }}</td>
                                        <td class="whitespace-nowrap px-4 py-3 text-gray-700">{{ $voucher->radiusServer?->name ?? '-' }}</td>
                                        <td class="whitespace-nowrap px-4 py-3 font-medium text-gray-700">{{ $voucher->partner_name ?: ($voucher->mitra?->name ?? 'SYSTEM') }}</td>
                                        <td class="whitespace-nowrap px-4 py-3 text-gray-700">{{ $voucher->outlet_name ?: ($voucher->outlet?->name ?? '-') }}</td>
                                        <td class="whitespace-nowrap px-4 py-3 tabular-nums text-gray-700">{{ number_format((float) $voucher->hpp, 0, ',', '.') }}</td>
                                        <td class="whitespace-nowrap px-4 py-3 tabular-nums text-gray-700">{{ number_format((float) $voucher->commission, 0, ',', '.') }}</td>
                                        <td class="whitespace-nowrap px-4 py-3 tabular-nums text-gray-700">{{ number_format((float) $voucher->price, 0, ',', '.') }}</td>
                                        <td class="whitespace-nowrap px-4 py-3">
                                            <span class="inline-flex rounded bg-amber-50 px-1.5 py-0.5 text-xs font-bold text-amber-500">
                                                {{ $voucher->balance_deducted ? 'Yes' : 'No' }}
                                            </span>
                                        </td>
                                        <td class="whitespace-nowrap px-4 py-3 font-medium text-gray-700">{{ $voucher->admin?->name ?? 'SYSTEM' }}</td>
                                        <td class="whitespace-nowrap px-4 py-3 tabular-nums text-gray-700">{{ $voucher->batch_code ?: '-' }}</td>
                                        <td class="whitespace-nowrap px-4 py-3 tabular-nums text-gray-700">{{ $this->voucherDurationLabel($voucher) }}</td>
                                        <td class="whitespace-nowrap px-4 py-3 tabular-nums text-gray-700">{{ $this->voucherQuotaLabel($voucher) }}</td>
                                        <td class="whitespace-nowrap px-4 py-3 tabular-nums text-gray-700">{{ $voucher->activated_at?->format('d/m/Y H:i:s') ?? '-' }}</td>
                                        <td class="whitespace-nowrap px-4 py-3 tabular-nums font-semibold {{ $voucher->expires_at && $voucher->expires_at->isPast() ? 'text-rose-500' : 'text-gray-700' }}">{{ $voucher->expires_at?->format('d/m/Y H:i:s') ?? '-' }}</td>
                                        <td class="whitespace-nowrap px-4 py-3 font-mono text-xs text-gray-700">{{ $voucher->mac_address ?? '-' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="19" class="px-4 py-10 text-center text-gray-500">Belum ada data voucher.</td>
                                    </tr>
                                @endforelse
                            @elseif ($pageType === 'online')
                                {{-- Online sessions rows rendering --}}
                                @forelse ($onlineRows as $row)
                                    <tr>
                                        <td class="px-4 py-3"><input type="checkbox" wire:model.live="selectedVouchers" value="{{ $row->id }}" class="rounded border-gray-300 text-emerald-600 focus:ring-emerald-500"></td>
                                        <td class="whitespace-nowrap px-4 py-3 font-semibold text-gray-700">{{ $row->username }}</td>
                                        <td class="whitespace-nowrap px-4 py-3">
                                            @if ($row->profile?->name)
                                                <span class="inline-block px-2 py-0.5 rounded text-xs font-bold" style="background-color: {{ $row->profile->attributes['Color'] ?? '#059669' }}20; color: {{ $row->profile->attributes['Color'] ?? '#059669' }}">
                                                    {{ $row->profile->name }}
                                                </span>
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td class="whitespace-nowrap px-4 py-3 tabular-nums text-gray-700">{{ $this->uptimeLabel($row->acctstarttime ?? null) }}</td>
                                        <td class="whitespace-nowrap px-4 py-3 tabular-nums text-gray-700">{{ $this->bytesLabel($row->acctinputoctets ?? 0) }}</td>
                                        <td class="whitespace-nowrap px-4 py-3 tabular-nums text-gray-700">{{ $this->bytesLabel($row->acctoutputoctets ?? 0) }}</td>
                                        <td class="whitespace-nowrap px-4 py-3 text-gray-700">{{ $row->router?->router_name ?? '-' }}</td>
                                        <td class="whitespace-nowrap px-4 py-3 text-gray-700">{{ $row->nasipaddress ?: '-' }}</td>
                                        <td class="whitespace-nowrap px-4 py-3 text-gray-700">{{ $row->radiusServer?->name ?? '-' }}</td>
                                        <td class="whitespace-nowrap px-4 py-3 font-mono text-xs text-gray-700">{{ $row->framedipaddress ?: '-' }}</td>
                                        <td class="whitespace-nowrap px-4 py-3 font-mono text-xs text-gray-700">{{ $row->callingstationid ?: ($row->mac_address ?? '-') }}</td>
                                        <td class="whitespace-nowrap px-4 py-3 font-medium text-gray-700">{{ $row->partner_name ?: ($row->mitra?->name ?? 'SYSTEM') }}</td>
                                        <td class="whitespace-nowrap px-4 py-3 text-gray-700">{{ $row->outlet_name ?: ($row->outlet?->name ?? '-') }}</td>
                                        <td class="whitespace-nowrap px-4 py-3 tabular-nums text-gray-700">{{ $row->acctstarttime ? \Illuminate\Support\Carbon::parse($row->acctstarttime)->format('d/m/Y H:i:s') : '-' }}</td>
                                        <td class="whitespace-nowrap px-4 py-3 tabular-nums text-gray-700">{{ $row->updated_at?->format('d/m/Y H:i:s') ?? '-' }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="15" class="px-4 py-10 text-center text-gray-500">Belum ada voucher online.</td></tr>
                                @endforelse
                            @elseif ($pageType === 'offline')
                                {{-- Offline sessions rows rendering --}}
                                @forelse ($offlineRows as $row)
                                    <tr>
                                        <td class="whitespace-nowrap px-4 py-3 font-semibold text-gray-700">{{ $row->username }}</td>
                                        <td class="whitespace-nowrap px-4 py-3 text-gray-700">{{ $row->router?->router_name ?? '-' }}</td>
                                        <td class="whitespace-nowrap px-4 py-3 text-gray-700">{{ $row->nasipaddress ?: '-' }}</td>
                                        <td class="whitespace-nowrap px-4 py-3 text-gray-700">{{ $row->radiusServer?->name ?? '-' }}</td>
                                        <td class="whitespace-nowrap px-4 py-3 font-mono text-xs text-gray-700">{{ $row->framedipaddress ?: '-' }}</td>
                                        <td class="whitespace-nowrap px-4 py-3 tabular-nums text-gray-700">{{ $this->bytesLabel($row->acctoutputoctets ?? 0) }}</td>
                                        <td class="whitespace-nowrap px-4 py-3 tabular-nums text-gray-700">{{ $this->bytesLabel($row->acctinputoctets ?? 0) }}</td>
                                        <td class="whitespace-nowrap px-4 py-3 tabular-nums text-gray-700">{{ $row->acctstarttime ? \Illuminate\Support\Carbon::parse($row->acctstarttime)->format('d/m/Y H:i:s') : '-' }}</td>
                                        <td class="whitespace-nowrap px-4 py-3 tabular-nums text-gray-700">{{ $row->acctstoptime ? \Illuminate\Support\Carbon::parse($row->acctstoptime)->format('d/m/Y H:i:s') : '-' }}</td>
                                        <td class="whitespace-nowrap px-4 py-3 text-gray-700">Stop</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="10" class="px-4 py-10 text-center text-gray-500">Belum ada voucher offline.</td></tr>
                                @endforelse
                            @elseif ($pageType === 'recap')
                                {{-- Recap rows rendering --}}
                                @forelse ($recapRows as $row)
                                    <tr>
                                        <td class="whitespace-nowrap px-4 py-3 text-gray-700">{{ $loop->iteration }}</td>
                                        <td class="whitespace-nowrap px-4 py-3 font-semibold text-gray-700">{{ $row->batch_code ?: '-' }}</td>
                                        <td class="whitespace-nowrap px-4 py-3 tabular-nums text-gray-700">{{ $row->created_date ? \Illuminate\Support\Carbon::parse($row->created_date)->format('d/m/Y') : '-' }}</td>
                                        <td class="whitespace-nowrap px-4 py-3 font-medium text-gray-700">{{ $row->partner_name ?: 'SYSTEM' }}</td>
                                        <td class="whitespace-nowrap px-4 py-3 text-gray-700">{{ $row->outlet_name ?: '-' }}</td>
                                        <td class="whitespace-nowrap px-4 py-3">
                                            @if ($row->profile?->name)
                                                <span class="inline-block px-2 py-0.5 rounded text-xs font-bold" style="background-color: {{ $row->profile->attributes['Color'] ?? '#059669' }}20; color: {{ $row->profile->attributes['Color'] ?? '#059669' }}">
                                                    {{ $row->profile->name }}
                                                </span>
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td class="whitespace-nowrap px-4 py-3 tabular-nums font-bold text-gray-700">{{ number_format((int) $row->qty, 0, ',', '.') }}</td>
                                        <td class="whitespace-nowrap px-4 py-3 tabular-nums text-gray-700">{{ number_format((int) $row->stock_qty, 0, ',', '.') }}</td>
                                        <td class="whitespace-nowrap px-4 py-3 tabular-nums text-gray-700">{{ number_format((int) $row->sold_qty, 0, ',', '.') }}</td>
                                        <td class="whitespace-nowrap px-4 py-3 tabular-nums text-right text-gray-700">{{ number_format((float) $row->hpp, 0, ',', '.') }}</td>
                                        <td class="whitespace-nowrap px-4 py-3 tabular-nums text-right text-gray-700">{{ number_format((float) $row->commission, 0, ',', '.') }}</td>
                                        <td class="whitespace-nowrap px-4 py-3 tabular-nums text-right font-semibold text-emerald-700">{{ number_format((float) $row->price, 0, ',', '.') }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="12" class="px-4 py-10 text-center text-gray-500">Belum ada rekap voucher.</td></tr>
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

                    @if (in_array($pageType, ['stock', 'sold'], true) && $voucherRows)
                        <div class="flex justify-end text-xs text-gray-700">
                            {{ $voucherRows->links() }}
                        </div>
                    @elseif ($pageType === 'online' && $onlineRows)
                        <div class="flex justify-end text-xs text-gray-700">
                            {{ $onlineRows->links() }}
                        </div>
                    @elseif ($pageType === 'offline' && $offlineRows)
                        <div class="flex justify-end text-xs text-gray-700">
                            {{ $offlineRows->links() }}
                        </div>
                    @elseif ($pageType === 'recap')
                        <div class="text-sm text-gray-700">
                            Showing {{ $recapRows->count() > 0 ? 1 : 0 }} to {{ $recapRows->count() }} of {{ $recapRows->count() }} entries
                        </div>
                    @endif
                @endif
            </div>
        </x-filament::section>

        @if ($pageType === 'recap' && $recapActionModal)
            <div class="fixed inset-0 z-50 flex items-center justify-center bg-gray-950/55 px-4 py-6">
                <div class="w-full max-w-xl overflow-hidden rounded-lg bg-white shadow-2xl ring-1 ring-gray-950/10">
                    <div class="flex items-center justify-between border-b border-gray-200 px-6 py-4">
                        <h3 class="inline-flex items-center gap-2 text-lg font-bold uppercase text-gray-800">
                            <x-filament::icon :icon="$recapActionModal === 'print' ? 'heroicon-m-printer' : 'heroicon-m-document-arrow-down'" class="h-5 w-5" />
                            {{ $recapActionModal === 'print' ? 'Print Data' : 'Export Data' }}
                        </h3>
                        <button type="button" wire:click="closeRecapActionModal" class="rounded-md p-1 text-gray-500 hover:bg-gray-100 hover:text-gray-800">
                            <x-filament::icon icon="heroicon-m-x-mark" class="h-6 w-6" />
                        </button>
                    </div>

                    <div class="space-y-4 px-6 py-5">
                        <label class="block">
                            <span class="mb-1 block text-sm font-semibold text-gray-700">Partner</span>
                            <div class="flex rounded-md border border-gray-300 bg-white shadow-sm focus-within:border-emerald-500 focus-within:ring-1 focus-within:ring-emerald-500">
                                <select wire:model="recapActionForm.partner_id" class="min-w-0 flex-1 border-0 text-sm focus:ring-0">
                                    <option value="">SYSTEM / Semua partner</option>
                                    @foreach ($this->partnerOptions() as $id => $name)
                                        <option value="{{ $id }}">{{ $name }}</option>
                                    @endforeach
                                </select>
                                <span class="grid w-14 place-items-center border-l border-gray-200 bg-gray-50 text-sky-600">
                                    <x-filament::icon icon="heroicon-m-magnifying-glass" class="h-6 w-6" />
                                </span>
                            </div>
                        </label>

                        <label class="block">
                            <span class="mb-1 block text-sm font-semibold text-gray-700">Tgl awal pembuatan</span>
                            <input type="date" wire:model="recapActionForm.date_from" class="h-10 w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                        </label>

                        <label class="block">
                            <span class="mb-1 block text-sm font-semibold text-gray-700">Sampai tgl pembuatan</span>
                            <input type="date" wire:model="recapActionForm.date_until" class="h-10 w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                        </label>
                    </div>

                    <div class="flex justify-end gap-2 border-t border-gray-200 bg-gray-50 px-6 py-4">
                        <button type="button" wire:click="closeRecapActionModal" class="nex-voucher-control nex-voucher-control--slate">
                            <x-filament::icon icon="heroicon-m-x-mark" class="h-4 w-4" /> Close
                        </button>

                        @if ($recapActionModal === 'print')
                            <button type="button" x-on:click="window.nexVoucherPrintWindow = window.open('about:blank', '_blank')" wire:click="openRecapPrintTab" class="nex-voucher-control nex-voucher-control--rose">
                                <x-filament::icon icon="heroicon-m-printer" class="h-4 w-4" /> Print
                            </button>
                        @else
                            <button type="button" wire:click="exportCreationRecap" class="nex-voucher-control nex-voucher-control--blue">
                                <x-filament::icon icon="heroicon-m-document-arrow-down" class="h-4 w-4" /> Export
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        @endif
    </div>
</x-filament-panels::page>
