<?php

namespace App\Services;

use App\Models\NasDevice;
use App\Models\RadiusServer;
use App\Models\Router;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class RouterProvisioningService
{
    public function __construct(private readonly FreeRadiusService $freeRadius) {}

    /**
     * @param array<string, mixed> $routerData
     * @return array<string, string>
     */
    public static function defaultSnmpProfile(?Router $router = null, array $routerData = []): array
    {
        $publicIp = (string) ($routerData['public_ip'] ?? $router?->public_ip ?? '');
        $managementIp = (string) ($routerData['management_ip'] ?? $router?->management_ip ?? '');
        $connectionType = (string) ($routerData['connection_type'] ?? $router?->connection_type ?? 'ip_public');
        $routerIp = $connectionType === 'vpn_radius'
            ? $managementIp
            : ($publicIp !== '' ? $publicIp : $managementIp);

        return [
            'snmp_community' => 'NEXRADIUS',
            'snmp_allowed_address' => '0.0.0.0/0',
            'time_zone' => 'Asia/Jakarta',
            'dns_servers' => '8.8.8.8,1.1.1.1',
            'ntp_servers' => '162.159.200.1,162.159.200.123',
            'radius_src_address' => $routerIp,
            'radius_incoming_port' => '3799',
            'radius_timeout' => '3s',
            'pool_name' => 'NEXPOOL',
            'pool_comment' => 'Network : 10.200.192.0/20',
            'pool_ranges' => '10.200.192.100-10.200.207.254',
            'isolir_pool_name' => 'NEXISOLIR',
            'isolir_pool_comment' => 'Network 10.200.208.0/23',
            'isolir_pool_ranges' => '10.200.208.10-10.200.209.254',
            'ppp_profile_name' => 'NEXRADIUS',
            'pppoe_service_name' => 'NEX-PPPOE',
            'pppoe_server_interface' => '',
            'ppp_local_address' => '10.200.192.1',
            'isolir_profile_name' => 'NEXISOLIR',
            'isolir_local_address' => '10.200.208.1',
            'isolir_redirect_host' => '103.253.27.164',
            'isolir_redirect_port' => '3125',
            'hotspot_server_name' => 'NEX-HOTSPOT',
            'hotspot_interface' => '',
            'hotspot_profile_name' => 'NEXHOTSPOT',
        ];
    }

    /**
     * @param array<string, mixed>|null $profile
     * @param array<string, mixed> $routerData
     * @return array<string, string>
     */
    public static function mergeDefaultSnmpProfile(?array $profile, ?Router $router = null, array $routerData = []): array
    {
        $profile ??= [];

        foreach (self::defaultSnmpProfile($router, $routerData) as $key => $value) {
            if (! array_key_exists($key, $profile) || blank($profile[$key])) {
                $profile[$key] = $value;
            }
        }

        return array_map(fn ($value): string => (string) $value, $profile);
    }

    public function radiusServerForRouter(Router $router): ?RadiusServer
    {
        $router->loadMissing('primaryNasDevice.radiusServer');

        return $router->primaryNasDevice?->radiusServer
            ?: RadiusServer::query()
                ->where('tenant_id', $router->tenant_id)
                ->where('status', 'active')
                ->orderBy('name')
                ->first();
    }

    public function ensureNasDevice(Router $router, RadiusServer $server, ?string $secret = null): NasDevice
    {
        $secret = trim((string) ($secret ?: $router->radius_secret ?: $server->shared_secret));
        $nasIpAddress = self::radiusNasAddress($router);

        $device = NasDevice::query()->updateOrCreate(
            [
                'tenant_id' => $router->tenant_id,
                'router_id' => $router->id,
            ],
            [
                'radius_server_id' => $server->id,
                'hostname' => $router->hostname,
                'nas_ip_address' => $nasIpAddress,
                'vendor_type' => $router->vendor ?: 'MikroTik',
                'secret' => $secret,
                'status' => 'active',
            ]
        );

        $router->update([
            'radius_secret' => $secret,
            'status' => $router->status === 'draft' ? 'active' : $router->status,
        ]);

        $this->freeRadius->syncNas($device);

        return $device->fresh(['router', 'radiusServer']);
    }

    /**
     * @return array<string, mixed>
     */
    public function testSnmp(Router $router): array
    {
        $config = self::mergeDefaultSnmpProfile($router->snmp_profile ?? [], $router);
        $community = $this->config($config, 'snmp_community', 'NEXRADIUS');
        $host = $router->management_ip ?: $router->public_ip;

        if (blank($host) || blank($community)) {
            $router->update(['snmp_status' => 'not_configured']);

            return [
                'status' => 'not_configured',
                'message' => 'IP router atau SNMP community belum lengkap.',
            ];
        }

        if (! function_exists('snmp2_get')) {
            $router->update(['snmp_status' => 'not_configured']);

            return [
                'status' => 'not_configured',
                'message' => 'PHP SNMP extension belum aktif di container API. Rebuild image setelah Dockerfile terbaru dipakai.',
            ];
        }

        @snmp_set_quick_print(true);

        $sysName = @snmp2_get($host, $community, '1.3.6.1.2.1.1.5.0', 1500000, 1);

        if ($sysName === false) {
            $router->update(['snmp_status' => 'unreachable']);

            return [
                'status' => 'unreachable',
                'message' => 'Router belum menjawab SNMP v2c dari API server.',
            ];
        }

        if ($router->snmp_status !== 'reachable') {
            $router->update(['snmp_status' => 'reachable']);
        }

        return [
            'status' => 'reachable',
            'message' => 'SNMP reachable: '.trim((string) $sysName, '"'),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function liveSnmpSnapshot(Router $router): array
    {
        $config = self::mergeDefaultSnmpProfile($router->snmp_profile ?? [], $router);
        $community = $this->config($config, 'snmp_community', 'NEXRADIUS');
        $host = $router->management_ip ?: $router->public_ip;

        $fallback = [
            'status' => 'unreachable',
            'identity' => $router->hostname ?: $router->router_name,
            'model' => $router->model ?: '-',
            'uptime' => '-',
            'version' => '-',
            'license' => '-',
            'temperature' => '0C / 0V',
            'cpu_load' => 0,
            'cpu_detail' => '-',
            'memory_used_percent' => 0,
            'memory_detail' => '-',
            'disk_used_percent' => 0,
            'disk_detail' => '-',
            'pppoe_online' => $router->pppoeOnlineCount(),
            'hotspot_online' => $router->hotspotOnlineCount(),
            'interfaces' => $this->routerInterfaces($router),
            'pppoe_sessions' => $this->activeRadiusSessions($router, Router::PPP_CONNECTION_TYPES),
            'hotspot_sessions' => $this->activeRadiusSessions($router, Router::HOTSPOT_CONNECTION_TYPES),
            'updated_at' => now('Asia/Jakarta')->format('H:i:s'),
        ];

        if (blank($host) || blank($community) || ! function_exists('snmp2_get')) {
            return $fallback;
        }

        @snmp_set_quick_print(true);

        $identity = $this->snmpString($host, $community, '1.3.6.1.2.1.1.5.0') ?: $fallback['identity'];
        $description = $this->snmpString($host, $community, '1.3.6.1.2.1.1.1.0') ?: '';
        $uptime = $this->snmpUptime($host, $community);
        $cpuLoad = $this->snmpCpuLoad($host, $community);
        $storage = $this->snmpStorage($host, $community);
        $interfaces = $this->snmpInterfaces($host, $community) ?: $fallback['interfaces'];

        if ($router->snmp_status !== 'reachable') {
            $router->update(['snmp_status' => 'reachable']);
        }

        return [
            ...$fallback,
            'status' => 'reachable',
            'identity' => $identity,
            'model' => $router->model ?: $this->modelFromDescription($description),
            'uptime' => $uptime,
            'version' => $this->versionFromDescription($description),
            'license' => $this->snmpString($host, $community, '1.3.6.1.4.1.14988.1.1.4.3.0') ?: '-',
            'temperature' => $this->temperatureVoltage($host, $community),
            'cpu_load' => $cpuLoad,
            'cpu_detail' => $cpuLoad > 0 ? $cpuLoad.'% average' : '-',
            'memory_used_percent' => $storage['memory_percent'],
            'memory_detail' => $storage['memory_detail'],
            'disk_used_percent' => $storage['disk_percent'],
            'disk_detail' => $storage['disk_detail'],
            'interfaces' => $interfaces,
            'pppoe_sessions' => $this->activeRadiusSessions($router, Router::PPP_CONNECTION_TYPES),
            'hotspot_sessions' => $this->activeRadiusSessions($router, Router::HOTSPOT_CONNECTION_TYPES),
            'updated_at' => now('Asia/Jakarta')->format('H:i:s'),
        ];
    }

    public function buildRouterScript(Router $router, ?RadiusServer $server = null): string
    {
        $config = self::mergeDefaultSnmpProfile($router->snmp_profile ?? [], $router);
        $server ??= $this->radiusServerForRouter($router);

        $snmpCommunity = $this->config($config, 'snmp_community', 'NEXRADIUS');
        $snmpAllowedAddress = $this->config($config, 'snmp_allowed_address', '0.0.0.0/0');
        $timeZone = $this->config($config, 'time_zone', 'Asia/Jakarta');
        $dnsServers = $this->config($config, 'dns_servers', '8.8.8.8,1.1.1.1');
        $ntpServers = $this->config($config, 'ntp_servers', '162.159.200.1,162.159.200.123');
        $radiusSrcAddress = $this->config($config, 'radius_src_address', self::radiusNasAddress($router));
        $incomingPort = $this->config($config, 'radius_incoming_port', '3799');
        $radiusTimeout = $this->config($config, 'radius_timeout', '3s');
        $poolName = $this->config($config, 'pool_name', 'NEXPOOL');
        $poolComment = $this->config($config, 'pool_comment', 'Network : 10.200.192.0/20');
        $poolRanges = $this->config($config, 'pool_ranges', '10.200.192.100-10.200.207.254');
        $isolirPoolName = $this->config($config, 'isolir_pool_name', 'NEXISOLIR');
        $isolirPoolComment = $this->config($config, 'isolir_pool_comment', 'Network 10.200.208.0/23');
        $isolirPoolRanges = $this->config($config, 'isolir_pool_ranges', '10.200.208.10-10.200.209.254');
        $profileName = $this->config($config, 'ppp_profile_name', 'NEXRADIUS');
        $localAddress = $this->config($config, 'ppp_local_address', '10.200.192.1');
        $isolirProfileName = $this->config($config, 'isolir_profile_name', 'NEXISOLIR');
        $isolirLocalAddress = $this->config($config, 'isolir_local_address', '10.200.208.1');
        $redirectHost = $this->config($config, 'isolir_redirect_host', '103.253.27.164');
        $redirectPort = $this->config($config, 'isolir_redirect_port', '3125');
        $pppoeServiceName = $this->config($config, 'pppoe_service_name', 'NEX-PPPOE');
        $pppoeInterface = $this->config($config, 'pppoe_server_interface', '');
        $hotspotServerName = $this->config($config, 'hotspot_server_name', 'NEX-HOTSPOT');
        $hotspotInterface = $this->config($config, 'hotspot_interface', '');
        $hotspotProfile = $this->config($config, 'hotspot_profile_name', 'NEXHOTSPOT');
        $radiusSecret = $router->radius_secret ?: $server?->shared_secret;

        $srcAddressOption = blank($radiusSrcAddress) ? '' : ' src-address='.$radiusSrcAddress;
        $radiusLine = $server && $radiusSecret
            ? '/radius add address='.$server->host.' comment="NEXBIL FreeRadius" authentication-port='.$server->auth_port.' accounting-port='.$server->acct_port.' secret="'.$radiusSecret.'" service=ppp,login,hotspot'.$srcAddressOption.' timeout='.$radiusTimeout
            : '# Radius server belum terhubung. Jalankan action Hubungkan Radius di aplikasi.';

        $pppoeServerLines = blank($pppoeInterface) ? [
            '# PPPoE server belum dibuat otomatis karena interface PPPoE belum diisi.',
            '# Isi Script MikroTik > PPPoE Server Interface, lalu download ulang script.',
        ] : [
            '/interface pppoe-server server remove [find service-name="'.$pppoeServiceName.'"]',
            '/interface pppoe-server server add authentication=pap,chap,mschap1,mschap2 default-profile='.$profileName.' disabled=no interface='.$pppoeInterface.' keepalive-timeout=10 max-mru=1480 max-mtu=1480 one-session-per-host=yes service-name="'.$pppoeServiceName.'"',
        ];

        $hotspotServerLines = blank($hotspotInterface) ? [
            '# Hotspot/WiFi server belum dibuat otomatis karena interface hotspot belum diisi.',
            '# Isi Script MikroTik > Hotspot Interface, lalu download ulang script.',
        ] : [
            '/ip hotspot profile remove [find name='.$hotspotProfile.']',
            '/ip hotspot profile add dns-name="" hotspot-address='.$localAddress.' html-directory=hotspot login-by=http-chap,http-pap name='.$hotspotProfile.' radius-accounting=yes radius-interim-update=5m use-radius=yes',
            '/ip hotspot remove [find name='.$hotspotServerName.']',
            '/ip hotspot add address-pool='.$poolName.' disabled=no interface='.$hotspotInterface.' name='.$hotspotServerName.' profile='.$hotspotProfile,
        ];

        return implode("\n", array_merge([
            '# NEXBIL Router Script',
            '# Router: '.$router->router_name.' / '.$router->hostname,
            '# Jalankan di MikroTik terminal dengan user admin.',
            '/system identity set name="'.$router->hostname.'"',
            '',
            '/snmp community',
            'set [ find default=yes ] disabled=yes write-access=no',
            'remove [find name!='.$snmpCommunity.']',
            'remove [find name='.$snmpCommunity.']',
            'add addresses='.$snmpAllowedAddress.' name='.$snmpCommunity.' write-access=yes',
            '/snmp set enabled=yes trap-community='.$snmpCommunity.' trap-version=2',
            '',
            '/system clock set time-zone-autodetect=no time-zone-name='.$timeZone,
            '/radius incoming set accept=yes port='.$incomingPort,
            '/ip dns set allow-remote-requests=yes servers='.$dnsServers,
            '/system ntp client servers remove [find]',
            '/system ntp client set enabled=yes servers='.$ntpServers,
            '',
            '/radius',
            'remove [find]',
            $radiusLine,
            '/radius set [find comment="NEXBIL FreeRadius"] require-message-auth=no timeout='.$radiusTimeout,
            '',
            '/ip pool',
            'remove [find name='.$poolName.']',
            'remove [find name='.$isolirPoolName.']',
            'add comment="'.$poolComment.'" name='.$poolName.' ranges='.$poolRanges,
            'add comment="'.$isolirPoolComment.'" name='.$isolirPoolName.' ranges='.$isolirPoolRanges,
            '',
            '/ppp profile',
            'remove [find name='.$profileName.']',
            'remove [find name='.$isolirProfileName.']',
            'add insert-queue-before=first local-address='.$localAddress.' name='.$profileName.' only-one=yes remote-address='.$poolName,
            'add insert-queue-before=first local-address='.$isolirLocalAddress.' name='.$isolirProfileName.' comment="default by NEXBIL (jangan dirubah)" only-one=yes remote-address='.$isolirPoolName,
            '/ppp aaa set use-radius=yes accounting=yes interim-update=5m',
            '',
            '# PPPoE server',
        ], $pppoeServerLines, [
            '',
            '# Hotspot/WiFi radius server',
        ], $hotspotServerLines, [
            '/ip hotspot user profile remove [find name='.$profileName.']',
            '/ip hotspot user profile add insert-queue-before=first keepalive-timeout=10m mac-cookie-timeout=1w name='.$profileName.' shared-users=unlimited transparent-proxy=yes open-status-page=always status-autorefresh=10m',
            '',
            '# NAT redirect isolir ke webproxy',
            '/ip firewall nat remove [find comment="NEXISOLIR"]',
            '/ip firewall nat add action=redirect chain=dstnat comment="NEXISOLIR" dst-address=!'.$redirectHost.' dst-port=80,443,8080 protocol=tcp src-address-list='.$isolirPoolName.' to-ports='.$redirectPort,
            '/ip firewall filter remove [find comment="NEXISOLIR"]',
            '/ip firewall filter add action=reject chain=forward comment=NEXISOLIR dst-address=!'.$redirectHost.' protocol=tcp reject-with=icmp-network-unreachable src-address-list='.$isolirPoolName,
            '/ip firewall filter add action=reject chain=forward comment=NEXISOLIR dst-address=!'.$redirectHost.' dst-port=!53,5353 protocol=udp reject-with=icmp-network-unreachable src-address-list='.$isolirPoolName,
            '',
            '/ip proxy set cache-administrator=webmaster@nexbil.local enabled=yes max-cache-object-size=1KiB max-cache-size=none max-client-connections=50 max-fresh-time=5m max-server-connections=50 port='.$redirectPort,
            '/ip proxy access remove [find]',
            'add action=redirect action-data=http://'.$redirectHost.' src-address='.$isolirPoolRanges,
        ]));
    }

    /**
     * @param array<string, mixed> $config
     */
    private function config(array $config, string $key, string $default): string
    {
        $value = $config[$key] ?? null;

        return blank($value) ? $default : (string) $value;
    }

    public static function radiusNasAddress(Router $router): string
    {
        $profile = self::mergeDefaultSnmpProfile($router->snmp_profile ?? [], $router);
        $radiusSrcAddress = trim((string) ($profile['radius_src_address'] ?? ''));

        if ($radiusSrcAddress !== '') {
            return $radiusSrcAddress;
        }

        if ($router->connection_type === 'vpn_radius') {
            return (string) $router->management_ip;
        }

        return (string) ($router->public_ip ?: $router->management_ip);
    }

    private function snmpString(string $host, string $community, string $oid): ?string
    {
        $value = @snmp2_get($host, $community, $oid, 900000, 1);

        if ($value === false) {
            return null;
        }

        return trim(preg_replace('/^(STRING|INTEGER|OID|Gauge32|Counter32|Counter64):\s*/', '', (string) $value) ?: '', '" ');
    }

    private function snmpUptime(string $host, string $community): string
    {
        $value = (string) (@snmp2_get($host, $community, '1.3.6.1.2.1.1.3.0', 900000, 1) ?: '');

        if (preg_match('/\((\d+)\)/', $value, $matches)) {
            $seconds = (int) floor(((int) $matches[1]) / 100);

            return $this->formatDuration($seconds);
        }

        return trim(str_replace('Timeticks:', '', $value)) ?: '-';
    }

    private function snmpCpuLoad(string $host, string $community): int
    {
        $values = @snmp2_walk($host, $community, '1.3.6.1.2.1.25.3.3.1.2', 900000, 1);

        if (! is_array($values) || $values === []) {
            return (int) ($this->snmpString($host, $community, '1.3.6.1.4.1.14988.1.1.3.14.0') ?: 0);
        }

        $loads = collect($values)
            ->map(fn (mixed $value): int => (int) preg_replace('/\D+/', '', (string) $value))
            ->filter(fn (int $value): bool => $value >= 0 && $value <= 100);

        return $loads->isEmpty() ? 0 : (int) round($loads->avg());
    }

    /**
     * @return array{memory_percent:int,memory_detail:string,disk_percent:int,disk_detail:string}
     */
    private function snmpStorage(string $host, string $community): array
    {
        $descriptions = @snmp2_real_walk($host, $community, '1.3.6.1.2.1.25.2.3.1.3', 900000, 1);
        $units = @snmp2_real_walk($host, $community, '1.3.6.1.2.1.25.2.3.1.4', 900000, 1);
        $sizes = @snmp2_real_walk($host, $community, '1.3.6.1.2.1.25.2.3.1.5', 900000, 1);
        $used = @snmp2_real_walk($host, $community, '1.3.6.1.2.1.25.2.3.1.6', 900000, 1);

        $memory = ['percent' => 0, 'detail' => '-'];
        $disk = ['percent' => 0, 'detail' => '-'];

        if (! is_array($descriptions) || ! is_array($units) || ! is_array($sizes) || ! is_array($used)) {
            return [
                'memory_percent' => 0,
                'memory_detail' => '-',
                'disk_percent' => 0,
                'disk_detail' => '-',
            ];
        }

        foreach ($descriptions as $oid => $description) {
            $index = substr((string) $oid, strrpos((string) $oid, '.') + 1);
            $label = strtolower($this->cleanSnmpValue((string) $description));
            $unit = $this->snmpIntByIndex($units, $index);
            $size = $this->snmpIntByIndex($sizes, $index);
            $usedValue = $this->snmpIntByIndex($used, $index);

            if ($unit <= 0 || $size <= 0) {
                continue;
            }

            $totalBytes = $size * $unit;
            $usedBytes = $usedValue * $unit;
            $percent = (int) min(100, round(($usedBytes / max(1, $totalBytes)) * 100));
            $detail = $this->formatBytes($usedBytes).' / '.$this->formatBytes($totalBytes);

            if ($memory['percent'] === 0 && str_contains($label, 'memory')) {
                $memory = ['percent' => $percent, 'detail' => $detail];
            }

            if ($disk['percent'] === 0 && (str_contains($label, 'disk') || str_contains($label, 'flash') || str_contains($label, 'storage'))) {
                $disk = ['percent' => $percent, 'detail' => $detail];
            }
        }

        return [
            'memory_percent' => $memory['percent'],
            'memory_detail' => $memory['detail'],
            'disk_percent' => $disk['percent'],
            'disk_detail' => $disk['detail'],
        ];
    }

    /**
     * @return array<int, array<string, string>>
     */
    private function snmpInterfaces(string $host, string $community): array
    {
        $names = @snmp2_real_walk($host, $community, '1.3.6.1.2.1.2.2.1.2', 900000, 1);
        $types = @snmp2_real_walk($host, $community, '1.3.6.1.2.1.2.2.1.3', 900000, 1);
        $mtus = @snmp2_real_walk($host, $community, '1.3.6.1.2.1.2.2.1.4', 900000, 1);
        $speeds = @snmp2_real_walk($host, $community, '1.3.6.1.2.1.2.2.1.5', 900000, 1);
        $adminStatuses = @snmp2_real_walk($host, $community, '1.3.6.1.2.1.2.2.1.7', 900000, 1);
        $operStatuses = @snmp2_real_walk($host, $community, '1.3.6.1.2.1.2.2.1.8', 900000, 1);

        if (! is_array($names) || $names === []) {
            return [];
        }

        return collect($names)
            ->map(function (mixed $name, mixed $oid) use ($types, $mtus, $speeds, $adminStatuses, $operStatuses): array {
                $oid = (string) $oid;
                $index = substr($oid, strrpos($oid, '.') + 1);
                $speed = $this->snmpIntByIndex(is_array($speeds) ? $speeds : [], $index);
                $adminStatus = $this->snmpStatusLabel($this->snmpIntByIndex(is_array($adminStatuses) ? $adminStatuses : [], $index));
                $operStatus = $this->snmpStatusLabel($this->snmpIntByIndex(is_array($operStatuses) ? $operStatuses : [], $index));

                return [
                    'name' => $this->cleanSnmpValue((string) $name),
                    'type' => $this->snmpInterfaceType($this->snmpIntByIndex(is_array($types) ? $types : [], $index)),
                    'ip_address' => '-',
                    'vlan' => '-',
                    'speed' => $speed > 0 ? $this->formatInterfaceSpeed($speed) : '-',
                    'mtu' => (string) ($this->snmpIntByIndex(is_array($mtus) ? $mtus : [], $index) ?: '-'),
                    'admin_status' => $adminStatus,
                    'status' => $operStatus,
                ];
            })
            ->values()
            ->all();
    }

    /**
     * @param array<string, mixed> $values
     */
    private function snmpIntByIndex(array $values, string $index): int
    {
        foreach ($values as $oid => $value) {
            if (str_ends_with((string) $oid, '.'.$index)) {
                return (int) preg_replace('/\D+/', '', (string) $value);
            }
        }

        return 0;
    }

    private function cleanSnmpValue(string $value): string
    {
        return trim(preg_replace('/^(STRING|INTEGER|OID|Gauge32|Counter32|Counter64):\s*/', '', $value) ?: '', '" ');
    }

    private function snmpStatusLabel(int $status): string
    {
        return match ($status) {
            1 => 'UP',
            2 => 'DOWN',
            3 => 'TESTING',
            default => '-',
        };
    }

    private function snmpInterfaceType(int $type): string
    {
        return match ($type) {
            6 => 'ethernet',
            23 => 'ppp',
            24 => 'softwareLoopback',
            53 => 'propVirtual',
            135 => 'l2vlan',
            161 => 'ieee8023adLag',
            default => $type > 0 ? 'type '.$type : '-',
        };
    }

    private function formatInterfaceSpeed(int $bitsPerSecond): string
    {
        if ($bitsPerSecond >= 1000000000) {
            return rtrim(rtrim(number_format($bitsPerSecond / 1000000000, 2), '0'), '.').' Gbps';
        }

        if ($bitsPerSecond >= 1000000) {
            return rtrim(rtrim(number_format($bitsPerSecond / 1000000, 2), '0'), '.').' Mbps';
        }

        if ($bitsPerSecond >= 1000) {
            return rtrim(rtrim(number_format($bitsPerSecond / 1000, 2), '0'), '.').' Kbps';
        }

        return $bitsPerSecond.' bps';
    }

    private function versionFromDescription(string $description): string
    {
        if (preg_match('/RouterOS\s+([^\s]+)/i', $description, $matches)) {
            return 'RouterOS '.$matches[1];
        }

        if (preg_match('/\b(\d+\.\d+(?:\.\d+)?)\b/', $description, $matches)) {
            return 'RouterOS '.$matches[1];
        }

        return '-';
    }

    private function modelFromDescription(string $description): string
    {
        if (preg_match('/\(([^)]+)\)/', $description, $matches)) {
            return $matches[1];
        }

        return '-';
    }

    private function temperatureVoltage(string $host, string $community): string
    {
        $temperature = (int) ($this->snmpString($host, $community, '1.3.6.1.4.1.14988.1.1.3.10.0') ?: 0);
        $voltage = (int) ($this->snmpString($host, $community, '1.3.6.1.4.1.14988.1.1.3.8.0') ?: 0);

        $voltageText = $voltage > 100 ? number_format($voltage / 10, 1).'V' : $voltage.'V';

        return $temperature.'C / '.$voltageText;
    }

    private function formatDuration(int $seconds): string
    {
        $days = intdiv($seconds, 86400);
        $seconds %= 86400;
        $hours = intdiv($seconds, 3600);
        $seconds %= 3600;
        $minutes = intdiv($seconds, 60);
        $seconds %= 60;

        return sprintf('%dd:%02d:%02d:%02d', $days, $hours, $minutes, $seconds);
    }

    private function formatBytes(float $bytes): string
    {
        $units = ['B', 'KiB', 'MiB', 'GiB', 'TiB'];
        $index = 0;

        while ($bytes >= 1024 && $index < count($units) - 1) {
            $bytes /= 1024;
            $index++;
        }

        return number_format($bytes, $index === 0 ? 0 : 2).' '.$units[$index];
    }

    /**
     * @return array<int, array<string, string>>
     */
    private function routerInterfaces(Router $router): array
    {
        return $router->interfaces()
            ->orderBy('interface_name')
            ->get()
            ->map(fn ($interface): array => [
                'name' => (string) $interface->interface_name,
                'type' => (string) ($interface->interface_type ?: '-'),
                'ip_address' => (string) ($interface->ip_address ?: '-'),
                'vlan' => $interface->vlan_id ? (string) $interface->vlan_id : '-',
                'speed' => $interface->speed_mbps ? $interface->speed_mbps.' Mbps' : '-',
                'status' => strtoupper((string) $interface->status),
            ])
            ->values()
            ->all();
    }

    /**
     * @param array<int, string> $connectionTypes
     * @return array<int, array<string, string>>
     */
    private function activeRadiusSessions(Router $router, array $connectionTypes): array
    {
        if (! Schema::hasTable('radacct')) {
            return [];
        }

        $users = $router->radiusUsers()
            ->with(['service.customer'])
            ->where('status', 'active')
            ->whereHas('service', fn ($query) => $query->whereIn(DB::raw('upper(connection_type)'), $connectionTypes))
            ->get()
            ->keyBy('username');

        if ($users->isEmpty()) {
            return [];
        }

        return DB::table('radacct')
            ->whereNull('acctstoptime')
            ->whereIn('username', $users->keys())
            ->orderByDesc('acctstarttime')
            ->get()
            ->map(function ($session) use ($users): array {
                $user = $users->get($session->username);
                $service = $user?->service;
                $start = $session->acctstarttime ? strtotime((string) $session->acctstarttime) : false;

                return [
                    'username' => (string) $session->username,
                    'customer' => (string) ($service?->customer?->name ?: '-'),
                    'cid' => (string) ($service?->cid ?: '-'),
                    'profile' => (string) ($service?->billing_profile_name ?: '-'),
                    'ip_address' => (string) ($session->framedipaddress ?: '-'),
                    'nas_port' => (string) ($session->nasportid ?: '-'),
                    'uptime' => $start ? $this->formatDuration(max(0, time() - $start)) : '-',
                ];
            })
            ->values()
            ->all();
    }
}
