<x-filament-panels::page>
    <div x-data="{ modal: null }" class="space-y-6">
        @if (count($this->stats()))
            <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                @foreach ($this->stats() as $stat)
                    <div class="flex items-center gap-4 rounded-xl bg-white p-4 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
                        <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-lg text-white" style="background: {{ $stat['color'] }}">
                            <x-dynamic-component :component="$stat['icon']" class="h-6 w-6" />
                        </div>
                        <div class="min-w-0">
                            <div class="truncate text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ $stat['label'] }}</div>
                            <div class="mt-1 text-xl font-bold text-gray-950 dark:text-white">{{ $stat['value'] }}</div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        <x-filament::section>
            <x-slot name="heading">{{ $this->headingTitle() }}</x-slot>

            <div class="space-y-4">
                <div class="flex flex-wrap items-center gap-2">
                    @foreach ($this->toolbarActions() as $action)
                        <x-filament::button
                            tag="button"
                            size="sm"
                            :color="$action['color']"
                            :icon="$action['icon'] ?? null"
                            x-on:click="modal = '{{ $action['modal'] ?? '' }}'"
                        >
                            {{ $action['label'] }}
                        </x-filament::button>
                    @endforeach
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    @foreach ($this->filters() as $filter)
                        @if (str_contains(strtolower($filter), 'search') || str_contains(strtolower($filter), 'cari') || str_contains(strtolower($filter), 'tgl'))
                            <x-filament::input.wrapper class="min-w-44">
                                <x-filament::input type="text" :placeholder="$filter" />
                            </x-filament::input.wrapper>
                        @else
                            <x-filament::input.wrapper>
                                <x-filament::input.select>
                                    <option>{{ $filter }}</option>
                                </x-filament::input.select>
                            </x-filament::input.wrapper>
                        @endif
                    @endforeach
                </div>

                <div class="overflow-x-auto rounded-lg ring-1 ring-gray-950/5 dark:ring-white/10">
                    <table class="w-full min-w-[1100px] text-left text-sm">
                        <thead>
                            <tr class="border-b border-gray-200 bg-gray-50 dark:border-white/10 dark:bg-white/5">
                                <th class="w-8 px-3 py-3"><input type="checkbox" class="rounded border-gray-300 dark:border-gray-600 dark:bg-gray-800" /></th>
                                @foreach ($this->columns() as $column)
                                    <th class="whitespace-nowrap px-3 py-3 text-xs font-semibold uppercase tracking-wide text-gray-600 dark:text-gray-300">{{ $column }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td colspan="{{ count($this->columns()) + 1 }}" class="px-3 py-12 text-center text-sm text-gray-500 dark:text-gray-400">
                                    No data available in table
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="flex items-center justify-between text-xs text-gray-500 dark:text-gray-400">
                    <span>Showing 0 to 0 of 0 entries</span>
                    <div class="flex gap-1">
                        <x-filament::button tag="button" size="xs" color="gray" outlined>Previous</x-filament::button>
                        <x-filament::button tag="button" size="xs" color="gray" outlined>Next</x-filament::button>
                    </div>
                </div>
            </div>
        </x-filament::section>

        <div x-show="modal" x-cloak x-transition.opacity class="fixed inset-0 z-50 flex items-start justify-center overflow-y-auto bg-gray-950/50 p-6 backdrop-blur-sm">
            <div
                x-show="modal"
                x-transition
                @click.outside="modal = null"
                x-on:keydown.escape.window="modal = null"
                class="w-full max-w-2xl rounded-xl bg-white shadow-2xl ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10"
            >
                <div class="flex items-center justify-between border-b border-gray-200 px-5 py-4 dark:border-white/10">
                    <h2 class="text-base font-semibold text-gray-950 dark:text-white" x-text="modalTitle(modal)"></h2>
                    <button type="button" x-on:click="modal = null" class="rounded-full p-1 text-gray-400 transition hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-white/10 dark:hover:text-gray-200">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>
                    </button>
                </div>

                <div class="space-y-4 p-5">
                    <template x-if="modal === 'profile-menu' || modal === 'stock-menu' || modal === 'sold-menu'">
                        <div class="grid gap-1 text-sm">
                            <button class="rounded-lg px-3 py-2 text-left font-medium text-success-600 transition hover:bg-gray-50 dark:text-success-400 dark:hover:bg-white/5">Set Aktif</button>
                            <button class="rounded-lg px-3 py-2 text-left font-medium text-warning-600 transition hover:bg-gray-50 dark:text-warning-400 dark:hover:bg-white/5">Non Aktif</button>
                            <button class="rounded-lg px-3 py-2 text-left font-medium text-info-600 transition hover:bg-gray-50 dark:text-info-400 dark:hover:bg-white/5" x-show="modal !== 'profile-menu'">Reset Counter</button>
                            <button class="rounded-lg px-3 py-2 text-left font-medium text-gray-700 transition hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-white/5" x-show="modal === 'stock-menu'">Lock MAC</button>
                            <button class="rounded-lg px-3 py-2 text-left font-medium text-gray-700 transition hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-white/5" x-show="modal === 'stock-menu'">Unlock MAC</button>
                            <button class="rounded-lg px-3 py-2 text-left font-medium text-danger-600 transition hover:bg-gray-50 dark:text-danger-400 dark:hover:bg-white/5">Hapus</button>
                        </div>
                    </template>

                    <template x-if="modal === 'profile-form'">
                        <div class="grid gap-4 md:grid-cols-2">
                            <x-nex-field label="Nama profile" placeholder="Nama profile" maxlength="80" />
                            <x-nex-field label="Warna voucher" placeholder="Warna voucher" maxlength="20" />
                            <x-nex-field label="Mikrotik group" value="RLRADIUS" maxlength="50" />
                            <x-nex-field label="Address list" placeholder="Address list" maxlength="50" />
                            <x-nex-field class="md:col-span-2" label="Mikrotik rate limit" value="1M/1500k 0/0 0/0 0/0 8 0/0" maxlength="120" />
                            <x-nex-field label="Shared" placeholder="Shared" maxlength="4" />
                            <x-nex-field label="Kuota" placeholder="Kuota" maxlength="12" />
                            <x-nex-field label="Durasi" placeholder="Durasi" maxlength="12" />
                            <x-nex-field label="Masa aktif" placeholder="Masa aktif" maxlength="4" />
                            <x-nex-field label="Harga jual" placeholder="Harga jual" maxlength="14" />
                            <x-nex-field label="Komisi reseller" placeholder="Komisi reseller" maxlength="14" />
                        </div>
                    </template>

                    <template x-if="modal === 'print-voucher'">
                        <div class="grid gap-4">
                            <x-nex-field label="Template" type="select" :options="['Pilih template']" />
                            <x-nex-field label="Nama hotspot" value="RL HOTSPOT" maxlength="80" />
                            <x-nex-field label="DNS name" value="wifi.radius.com" maxlength="120" />
                            <x-nex-field label="CS phone" value="082170000000" maxlength="20" />
                            <x-nex-field label="Kode" placeholder="Kode" maxlength="50" />
                        </div>
                    </template>

                    <template x-if="modal === 'create-user' || modal === 'import-voucher'">
                        <div class="grid gap-4 md:grid-cols-2">
                            <x-nex-field class="md:col-span-2" label="Partner" placeholder="Cari partner" maxlength="80" />
                            <x-nex-field label="Potong saldo partner" type="select" :options="['YES', 'NO']" />
                            <x-nex-field label="Username" placeholder="Username" maxlength="64" />
                            <x-nex-field label="Password" placeholder="Password" maxlength="64" />
                            <x-nex-field label="Router" type="select" :options="['Pilih router']" />
                            <x-nex-field label="Server" type="select" :options="['Pilih server']" />
                            <x-nex-field label="Outlet" type="select" :options="['Pilih outlet']" />
                            <x-nex-field label="Profile" type="select" :options="['Pilih profile']" />
                            <x-nex-field label="HPP" placeholder="HPP" maxlength="14" />
                            <x-nex-field label="Harga" placeholder="Harga" maxlength="14" />
                            <x-nex-field class="md:col-span-2" label="Komisi" placeholder="Komisi" maxlength="14" />
                        </div>
                    </template>

                    <template x-if="modal === 'outlet'">
                        <div class="space-y-4">
                            <div class="rounded-lg bg-info-50 p-3 text-sm text-info-700 ring-1 ring-info-600/20 dark:bg-info-400/10 dark:text-info-300 dark:ring-info-400/20">
                                Outlet adalah toko, warung atau individu yang menjual voucher langsung ke konsumen dan berada di bawah kontrol partner reseller.
                            </div>
                            <div class="overflow-x-auto rounded-lg ring-1 ring-gray-950/5 dark:ring-white/10">
                                <table class="w-full text-left text-sm">
                                    <thead>
                                        <tr class="border-b border-gray-200 bg-gray-50 dark:border-white/10 dark:bg-white/5">
                                            @foreach (['OUTLET', 'PEMILIK', 'PHONE', 'STOK VC', 'PARTNER', 'ALAMAT'] as $col)
                                                <th class="whitespace-nowrap px-3 py-2 text-xs font-semibold uppercase tracking-wide text-gray-600 dark:text-gray-300">{{ $col }}</th>
                                            @endforeach
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr><td colspan="6" class="px-3 py-8 text-center text-sm text-gray-500 dark:text-gray-400">No data available in table</td></tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </template>

                    <template x-if="modal === 'hotspot-setting'">
                        <div class="grid gap-4">
                            <x-nex-field label="Nama hotspot" value="RL HOTSPOT" maxlength="80" />
                            <x-nex-field label="DNS name" value="wifi.radius.com" maxlength="120" />
                            <x-nex-field label="CS phone" value="082170000000" maxlength="20" />
                            <x-nex-field label="Logo" type="file" />
                        </div>
                    </template>

                    <template x-if="modal === 'export-voucher' || modal === 'export-data' || modal === 'recap-sale' || modal === 'delete-expired'">
                        <div class="grid gap-4 md:grid-cols-2">
                            <x-nex-field label="Partner" placeholder="SEMUA PARTNER" maxlength="80" />
                            <x-nex-field label="Outlet" type="select" :options="['Semua Outlet']" />
                            <x-nex-field label="Dari tanggal" :value="now()->format('d/m/Y')" />
                            <x-nex-field label="Sampai tanggal" :value="now()->format('d/m/Y')" />
                        </div>
                    </template>
                </div>

                <div class="flex justify-end gap-2 border-t border-gray-200 px-5 py-4 dark:border-white/10">
                    <x-filament::button tag="button" color="gray" x-on:click="modal = null">Tutup</x-filament::button>
                    <x-filament::button tag="button" color="warning" x-show="modal === 'create-user'">Reset</x-filament::button>
                    <x-filament::button tag="button" color="primary" x-on:click="modal = null" x-text="modalSubmit(modal)"></x-filament::button>
                </div>
            </div>
        </div>
    </div>

    <style>
        [x-cloak] { display: none !important; }
    </style>

    <script>
        function modalTitle(modal) {
            return {
                'profile-menu': 'Menu Profile Voucher',
                'stock-menu': 'Menu Stok Voucher',
                'sold-menu': 'Menu Voucher Terjual',
                'profile-form': 'Profile Voucher',
                'print-voucher': 'Print Voucher',
                'create-user': 'Buat User',
                'outlet': 'Data Outlet',
                'hotspot-setting': 'Setting Hotspot',
                'import-voucher': 'Import Voucher',
                'export-voucher': 'Export Voucher',
                'export-data': 'Export Data',
                'recap-sale': 'Rekap Penjualan',
                'delete-expired': 'Hapus Voucher Expired',
            }[modal] || 'Voucher';
        }

        function modalSubmit(modal) {
            return {
                'print-voucher': 'Print voucher',
                'create-user': 'Buat user',
                'import-voucher': 'Import',
                'export-voucher': 'Export',
                'export-data': 'Export',
                'recap-sale': 'Print data',
                'delete-expired': 'Hapus',
            }[modal] || 'Simpan';
        }
    </script>
</x-filament-panels::page>
