<x-filament-panels::page>
    <div class="grid gap-4 md:grid-cols-3">
        @php
            $cards = [
                ['label' => 'Pemasukan voucher', 'value' => 'Rp'.number_format($voucherIncomeToday, 0, ',', '.'), 'icon' => 'heroicon-o-circle-stack', 'color' => '#0891b2'],
                ['label' => 'Pemasukan invoice', 'value' => 'Rp'.number_format($invoiceToday, 0, ',', '.'), 'icon' => 'heroicon-o-calendar-days', 'color' => '#16a34a'],
                ['label' => 'Pengeluaran', 'value' => 'Rp'.number_format($expenseToday, 0, ',', '.'), 'icon' => 'heroicon-o-calendar', 'color' => '#f59e0b'],
                ['label' => 'Voucher Online', 'value' => number_format($voucherOnline, 0, ',', '.'), 'icon' => 'heroicon-o-wifi', 'color' => '#16a34a'],
                ['label' => 'Langganan Online', 'value' => number_format($subscriptionOnline, 0, ',', '.'), 'icon' => 'heroicon-o-user-group', 'color' => '#0891b2'],
                ['label' => 'SNMP Aktif', 'value' => number_format($routerSnmpActive, 0, ',', '.').'/'.number_format($routerCount, 0, ',', '.'), 'icon' => 'heroicon-o-signal', 'color' => '#8b5cf6'],
                ['label' => 'Pelanggan Terisolir', 'value' => number_format($isolatedCustomers, 0, ',', '.'), 'icon' => 'heroicon-o-calendar-date-range', 'color' => '#6b7280'],
            ];
        @endphp

        @foreach ($cards as $card)
            <div class="rounded-md border border-slate-200 bg-white p-5 shadow-sm">
                <div class="flex h-10 w-10 items-center justify-center rounded-md" style="background: color-mix(in srgb, {{ $card['color'] }} 12%, white)">
                    <x-dynamic-component :component="$card['icon']" class="h-6 w-6" style="color: {{ $card['color'] }}" />
                </div>
                <div class="mt-5 flex items-end justify-between">
                    <div>
                        <div class="text-2xl font-semibold text-slate-950">{{ $card['value'] }}</div>
                        <div class="mt-3 text-sm font-medium text-slate-500">{{ $card['label'] }}</div>
                    </div>
                    <div class="text-xs font-semibold uppercase text-slate-400">Hari ini</div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="grid gap-4 lg:grid-cols-2">
        <div class="rounded-md border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-200 px-4 py-3 font-semibold text-slate-900">LOG APLIKASI</div>
            <div class="divide-y divide-slate-100">
                @forelse ($logs as $log)
                    <div class="flex gap-3 px-4 py-3">
                        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-md bg-emerald-600 text-white">
                            <x-heroicon-o-user class="h-5 w-5" />
                        </div>
                        <div>
                            <div class="font-medium text-slate-900">{{ $log->user?->name ?? 'SYSTEM' }}</div>
                            <div class="text-sm text-slate-500">{{ $log->action }} - {{ $log->created_at?->format('d/m/Y H:i:s') }}</div>
                        </div>
                    </div>
                @empty
                    <div class="px-4 py-6 text-sm text-slate-500">Belum ada log aplikasi.</div>
                @endforelse
            </div>
        </div>

        <div class="rounded-md border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-200 px-4 py-3 font-semibold text-slate-900">INFORMASI LISENSI</div>
            <div class="space-y-4 p-4">
                <h2 class="text-2xl font-semibold text-slate-950">{{ $licenseName }}</h2>
                <div>
                    <div class="font-medium text-slate-700">Total Sesi Online {{ number_format($totalOnline, 0, ',', '.') }}/{{ number_format($maxSessions, 0, ',', '.') }}</div>
                    <div class="mt-2 h-3 rounded-full bg-slate-100"><div class="h-3 rounded-full bg-emerald-500" style="width: {{ min(100, $totalOnline / max(1, $maxSessions) * 100) }}%"></div></div>
                </div>
                <div>
                    <div class="font-medium text-slate-700">Total Voucher {{ number_format($voucherOnline, 0, ',', '.') }}/{{ number_format($maxVouchers, 0, ',', '.') }}</div>
                    <div class="mt-2 h-3 rounded-full bg-slate-100"><div class="h-3 rounded-full bg-cyan-500" style="width: {{ min(100, $voucherOnline / max(1, $maxVouchers) * 100) }}%"></div></div>
                </div>
                <div>
                    <div class="font-medium text-slate-700">Total Berlangganan {{ number_format($activeSubscriptions, 0, ',', '.') }}/{{ number_format($maxSubscriptions, 0, ',', '.') }}</div>
                    <div class="mt-2 h-3 rounded-full bg-slate-100"><div class="h-3 rounded-full bg-sky-500" style="width: {{ min(100, $activeSubscriptions / max(1, $maxSubscriptions) * 100) }}%"></div></div>
                </div>
                <div>
                    <div class="font-medium text-slate-700">Total Router {{ number_format($routerActive, 0, ',', '.') }}/{{ number_format($maxRouters, 0, ',', '.') }}</div>
                    <div class="mt-2 h-3 rounded-full bg-slate-100"><div class="h-3 rounded-full bg-indigo-500" style="width: {{ min(100, $routerActive / max(1, $maxRouters) * 100) }}%"></div></div>
                </div>
                <div class="rounded-md bg-emerald-600 p-5 font-semibold text-white">
                    {{ strtoupper($tenant?->name ?? 'NEX ISP PLATFORM') }}<br>
                    <span class="text-sm">TimeZone : {{ $timezone }}<br>Sisa Masa Aktif : Unlimited</span>
                </div>
            </div>
        </div>
    </div>
</x-filament-panels::page>
