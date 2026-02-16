<?php

namespace Database\Seeders;

use App\Models\Core\User;
use App\Models\Isp\Billing\IspPlan as BillingPlan;
use App\Models\Isp\Customer\CustomerDevice;
use App\Models\Isp\Billing\Invoice;
use App\Models\Isp\Network\ServiceNode;
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
        ServiceNode::updateOrCreate(
            ['ip_address' => '10.0.0.1'],
            [
                'name' => 'Main POP - Central High',
                'type' => 'POP',
                'location_lat' => -7.8667,
                'location_lng' => 112.6500,
                'status' => 'active',
                'metadata' => ['vendor' => 'Cisco', 'capacity' => '10Gbps'],
                'radius_secret' => 'secret123',
            ]
        );

        ServiceNode::updateOrCreate(
            ['ip_address' => '10.0.1.1'],
            [
                'name' => 'Edge Router - North Sector',
                'type' => 'Router',
                'location_lat' => -7.8500,
                'location_lng' => 112.6600,
                'status' => 'active',
                'metadata' => ['vendor' => 'Mikrotik', 'model' => 'CCR2116'],
                'radius_secret' => 'secret123',
            ]
        );

        ServiceNode::updateOrCreate(
            ['ip_address' => '10.0.2.1'],
            [
                'name' => 'OLT - Residential Block A',
                'type' => 'OLT',
                'location_lat' => -7.8700,
                'location_lng' => 112.6400,
                'status' => 'active',
                'metadata' => ['vendor' => 'Huawei', 'ports' => 16],
                'radius_secret' => 'secret123',
            ]
        );
    }

    private function seedPlans(): void
    {
        BillingPlan::updateOrCreate(
            ['name' => 'Home Basic 20M'],
            [
                'download_limit' => '20M',
                'upload_limit' => '10M',
                'price' => 150000,
                'type' => 'fiber',
                'features' => ['Unlimited', 'Support 24/7'],
            ]
        );

        BillingPlan::updateOrCreate(
            ['name' => 'Home Ultra 50M'],
            [
                'download_limit' => '50M',
                'upload_limit' => '20M',
                'price' => 300000,
                'type' => 'fiber',
                'features' => ['Unlimited', 'Priority Support', 'Public IP'],
            ]
        );

        BillingPlan::updateOrCreate(
            ['name' => 'Business Pro 100M'],
            [
                'download_limit' => '100M',
                'upload_limit' => '100M',
                'price' => 750000,
                'type' => 'fiber',
                'features' => ['Dedicated', 'SLA 99.9%', 'Static IP'],
            ]
        );
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
