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
            if (! Schema::hasColumn('isp_customers', 'reseller_id')) {
                $table->foreignId('reseller_id')->nullable()->constrained('users')->nullOnDelete();
            }
            if (! Schema::hasColumn('isp_customers', 'router_id')) {
                $table->foreignId('router_id')->nullable()->constrained('isp_routers')->nullOnDelete();
            }
            if (! Schema::hasColumn('isp_customers', 'server_id')) {
                $table->foreignId('server_id')->nullable()->constrained('isp_data_servers')->nullOnDelete();
            }
            if (! Schema::hasColumn('isp_customers', 'service_category')) {
                $table->string('service_category')->nullable();
            }
            if (! Schema::hasColumn('isp_customers', 'billing_due_date')) {
                $table->date('billing_due_date')->nullable();
            }
            if (! Schema::hasColumn('isp_customers', 'billing_notes')) {
                $table->text('billing_notes')->nullable();
            }
            if (! Schema::hasColumn('isp_customers', 'is_taxed')) {
                $table->boolean('is_taxed')->default(false);
            }
            if (! Schema::hasColumn('isp_customers', 'unique_code')) {
                $table->integer('unique_code')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('isp_customers', function (Blueprint $table) {
            $table->dropForeign(['reseller_id']);
            $table->dropForeign(['router_id']);
            $table->dropForeign(['server_id']);
            $table->dropColumn([
                'reseller_id',
                'router_id',
                'server_id',
                'service_category',
                'billing_due_date',
                'billing_notes',
                'is_taxed',
                'unique_code',
            ]);
        });
    }
};
