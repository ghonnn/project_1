<?php

namespace App\Services;

use App\Models\NasDevice;
use App\Models\RadiusServer;
use App\Models\RadiusSyncLog;
use App\Models\RadiusUser;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class FreeRadiusService
{
    public function testServerConnection(RadiusServer $server): array
    {
        $host = trim((string) $server->host);
        $authPort = (int) $server->auth_port;
        $acctPort = (int) $server->acct_port;

        $status = 'success';
        $message = 'Konfigurasi FreeRadius valid dan siap dipakai untuk sync NAS, PPPoE, dan Hotspot.';

        if ($host === '') {
            $status = 'failed';
            $message = 'Host FreeRadius belum diisi.';
        } elseif ($authPort < 1 || $authPort > 65535 || $acctPort < 1 || $acctPort > 65535) {
            $status = 'failed';
            $message = 'Port authentication/accounting FreeRadius tidak valid.';
        } elseif (filter_var($host, FILTER_VALIDATE_IP) === false && gethostbyname($host) === $host) {
            $status = 'failed';
            $message = 'Host FreeRadius tidak bisa di-resolve dari aplikasi.';
        } elseif (config('services.freeradius.sync_mode', 'simulated') === 'database') {
            $message = 'Konfigurasi FreeRadius valid. Mode sync database aktif; data NAS dan user akan ditulis ke tabel SQL FreeRadius.';
        }

        $server->update([
            'last_test_status' => $status,
            'last_test_message' => $message,
            'last_tested_at' => Carbon::now(),
        ]);

        return compact('status', 'message');
    }

    public function syncUser(RadiusUser $user): RadiusSyncLog
    {
        $mode = config('services.freeradius.sync_mode', 'simulated');

        if ($mode === 'database') {
            try {
                $payload = $this->syncDatabaseUser($user->fresh(['profile', 'service.product', 'service.customer', 'router']));

                return RadiusSyncLog::create([
                    'tenant_id' => $user->tenant_id,
                    'radius_user_id' => $user->id,
                    'action' => 'sync',
                    'status' => 'synced',
                    'message' => 'FreeRadius SQL tables updated.',
                    'payload' => $payload,
                ]);
            } catch (\Throwable $exception) {
                return RadiusSyncLog::create([
                    'tenant_id' => $user->tenant_id,
                    'radius_user_id' => $user->id,
                    'action' => 'sync',
                    'status' => 'failed',
                    'message' => $exception->getMessage(),
                    'payload' => ['username' => $user->username, 'mode' => $mode],
                ]);
            }
        }

        return RadiusSyncLog::create([
            'tenant_id' => $user->tenant_id,
            'radius_user_id' => $user->id,
            'action' => 'sync',
            'status' => 'simulated',
            'message' => 'Simulated FreeRadius sync completed.',
            'payload' => ['username' => $user->username, 'mode' => $mode],
        ]);
    }

    public function suspendUser(RadiusUser $user): RadiusSyncLog
    {
        $user->update(['status' => 'suspended']);

        if (config('services.freeradius.sync_mode', 'simulated') === 'database') {
            try {
                $payload = $this->syncDatabaseSuspendedUser($user->fresh(['profile', 'service.routerMappings.router', 'router']));

                return RadiusSyncLog::create([
                    'tenant_id' => $user->tenant_id,
                    'radius_user_id' => $user->id,
                    'action' => 'suspend',
                    'status' => 'synced',
                    'message' => 'FreeRadius user moved to isolation profile.',
                    'payload' => $payload,
                ]);
            } catch (\Throwable $exception) {
                return RadiusSyncLog::create([
                    'tenant_id' => $user->tenant_id,
                    'radius_user_id' => $user->id,
                    'action' => 'suspend',
                    'status' => 'failed',
                    'message' => $exception->getMessage(),
                ]);
            }
        }

        return RadiusSyncLog::create([
            'tenant_id' => $user->tenant_id,
            'radius_user_id' => $user->id,
            'action' => 'suspend',
            'status' => 'simulated',
            'message' => 'Radius user suspended in simulated mode.',
        ]);
    }

    public function activateUser(RadiusUser $user): RadiusSyncLog
    {
        $user->update(['status' => 'active']);

        if (config('services.freeradius.sync_mode', 'simulated') === 'database') {
            return $this->syncUser($user->fresh());
        }

        return RadiusSyncLog::create([
            'tenant_id' => $user->tenant_id,
            'radius_user_id' => $user->id,
            'action' => 'activate',
            'status' => 'simulated',
            'message' => 'Radius user activated in simulated mode.',
        ]);
    }

    public function syncNas(NasDevice $device): RadiusSyncLog
    {
        $mode = config('services.freeradius.sync_mode', 'simulated');

        if ($mode === 'database') {
            try {
                $payload = $this->syncDatabaseNas($device->fresh(['router', 'radiusServer']));

                return RadiusSyncLog::create([
                    'tenant_id' => $device->tenant_id,
                    'radius_server_id' => $device->radius_server_id,
                    'action' => 'sync_nas',
                    'status' => 'synced',
                    'message' => 'FreeRadius NAS client updated.',
                    'payload' => $payload,
                ]);
            } catch (\Throwable $exception) {
                return RadiusSyncLog::create([
                    'tenant_id' => $device->tenant_id,
                    'radius_server_id' => $device->radius_server_id,
                    'action' => 'sync_nas',
                    'status' => 'failed',
                    'message' => $exception->getMessage(),
                ]);
            }
        }

        return RadiusSyncLog::create([
            'tenant_id' => $device->tenant_id,
            'radius_server_id' => $device->radius_server_id,
            'action' => 'sync_nas',
            'status' => 'simulated',
            'message' => 'Simulated NAS sync completed.',
            'payload' => [
                'nas_ip_address' => $device->nas_ip_address,
                'mode' => $mode,
            ],
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function syncDatabaseUser(RadiusUser $user): array
    {
        $this->ensureRadiusTables();

        $profile = $user->profile;
        $attributes = $profile?->attributes ?? [];
        $groupName = $this->radiusName((string) ($attributes['Mikrotik-Group'] ?? $profile?->name ?? 'NEX-DEFAULT'));
        $username = $this->radiusName($user->username);

        DB::transaction(function () use ($user, $username, $groupName, $attributes): void {
            $this->deleteSqlUser($username);
            $this->syncDatabaseGroup($groupName, $attributes);

            DB::table('radcheck')->insert([
                'username' => $username,
                'attribute' => 'Cleartext-Password',
                'op' => ':=',
                'value' => (string) $user->secret,
            ]);

            if (! blank($attributes['Shared-Users'] ?? null)) {
                DB::table('radcheck')->insert([
                    'username' => $username,
                    'attribute' => 'Simultaneous-Use',
                    'op' => ':=',
                    'value' => (string) $attributes['Shared-Users'],
                ]);
            }

            if (! blank($user->service?->ip_address)) {
                DB::table('radreply')->insert([
                    'username' => $username,
                    'attribute' => 'Framed-IP-Address',
                    'op' => ':=',
                    'value' => (string) $user->service->ip_address,
                ]);
            }

            DB::table('radusergroup')->insert([
                'username' => $username,
                'groupname' => $groupName,
                'priority' => 1,
            ]);
        });

        return [
            'username' => $username,
            'groupname' => $groupName,
            'tables' => ['radcheck', 'radreply', 'radusergroup', 'radgroupreply'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function syncDatabaseSuspendedUser(RadiusUser $user): array
    {
        $this->ensureRadiusTables();

        $username = $this->radiusName($user->username);
        $groupName = $this->radiusName($this->isolationGroupName($user));

        DB::transaction(function () use ($user, $username, $groupName): void {
            $this->deleteSqlUser($username);
            $this->syncDatabaseGroup($groupName, ['Mikrotik-Group' => $groupName]);

            DB::table('radcheck')->insert([
                'username' => $username,
                'attribute' => 'Cleartext-Password',
                'op' => ':=',
                'value' => (string) $user->secret,
            ]);

            DB::table('radusergroup')->insert([
                'username' => $username,
                'groupname' => $groupName,
                'priority' => 1,
            ]);
        });

        return [
            'username' => $username,
            'groupname' => $groupName,
            'status' => 'suspended',
        ];
    }

    /**
     * @param array<string, mixed> $attributes
     */
    private function syncDatabaseGroup(string $groupName, array $attributes): void
    {
        DB::table('radgroupreply')->where('groupname', $groupName)->delete();

        foreach ($this->radiusReplyAttributes($attributes) as $attribute => $value) {
            if (blank($value)) {
                continue;
            }

            DB::table('radgroupreply')->insert([
                'groupname' => $groupName,
                'attribute' => $attribute,
                'op' => ':=',
                'value' => (string) $value,
            ]);
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function syncDatabaseNas(NasDevice $device): array
    {
        $this->ensureRadiusTables();

        $routerProfile = $device->router?->snmp_profile ?? [];
        $community = $routerProfile['snmp_community'] ?? 'NEXRADIUS';

        DB::table('nas')->updateOrInsert(
            ['nasname' => $device->nas_ip_address],
            [
                'shortname' => $this->radiusName($device->hostname, 32),
                'type' => strtolower((string) ($device->vendor_type ?: 'mikrotik')),
                'ports' => null,
                'secret' => $device->secret,
                'server' => $device->radiusServer?->host,
                'community' => $community,
                'description' => 'Managed by NEXBIL router '.$device->router?->router_name,
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );

        return [
            'nasname' => $device->nas_ip_address,
            'shortname' => $device->hostname,
            'radius_server' => $device->radiusServer?->host,
        ];
    }

    private function deleteSqlUser(string $username): void
    {
        DB::table('radcheck')->where('username', $username)->delete();
        DB::table('radreply')->where('username', $username)->delete();
        DB::table('radusergroup')->where('username', $username)->delete();
    }

    /**
     * @param array<string, mixed> $attributes
     * @return array<string, mixed>
     */
    private function radiusReplyAttributes(array $attributes): array
    {
        return collect($attributes)
            ->reject(function (mixed $value, mixed $attribute): bool {
                $attribute = (string) $attribute;

                return blank($value)
                    || $attribute === ''
                    || ctype_digit($attribute)
                    || str_starts_with($attribute, 'Profile-')
                    || in_array($attribute, ['Shared-Users', 'Active-Days'], true);
            })
            ->all();
    }

    private function isolationGroupName(RadiusUser $user): string
    {
        $router = $user->router ?: $user->service?->routerMappings()->where('is_primary', true)->first()?->router;
        $profile = $router?->snmp_profile ?? [];

        return (string) ($profile['isolir_profile_name'] ?? 'NEXISOLIR');
    }

    private function ensureRadiusTables(): void
    {
        foreach (['nas', 'radcheck', 'radreply', 'radusergroup', 'radgroupreply'] as $table) {
            if (! Schema::hasTable($table)) {
                throw new \RuntimeException('FreeRadius table '.$table.' is missing. Run php artisan migrate.');
            }
        }
    }

    private function radiusName(string $value, int $limit = 64): string
    {
        $value = trim($value);

        if ($value === '') {
            $value = 'NEX-DEFAULT';
        }

        return substr($value, 0, $limit);
    }
}
