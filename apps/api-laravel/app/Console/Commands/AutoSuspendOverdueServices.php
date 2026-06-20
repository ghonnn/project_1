<?php

namespace App\Console\Commands;

use App\Models\AuditLog;
use App\Models\InvoiceItem;
use App\Models\Service;
use App\Services\FreeRadiusService;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class AutoSuspendOverdueServices extends Command
{
    protected $signature = 'billing:auto-suspend {--date=}';

    protected $description = 'Suspend active services with an unpaid invoice past its isolation/due date, and disable their Radius users.';

    public function __construct(private readonly FreeRadiusService $freeRadius)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $today = $this->option('date') ? Carbon::parse($this->option('date')) : Carbon::today();

        $services = Service::query()
            ->with('radiusUsers')
            ->where('status', 'active')
            ->whereNotNull('billing_isolation_date')
            ->whereDate('billing_isolation_date', '<=', $today->toDateString())
            ->get();

        $suspended = 0;

        foreach ($services as $service) {
            $hasUnpaidOverdueInvoice = InvoiceItem::query()
                ->where('service_id', $service->id)
                ->whereHas('invoice', fn ($query) => $query
                    ->where('status', '!=', 'paid')
                    ->whereDate('due_date', '<=', $today->toDateString()))
                ->exists();

            if (! $hasUnpaidOverdueInvoice) {
                continue;
            }

            $service->update(['status' => 'suspended', 'suspended_at' => $today->toDateString()]);

            foreach ($service->radiusUsers as $radiusUser) {
                $this->freeRadius->suspendUser($radiusUser);
            }

            AuditLog::create([
                'tenant_id' => $service->tenant_id,
                'action' => 'service.suspended.auto_billing',
                'entity_type' => 'services',
                'entity_id' => $service->id,
                'new_values' => ['billing_isolation_date' => $service->billing_isolation_date],
            ]);

            $suspended++;
        }

        $this->info("Suspended {$suspended} service(s) for ".$today->toDateString());

        return self::SUCCESS;
    }
}
