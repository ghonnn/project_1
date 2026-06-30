<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Rekap Pembuatan Voucher</title>
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; background: #fff; color: #000; font-family: Arial, Helvetica, sans-serif; font-size: 12px; }
        .page { width: 100%; padding: 22mm 18mm; }
        h1 { margin: 0 0 6px; font-size: 24px; line-height: 1.2; }
        .rule { border-top: 1px solid #222; margin-bottom: 18px; }
        .meta { display: flex; justify-content: space-between; gap: 16px; margin-bottom: 16px; font-weight: 700; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #222; padding: 6px 5px; text-align: left; }
        th { background: #f2f4dd; font-weight: 800; }
        td.num, th.num { text-align: right; }
        tfoot td { background: #f2f4dd; font-weight: 800; }
        @page { size: A4 landscape; margin: 10mm; }
        @media print {
            .page { padding: 0; }
        }
    </style>
</head>
<body>
    @php
        $totalQty = $rows->sum('qty');
        $totalStock = $rows->sum('stock_qty');
        $totalSold = $rows->sum('sold_qty');
        $totalCommission = $rows->sum('commission');
        $totalSales = $rows->sum('price');
    @endphp

    <main class="page">
        <h1>REKAP PEMBUATAN VOUCHER</h1>
        <div class="rule"></div>

        <div class="meta">
            <div>
                <div>Nama Partner : {{ $partnerName }}</div>
                <div>Tanggal pembuatan : {{ \Illuminate\Support\Carbon::parse($dateFrom)->format('d/m/Y') }} - {{ \Illuminate\Support\Carbon::parse($dateUntil)->format('d/m/Y') }}</div>
            </div>
            <div>Tanggal cetak : {{ now('Asia/Jakarta')->format('d/m/Y H:i:s') }}</div>
        </div>

        <table>
            <thead>
                <tr>
                    <th style="width: 42px;">NO</th>
                    <th>TANGGAL PEMBUATAN</th>
                    <th>KODE</th>
                    <th>OUTLET</th>
                    <th>PROFIL</th>
                    <th class="num">QTY</th>
                    <th class="num">STOK</th>
                    <th class="num">TERJUAL</th>
                    <th class="num">KOMISI</th>
                    <th class="num">PENJUALAN</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($rows as $row)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $row->created_date ? \Illuminate\Support\Carbon::parse($row->created_date)->format('d/m/Y') : '-' }}</td>
                        <td>{{ $row->batch_code ?: '-' }}</td>
                        <td>{{ $row->outlet_name ?: '-' }}</td>
                        <td>{{ $row->profile?->name ?: '-' }}</td>
                        <td class="num">{{ number_format((int) $row->qty, 0, ',', '.') }}</td>
                        <td class="num">{{ number_format((int) $row->stock_qty, 0, ',', '.') }}</td>
                        <td class="num">{{ number_format((int) $row->sold_qty, 0, ',', '.') }}</td>
                        <td class="num">Rp{{ number_format((float) $row->commission, 0, ',', '.') }}</td>
                        <td class="num">Rp{{ number_format((float) $row->price, 0, ',', '.') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" style="text-align:center;">Tidak ada data rekap voucher.</td>
                    </tr>
                @endforelse
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="5" class="num">TOTAL</td>
                    <td class="num">{{ number_format((int) $totalQty, 0, ',', '.') }}</td>
                    <td class="num">{{ number_format((int) $totalStock, 0, ',', '.') }}</td>
                    <td class="num">{{ number_format((int) $totalSold, 0, ',', '.') }}</td>
                    <td class="num">Rp{{ number_format((float) $totalCommission, 0, ',', '.') }}</td>
                    <td class="num">Rp{{ number_format((float) $totalSales, 0, ',', '.') }}</td>
                </tr>
            </tfoot>
        </table>
    </main>

    <script>
        window.addEventListener('load', () => setTimeout(() => window.print(), 250));
    </script>
</body>
</html>
