<?php

namespace Database\Seeders\Isp;

use App\Models\Core\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            [
                'key' => 'radius_server_ip',
                'value' => '172.31.192.1',
                'type' => 'string',
                'group' => 'isp_radius',
                'description' => 'The IP Address of the FreeRADIUS server.',
            ],
            [
                'key' => 'vpn_server_host',
                'value' => 'server2.JAcloud.biz',
                'type' => 'string',
                'group' => 'isp_tunnel',
                'description' => 'The host of the VPN Management server.',
            ],
            [
                'key' => 'dns_servers',
                'value' => '8.8.8.8,1.1.1.1',
                'type' => 'string',
                'group' => 'isp_network',
                'description' => 'Default DNS servers for MikroTik configuration.',
            ],
            [
                'key' => 'ntp_servers',
                'value' => '162.159.200.1,162.159.200.123',
                'type' => 'string',
                'group' => 'isp_network',
                'description' => 'NTP servers for MikroTik time synchronization.',
            ],
            [
                'key' => 'isolir_web_ip',
                'value' => '103.253.27.164',
                'type' => 'string',
                'group' => 'isp_network',
                'description' => 'The IP Address of the Web Server for Isolated (Isolir) clients.',
            ],
            [
                'key' => 'isp_brand_name',
                'value' => 'JANET',
                'type' => 'string',
                'group' => 'isp_general',
                'description' => 'The default brand name used in MikroTik scripts and notifications.',
            ],
        ];

        foreach ($settings as $setting) {
            Setting::updateOrCreate(['key' => $setting['key']], $setting);
        }
    }
}
