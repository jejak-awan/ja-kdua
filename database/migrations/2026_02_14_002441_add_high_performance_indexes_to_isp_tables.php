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
        Schema::table('isp_customers', function (Blueprint $table) {
            $table->index('identity_number');
            $table->index('onu_serial');
        });

        Schema::table('isp_customer_devices', function (Blueprint $table) {
            $table->index('mac_address');
            $table->index('serial_number');
        });

        Schema::table('isp_odps', function (Blueprint $table) {
            $table->index('name');
        });

        Schema::table('isp_service_nodes', function (Blueprint $table) {
            $table->index('name');
        });

        Schema::table('isp_audit_logs', function (Blueprint $table) {
            $table->index('ip_address');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('isp_audit_logs', function (Blueprint $table) {
            $table->dropIndex(['ip_address']);
        });

        Schema::table('isp_service_nodes', function (Blueprint $table) {
            $table->dropIndex(['name']);
        });

        Schema::table('isp_odps', function (Blueprint $table) {
            $table->dropIndex(['name']);
        });

        Schema::table('isp_customer_devices', function (Blueprint $table) {
            $table->dropIndex(['mac_address']);
            $table->dropIndex(['serial_number']);
        });

        Schema::table('isp_customers', function (Blueprint $table) {
            $table->dropIndex(['identity_number']);
            $table->dropIndex(['onu_serial']);
        });
    }
};
