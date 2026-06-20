<x-filament-panels::page>
    <div x-data="{ modal: null }" class="space-y-4">
        @if (count($this->stats()))
            <div class="grid gap-3 md:grid-cols-2 xl:grid-cols-4">
                @foreach ($this->stats() as $stat)
                    <div class="flex items-center gap-4 border border-slate-700 bg-slate-900/70 p-4">
                        <div class="flex h-16 w-16 items-center justify-center text-white" style="background: {{ $stat['color'] }}">
                            <x-dynamic-component :component="$stat['icon']" class="h-10 w-10" />
                        </div>
                        <div>
                            <div class="text-xs font-semibold uppercase text-slate-400">{{ $stat['label'] }}</div>
                            <div class="mt-2 text-xl font-semibold text-white">{{ $stat['value'] }}</div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        <section class="overflow-hidden border border-slate-700 bg-slate-900/80">
            <div class="bg-slate-700/70 px-4 py-3 text-sm font-bold uppercase text-white">
                {{ $this->headingTitle() }}
            </div>

            <div class="space-y-3 p-4">
                <div class="flex flex-wrap gap-2">
                    @foreach ($this->toolbarActions() as $action)
                        <button
                            type="button"
                            class="rounded px-3 py-1.5 text-xs font-bold uppercase text-white"
                            style="background: {{ $action['color'] }}"
                            @if ($action['modal'] ?? null) x-on:click="modal = '{{ $action['modal'] }}'" @endif
                        >
                            {{ $action['label'] }}
                        </button>
                    @endforeach
                </div>

                <div class="flex flex-wrap gap-2">
                    @foreach ($this->filters() as $filter)
                        @if (str_contains(strtolower($filter), 'search') || str_contains(strtolower($filter), 'cari') || str_contains(strtolower($filter), 'tgl'))
                            <input class="min-w-44 rounded border-slate-700 bg-slate-800 px-3 py-1.5 text-xs text-slate-200" placeholder="{{ $filter }}" />
                        @else
                            <select class="rounded border-slate-700 bg-slate-800 px-3 py-1.5 text-xs text-slate-200">
                                <option>{{ $filter }}</option>
                            </select>
                        @endif
                    @endforeach
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full min-w-[1100px] text-left text-xs text-slate-300">
                        <thead class="border-y border-slate-700 uppercase text-slate-200">
                            <tr>
                                <th class="w-8 px-3 py-3"><input type="checkbox" /></th>
                                @foreach ($this->columns() as $column)
                                    <th class="whitespace-nowrap px-3 py-3">{{ $column }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td colspan="{{ count($this->columns()) + 1 }}" class="px-3 py-8 text-center text-slate-400">
                                    No data available in table
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="flex items-center justify-between text-xs text-slate-400">
                    <span>Showing 0 to 0 of 0 entries</span>
                    <div class="flex gap-1">
                        <button class="rounded border border-slate-700 px-3 py-1">Previous</button>
                        <button class="rounded border border-slate-700 px-3 py-1">Next</button>
                    </div>
                </div>
            </div>
        </section>

        <div x-show="modal" x-cloak class="fixed inset-0 z-50 flex items-start justify-center bg-black/65 p-6">
            <div class="w-full max-w-2xl rounded-lg border border-slate-700 bg-slate-800 text-slate-100 shadow-xl">
                <div class="flex items-center justify-between border-b border-slate-700 px-5 py-4">
                    <h2 class="text-lg font-bold uppercase" x-text="modalTitle(modal)"></h2>
                    <button type="button" x-on:click="modal = null" class="text-2xl text-slate-400">&times;</button>
                </div>

                <div class="space-y-4 p-5">
                    <template x-if="modal === 'profile-menu' || modal === 'stock-menu' || modal === 'sold-menu'">
                        <div class="grid gap-2 text-sm">
                            <button class="text-left text-emerald-400">Set Aktif</button>
                            <button class="text-left text-amber-400">Non Aktif</button>
                            <button class="text-left text-cyan-400" x-show="modal !== 'profile-menu'">Reset Counter</button>
                            <button class="text-left text-slate-300" x-show="modal === 'stock-menu'">Lock MAC</button>
                            <button class="text-left text-slate-300" x-show="modal === 'stock-menu'">Unlock MAC</button>
                            <button class="text-left text-rose-400">Hapus</button>
                        </div>
                    </template>

                    <template x-if="modal === 'profile-form'">
                        <div class="grid gap-4 md:grid-cols-2">
                            <input class="nex-input" placeholder="Nama profile" maxlength="80">
                            <input class="nex-input" placeholder="Warna voucher" maxlength="20">
                            <input class="nex-input" placeholder="Mikrotik group" value="RLRADIUS" maxlength="50">
                            <input class="nex-input" placeholder="Address list" maxlength="50">
                            <input class="nex-input md:col-span-2" placeholder="Mikrotik rate limit" value="1M/1500k 0/0 0/0 0/0 8 0/0" maxlength="120">
                            <input class="nex-input" placeholder="Shared" maxlength="4">
                            <input class="nex-input" placeholder="Kuota" maxlength="12">
                            <input class="nex-input" placeholder="Durasi" maxlength="12">
                            <input class="nex-input" placeholder="Masa aktif" maxlength="4">
                            <input class="nex-input" placeholder="Harga Jual" maxlength="14">
                            <input class="nex-input" placeholder="Komisi Reseller" maxlength="14">
                        </div>
                    </template>

                    <template x-if="modal === 'print-voucher'">
                        <div class="grid gap-3">
                            <select class="nex-input"><option>Pilih template</option></select>
                            <input class="nex-input" placeholder="Nama hotspot" value="RL HOTSPOT" maxlength="80">
                            <input class="nex-input" placeholder="DNS name" value="wifi.radius.com" maxlength="120">
                            <input class="nex-input" placeholder="CS phone" value="082170000000" maxlength="20">
                            <input class="nex-input" placeholder="Kode" maxlength="50">
                        </div>
                    </template>

                    <template x-if="modal === 'create-user' || modal === 'import-voucher'">
                        <div class="grid gap-3 md:grid-cols-2">
                            <input class="nex-input md:col-span-2" placeholder="Cari partner" maxlength="80">
                            <select class="nex-input"><option>Potong saldo partner</option><option>YES</option><option>NO</option></select>
                            <input class="nex-input" placeholder="Username" maxlength="64">
                            <input class="nex-input" placeholder="Password" maxlength="64">
                            <select class="nex-input"><option>Router</option></select>
                            <select class="nex-input"><option>Server</option></select>
                            <select class="nex-input"><option>Outlet</option></select>
                            <select class="nex-input"><option>Profile</option></select>
                            <input class="nex-input" placeholder="HPP" maxlength="14">
                            <input class="nex-input" placeholder="Harga" maxlength="14">
                            <input class="nex-input md:col-span-2" placeholder="Komisi" maxlength="14">
                        </div>
                    </template>

                    <template x-if="modal === 'outlet'">
                        <div>
                            <div class="mb-4 rounded border border-cyan-900 bg-cyan-950/50 p-3 text-sm text-slate-300">Outlet adalah toko, warung atau individu yang menjual voucher langsung ke konsumen dan berada dibawah kontrol partner reseller.</div>
                            <div class="overflow-x-auto">
                                <table class="w-full text-xs">
                                    <thead><tr><th>OUTLET</th><th>PEMILIK</th><th>PHONE</th><th>STOK VC</th><th>PARTNER</th><th>ALAMAT</th></tr></thead>
                                    <tbody><tr><td colspan="6" class="py-8 text-center text-slate-400">No data available in table</td></tr></tbody>
                                </table>
                            </div>
                        </div>
                    </template>

                    <template x-if="modal === 'hotspot-setting'">
                        <div class="grid gap-3">
                            <input class="nex-input" placeholder="Nama hotspot" value="RL HOTSPOT" maxlength="80">
                            <input class="nex-input" placeholder="DNS name" value="wifi.radius.com" maxlength="120">
                            <input class="nex-input" placeholder="CS Phone" value="082170000000" maxlength="20">
                            <input class="nex-input" type="file">
                        </div>
                    </template>

                    <template x-if="modal === 'export-voucher' || modal === 'export-data' || modal === 'recap-sale' || modal === 'delete-expired'">
                        <div class="grid gap-3">
                            <input class="nex-input" placeholder="SEMUA PARTNER" maxlength="80">
                            <select class="nex-input"><option>Semua Outlet</option></select>
                            <input class="nex-input" placeholder="Dari tanggal" value="{{ now()->format('d/m/Y') }}">
                            <input class="nex-input" placeholder="Sampai tanggal" value="{{ now()->format('d/m/Y') }}">
                        </div>
                    </template>
                </div>

                <div class="flex justify-end gap-2 border-t border-slate-700 px-5 py-4">
                    <button type="button" x-on:click="modal = null" class="rounded bg-slate-500 px-4 py-2 text-sm text-white">Close</button>
                    <button type="button" class="rounded bg-amber-500 px-4 py-2 text-sm text-white" x-show="modal === 'create-user'">Reset</button>
                    <button type="button" x-on:click="modal = null" class="rounded bg-primary-600 px-4 py-2 text-sm text-white" x-text="modalSubmit(modal)"></button>
                </div>
            </div>
        </div>
    </div>

    <style>
        [x-cloak] { display: none !important; }
        .nex-input {
            width: 100%;
            border-radius: 6px;
            border: 1px solid #475569;
            background: #334155;
            padding: 9px 12px;
            color: #e2e8f0;
            font-size: 13px;
        }
        .nex-input::placeholder { color: #94a3b8; }
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
