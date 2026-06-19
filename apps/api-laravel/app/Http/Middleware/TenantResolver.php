<?php

namespace App\Http\Middleware;

use App\Models\Tenant;
use Closure;
use Illuminate\Http\Request;

class TenantResolver
{
    public function handle(Request $request, Closure $next)
    {
        $tenantId = $request->route('tenant_id') ?: $request->header('X-Tenant-ID');
        $tenant = $tenantId ? Tenant::find($tenantId) : null;

        if (! $tenant) {
            return response()->json([
                'success' => false,
                'message' => 'Tenant not found.',
                'errors' => ['tenant_id' => ['Invalid tenant context.']],
                'trace_id' => (string) str()->uuid(),
            ], 403);
        }

        $user = $request->user();
        if ($user && ! $user->isPlatformOwner() && $user->tenant_id !== $tenant->id) {
            return response()->json([
                'success' => false,
                'message' => 'Tenant access denied.',
                'errors' => ['tenant_id' => ['You cannot access this tenant.']],
                'trace_id' => (string) str()->uuid(),
            ], 403);
        }

        app()->instance('tenant', $tenant);
        $request->attributes->set('tenant', $tenant);

        return $next($request);
    }
}
