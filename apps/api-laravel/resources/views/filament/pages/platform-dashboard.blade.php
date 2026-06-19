<x-filament-panels::page>
    <div class="grid gap-4 md:grid-cols-4">
        @php
            $cards = [
                ['label' => 'Total Tenant', 'value' => $tenantTotal, 'icon' => 'heroicon-o-building-office-2', 'color' => '#0ea5e9'],
                ['label' => 'Tenant Aktif', 'value' => $tenantActive, 'icon' => 'heroicon-o-check-circle', 'color' => '#16a34a'],
                ['label' => 'Tenant Suspend', 'value' => $tenantSuspended, 'icon' => 'heroicon-o-no-symbol', 'color' => '#ef4444'],
                ['label' => 'Total User', 'value' => $userTotal, 'icon' => 'heroicon-o-users', 'color' => '#8b5cf6'],
                ['label' => 'Total Layanan', 'value' => $serviceTotal, 'icon' => 'heroicon-o-bolt', 'color' => '#f59e0b'],
                ['label' => 'Sesi Online', 'value' => $onlineTotal, 'icon' => 'heroicon-o-signal', 'color' => '#06b6d4'],
                ['label' => 'Total Router', 'value' => $routerTotal, 'icon' => 'heroicon-o-server-stack', 'color' => '#64748b'],
                ['label' => 'Total Invoice', 'value' => 'Rp'.number_format($invoiceTotal, 0, ',', '.'), 'icon' => 'heroicon-o-document-text', 'color' => '#10b981'],
            ];
        @endphp

        @foreach ($cards as $card)
            <div class="rounded bg-white p-4 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900">
                <x-dynamic-component :component="$card['icon']" class="h-7 w-7" style="color: {{ $card['color'] }}" />
                <div class="mt-4 text-2xl font-semibold">{{ is_numeric($card['value']) ? number_format($card['value'], 0, ',', '.') : $card['value'] }}</div>
                <div class="mt-1 text-sm text-gray-500">{{ $card['label'] }}</div>
            </div>
        @endforeach
    </div>

    <x-filament::section>
        <x-slot name="heading">Manajemen Tenant</x-slot>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="border-b dark:border-gray-800">
                        <th class="px-3 py-2">Tenant</th>
                        <th class="px-3 py-2">Slug</th>
                        <th class="px-3 py-2">Paket Lisensi</th>
                        <th class="px-3 py-2">Sesi</th>
                        <th class="px-3 py-2">Langganan</th>
                        <th class="px-3 py-2">Router</th>
                        <th class="px-3 py-2">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($tenants as $tenant)
                        <tr class="border-b dark:border-gray-800">
                            <td class="px-3 py-2 font-medium">{{ $tenant->name }}</td>
                            <td class="px-3 py-2">{{ $tenant->slug }}</td>
                            <td class="px-3 py-2">{{ $tenant->plan ?: 'NEX BASIC' }}</td>
                            <td class="px-3 py-2">{{ number_format($tenant->license_max_sessions ?: 250, 0, ',', '.') }}</td>
                            <td class="px-3 py-2">{{ number_format($tenant->license_max_subscriptions ?: 200, 0, ',', '.') }}</td>
                            <td class="px-3 py-2">{{ number_format($tenant->license_max_routers ?: 2, 0, ',', '.') }}</td>
                            <td class="px-3 py-2">{{ strtoupper($tenant->status) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-3 py-6 text-center text-gray-500">Belum ada tenant.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-filament::section>
</x-filament-panels::page>
