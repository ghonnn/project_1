<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>NEX ISP Hotspot Voucher</title>
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; padding: 18px; font-family: Arial, sans-serif; color: #0f172a; background: #f8fafc; }
        .sheet { display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; }
        .voucher { min-height: 152px; border: 1px solid #cbd5e1; border-radius: 10px; background: #fff; padding: 12px; break-inside: avoid; }
        .head { display: flex; align-items: center; justify-content: space-between; border-bottom: 1px dashed #cbd5e1; padding-bottom: 8px; margin-bottom: 10px; }
        .brand { font-weight: 800; font-size: 15px; }
        .tag { border-radius: 999px; background: #ecfdf5; color: #047857; padding: 4px 8px; font-size: 11px; font-weight: 700; }
        .row { display: flex; justify-content: space-between; gap: 12px; margin: 6px 0; font-size: 13px; }
        .value { font-family: Consolas, monospace; font-weight: 800; }
        .price { margin-top: 10px; font-weight: 800; font-size: 18px; color: #059669; }
        .foot { margin-top: 8px; font-size: 10px; color: #64748b; }
        @media print {
            body { background: #fff; padding: 0; }
            .sheet { gap: 6px; }
            .voucher { border-color: #94a3b8; }
        }
    </style>
</head>
<body>
    <div class="sheet">
        @foreach ($vouchers as $voucher)
            <section class="voucher">
                <div class="head">
                    <div class="brand">NEX ISP Platform</div>
                    <div class="tag">{{ $voucher->profile?->name ?? 'Hotspot' }}</div>
                </div>
                <div class="row"><span>Username</span><span class="value">{{ $voucher->username }}</span></div>
                <div class="row"><span>Password</span><span class="value">{{ $voucher->password }}</span></div>
                <div class="row"><span>Batch</span><span>{{ $voucher->batch_code ?: '-' }}</span></div>
                <div class="row"><span>Outlet</span><span>{{ $voucher->outlet_name ?: '-' }}</span></div>
                <div class="price">Rp{{ number_format((float) $voucher->price, 0, ',', '.') }}</div>
                <div class="foot">Login via captive portal MikroTik. Simpan voucher ini sampai masa akses selesai.</div>
            </section>
        @endforeach
    </div>
    <script>
        window.addEventListener('load', () => window.print());
    </script>
</body>
</html>
