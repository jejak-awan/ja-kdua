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
            $table->string('mikrotik_group')->nullable()->after('name');
            $table->string('mikrotik_rate_limit')->nullable()->after('speed_limit');
            $table->integer('shared_users')->default(1)->after('mikrotik_rate_limit');
            $table->integer('active_period')->default(30)->after('shared_users');
            $table->decimal('cost_price', 15, 2)->default(0)->after('price');
            $table->decimal('commission', 15, 2)->default(0)->after('cost_price');
            $table->boolean('is_active')->default(true)->after('commission');
        });
    }

    public function down(): void
    {
        Schema::table('isp_billing_plans', function (Blueprint $table) {
            $table->dropColumn([
                'mikrotik_group',
                'mikrotik_rate_limit',
                'shared_users',
                'active_period',
                'cost_price',
                'commission',
                'is_active',
            ]);
        });
    }
};
