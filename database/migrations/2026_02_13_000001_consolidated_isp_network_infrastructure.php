<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Service Nodes (Routers / OLTs unified target)
        Schema::create('isp_service_nodes', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('description')->nullable();
            $table->string('type');
            $table->string('sub_type')->nullable()->comment('core, gateway, distribution');
            $table->string('ip_address')->unique();
            $table->string('secret')->nullable();
            $table->enum('connection_type', ['IP PUBLIC', 'VPN RADIUS'])->default('VPN RADIUS');
            $table->integer('management_port')->default(8291);
            $table->enum('connection_method', ['none', 'snmp', 'api'])->default('none');
            $table->string('api_username')->nullable();
            $table->string('api_password')->nullable();
            $table->integer('api_port')->default(8728);
            $table->string('snmp_community')->default('public');
            $table->integer('snmp_port')->default(161);
            $table->boolean('radius_enabled')->default(false);
            $table->string('radius_secret')->nullable();
            $table->boolean('is_vpn_server')->default(false);
            $table->integer('last_active_count')->default(0);
            $table->string('status')->default('active');
            $table->decimal('location_lat', 10, 8)->nullable();
            $table->decimal('location_lng', 11, 8)->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // 2. Service Zones (Formerly data_servers)
        Schema::create('isp_service_zones', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('role')->default('main')->comment('main, backup');
            $table->string('ip_address')->nullable();
            $table->boolean('is_active')->default(true);
            $table->string('status')->default('active');
            $table->timestamps();
            $table->softDeletes();
        });

        // 3. ODPs
        Schema::create('isp_odps', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('olt_id')->constrained('isp_service_nodes')->onDelete('cascade');
            $table->integer('total_ports')->default(8);
            $table->string('location_address')->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->enum('status', ['Active', 'Full', 'Maintenance', 'Inactive'])->default('Active');
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // 4. IP Pools
        Schema::create('isp_ip_pools', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('network');
            $table->string('gateway')->nullable();
            $table->string('dns_primary')->nullable();
            $table->string('dns_secondary')->nullable();
            $table->unsignedSmallInteger('vlan_id')->nullable();
            $table->unsignedBigInteger('router_id')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->text('description')->nullable();
            $table->timestamps();
            $table->index('status');
        });

        // 5. IP Pool Addresses
        Schema::create('isp_ip_pool_addresses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pool_id')->constrained('isp_ip_pools')->cascadeOnDelete();
            $table->string('ip_address', 45);
            $table->unsignedBigInteger('customer_id')->nullable();
            $table->enum('status', ['available', 'assigned', 'reserved'])->default('available');
            $table->timestamp('assigned_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->unique(['pool_id', 'ip_address']);
            $table->index('status');
        });

        // 6. Subnets
        Schema::create('isp_subnets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('node_id')->constrained('isp_service_nodes')->onDelete('cascade');
            $table->string('name');
            $table->string('prefix');
            $table->string('gateway')->nullable();
            $table->integer('vlan_id')->nullable();
            $table->string('type')->default('dynamic');
            $table->timestamps();
            $table->softDeletes();
        });

        // 7. IP Addresses (Unified mapping)
        Schema::create('isp_ip_addresses', function (Blueprint $table) {
            $table->id();
            $table->string('address')->unique();
            $table->string('status')->default('available');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // 8. Traffic Logs
        Schema::create('isp_traffic_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_node_id')->constrained('isp_service_nodes')->onDelete('cascade');
            $table->string('interface_name');
            $table->string('interface_index')->nullable();
            $table->unsignedBigInteger('rx_octets');
            $table->unsignedBigInteger('tx_octets');
            $table->unsignedBigInteger('rx_bps')->nullable();
            $table->unsignedBigInteger('tx_bps')->nullable();
            $table->timestamp('logged_at')->useCurrent();
            $table->index(['service_node_id', 'logged_at']);
        });

        // 9. Node Health History
        Schema::create('isp_node_health_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_node_id')->constrained('isp_service_nodes')->onDelete('cascade');
            $table->unsignedTinyInteger('cpu_load')->default(0);
            $table->unsignedBigInteger('memory_used')->default(0);
            $table->unsignedBigInteger('memory_total')->default(0);
            $table->unsignedBigInteger('traffic_in_bps')->default(0);
            $table->unsignedBigInteger('traffic_out_bps')->default(0);
            $table->unsignedInteger('active_sessions')->default(0);
            $table->float('latency_ms', 8, 2)->nullable();
            $table->timestamp('recorded_at')->useCurrent();
            $table->index(['service_node_id', 'recorded_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('isp_node_health_history');
        Schema::dropIfExists('isp_traffic_logs');
        Schema::dropIfExists('isp_ip_addresses');
        Schema::dropIfExists('isp_subnets');
        Schema::dropIfExists('isp_ip_pool_addresses');
        Schema::dropIfExists('isp_ip_pools');
        Schema::dropIfExists('isp_odps');
        Schema::dropIfExists('isp_service_zones');
        Schema::dropIfExists('isp_service_nodes');
    }
};
