<?php

namespace App\Console\Commands;

use App\Models\AuditLog;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Service;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class GenerateRecurringInvoices extends Command
{
    protected $signature = 'billing:generate-invoices {--date=}';

    protected $description = 'Generate recurring invoices for active services whose invoice_issue_date is due, with prorate on first cycle.';

    public function handle(): int
    {
        $today = $this->option('date') ? Carbon::parse($this->option('date')) : Carbon::today();

        $services = Service::query()
            ->where('status', 'active')
            ->whereNotNull('invoice_issue_date')
            ->whereNotNull('billing_active_date')
            ->whereDate('invoice_issue_date', '<=', $today->toDateString())
            ->get();

        $generated = 0;

        foreach ($services as $service) {
            $alreadyInvoiced = InvoiceItem::query()
                ->where('service_id', $service->id)
                ->whereHas('invoice', fn ($query) => $query->whereDate('issue_date', $service->invoice_issue_date))
                ->exists();

            if ($alreadyInvoiced) {
                continue;
            }

            DB::transaction(function () use ($service, $today): void {
                $isFirstInvoice = ! InvoiceItem::query()->where('service_id', $service->id)->exists();
                $price = (float) $service->profile_price;

                $dpp = $price;
                if ($isFirstInvoice && $service->billing_active_date && $service->billing_isolation_date) {
                    $daysUsed = Carbon::parse($service->billing_active_date)->diffInDays(Carbon::parse($service->billing_isolation_date));
                    $dpp = round($price * min(30, max(0, $daysUsed)) / 30, 2);
                }

                $ppn = $service->ppn_enabled ? round($dpp * ((float) $service->ppn_rate) / 100, 2) : 0.0;
                $total = $dpp + $ppn;
                $issueDate = Carbon::parse($service->invoice_issue_date);
                $dueDate = $service->billing_isolation_date ? Carbon::parse($service->billing_isolation_date) : $issueDate->copy()->addDays(14);

                $invoice = Invoice::create([
                    'tenant_id' => $service->tenant_id,
                    'customer_id' => $service->customer_id,
                    'invoice_number' => 'INV-'.$issueDate->format('Ymd').'-'.random_int(1000, 9999),
                    'issue_date' => $issueDate->toDateString(),
                    'due_date' => $dueDate->toDateString(),
                    'status' => 'issued',
                    'total_amount' => $total,
                ]);

                InvoiceItem::create([
                    'tenant_id' => $service->tenant_id,
                    'invoice_id' => $invoice->id,
                    'service_id' => $service->id,
                    'description' => ($service->billing_profile_name ?: 'Layanan').($isFirstInvoice ? ' (prorate)' : ''),
                    'quantity' => 1,
                    'unit_amount' => $dpp,
                    'total_amount' => $total,
                ]);

                AuditLog::create([
                    'tenant_id' => $service->tenant_id,
                    'action' => 'invoice.generated.recurring',
                    'entity_type' => 'invoices',
                    'entity_id' => $invoice->id,
                    'new_values' => ['service_id' => $service->id, 'total_amount' => $total, 'prorate' => $isFirstInvoice],
                ]);

                $service->update(['billing_active_date' => Carbon::parse($service->billing_active_date)->addMonthNoOverflow()->toDateString()]);
            });

            $generated++;
        }

        $this->info("Generated {$generated} invoice(s) for ".$today->toDateString());

        return self::SUCCESS;
    }
}
