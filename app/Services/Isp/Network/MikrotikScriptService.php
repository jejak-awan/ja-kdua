<?php

declare(strict_types=1);

namespace App\Services\Isp\Network;

use App\Models\Core\Setting;
use App\Models\Isp\Network\ServiceNode;
use Illuminate\Support\Str;

class MikrotikScriptService
{
    /** @var array{version: 'v6'|'v7', tunnel_type: 'none'|'ovpn'|'sstp'|'l2tp', service: 'vpn_only'|'pppoe_only'|'hotspot_only'|'both', connection_type?: 'IP PUBLIC'|'VPN RADIUS', vpn_ip?: string} */
    protected array $options;

    protected ServiceNode $router;

    protected string $osVersion;

    protected string $connectionType;

    protected string $brandName = 'JANET';

    /**
     * Generate a configuration script for Mikrotik.
     *
     * @param array{
     *   version: 'v6'|'v7',
     *   tunnel_type: 'none'|'ovpn'|'sstp'|'l2tp',
     *   service: 'vpn_only'|'pppoe_only'|'hotspot_only'|'both',
     *   connection_type?: 'IP PUBLIC'|'VPN RADIUS',
     *   vpn_ip?: string
     * } $options
     */
    public function generate(ServiceNode $router, array $options): string
    {
        $this->router = $router;
        $this->options = $options;
        $this->osVersion = $options['version'];
        $this->connectionType = $options['connection_type'] ?? 'IP PUBLIC';
        $this->brandName = $this->getSettingString('isp_brand_name', 'JANET');

        $sections = [
            $this->renderHeader(),
        ];

        // Conditional SNMP: Skip if connection method is API
        if (strtolower($this->router->connection_method ?? '') !== 'api') {
            $sections[] = $this->renderSnmp();
        }

        $sections[] = $this->renderIdentity();
        $sections[] = $this->renderLogging();
        $sections[] = $this->renderClock();
        $sections[] = $this->renderRadiusIncoming();
        $sections[] = $this->renderDns();
        $sections[] = $this->renderCleanup();
        $sections[] = $this->renderNtp();
        $sections[] = $this->renderRadiusConfig();
        $sections[] = $this->renderIpPools();
        $sections[] = $this->renderPppProfiles();
        $sections[] = $this->renderPppAaa();
        $sections[] = $this->renderFirewallIsolir();
        $sections[] = $this->renderWebProxy();

        // Conditional VPN: Skip if connection type is IP PUBLIC
        // Only render VPN if it's NOT IP Public AND tunnel type is selected
        if ($this->connectionType !== 'IP PUBLIC' && $options['tunnel_type'] !== 'none') {
            $sections[] = $this->renderVpnTunnel();
            $sections[] = $this->renderFailoverScheduler();
        }

        return implode("\n\n", array_filter($sections));
    }

    protected function renderHeader(): string
    {
        $now = now()->toDateTimeString();

        return <<<EOD
#-----------------------------------------------------------
# {$this->brandName} MIKROTIK PROFESSIONAL CONFIGURATION
# Router: {$this->router->name}
# Mode: {$this->connectionType}
# Generated: {$now}
#-----------------------------------------------------------

#COPY PASTE SEMUA SCRIPT {$this->brandName} DIBAWAH INI KE NEW TERMINAL MIKROTIK
#JIKA DIBUKA MENGGUNAKAN WINDOWS 11, AGAR TIDAK ERROR BUKA FILE INI DENGAN NOTEPAD++
#Link download notepad++ = https://notepad-plus-plus.org/downloads
#######################################################################
EOD;
    }

    protected function renderSnmp(): string
    {
        $radiusIp = $this->getSettingString('radius_server_ip', '172.31.192.1');

        return <<<EOD
/snmp community
set [ find default=yes ] disabled=yes write-access=no
rem [find name!=public]
add addresses={$radiusIp} name={$this->brandName} write-access=yes
/snmp
set enabled=yes trap-community={$this->brandName} trap-version=2
EOD;
    }

    protected function renderIdentity(): string
    {
        $name = addslashes($this->router->name ?: 'MikroTik');

        return "/system identity\nset name=\"{$name}\"";
    }

    protected function renderLogging(): string
    {
        return '/sy log dis [find where topics~"debug"]';
    }

    protected function renderClock(): string
    {
        $timezone = $this->getSettingString('system_timezone', 'Asia/Jakarta');

        return "/system clock\nset time-zone-autodetect=no time-zone-name={$timezone}";
    }

    protected function renderRadiusIncoming(): string
    {
        return "/radius incoming\nset accept=yes port=3799";
    }

    protected function renderDns(): string
    {
        return "/ip dns \nset allow-remote-requests=yes";
    }

    protected function renderCleanup(): string
    {
        $brand = strtoupper($this->brandName);

        return <<<EOD
/system scheduler
rem [find name~"{$brand}"]
/interface sstp-client
rem [find name~"{$brand}"]
/interface ovpn-client
rem [find name~"{$brand}"]
/interface l2tp-client
rem [find name~"{$brand}"]
/ppp profile
rem [find name={$brand}VPN]
EOD;
    }

    protected function getSettingString(string $key, string $default): string
    {
        $value = Setting::get($key, $default);

        return is_string($value) ? $value : $default;
    }

    protected function renderNtp(): string
    {
        $ntpServers = $this->getSettingString('ntp_servers', '162.159.200.1,162.159.200.123');
        if ($this->osVersion === 'v7') {
            return <<<EOD
/system ntp client server rem [find]
/system ntp client set enabled=yes servers={$ntpServers}
EOD;
        }

        return '/system ntp client set enabled=yes primary-ntp=162.159.200.1 secondary-ntp=162.159.200.123';
    }

    protected function renderRadiusConfig(): string
    {
        $radiusIp = $this->getSettingString('radius_server_ip', '172.31.192.1');
        $secret = (string) ($this->router->radius_secret ?: ($this->router->secret ?: 'testing123'));

        $srcAddress = '';
        if ($this->connectionType === 'VPN RADIUS') {
            $srcAddress = (string) ($this->options['vpn_ip'] ?? '');
        } else {
            $srcAddress = (string) ($this->router->ip_address ?? '');
        }

        $srcAttr = $srcAddress ? " src-address={$srcAddress}" : '';
        $services = 'ppp,login';

        return <<<EOD
/radius
rem [find]
add address={$radiusIp} comment={$this->brandName} authentication-port=1812 accounting-port=1813 secret="{$secret}" service={$services}{$srcAttr} timeout=3s
/radius
set require-message-auth=no [find address={$radiusIp}]
EOD;
    }

    protected function renderIpPools(): string
    {
        $poolName = "{$this->brandName}POOL";
        $isolirName = "{$this->brandName}ISOLIR";

        $poolRange = $this->getSettingString('isp_pool_range', '10.200.192.100-10.200.207.254');
        $isolirRange = $this->getSettingString('isp_isolir_range', '10.200.208.10-10.200.209.254');

        return <<<EOD
/ip pool
add comment="Network : Default Pool" name={$poolName} ranges={$poolRange}
add comment="Network : Isolir Pool" name={$isolirName} ranges={$isolirRange}
EOD;
    }

    protected function renderPppProfiles(): string
    {
        $poolName = "{$this->brandName}POOL";
        $isolirName = "{$this->brandName}ISOLIR";
        $localAddress = $this->getSettingString('isp_local_gateway', '10.200.192.1');
        $isolirGateway = $this->getSettingString('isp_isolir_gateway', '10.200.208.1');

        return <<<EOD
/ppp profile
add insert-queue-before=first local-address={$localAddress} name={$this->brandName} only-one=yes remote-address={$poolName}
add insert-queue-before=first local-address={$isolirGateway} name={$isolirName} comment="default by {$this->brandName} (jangan dirubah)" only-one=yes remote-address={$isolirName}
EOD;
    }

    protected function renderPppAaa(): string
    {
        return <<<'EOD'
/ppp aaa
set use-radius=yes accounting=yes
set interim-update=0s
EOD;
    }

    protected function renderFirewallIsolir(): string
    {
        $isolirName = "{$this->brandName}ISOLIR";
        $webServerIp = $this->getSettingString('isolir_web_ip', '103.253.27.164');

        return <<<EOD
#NAT REDIRECT ISOLIR TO WEBPROXY
/ip firewall nat
rem [find src-address-list~"{$this->brandName}"]
add action=redirect chain=dstnat comment="{$isolirName}" dst-address=!{$webServerIp} dst-port=80,443,8080 protocol=tcp src-address-list={$isolirName} to-ports=3125
/ip firewall filter
rem [find src-address-list~"{$this->brandName}"]
add action=reject chain=forward comment={$isolirName} dst-address=!{$webServerIp} protocol=tcp reject-with=icmp-network-unreachable src-address-list={$isolirName}
add action=reject chain=forward comment={$isolirName} dst-address=!{$webServerIp} dst-port=!53,5353 protocol=udp reject-with=icmp-network-unreachable src-address-list={$isolirName}
EOD;
    }

    protected function renderWebProxy(): string
    {
        $webServerIp = $this->getSettingString('isolir_web_ip', '103.253.27.164');
        $isolirNetwork = $this->getSettingString('isp_isolir_network', '10.200.208.0/23');

        return <<<EOD
#WEBPROXY REDIRECT TO URL ISOLIR
/ip proxy
set cache-administrator=webmaster@{$this->brandName}.com enabled=yes max-cache-object-size=1KiB max-cache-size=none max-client-connections=50 max-fresh-time=5m max-server-connections=50 port=3125
/ip proxy access
rem [find]
add action=redirect action-data=http://{$webServerIp} src-address={$isolirNetwork}
EOD;
    }

    protected function renderVpnTunnel(): string
    {
        $tunnelType = (string) ($this->options['tunnel_type'] ?? 'none');
        if ($tunnelType === 'none') {
            return '';
        }

        $vpnUser = $this->getSettingString('vpn_user_prefix', 'knet').(string) $this->router->id;
        $vpnPass = (string) ($this->router->secret ?: 'JA-'.Str::random(8));
        $vpnHost = $this->getSettingString('vpn_server_host', 'server2.JAcloud.biz');

        $script = "/ppp profile\nadd change-tcp-mss=yes comment=\"default by {$this->brandName} (jangan dirubah)\" name={$this->brandName}VPN only-one=yes use-encryption=yes\n";

        switch ($tunnelType) {
            case 'ovpn':
                $script .= "/interface ovpn-client\nadd connect-to={$vpnHost} name={$this->brandName}CLOUD profile={$this->brandName}VPN user={$vpnUser} password={$vpnPass} disabled=no";
                break;
            case 'sstp':
                $script .= "/interface sstp-client\nadd connect-to={$vpnHost} name={$this->brandName}CLOUD profile={$this->brandName}VPN user={$vpnUser} password={$vpnPass} disabled=no";
                break;
            case 'l2tp':
                $script .= "/interface l2tp-client\nadd connect-to={$vpnHost} name={$this->brandName}CLOUD profile={$this->brandName}VPN user={$vpnUser} password={$vpnPass} disabled=no";
                break;
        }

        return $script;
    }

    protected function renderFailoverScheduler(): string
    {
        $radiusIp = $this->getSettingString('radius_server_ip', '172.31.192.1');
        $brand = $this->brandName;
        $vpnIntName = "{$brand}CLOUD";

        return <<<EOD
/system scheduler
rem [find name={$brand}failover]
add interval=7s name={$brand}failover on-event="{\r\
    \n:global intname {$vpnIntName}\r\
    \n:global indexvpn ([:pick [/system clock get time] 6 8] % 20+1)\r\
    \n:global jenisvpn [/interface get \$intname type];\r\
    \n:global address \"server\$indexvpn.JAcloud.biz\"\r\
    \n:local pingresult ([/ping {$radiusIp} interface=\$intname count=5])\r\
    \n:if (\$pingresult = 0) do={\r\
    \n     /int set \$intname disabled=no;\r\
    \n    :if ( \$jenisvpn = \"sstp-out\" ) do={ /int sstp-client set connect-\
    to=\$address comment=\"ServerVPN\$indexvpn\" [find name=\$intname] }\r\
    \n    :if ( \$jenisvpn = \"ovpn-out\" ) do={ /int ovpn-client set connect-\
    to=\$address comment=\"ServerVPN\$indexvpn\" [find name=\$intname] }\r\
    \n    :if ( \$jenisvpn = \"l2tp-out\" ) do={ /int l2tp-client set connect-\
    to=\$address comment=\"ServerVPN\$indexvpn\" [find name=\$intname] }\r\
    \n}\r\
    \n}"
EOD;
    }
}
