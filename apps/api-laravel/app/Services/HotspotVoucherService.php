<?php

namespace App\Services;

use App\Models\HotspotTemplate;
use App\Models\HotspotVoucher;
use App\Models\RadiusProfile;
use App\Models\Mitra;
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
        $batchCode = ! empty($data['batch_code']) ? $data['batch_code'] : now('Asia/Jakarta')->format('YmdHis');
        $vouchers = [];

        $this->syncProfile($profile);

        DB::transaction(function () use ($data, $profile, $qty, $prefix, $batchCode, &$vouchers): void {
            // Lock partner balance if needed
            $partner = null;
            if (!empty($data['partner_id'])) {
                $partner = Mitra::find($data['partner_id']);
            } elseif (!empty($data['partner_name'])) {
                $partner = Mitra::where('tenant_id', $data['tenant_id'])->where('name', $data['partner_name'])->first();
            }

            $deductBalance = $partner && ($data['potong_saldo'] ?? 'no') === 'yes';

            if ($deductBalance) {
                $price = (float) ($data['price'] ?? ($profile->attributes['Price'] ?? 0));
                $commission = (float) ($data['commission'] ?? ($profile->attributes['Commission'] ?? 0));
                $singleNet = max(0.0, $price - $commission);
                
                $isImportOrIndividual = !empty($data['username']);
                $totalDeduct = $isImportOrIndividual ? $singleNet : ($singleNet * $qty);

                if ($partner->balance < $totalDeduct) {
                    throw new \Exception("Saldo partner tidak mencukupi. Saldo saat ini: " . number_format($partner->balance, 0, ',', '.') . ", dibutuhkan: " . number_format($totalDeduct, 0, ',', '.'));
                }

                $partner->decrement('balance', $totalDeduct);
            }

            $isImportOrIndividual = !empty($data['username']);
            $loopCount = $isImportOrIndividual ? 1 : $qty;

            for ($i = 0; $i < $loopCount; $i++) {
                $username = $isImportOrIndividual ? $data['username'] : $this->uniqueUsername((string) $data['tenant_id'], $prefix);
                $password = $isImportOrIndividual ? $data['password'] : $this->randomPassword((int) ($data['password_length'] ?? 6));

                if ($isImportOrIndividual && HotspotVoucher::where('tenant_id', $data['tenant_id'])->where('username', $username)->exists()) {
                    continue; // Skip existing usernames in imports
                }

                $voucher = HotspotVoucher::create([
                    'tenant_id' => $data['tenant_id'],
                    'profile_id' => $profile->id,
                    'router_id' => ($data['router_id'] ?? null) ?: null,
                    'radius_server_id' => ($data['radius_server_id'] ?? null) ?: null,
                    'outlet_id' => ($data['outlet_id'] ?? null) ?: null,
                    'mitra_id' => $partner?->id,
                    'admin_user_id' => $data['admin_user_id'] ?? null,
                    'balance_deducted' => $deductBalance,
                    'username' => $username,
                    'password' => $password,
                    'batch_code' => $batchCode,
                    'partner_name' => $partner ? $partner->name : ($data['partner_name'] ?? null),
                    'outlet_name' => ($data['outlet_name'] ?? null) ?: null,
                    'hpp' => (float) ($data['hpp'] ?? 0),
                    'commission' => (float) ($data['commission'] ?? ($profile->attributes['Commission'] ?? 0)),
                    'price' => (float) ($data['price'] ?? ($profile->attributes['Price'] ?? 0)),
                    'mac_address' => !empty($data['mac_address']) ? $data['mac_address'] : null,
                    'status' => 'stock',
                ]);

                $this->syncVoucher($voucher);
                $vouchers[] = $voucher->fresh(['profile', 'router', 'radiusServer', 'outlet']);
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

            // If the voucher is inactive, do not recreate checking/reply rows
            if ($voucher->status === 'inactive') {
                return;
            }

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

            if (! blank($voucher->mac_address)) {
                DB::table('radcheck')->insert([
                    'username' => $username,
                    'attribute' => 'Calling-Station-Id',
                    'op' => '==',
                    'value' => $voucher->mac_address,
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
            'sync_message' => $voucher->status === 'inactive' ? 'Deactivated in FreeRadius SQL.' : 'Synced to FreeRadius SQL.',
        ]);
    }

    /** @return array<int, array{name: string, hotspot_name: string, dns_name: string, support_phone: string, status: string, html_body: string}> */
    public function defaultPrintTemplates(): array
    {
        return [
            [
                'name' => 'DEFAULT QR COMPACT',
                'hotspot_name' => 'NEX Hotspot',
                'dns_name' => 'loginwifi.nex.net.id',
                'support_phone' => '082112121212',
                'status' => 'active',
                'html_body' => $this->compactVoucherTemplate(),
            ],
            [
                'name' => 'DEFAULT QR STRIP',
                'hotspot_name' => 'NEX Hotspot',
                'dns_name' => 'loginwifi.nex.net.id',
                'support_phone' => '082112121212',
                'status' => 'active',
                'html_body' => $this->stripVoucherTemplate(),
            ],
        ];
    }

    public function defaultTemplate(string $hotspotName = 'NEX ISP Hotspot', string $phone = '082170000000'): string
    {
        return $this->compactVoucherTemplate();
    }

    public function hotspotLoginUrl(HotspotTemplate $template, HotspotVoucher $voucher): string
    {
        $host = trim((string) $template->dns_name) ?: 'loginwifi.nex.net.id';
        $host = preg_replace('#^https?://#i', '', $host) ?: 'loginwifi.nex.net.id';

        return 'http://'.$host.'/login?username='.rawurlencode($voucher->username).'&password='.rawurlencode($voucher->password);
    }

    private function compactVoucherTemplate(): string
    {
        return <<<'HTML'
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>PRINT VOUCHER</title>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/qrious/4.0.2/qrious.min.js"></script>
  <style>
    *{box-sizing:border-box}body{margin:0;background:#fff;color:#000;font-family:Arial,Helvetica,sans-serif;font-size:9px}.voucher{display:inline-block;width:180px;min-height:116px;margin:2px;border:1px solid #color#;padding:3px;overflow:hidden;break-inside:avoid}.top{display:flex;align-items:flex-start;justify-content:space-between;gap:4px}.logo{height:18px;max-width:58px}.price{font-size:15px;font-weight:800;color:#color#}.body{display:grid;grid-template-columns:1fr 64px;gap:3px;margin-top:1px}.code{font-size:16px;font-weight:800;color:#color#;line-height:1.05}.label{font-size:9px;font-weight:700}.muted{font-size:8px}.qr{width:62px;height:62px}.foot{margin-top:2px;background:#color#;color:#fff;font-size:9px;font-weight:800;padding:2px 3px}
  </style>
</head>
<body>
  <table class="voucher"><tr><td>
    <div class="top"><img class="logo" src="#logo#" alt="NEX"><div class="price">#harga#</div></div>
    <div class="body">
      <div>
        <div class="label">U : <span class="code">#username#</span></div>
        <div class="label">P : <span class="code">#password#</span></div>
        <div class="muted"><b>#profile#</b><br>Partner : #partner#<br>Tgl cetak #printdate#<br>No : #nomor#</div>
      </div>
      <canvas class="qr" id="qr-#nomor#"></canvas>
    </div>
    <div class="foot">#csphone#</div>
  </td></tr></table>
  <script>new QRious({element:document.getElementById('qr-#nomor#'),value:'#loginurl#',size:124,level:'M'});</script>
</body>
</html>
HTML;
    }

    private function stripVoucherTemplate(): string
    {
        return <<<'HTML'
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>PRINT VOUCHER</title>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/qrious/4.0.2/qrious.min.js"></script>
  <style>
    *{box-sizing:border-box}body{margin:0;background:#fff;color:#0f172a;font-family:Arial,Helvetica,sans-serif}.ticket{display:inline-grid;grid-template-columns:88px 1fr 58px;width:245px;min-height:92px;margin:3px;border:1px solid #color#;break-inside:avoid}.brand{background:#color#;color:#fff;padding:6px;font-size:10px;font-weight:800}.brand b{display:block;font-size:14px}.mid{padding:6px;font-size:9px}.user{font-size:16px;font-weight:900;color:#color#;line-height:1}.row{margin-top:2px}.qrwrap{display:grid;place-items:center;border-left:1px dashed #color#}.qr{width:54px;height:54px}.phone{margin-top:4px;background:#0f172a;color:white;padding:2px 4px;font-size:8px;font-weight:700}
  </style>
</head>
<body>
  <section class="ticket">
    <div class="brand"><b>#hsname#</b>#harga#<br>#profile#</div>
    <div class="mid">
      <div class="user">#username#</div>
      <div class="row"><b>Pass:</b> #password#</div>
      <div class="row"><b>Durasi:</b> #durasi# | <b>Kuota:</b> #kuota#</div>
      <div class="row"><b>Partner:</b> #partner#</div>
      <div class="phone">CS #csphone#</div>
    </div>
    <div class="qrwrap"><canvas class="qr" id="qr-#nomor#"></canvas></div>
  </section>
  <script>new QRious({element:document.getElementById('qr-#nomor#'),value:'#loginurl#',size:110,level:'M'});</script>
</body>
</html>
HTML;
    }

    public function renderTemplate(HotspotTemplate $template, ?HotspotVoucher $voucher = null, int $number = 1): string
    {
        $html = str_replace(
            ['{{hotspot_name}}', '{{support_phone}}', '{{dns_name}}'],
            [$template->hotspot_name, $template->support_phone ?: '-', $template->dns_name ?: ''],
            $template->html_body
        );

        if (! $voucher) {
            return $this->replaceVoucherPlaceholders($html, $template, null, $number);
        }

        return $this->replaceVoucherPlaceholders($html, $template, $voucher, $number);
    }

    private function replaceVoucherPlaceholders(string $html, HotspotTemplate $template, ?HotspotVoucher $voucher, int $number): string
    {
        $profile = $voucher?->profile;
        $partnerName = $voucher ? ($voucher->partner_name ?: ($voucher->mitra?->name ?? 'SYSTEM')) : 'SYSTEM';
        $outletName = $voucher ? ($voucher->outlet_name ?: ($voucher->outlet?->name ?? '-')) : '-';
        $price = $voucher ? number_format((float) $voucher->price, 0, ',', '.') : '0';
        $color = $profile?->attributes['Color'] ?? $profile?->attributes['color'] ?? '#0891b2';
        $loginUrl = $voucher ? $this->hotspotLoginUrl($template, $voucher) : 'http://'.($template->dns_name ?: 'loginwifi.nex.net.id').'/login?username=demo&password=demo';
        $logo = $template->logo_path ? asset('storage/'.$template->logo_path) : 'https://dummyimage.com/120x36/ffffff/0891b2&text=NEX';

        return str_replace([
            '#username#', '#password#', '#profile#', '#harga#', '#aktif#', '#durasi#', '#kuota#',
            '#color#', '#dns#', '#hsname#', '#printdate#', '#printtime#', '#mitra#', '#partner#',
            '#outlet#', '#nomor#', '#logo#', '#kode#', '#mitraphone#', '#partnerphone#',
            '#csphone#', '#loginurl#', '#kodevoucher#', '#usernamepassword#',
        ], [
            $voucher?->username ?? 'NEXDEMO',
            $voucher?->password ?? '123456',
            $profile?->name ?? 'Voucher Hotspot',
            $price,
            $profile?->attributes['Active-Days'] ?? '-',
            $profile?->attributes['Session-Timeout'] ?? 'UNLIMITED',
            $profile?->attributes['Quota-MB'] ?? 'UNLIMITED',
            $color,
            $template->dns_name ?: '',
            $template->hotspot_name ?: 'NEX Hotspot',
            now('Asia/Jakarta')->format('d/m/Y'),
            now('Asia/Jakarta')->format('H:i:s'),
            $partnerName,
            $partnerName,
            $outletName,
            (string) $number,
            $logo,
            $voucher?->batch_code ?? '-',
            $voucher?->mitra?->phone ?? '',
            $voucher?->mitra?->phone ?? '',
            $template->support_phone ?: '-',
            $loginUrl,
            ($voucher?->batch_code ?? '-').' '.$number,
            ($voucher?->username ?? 'NEXDEMO').'/'.($voucher?->password ?? '123456'),
        ], $html);
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
