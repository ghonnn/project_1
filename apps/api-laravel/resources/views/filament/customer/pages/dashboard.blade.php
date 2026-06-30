<x-filament-panels::page>
    @if (! $customer)
        <div class="rounded-md border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900">
            Akun login belum terhubung ke data pelanggan. Samakan email user login dengan email pelanggan atau tambahkan relasi user pelanggan pada fase berikutnya.
        </div>
    @else
        <div class="grid gap-4 md:grid-cols-3">
            <div class="rounded-md border border-slate-200 bg-white p-5 shadow-sm">
                <div class="text-sm font-semibold text-slate-500">Pelanggan</div>
                <div class="mt-2 text-2xl font-bold text-slate-950">{{ $customer->name }}</div>
                <div class="mt-1 text-sm text-slate-500">{{ $customer->customer_number ?: '-' }}</div>
            </div>
            <div class="rounded-md border border-slate-200 bg-white p-5 shadow-sm">
                <div class="text-sm font-semibold text-slate-500">Layanan Aktif</div>
                <div class="mt-2 text-2xl font-bold text-emerald-700">{{ number_format($activeServices, 0, ',', '.') }}</div>
                <div class="mt-1 text-sm text-slate-500">Dari {{ number_format($services->count(), 0, ',', '.') }} layanan</div>
            </div>
            <div class="rounded-md border border-slate-200 bg-white p-5 shadow-sm">
                <div class="text-sm font-semibold text-slate-500">Tagihan Belum Dibayar</div>
                <div class="mt-2 text-2xl font-bold text-rose-700">Rp{{ number_format($unpaidTotal, 0, ',', '.') }}</div>
                <div class="mt-1 text-sm text-slate-500">Invoice issued/overdue</div>
            </div>
        </div>

        <div class="grid gap-4 lg:grid-cols-2">
            <section class="rounded-md border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-200 px-4 py-3 font-semibold text-slate-900">Layanan Saya</div>
                <div class="divide-y divide-slate-100">
                    @forelse ($services as $service)
                        <div class="grid gap-1 px-4 py-3 text-sm">
                            <div class="flex items-center justify-between gap-3">
                                <span class="font-semibold text-slate-950">{{ $service->cid ?: $service->billing_profile_name }}</span>
                                <span class="rounded-full bg-emerald-50 px-2 py-1 text-xs font-bold text-emerald-700">{{ $service->status }}</span>
                            </div>
                            <div class="text-slate-500">{{ $service->connection_type ?: '-' }} / {{ $service->primaryRadiusUser?->username ?: '-' }}</div>
                            <div class="text-slate-500">{{ $service->primaryRouterMapping?->router?->router_name ?: 'Router belum terhubung' }}</div>
                        </div>
                    @empty
                        <div class="px-4 py-6 text-sm text-slate-500">Belum ada layanan.</div>
                    @endforelse
                </div>
            </section>

            <section class="rounded-md border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-200 px-4 py-3 font-semibold text-slate-900">Invoice Terakhir</div>
                <div class="divide-y divide-slate-100">
                    @forelse ($invoices as $invoice)
                        <div class="flex items-center justify-between gap-3 px-4 py-3 text-sm">
                            <div>
                                <div class="font-semibold text-slate-950">{{ $invoice->invoice_number }}</div>
                                <div class="text-slate-500">{{ $invoice->due_date?->format('d/m/Y') ?: '-' }}</div>
                            </div>
                            <div class="text-right">
                                <div class="font-semibold text-slate-950">Rp{{ number_format($invoice->total_amount, 0, ',', '.') }}</div>
                                <div class="text-xs font-bold text-slate-500">{{ strtoupper($invoice->status) }}</div>
                            </div>
                        </div>
                    @empty
                        <div class="px-4 py-6 text-sm text-slate-500">Belum ada invoice.</div>
                    @endforelse
                </div>
            </section>
        </div>
    @endif
</x-filament-panels::page>
