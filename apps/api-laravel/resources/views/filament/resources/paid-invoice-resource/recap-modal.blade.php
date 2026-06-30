@php
    $items = [
        ['label' => 'Invoice Lunas', 'value' => number_format($recap['invoice_count'], 0, ',', '.'), 'hint' => 'Jumlah invoice pada periode ini'],
        ['label' => 'Total Pembayaran', 'value' => 'Rp'.number_format($recap['gross'], 0, ',', '.'), 'hint' => 'Nilai final termasuk PPN'],
        ['label' => 'Pendapatan DPP', 'value' => 'Rp'.number_format($recap['dpp'], 0, ',', '.'), 'hint' => 'Pendapatan sebelum PPN'],
        ['label' => 'Total PPN', 'value' => 'Rp'.number_format($recap['ppn'], 0, ',', '.'), 'hint' => 'PPN dari invoice berbayar'],
        ['label' => 'BHP Telekomunikasi', 'value' => 'Rp'.number_format($recap['bhp'], 0, ',', '.'), 'hint' => '0,5% dari DPP sebelum PPN'],
        ['label' => 'Kontribusi USO', 'value' => 'Rp'.number_format($recap['uso'], 0, ',', '.'), 'hint' => '1,25% dari DPP sebelum PPN'],
    ];
@endphp

<div class="space-y-4">
    <div class="rounded-lg border border-slate-200 bg-slate-50 p-4">
        <div class="text-sm font-semibold text-slate-500">{{ $recap['label'] }}</div>
        <div class="mt-1 text-base font-bold text-slate-950">{{ $recap['start'] }} - {{ $recap['end'] }}</div>
        <div class="mt-2 text-sm text-slate-600">
            Tarif self-assessment dihitung dari DPP/sebelum PPN: BHP Telekomunikasi 0,5% dan kontribusi USO 1,25%.
        </div>
    </div>

    <div class="grid gap-3 md:grid-cols-2">
        @foreach ($items as $item)
            <div class="rounded-lg border border-slate-200 bg-white p-4">
                <div class="text-sm font-semibold text-slate-500">{{ $item['label'] }}</div>
                <div class="mt-2 text-xl font-bold text-slate-950">{{ $item['value'] }}</div>
                <div class="mt-1 text-xs font-medium text-slate-500">{{ $item['hint'] }}</div>
            </div>
        @endforeach
    </div>
</div>
