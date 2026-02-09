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
            $table->unsignedBigInteger('current_usage_bytes')->default(0)->after('status');
            $table->timestamp('last_usage_reset_at')->nullable()->after('current_usage_bytes');
            $table->boolean('is_fup_active')->default(false)->after('last_usage_reset_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('isp_customers', function (Blueprint $table) {
            $table->dropColumn(['current_usage_bytes', 'last_usage_reset_at', 'is_fup_active']);
        });
    }
};
