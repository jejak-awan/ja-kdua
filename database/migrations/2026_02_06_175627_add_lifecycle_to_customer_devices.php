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
        Schema::table('isp_customer_devices', function (Blueprint $table) {
            $table->timestamp('activated_at')->nullable()->after('status');
            $table->timestamp('expiration_date')->nullable()->after('activated_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('isp_customer_devices', function (Blueprint $table) {
            $table->dropColumn(['activated_at', 'expiration_date']);
        });
    }
};
