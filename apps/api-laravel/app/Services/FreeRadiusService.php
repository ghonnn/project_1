<?php

namespace App\Services;

use App\Models\RadiusServer;
use App\Models\RadiusSyncLog;
use App\Models\RadiusUser;
use Illuminate\Support\Carbon;

class FreeRadiusService
{
    public function testServerConnection(RadiusServer $server): array
    {
        $status = 'warning';
        $message = 'UDP Radius ports cannot be fully verified without a Radius packet exchange; host configuration saved and simulated warning returned.';

        foreach ([$server->auth_port, $server->acct_port] as $port) {
            $socket = @fsockopen('udp://'.$server->host, (int) $port, $errno, $errstr, 1);
            if ($socket) {
                fclose($socket);
                $status = 'warning';
            }
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

        return RadiusSyncLog::create([
            'tenant_id' => $user->tenant_id,
            'radius_user_id' => $user->id,
            'action' => 'sync',
            'status' => $mode === 'database' ? 'queued' : 'simulated',
            'message' => $mode === 'database' ? 'Database sync mode requested but adapter is not enabled in MVP.' : 'Simulated FreeRadius sync completed.',
            'payload' => ['username' => $user->username, 'mode' => $mode],
        ]);
    }

    public function suspendUser(RadiusUser $user): RadiusSyncLog
    {
        $user->update(['status' => 'suspended']);

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

        return RadiusSyncLog::create([
            'tenant_id' => $user->tenant_id,
            'radius_user_id' => $user->id,
            'action' => 'activate',
            'status' => 'simulated',
            'message' => 'Radius user activated in simulated mode.',
        ]);
    }
}
