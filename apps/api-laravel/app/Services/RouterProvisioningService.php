<?php

namespace App\Services;

use App\Models\NasDevice;
use App\Models\RadiusServer;
use App\Models\Router;

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
        $routerIp = $publicIp !== '' ? $publicIp : $managementIp;

        return [
            'snmp_community' => 'NEXRADIUS',
            'snmp_allowed_address' => '0.0.0.0/0',
            'time_zone' => 'Asia/Jakarta',
            'dns_servers' => '8.8.8.8,1.1.1.1',
            'ntp_servers' => '162.159.200.1,162.159.200.123',
            'radius_src_address' => $routerIp,
            'radius_incoming_port' => '3799',
            'radius_timeout' => '5s',
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

        $device = NasDevice::query()->updateOrCreate(
            [
                'tenant_id' => $router->tenant_id,
                'router_id' => $router->id,
            ],
            [
                'radius_server_id' => $server->id,
                'hostname' => $router->hostname,
                'nas_ip_address' => $router->public_ip ?: $router->management_ip,
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

        $router->update(['snmp_status' => 'reachable']);

        return [
            'status' => 'reachable',
            'message' => 'SNMP reachable: '.trim((string) $sysName, '"'),
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
        $radiusSrcAddress = $this->config($config, 'radius_src_address', $router->public_ip ?: $router->management_ip);
        $incomingPort = $this->config($config, 'radius_incoming_port', '3799');
        $radiusTimeout = $this->config($config, 'radius_timeout', '5s');
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

        $radiusLine = $server && $radiusSecret
            ? '/radius add address='.$server->host.' comment="NEXBIL FreeRadius" authentication-port='.$server->auth_port.' accounting-port='.$server->acct_port.' secret="'.$radiusSecret.'" service=ppp,login,hotspot src-address='.$radiusSrcAddress.' timeout='.$radiusTimeout
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
}
