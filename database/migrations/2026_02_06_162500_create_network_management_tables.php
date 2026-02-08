<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Service Profiles (Bandwidth Packages)
        Schema::create('isp_service_profiles', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->integer('download_limit'); // Mbps
            $table->integer('upload_limit');   // Mbps
            $table->string('burst_limit')->nullable();
            $table->string('status')->default('active');
            $table->timestamps();
            $table->softDeletes();
        });

        // Subnets (IP Pools)
        Schema::create('isp_subnets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('node_id')->constrained('isp_service_nodes')->onDelete('cascade');
            $table->string('name');
            $table->string('prefix'); // e.g., 192.168.1.0/24
            $table->string('gateway')->nullable();
            $table->integer('vlan_id')->nullable();
            $table->enum('type', ['WAN', 'LAN', 'CGNAT'])->default('LAN');
            $table->string('status')->default('active');
            $table->timestamps();
            $table->softDeletes();
        });

        // IP Addresses (Individual Allocations)
        Schema::create('isp_ip_addresses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subnet_id')->constrained('isp_subnets')->onDelete('cascade');
            $table->foreignId('device_id')->nullable()->constrained('isp_customer_devices')->onDelete('set null');
            $table->string('address');
            $table->enum('status', ['Available', 'Reserved', 'Assigned'])->default('Available');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['subnet_id', 'address']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('isp_ip_addresses');
        Schema::dropIfExists('isp_subnets');
        Schema::dropIfExists('isp_service_profiles');
    }
};
