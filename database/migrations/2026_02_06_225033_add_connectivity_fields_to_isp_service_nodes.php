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
            $table->enum('connection_method', ['none', 'snmp', 'api'])->default('none')->after('management_port');
            $table->string('api_username')->nullable()->after('connection_method');
            $table->string('api_password')->nullable()->after('api_username');
            $table->integer('api_port')->default(8728)->after('api_password');
            $table->string('snmp_community')->default('public')->after('api_port');
            $table->integer('snmp_port')->default(161)->after('snmp_community');
            $table->integer('last_active_count')->default(0)->after('snmp_port');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('isp_service_nodes', function (Blueprint $table) {
            $table->dropColumn([
                'connection_method',
                'api_username',
                'api_password',
                'api_port',
                'snmp_community',
                'snmp_port',
                'last_active_count',
            ]);
        });
    }
};
