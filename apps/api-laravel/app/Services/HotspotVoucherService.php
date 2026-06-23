<?php

namespace App\Services;

use App\Models\HotspotTemplate;
use App\Models\HotspotVoucher;
use App\Models\RadiusProfile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class HotspotVoucherService
{
    /**
     * @param array<string, mixed> $data
     * @return array<int, HotspotVoucher>
     */
    public function generate(array $data): array
    {
        $profile = RadiusProfile::query()->findOrFail($data['profile_id']);
        $qty = max(1, min(500, (int) ($data['qty'] ?? 1)));
        $prefix = strtoupper(preg_replace('/[^A-Z0-9]/i', '', (string) ($data['prefix'] ?? 'NEX')) ?: 'NEX');
        $batchCode = $data['batch_code'] ?: now('Asia/Jakarta')->format('YmdHis');
        $vouchers = [];

        $this->syncProfile($profile);

        DB::transaction(function () use ($data, $profile, $qty, $prefix, $batchCode, &$vouchers): void {
            for ($i = 0; $i < $qty; $i++) {
                $username = $this->uniqueUsername((string) $data['tenant_id'], $prefix);
                $password = $this->randomPassword((int) ($data['password_length'] ?? 6));

                $voucher = HotspotVoucher::create([
                    'tenant_id' => $data['tenant_id'],
                    'profile_id' => $profile->id,
                    'router_id' => $data['router_id'] ?: null,
                    'radius_server_id' => $data['radius_server_id'] ?: null,
                    'username' => $username,
                    'password' => $password,
                    'batch_code' => $batchCode,
                    'partner_name' => $data['partner_name'] ?: null,
                    'outlet_name' => $data['outlet_name'] ?: null,
                    'hpp' => (float) ($data['hpp'] ?? ($profile->attributes['HPP'] ?? 0)),
                    'commission' => (float) ($data['commission'] ?? ($profile->attributes['Commission'] ?? 0)),
                    'price' => (float) ($data['price'] ?? ($profile->attributes['Price'] ?? 0)),
                    'status' => 'stock',
                ]);

                $this->syncVoucher($voucher);
                $vouchers[] = $voucher->fresh(['profile', 'router', 'radiusServer']);
            }
        });

        return $vouchers;
    }

    public function syncProfile(RadiusProfile $profile): void
    {
        if (! $this->radiusTablesReady()) {
            return;
        }

        $attributes = $profile->attributes ?? [];
        $groupName = $this->radiusName((string) ($attributes['Mikrotik-Group'] ?? $profile->name));

        DB::table('radgroupreply')->where('groupname', $groupName)->delete();

        foreach ($this->replyAttributes($attributes) as $attribute => $value) {
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

    public function syncVoucher(HotspotVoucher $voucher): void
    {
        if (! $this->radiusTablesReady()) {
            $voucher->update(['sync_message' => 'FreeRadius SQL tables belum siap.']);

            return;
        }

        $voucher->loadMissing('profile');
        $attributes = $voucher->profile?->attributes ?? [];
        $username = $this->radiusName($voucher->username);
        $groupName = $this->radiusName((string) ($attributes['Mikrotik-Group'] ?? $voucher->profile?->name ?? 'NEX-HOTSPOT'));

        DB::transaction(function () use ($voucher, $username, $groupName, $attributes): void {
            DB::table('radcheck')->where('username', $username)->delete();
            DB::table('radreply')->where('username', $username)->delete();
            DB::table('radusergroup')->where('username', $username)->delete();

            DB::table('radcheck')->insert([
                'username' => $username,
                'attribute' => 'Cleartext-Password',
                'op' => ':=',
                'value' => $voucher->password,
            ]);

            if (! blank($attributes['Shared-Users'] ?? null)) {
                DB::table('radcheck')->insert([
                    'username' => $username,
                    'attribute' => 'Simultaneous-Use',
                    'op' => ':=',
                    'value' => (string) $attributes['Shared-Users'],
                ]);
            }

            DB::table('radusergroup')->insert([
                'username' => $username,
                'groupname' => $groupName,
                'priority' => 1,
            ]);
        });

        $voucher->update([
            'synced_at' => now(),
            'sync_message' => 'Synced to FreeRadius SQL.',
        ]);
    }

    public function defaultTemplate(string $hotspotName = 'NEX ISP Hotspot', string $phone = '082170000000'): string
    {
        return <<<'HTML'
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>NEX ISP Hotspot</title>
  <style>
    :root{font-family:Inter,Segoe UI,Arial,sans-serif;color:#0f172a;background:#f8fafc}
    body{margin:0;min-height:100vh;display:grid;place-items:center;background:linear-gradient(135deg,#ecfdf5,#f8fafc)}
    .card{width:min(420px,92vw);background:#fff;border:1px solid #dbe3ef;border-radius:16px;box-shadow:0 24px 80px rgba(15,23,42,.12);padding:28px}
    .brand{display:flex;align-items:center;gap:12px;font-weight:800;font-size:22px}.mark{display:grid;place-items:center;width:42px;height:42px;border-radius:10px;background:#059669;color:#fff}
    h1{font-size:22px;margin:24px 0 6px}.muted{color:#64748b;font-size:14px;margin:0 0 22px}
    label{display:block;font-size:13px;font-weight:700;margin:12px 0 6px}input{width:100%;box-sizing:border-box;border:1px solid #cbd5e1;border-radius:10px;padding:13px 14px;font-size:15px}
    button{width:100%;border:0;border-radius:10px;background:#059669;color:white;padding:13px 16px;font-weight:800;margin-top:18px;cursor:pointer}
    .foot{font-size:12px;color:#64748b;text-align:center;margin-top:18px}
  </style>
</head>
<body>
  <main class="card">
    <div class="brand"><div class="mark">NEX</div><span>{{hotspot_name}}</span></div>
    <h1>Masuk WiFi</h1>
    <p class="muted">Gunakan username dan password pada voucher Anda.</p>
    <form name="login" action="$(link-login-only)" method="post">
      <input type="hidden" name="dst" value="$(link-orig)">
      <input type="hidden" name="popup" value="true">
      <label>Username</label>
      <input name="username" autocomplete="username" required>
      <label>Password</label>
      <input name="password" type="password" autocomplete="current-password" required>
      <button type="submit">Connect</button>
    </form>
    <div class="foot">Bantuan: {{support_phone}}</div>
  </main>
</body>
</html>
HTML;
    }

    public function renderTemplate(HotspotTemplate $template): string
    {
        return str_replace(
            ['{{hotspot_name}}', '{{support_phone}}', '{{dns_name}}'],
            [$template->hotspot_name, $template->support_phone ?: '-', $template->dns_name ?: ''],
            $template->html_body
        );
    }

    private function radiusTablesReady(): bool
    {
        foreach (['radcheck', 'radusergroup', 'radgroupreply'] as $table) {
            if (! Schema::hasTable($table)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param array<string, mixed> $attributes
     * @return array<string, mixed>
     */
    private function replyAttributes(array $attributes): array
    {
        return collect([
            'Mikrotik-Group' => $attributes['Mikrotik-Group'] ?? null,
            'Mikrotik-Rate-Limit' => $attributes['Mikrotik-Rate-Limit'] ?? null,
            'Mikrotik-Address-List' => $attributes['Mikrotik-Address-List'] ?? null,
            'Mikrotik-Total-Limit' => $this->quotaBytes($attributes['Quota-MB'] ?? null),
            'Session-Timeout' => $this->durationSeconds($attributes['Duration-Minutes'] ?? null),
        ])->filter(fn (mixed $value): bool => ! blank($value))->all();
    }

    private function uniqueUsername(string $tenantId, string $prefix): string
    {
        do {
            $username = $prefix.Str::upper(Str::random(6));
        } while (HotspotVoucher::where('tenant_id', $tenantId)->where('username', $username)->exists());

        return $username;
    }

    private function randomPassword(int $length): string
    {
        $length = max(4, min(12, $length));
        $alphabet = '23456789ABCDEFGHJKLMNPQRSTUVWXYZ';
        $password = '';

        for ($i = 0; $i < $length; $i++) {
            $password .= $alphabet[random_int(0, strlen($alphabet) - 1)];
        }

        return $password;
    }

    private function quotaBytes(mixed $quotaMb): ?string
    {
        $quota = (int) $quotaMb;

        return $quota > 0 ? (string) ($quota * 1024 * 1024) : null;
    }

    private function durationSeconds(mixed $minutes): ?string
    {
        $duration = (int) $minutes;

        return $duration > 0 ? (string) ($duration * 60) : null;
    }

    private function radiusName(string $value, int $limit = 64): string
    {
        $value = trim($value) ?: 'NEX-HOTSPOT';

        return substr($value, 0, $limit);
    }
}
