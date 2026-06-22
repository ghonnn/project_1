<x-filament-panels::page>
    <div class="nex-dashboard-grid">
        @php
            $cards = [
                ['label' => 'Pemasukan voucher', 'value' => 'Rp'.number_format($voucherIncomeToday, 0, ',', '.'), 'icon' => 'heroicon-o-circle-stack', 'color' => '#0891b2', 'soft' => '#ecfeff', 'hint' => 'Pendapatan voucher hari ini'],
                ['label' => 'Pemasukan invoice', 'value' => 'Rp'.number_format($invoiceToday, 0, ',', '.'), 'icon' => 'heroicon-o-banknotes', 'color' => '#059669', 'soft' => '#ecfdf5', 'hint' => 'Pembayaran invoice masuk'],
                ['label' => 'Pengeluaran', 'value' => 'Rp'.number_format($expenseToday, 0, ',', '.'), 'icon' => 'heroicon-o-arrow-trending-down', 'color' => '#f59e0b', 'soft' => '#fffbeb', 'hint' => 'Biaya tercatat hari ini'],
                ['label' => 'Voucher Online', 'value' => number_format($voucherOnline, 0, ',', '.'), 'icon' => 'heroicon-o-wifi', 'color' => '#10b981', 'soft' => '#ecfdf5', 'hint' => 'Hotspot aktif dari RADIUS'],
                ['label' => 'Langganan Online', 'value' => number_format($subscriptionOnline, 0, ',', '.'), 'icon' => 'heroicon-o-user-group', 'color' => '#0284c7', 'soft' => '#eff6ff', 'hint' => 'PPPoE aktif dari MikroTik'],
                ['label' => 'SNMP Aktif', 'value' => number_format($routerSnmpActive, 0, ',', '.').'/'.number_format($routerCount, 0, ',', '.'), 'icon' => 'heroicon-o-signal', 'color' => '#7c3aed', 'soft' => '#f5f3ff', 'hint' => 'Router reachable via SNMP'],
                ['label' => 'Pelanggan Terisolir', 'value' => number_format($isolatedCustomers, 0, ',', '.'), 'icon' => 'heroicon-o-no-symbol', 'color' => '#64748b', 'soft' => '#f1f5f9', 'hint' => 'Layanan suspend/isolir'],
            ];
        @endphp

        @foreach ($cards as $card)
            <div class="nex-metric-card" style="--metric-color: {{ $card['color'] }}; --metric-soft: {{ $card['soft'] }}">
                <div class="nex-metric-head">
                    <div class="nex-metric-icon">
                        <x-dynamic-component :component="$card['icon']" class="h-6 w-6" />
                    </div>
                    <div class="rounded-full bg-slate-100 px-3 py-1 text-xs font-bold text-slate-500">Hari ini</div>
                </div>
                <div class="nex-metric-body">
                    <div class="nex-metric-value">{{ $card['value'] }}</div>
                    <div class="nex-metric-label">{{ $card['label'] }}</div>
                </div>
                <div class="nex-metric-foot">{{ $card['hint'] }}</div>
            </div>
        @endforeach
    </div>

    <div class="grid gap-4 lg:grid-cols-2">
        <div class="nex-panel-card">
            <div class="nex-panel-header">
                <div>
                    <div class="nex-panel-title">Log Aplikasi</div>
                    <div class="nex-panel-subtitle">Aktivitas sistem terakhir</div>
                </div>
            </div>
            <div class="divide-y divide-slate-100">
                @forelse ($logs as $log)
                    <div class="flex gap-3 px-5 py-4">
                        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-md bg-emerald-600 text-white">
                            <x-heroicon-o-user class="h-5 w-5" />
                        </div>
                        <div>
                            <div class="font-medium text-slate-900">{{ $log->user?->name ?? 'SYSTEM' }}</div>
                            <div class="text-sm text-slate-500">{{ $log->action }} - {{ $log->created_at?->format('d/m/Y H:i:s') }}</div>
                        </div>
                    </div>
                @empty
                    <div class="px-5 py-8 text-sm font-medium text-slate-500">Belum ada log aplikasi.</div>
                @endforelse
            </div>
        </div>

        <div class="nex-panel-card">
            <div class="nex-panel-header">
                <div>
                    <div class="nex-panel-title">Informasi Lisensi</div>
                    <div class="nex-panel-subtitle">{{ strtoupper($tenant?->name ?? 'NEX ISP PLATFORM') }}</div>
                </div>
                <div class="rounded-full bg-emerald-50 px-3 py-1 text-xs font-bold text-emerald-700">{{ $licenseName }}</div>
            </div>
            <div class="space-y-5 p-5">
                <div class="nex-progress-row">
                    <div class="nex-progress-label"><span>Total Sesi Online</span><span>{{ number_format($totalOnline, 0, ',', '.') }}/{{ number_format($maxSessions, 0, ',', '.') }}</span></div>
                    <div class="nex-progress-track"><div class="nex-progress-fill" style="--progress-color: #059669; width: {{ min(100, $totalOnline / max(1, $maxSessions) * 100) }}%"></div></div>
                </div>
                <div class="nex-progress-row">
                    <div class="nex-progress-label"><span>Total Voucher</span><span>{{ number_format($voucherOnline, 0, ',', '.') }}/{{ number_format($maxVouchers, 0, ',', '.') }}</span></div>
                    <div class="nex-progress-track"><div class="nex-progress-fill" style="--progress-color: #0891b2; width: {{ min(100, $voucherOnline / max(1, $maxVouchers) * 100) }}%"></div></div>
                </div>
                <div class="nex-progress-row">
                    <div class="nex-progress-label"><span>Total Berlangganan</span><span>{{ number_format($activeSubscriptions, 0, ',', '.') }}/{{ number_format($maxSubscriptions, 0, ',', '.') }}</span></div>
                    <div class="nex-progress-track"><div class="nex-progress-fill" style="--progress-color: #0284c7; width: {{ min(100, $activeSubscriptions / max(1, $maxSubscriptions) * 100) }}%"></div></div>
                </div>
                <div class="nex-progress-row">
                    <div class="nex-progress-label"><span>Total Router</span><span>{{ number_format($routerActive, 0, ',', '.') }}/{{ number_format($maxRouters, 0, ',', '.') }}</span></div>
                    <div class="nex-progress-track"><div class="nex-progress-fill" style="--progress-color: #7c3aed; width: {{ min(100, $routerActive / max(1, $maxRouters) * 100) }}%"></div></div>
                </div>
                <div class="rounded-lg bg-slate-950 p-5 font-semibold text-white">
                    <div class="text-lg">{{ strtoupper($tenant?->name ?? 'NEX ISP PLATFORM') }}</div>
                    <div class="mt-2 text-sm text-slate-300">TimeZone: {{ $timezone }} / Sisa Masa Aktif: Unlimited</div>
                </div>
            </div>
        </div>
    </div>
</x-filament-panels::page>
