<x-filament-panels::page>
    <div class="grid gap-4 md:grid-cols-3">
        @php
            $cards = [
                ['label' => 'Pemasukan voucher', 'value' => 'Rp'.number_format($voucherIncomeToday, 0, ',', '.'), 'icon' => 'heroicon-o-circle-stack', 'color' => '#0891b2'],
                ['label' => 'Pemasukan invoice', 'value' => 'Rp'.number_format($invoiceToday, 0, ',', '.'), 'icon' => 'heroicon-o-calendar-days', 'color' => '#16a34a'],
                ['label' => 'Pengeluaran', 'value' => 'Rp'.number_format($expenseToday, 0, ',', '.'), 'icon' => 'heroicon-o-calendar', 'color' => '#f59e0b'],
                ['label' => 'Voucher Online', 'value' => number_format($voucherOnline, 0, ',', '.'), 'icon' => 'heroicon-o-wifi', 'color' => '#16a34a'],
                ['label' => 'Langganan Online', 'value' => number_format($subscriptionOnline, 0, ',', '.'), 'icon' => 'heroicon-o-user-group', 'color' => '#0891b2'],
                ['label' => 'Pelanggan Terisolir', 'value' => number_format($isolatedCustomers, 0, ',', '.'), 'icon' => 'heroicon-o-calendar-date-range', 'color' => '#6b7280'],
            ];
        @endphp

        @foreach ($cards as $card)
            <div class="rounded bg-white p-5 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900">
                <x-dynamic-component :component="$card['icon']" class="h-8 w-8" style="color: {{ $card['color'] }}" />
                <div class="mt-5 flex items-end justify-between">
                    <div>
                        <div class="text-2xl font-semibold">{{ $card['value'] }}</div>
                        <div class="mt-4 text-sm text-gray-600 dark:text-gray-300">{{ $card['label'] }}</div>
                    </div>
                    <div class="text-sm text-gray-500">Total hari ini</div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="grid gap-4 lg:grid-cols-2">
        <div class="rounded bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900">
            <div class="border-b px-4 py-3 font-semibold dark:border-gray-800">LOG APLIKASI</div>
            <div class="divide-y dark:divide-gray-800">
                @forelse ($logs as $log)
                    <div class="flex gap-3 px-4 py-3">
                        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded bg-sky-600 text-white">
                            <x-heroicon-o-user class="h-5 w-5" />
                        </div>
                        <div>
                            <div class="font-medium">{{ $log->user?->name ?? 'SYSTEM' }}</div>
                            <div class="text-sm text-gray-500">{{ $log->action }} - {{ $log->created_at?->format('d/m/Y H:i:s') }}</div>
                        </div>
                    </div>
                @empty
                    <div class="px-4 py-6 text-sm text-gray-500">Belum ada log aplikasi.</div>
                @endforelse
            </div>
        </div>

        <div class="rounded bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900">
            <div class="border-b px-4 py-3 font-semibold dark:border-gray-800">INFORMASI LISENSI</div>
            <div class="space-y-4 p-4">
                <h2 class="text-2xl font-semibold">{{ $licenseName }}</h2>
                <div>
                    <div class="font-medium">Total Sesi Online {{ number_format($subscriptionOnline, 0, ',', '.') }}/{{ number_format($maxSessions, 0, ',', '.') }}</div>
                    <div class="mt-2 h-3 rounded bg-gray-200 dark:bg-gray-800"><div class="h-3 rounded bg-sky-500" style="width: {{ min(100, $subscriptionOnline / max(1, $maxSessions) * 100) }}%"></div></div>
                </div>
                <div>
                    <div class="font-medium">Total Voucher {{ number_format($voucherOnline, 0, ',', '.') }}/{{ number_format($maxVouchers, 0, ',', '.') }}</div>
                    <div class="mt-2 h-3 rounded bg-gray-200 dark:bg-gray-800"><div class="h-3 rounded bg-emerald-500" style="width: {{ min(100, $voucherOnline / max(1, $maxVouchers) * 100) }}%"></div></div>
                </div>
                <div>
                    <div class="font-medium">Total Berlangganan {{ number_format($activeSubscriptions, 0, ',', '.') }}/{{ number_format($maxSubscriptions, 0, ',', '.') }}</div>
                    <div class="mt-2 h-3 rounded bg-gray-200 dark:bg-gray-800"><div class="h-3 rounded bg-rose-500" style="width: {{ min(100, $activeSubscriptions / max(1, $maxSubscriptions) * 100) }}%"></div></div>
                </div>
                <div>
                    <div class="font-medium">Total Router {{ number_format($routerActive, 0, ',', '.') }}/{{ number_format($maxRouters, 0, ',', '.') }}</div>
                    <div class="mt-2 h-3 rounded bg-gray-200 dark:bg-gray-800"><div class="h-3 rounded bg-blue-500" style="width: {{ min(100, $routerActive / max(1, $maxRouters) * 100) }}%"></div></div>
                </div>
                <div class="rounded bg-blue-600 p-5 font-semibold text-white">
                    {{ strtoupper($tenant?->name ?? 'NEX ISP PLATFORM') }}<br>
                    <span class="text-sm">TimeZone : {{ $timezone }}<br>Sisa Masa Aktif : Unlimited</span>
                </div>
            </div>
        </div>
    </div>
</x-filament-panels::page>
