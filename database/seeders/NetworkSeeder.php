<?php

namespace Database\Seeders;

use App\Models\Isp\CustomerDevice;
use App\Models\Isp\ServiceNode;
use App\Models\Isp\ServiceProfile;
use App\Models\Isp\Subnet;
use Illuminate\Database\Seeder;

class NetworkSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Create Service Profiles
        $profiles = [
            ['name' => 'Home Basic', 'download_limit' => 20, 'upload_limit' => 5],
            ['name' => 'Home Standard', 'download_limit' => 50, 'upload_limit' => 20],
            ['name' => 'Home Ultra', 'download_limit' => 100, 'upload_limit' => 50],
            ['name' => 'Business Pro', 'download_limit' => 200, 'upload_limit' => 100],
        ];

        foreach ($profiles as $p) {
            ServiceProfile::create($p);
        }

        // 2. Create Subnets and Generate IPs
        $nodes = ServiceNode::all();

        if ($nodes->isEmpty()) {
            return;
        }

        foreach ($nodes as $index => $node) {
            $octet = 10 + $index;
            $subnet = Subnet::create([
                'node_id' => $node->id,
                'name' => "LAN Pool - {$node->name}",
                'prefix' => "172.{$octet}.0.0/24",
                'gateway' => "172.{$octet}.0.1",
                'vlan_id' => 100 + $index,
                'type' => 'LAN',
            ]);

            $subnet->generateIps();

            // Assign first few IPs to customer devices on this node (if any)
            $devices = CustomerDevice::whereNull('status') // Or some other criteria
                ->take(5)
                ->get();

            foreach ($devices as $dIndex => $device) {
                $ip = $subnet->ipAddresses()->where('status', 'Available')->first();
                if ($ip) {
                    $ip->update([
                        'device_id' => $device->id,
                        'status' => 'Assigned',
                        'notes' => "Auto-assigned to {$device->serial_number}",
                    ]);
                }
            }
        }
    }
}
