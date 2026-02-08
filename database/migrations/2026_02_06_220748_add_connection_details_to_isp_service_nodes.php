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
        Schema::table('isp_service_nodes', function (Blueprint $table) {
            $table->string('secret')->nullable()->after('ip_address');
            $table->enum('connection_type', ['IP PUBLIC', 'VPN RADIUS'])->default('IP PUBLIC')->after('secret');
            $table->integer('management_port')->default(80)->after('connection_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('isp_service_nodes', function (Blueprint $table) {
            $table->dropColumn(['secret', 'connection_type', 'management_port']);
        });
    }
};
