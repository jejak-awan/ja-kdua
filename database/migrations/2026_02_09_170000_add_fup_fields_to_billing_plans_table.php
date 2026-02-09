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
        Schema::table('isp_billing_plans', function (Blueprint $table) {
            $table->double('fup_limit_gb')->nullable()->after('mikrotik_rate_limit')->comment('Data cap in GB');
            $table->string('fup_speed')->nullable()->after('fup_limit_gb')->comment('Speed limit after FUP (e.g. 2M/2M)');
            $table->boolean('fup_enabled')->default(false)->after('fup_speed');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('isp_billing_plans', function (Blueprint $table) {
            $table->dropColumn(['fup_limit_gb', 'fup_speed', 'fup_enabled']);
        });
    }
};
