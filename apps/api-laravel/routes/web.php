<?php

use App\Models\Invoice;
use App\Models\HotspotVoucher;
use App\Http\Controllers\RouterSnmpController;
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

    return view('exports.hotspot-vouchers-print', [
        'vouchers' => $vouchers,
    ]);
})->middleware('auth')->name('voucher.print');

Route::get('/admin/routers/{router}/snmp/live', [RouterSnmpController::class, 'show'])
    ->middleware('auth')
    ->name('routers.snmp.live');
