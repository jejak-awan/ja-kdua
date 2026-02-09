<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ip_pools', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('network'); // CIDR notation, e.g., 10.10.10.0/24
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

        Schema::create('ip_pool_addresses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pool_id')->constrained('ip_pools')->cascadeOnDelete();
            $table->string('ip_address', 45); // IPv4/IPv6
            $table->unsignedBigInteger('customer_id')->nullable();
            $table->enum('status', ['available', 'assigned', 'reserved'])->default('available');
            $table->timestamp('assigned_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['pool_id', 'ip_address']);
            $table->index('status');
            $table->index('customer_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ip_pool_addresses');
        Schema::dropIfExists('ip_pools');
    }
};
