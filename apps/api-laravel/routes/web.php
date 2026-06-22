<?php

use App\Models\Invoice;
use App\Http\Controllers\RouterSnmpController;
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

Route::get('/admin/routers/{router}/snmp/live', [RouterSnmpController::class, 'show'])
    ->middleware('auth')
    ->name('routers.snmp.live');
