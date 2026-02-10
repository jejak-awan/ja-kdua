<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Service Nodes (Routers)
        Schema::create('isp_service_nodes', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('type');
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
            $table->integer('last_active_count')->default(0);
            $table->string('status')->default('active');
            $table->decimal('location_lat', 10, 8)->nullable();
            $table->decimal('location_lng', 11, 8)->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // 2. Data Servers
        Schema::create('isp_data_servers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('ip_address')->nullable();
            $table->string('status')->default('active');
            $table->timestamps();
        });

        // 3. OLTs
        Schema::create('olts', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('ip_address')->unique();
            $table->string('type');
            $table->string('username')->nullable();
            $table->string('password')->nullable();
            $table->integer('port')->default(23);
            $table->string('status')->default('active');
            $table->text('details')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // 4. ODPs (Optical Distribution Points)
        Schema::create('odps', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('olt_id')->constrained('olts')->onDelete('cascade');
            $table->integer('total_ports')->default(8);
            $table->string('location_address')->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->enum('status', ['Active', 'Full', 'Maintenance', 'Inactive'])->default('Active');
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // 5. IP Pools
        Schema::create('ip_pools', function (Blueprint $table) {
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

        // 6. IP Pool Addresses
        Schema::create('ip_pool_addresses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pool_id')->constrained('ip_pools')->cascadeOnDelete();
            $table->string('ip_address', 45);
            $table->unsignedBigInteger('customer_id')->nullable(); // Foreign key to be defined in customer migration if needed
            $table->enum('status', ['available', 'assigned', 'reserved'])->default('available');
            $table->timestamp('assigned_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->unique(['pool_id', 'ip_address']);
            $table->index('status');
            $table->index('customer_id');
        });

        // 7. Subnets
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

        // 8. IP Addresses (Legacy mapping)
        Schema::create('isp_ip_addresses', function (Blueprint $table) {
            $table->id();
            $table->string('address')->unique();
            $table->string('status')->default('available');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('isp_ip_addresses');
        Schema::dropIfExists('isp_subnets');
        Schema::dropIfExists('ip_pool_addresses');
        Schema::dropIfExists('ip_pools');
        Schema::dropIfExists('odps');
        Schema::dropIfExists('olts');
        Schema::dropIfExists('isp_data_servers');
        Schema::dropIfExists('isp_service_nodes');
    }
};
