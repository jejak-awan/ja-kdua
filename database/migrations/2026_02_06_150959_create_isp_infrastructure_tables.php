<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('isp_service_nodes', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('type', ['OLT', 'POP', 'Router'])->default('Router');
            $table->string('ip_address')->nullable();
            $table->decimal('location_lat', 10, 8)->nullable();
            $table->decimal('location_lng', 11, 8)->nullable();
            $table->string('status')->default('active');
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('isp_customer_devices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('users')->onDelete('cascade');
            $table->enum('type', ['ONU', 'ONT', 'CPE'])->default('ONT');
            $table->string('serial_number')->unique();
            $table->string('mac_address')->unique()->nullable();
            $table->string('status')->default('active');
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('isp_customer_devices');
        Schema::dropIfExists('isp_service_nodes');
    }
};
