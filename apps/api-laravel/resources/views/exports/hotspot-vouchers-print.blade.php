<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>NEX Voucher Print</title>
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; padding: 10mm; background: #fff; color: #000; }
        .nex-voucher-sheet { display: block; }
        .nex-voucher-item { display: inline-block; break-inside: avoid; page-break-inside: avoid; vertical-align: top; }
        @page { size: A4 portrait; margin: 8mm; }
        @media print {
            body { padding: 0; }
        }
    </style>
</head>
<body>
    <main class="nex-voucher-sheet">
        @foreach ($vouchers as $voucher)
            <div class="nex-voucher-item">
                @php
                    $html = app(\App\Services\HotspotVoucherService::class)->renderTemplate($template, $voucher, $loop->iteration);
                    $html = preg_replace('/^.*?<body[^>]*>/is', '', $html) ?? $html;
                    $html = preg_replace('/<\/body>\s*<\/html>\s*$/is', '', $html) ?? $html;
                @endphp
                {!! $html !!}
            </div>
        @endforeach
    </main>

    <script>
        window.addEventListener('load', () => setTimeout(() => window.print(), 350));
    </script>
</body>
</html>
