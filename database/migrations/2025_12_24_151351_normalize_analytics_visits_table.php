<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Normalize analytics_visits table by removing duplicate columns
     * that are already stored in analytics_sessions table.
     * These columns will be retrieved via JOIN when needed.
     */
    public function up(): void
    {
        Schema::table('analytics_visits', function (Blueprint $table) {
            // Drop duplicate columns - these exist in analytics_sessions
            $table->dropColumn([
                'device_type',
                'browser',
                'os',
                'country',
                'city',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('analytics_visits', function (Blueprint $table) {
            // Restore dropped columns
            $table->string('device_type')->nullable()->after('status_code');
            $table->string('browser')->nullable()->after('device_type');
            $table->string('os')->nullable()->after('browser');
            $table->string('country')->nullable()->after('os');
            $table->string('city')->nullable()->after('country');
        });
    }
};
