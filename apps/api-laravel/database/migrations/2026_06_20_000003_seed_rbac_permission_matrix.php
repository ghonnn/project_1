<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        $newCodes = [
            ['module' => 'payment', 'action' => 'manage', 'code' => 'payment.manage'],
            ['module' => 'mitra', 'action' => 'manage', 'code' => 'mitra.manage'],
            ['module' => 'ticket', 'action' => 'manage', 'code' => 'ticket.manage'],
        ];

        foreach ($newCodes as $permission) {
            if (! DB::table('permissions')->where('code', $permission['code'])->exists()) {
                DB::table('permissions')->insert($permission + [
                    'id' => (string) Str::uuid(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        $permissionIdsByCode = DB::table('permissions')->pluck('id', 'code');
        $allCodes = $permissionIdsByCode->keys()->all();

        $matrix = [
            'platform_owner' => $allCodes,
            'tenant_owner' => $allCodes,
            'tenant_admin' => ['customer.manage', 'service.manage', 'router.manage', 'radius.manage', 'mitra.manage', 'ticket.manage', 'audit.read'],
            'finance' => ['billing.manage', 'payment.manage'],
            'noc' => ['router.manage', 'radius.manage', 'ticket.manage'],
            'sales' => ['customer.manage', 'service.manage', 'ticket.manage'],
            'technician' => ['ticket.manage'],
            'partner' => [],
            'customer' => [],
        ];

        foreach (DB::table('roles')->get() as $role) {
            $codes = $matrix[$role->code] ?? [];
            DB::table('role_permissions')->where('role_id', $role->id)->delete();

            foreach ($codes as $code) {
                if (! isset($permissionIdsByCode[$code])) {
                    continue;
                }

                DB::table('role_permissions')->insert([
                    'role_id' => $role->id,
                    'permission_id' => $permissionIdsByCode[$code],
                ]);
            }
        }
    }

    public function down(): void
    {
        // RBAC matrix is a data correction; no automatic revert to the previous "all roles get all permissions" state.
    }
};
