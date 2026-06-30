<?php

use App\Models\Invoice;
use App\Models\Mitra;
use App\Models\HotspotTemplate;
use App\Models\HotspotVoucher;
use App\Http\Controllers\RouterSnmpController;
use App\Services\HotspotVoucherService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/invoice/print/{invoice}', function (Invoice $invoice) {
    $invoice->load(['tenant', 'customer', 'items.service', 'payments']);

    return view('invoices.print', [
        'invoice' => $invoice,
        'paper' => request('paper', 'roll'),
    ]);
})->name('invoice.print');

Route::get('/voucher/print/{token}', function (string $token) {
    $payload = Cache::pull('voucher-print:'.$token);

    abort_unless(is_array($payload) && ! empty($payload['voucher_ids']), 404);

    $vouchers = HotspotVoucher::with(['profile', 'router', 'radiusServer', 'outlet', 'mitra', 'admin'])
        ->where('tenant_id', $payload['tenant_id'] ?? null)
        ->whereIn('id', $payload['voucher_ids'])
        ->orderBy('batch_code')
        ->orderBy('username')
        ->get();

    abort_if($vouchers->isEmpty(), 404);

    $template = HotspotTemplate::where('tenant_id', $payload['tenant_id'] ?? null)
        ->when($payload['template_id'] ?? null, fn ($query, $templateId) => $query->where('id', $templateId))
        ->first()
        ?: HotspotTemplate::where('tenant_id', $payload['tenant_id'] ?? null)->where('status', 'active')->orderBy('name')->first()
        ?: new HotspotTemplate(app(HotspotVoucherService::class)->defaultPrintTemplates()[0] + ['tenant_id' => $payload['tenant_id'] ?? null]);

    return view('exports.hotspot-vouchers-print', [
        'vouchers' => $vouchers,
        'template' => $template,
    ]);
})->middleware('auth')->name('voucher.print');

Route::get('/voucher/rekap/print/{token}', function (string $token) {
    $payload = Cache::pull('voucher-recap-print:'.$token);

    abort_unless(is_array($payload), 404);

    $partner = ! empty($payload['partner_id']) ? Mitra::find($payload['partner_id']) : null;
    $dateFrom = $payload['date_from'] ?? now('Asia/Jakarta')->startOfMonth()->format('Y-m-d');
    $dateUntil = $payload['date_until'] ?? now('Asia/Jakarta')->format('Y-m-d');

    $rows = HotspotVoucher::query()
        ->with('profile')
        ->where('tenant_id', $payload['tenant_id'] ?? null)
        ->whereDate('created_at', '>=', $dateFrom)
        ->whereDate('created_at', '<=', $dateUntil)
        ->when($partner, function ($query) use ($partner): void {
            $query->where(function ($query) use ($partner): void {
                $query->where('mitra_id', $partner->id)->orWhere('partner_name', $partner->name);
            });
        })
        ->selectRaw('batch_code, date(created_at) as created_date, partner_name, outlet_name, profile_id, count(*) as qty, sum(case when status = \'stock\' then 1 else 0 end) as stock_qty, sum(case when status in (\'sold\', \'expired\') then 1 else 0 end) as sold_qty, sum(hpp) as hpp, sum(commission) as commission, sum(price) as price')
        ->groupByRaw('batch_code, date(created_at), partner_name, outlet_name, profile_id')
        ->latest('created_date')
        ->get();

    return view('exports.voucher-recap-print', [
        'rows' => $rows,
        'partnerName' => $partner?->name ?? 'SYSTEM',
        'dateFrom' => $dateFrom,
        'dateUntil' => $dateUntil,
    ]);
})->middleware('auth')->name('voucher.recap.print');

Route::get('/voucher/template/preview/{token}', function (string $token) {
    $payload = Cache::pull('voucher-template-preview:'.$token);

    abort_unless(is_array($payload) && isset($payload['html']), 404);

    return response($payload['html']);
})->middleware('auth')->name('voucher.template.preview');

Route::get('/admin/routers/{router}/snmp/live', [RouterSnmpController::class, 'show'])
    ->middleware('auth')
    ->name('routers.snmp.live');
