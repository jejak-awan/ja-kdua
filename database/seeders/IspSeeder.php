<?php

namespace Database\Seeders;

use App\Models\Isp\BillingPlan;
use App\Models\Isp\CustomerDevice;
use App\Models\Isp\Invoice;
use App\Models\Isp\ServiceNode;
use App\Models\User;
use Illuminate\Database\Seeder;

class IspSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Create Core Infrastructure Nodes
        $this->seedNodes();

        // 2. Create Billing Plans
        $this->seedPlans();

        // 3. Create Customers and Devices
        $this->seedCustomersAndDevices();
    }

    private function seedNodes(): void
    {
        ServiceNode::create([
            'name' => 'Main POP - Central High',
            'type' => 'POP',
            'ip_address' => '10.0.0.1',
            'location_lat' => -7.8667,
            'location_lng' => 112.6500,
            'status' => 'active',
            'metadata' => ['vendor' => 'Cisco', 'capacity' => '10Gbps'],
        ]);

        ServiceNode::create([
            'name' => 'Edge Router - North Sector',
            'type' => 'Router',
            'ip_address' => '10.0.1.1',
            'location_lat' => -7.8500,
            'location_lng' => 112.6600,
            'status' => 'active',
            'metadata' => ['vendor' => 'Mikrotik', 'model' => 'CCR2116'],
        ]);

        ServiceNode::create([
            'name' => 'OLT - Residential Block A',
            'type' => 'OLT',
            'ip_address' => '10.0.2.1',
            'location_lat' => -7.8700,
            'location_lng' => 112.6400,
            'status' => 'active',
            'metadata' => ['vendor' => 'Huawei', 'ports' => 16],
        ]);

        // Add some random nodes
        ServiceNode::factory()->count(10)->create();
    }

    private function seedPlans(): void
    {
        BillingPlan::create([
            'name' => 'Home Basic 20M',
            'speed_limit' => '20 Mbps',
            'price' => 150000,
            'type' => 'prepaid',
            'features' => ['Unlimited', 'Support 24/7'],
        ]);

        BillingPlan::create([
            'name' => 'Home Ultra 50M',
            'speed_limit' => '50 Mbps',
            'price' => 300000,
            'type' => 'prepaid',
            'features' => ['Unlimited', 'Priority Support', 'Public IP'],
        ]);

        BillingPlan::create([
            'name' => 'Business Pro 100M',
            'speed_limit' => '100 Mbps',
            'price' => 750000,
            'type' => 'postpaid',
            'features' => ['Dedicated', 'SLA 99.9%', 'Static IP'],
        ]);
    }

    private function seedCustomersAndDevices(): void
    {
        // Get some users to act as customers
        $customers = User::factory()->count(20)->create();

        foreach ($customers as $customer) {
            CustomerDevice::factory()->create([
                'customer_id' => $customer->id,
                'status' => 'online',
            ]);

            // Add an invoice for some
            if (rand(0, 1)) {
                Invoice::create([
                    'user_id' => $customer->id,
                    'amount' => 150000,
                    'due_date' => now()->addDays(15),
                    'status' => 'unpaid',
                    'billing_period' => now()->format('Y-m'),
                ]);
            }
        }
    }
}
