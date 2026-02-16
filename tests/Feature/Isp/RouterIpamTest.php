<?php

namespace Tests\Feature\Isp;

use App\Models\Core\Setting;
use App\Models\Core\User;
use App\Models\Isp\Network\ServiceNode;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RouterIpamTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed permissions
        \Spatie\Permission\Models\Permission::firstOrCreate(['name' => 'manage isp', 'guard_name' => 'web']);

        // Seed settings
        Setting::updateOrCreate(
            ['group' => 'isp', 'key' => 'isp_local_network_cidr'],
            ['value' => '10.0.0.0/8', 'type' => 'string']
        );
        Setting::updateOrCreate(
            ['group' => 'isp', 'key' => 'isp_subnet_size'],
            ['value' => 30, 'type' => 'integer']
        );
    }

    public function test_router_creation_auto_allocates_ip()
    {
        $user = User::factory()->create();
        $user->givePermissionTo('manage isp');

        $response = $this->actingAs($user, 'sanctum')->postJson('/api/core/admin/ja/isp/routers', [
            'name' => 'Test Router 1',
            'type' => 'Router',
            'status' => 'active',
            'connection_type' => 'VPN RADIUS',
            'ip_address' => '', // Empty for auto-allocation
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('isp_service_nodes', [
            'name' => 'Test Router 1',
        ]);

        $router = ServiceNode::where('name', 'Test Router 1')->first();
        $this->assertNotNull($router->ip_address);
        $this->assertStringStartsWith('10.', $router->ip_address);
    }

    public function test_router_update_auto_allocates_ip_when_cleared()
    {
        $user = User::factory()->create();
        $user->givePermissionTo('manage isp');

        $router = ServiceNode::factory()->create([
            'type' => 'Router',
            'ip_address' => '192.168.1.1',
            'connection_type' => 'VPN RADIUS',
        ]);

        $response = $this->actingAs($user, 'sanctum')->putJson("/api/core/admin/ja/isp/routers/{$router->id}", [
            'name' => 'Updated Router',
            'ip_address' => '', // Clear to trigger re-allocation
            'connection_type' => 'VPN RADIUS',
        ]);

        $response->assertStatus(200);

        $router->refresh();
        $this->assertNotEquals('192.168.1.1', $router->ip_address);
        $this->assertStringStartsWith('10.', $router->ip_address);
    }
}
