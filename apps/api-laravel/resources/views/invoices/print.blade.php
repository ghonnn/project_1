@php
    $isRoll = $paper === 'roll';
    $service = $invoice->items->first()?->service;
    $subtotal = $service?->ppn_enabled ? round(((float) $invoice->total_amount) / (1 + (((float) $service->ppn_rate ?: 11) / 100))) : (float) $invoice->total_amount;
    $ppn = max(0, (float) $invoice->total_amount - $subtotal);
    $format = fn ($amount) => number_format((float) $amount, 0, ',', '.');
@endphp
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Invoice {{ $invoice->invoice_number }}</title>
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; background: #eef2f7; color: #111827; font-family: Arial, sans-serif; }
        .sheet { background: white; margin: 28px auto; padding: {{ $isRoll ? '18px' : '46px' }}; width: {{ $isRoll ? '80mm' : '210mm' }}; min-height: {{ $isRoll ? 'auto' : '297mm' }}; }
        .center { text-align: center; }
        .muted { color: #475569; }
        .title { font-size: {{ $isRoll ? '13px' : '28px' }}; font-weight: 700; text-transform: uppercase; }
        .status { color: #ef4444; font-size: 28px; font-weight: 700; }
        .line { border-top: 1px dashed #111827; margin: 12px 0; }
        .box { border: 1px solid #cbd5e1; border-radius: 8px; padding: 14px; margin: 16px 0; }
        .grid { display: grid; grid-template-columns: 1fr 1fr; gap: 18px; }
        table { border-collapse: collapse; width: 100%; }
        th, td { padding: {{ $isRoll ? '4px 0' : '9px 10px' }}; vertical-align: top; }
        th { text-align: left; border-bottom: 1px solid #cbd5e1; }
        .right { text-align: right; }
        .totals { margin-left: auto; width: {{ $isRoll ? '100%' : '45%' }}; }
        .grand { background: #f1f5f9; font-size: {{ $isRoll ? '13px' : '22px' }}; font-weight: 700; }
        .qr { width: {{ $isRoll ? '72px' : '88px' }}; height: {{ $isRoll ? '72px' : '88px' }}; margin-top: 10px; }
        @media print {
            body { background: white; }
            .sheet { margin: 0 auto; box-shadow: none; }
            @page { size: {{ $isRoll ? '80mm auto' : 'A4 portrait' }}; margin: {{ $isRoll ? '4mm' : '12mm' }}; }
        }
    </style>
</head>
<body>
    <main class="sheet">
        @if ($isRoll)
            <div class="center">
                <div class="title">{{ $invoice->tenant?->name ?? 'NEX ISP PLATFORM' }}</div>
                <div class="muted">Invoice pelanggan internet</div>
            </div>
            <div class="line"></div>
            <div>Tgl cetak: {{ now('Asia/Jakarta')->format('d/m/Y H:i:s') }}</div>
            <div>Pelanggan: {{ $invoice->customer?->name ?? '-' }}</div>
            <div>HP: {{ $invoice->customer?->phone ?? '-' }}</div>
            <div>No.Invoice: {{ $invoice->invoice_number }}</div>
            <div>No.Layanan: {{ $service?->cid ?? '-' }}</div>
            <div>Tgl terbit: {{ $invoice->issue_date?->format('d/m/Y') }}</div>
            <div>Jatuh tempo: {{ $invoice->due_date?->format('d/m/Y') }}</div>
            <div>Status: {{ strtoupper($invoice->status === 'paid' ? 'Lunas' : 'Belum Bayar') }}</div>
            <div class="line"></div>
            <table>
                <thead><tr><th>ITEM</th><th class="right">TOTAL</th></tr></thead>
                <tbody>
                    @foreach ($invoice->items as $item)
                        <tr><td>{{ $item->description }}</td><td class="right">{{ $format($item->total_amount) }}</td></tr>
                    @endforeach
                </tbody>
            </table>
            <div class="line"></div>
            <table class="totals">
                <tr><td>Sub-Total</td><td class="right">{{ $format($subtotal) }}</td></tr>
                <tr><td>PPN</td><td class="right">{{ $format($ppn) }}</td></tr>
                <tr><td><strong>Grand Total</strong></td><td class="right"><strong>{{ $format($invoice->total_amount) }}</strong></td></tr>
            </table>
            <div class="line"></div>
            <div class="center">
                <img class="qr" src="https://api.qrserver.com/v1/create-qr-code/?size=120x120&data={{ urlencode($invoice->invoice_number) }}" alt="QR">
                <div class="muted">Scan QR untuk pembayaran</div>
            </div>
        @else
            <div style="display:flex;justify-content:space-between;align-items:center;border-bottom:1px solid #cbd5e1;padding-bottom:18px">
                <div class="title">{{ $invoice->tenant?->name ?? 'NEX ISP PLATFORM' }}</div>
                <div class="status">{{ strtoupper($invoice->status === 'paid' ? 'PAID' : 'UNPAID') }}</div>
            </div>
            <div class="grid box">
                <div>
                    <strong>Kepada Yth:</strong><br>
                    {{ $invoice->customer?->name ?? '-' }}<br>
                    {{ $invoice->customer?->address ?? '-' }}<br>
                    {{ $invoice->customer?->phone ?? '-' }}
                </div>
                <div>
                    <strong>Dari:</strong><br>
                    {{ $invoice->tenant?->name ?? 'NEX ISP PLATFORM' }}<br>
                    Billing Department<br>
                    Indonesia
                </div>
            </div>
            <div class="grid box">
                <div>No. Invoice<br><strong>{{ $invoice->invoice_number }}</strong></div>
                <div>Tgl Terbit<br><strong>{{ $invoice->issue_date?->format('d F Y') }}</strong></div>
                <div>No. Layanan<br><strong>{{ $service?->cid ?? '-' }}</strong></div>
                <div>Jatuh Tempo<br><strong>{{ $invoice->due_date?->format('d F Y') }}</strong></div>
            </div>
            <table>
                <thead><tr><th>DESKRIPSI</th><th class="right">JUMLAH</th></tr></thead>
                <tbody>
                    @foreach ($invoice->items as $item)
                        <tr><td>{{ $item->description }}</td><td class="right">{{ $format($item->total_amount) }}</td></tr>
                    @endforeach
                </tbody>
            </table>
            <table class="totals">
                <tr><td>Subtotal</td><td class="right">{{ $format($subtotal) }}</td></tr>
                <tr><td>PPN</td><td class="right">{{ $format($ppn) }}</td></tr>
                <tr class="grand"><td>Grand Total</td><td class="right">{{ $format($invoice->total_amount) }}</td></tr>
            </table>
            <div style="margin-top:48px">
                <strong>Catatan</strong>
                <ol>
                    <li>Transfer sejumlah yang tertera di atas sampai digit terakhir.</li>
                    <li>Bayarlah sebelum tanggal jatuh tempo agar layanan tetap aktif.</li>
                </ol>
                <img class="qr" src="https://api.qrserver.com/v1/create-qr-code/?size=120x120&data={{ urlencode($invoice->invoice_number) }}" alt="QR">
            </div>
        @endif
    </main>
    <script>window.addEventListener('load', () => window.print());</script>
</body>
</html>
