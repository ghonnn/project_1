<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Http\Request;

class AuditService
{
    public function log(string $action, string $entityType, ?string $entityId = null, array $old = [], array $new = [], ?Request $request = null): void
    {
        AuditLog::create([
            'tenant_id' => app()->bound('tenant') ? app('tenant')->id : null,
            'user_id' => $request?->user()?->id,
            'action' => $action,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'old_values' => $old ?: null,
            'new_values' => $new ?: null,
            'ip_address' => $request?->ip(),
            'user_agent' => $request?->userAgent(),
        ]);
    }
}
