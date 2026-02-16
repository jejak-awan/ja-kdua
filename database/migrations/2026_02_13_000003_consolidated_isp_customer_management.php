<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Plans (Moved before customer for FK)
        Schema::create('isp_plans', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('type', ['fiber', 'hotspot'])->default('fiber');
            $table->string('download_limit')->nullable();
            $table->string('upload_limit')->nullable();
            $table->string('burst_limit')->nullable();
            $table->string('mikrotik_group')->nullable();
            $table->string('mikrotik_rate_limit')->nullable();
            $table->decimal('price', 15, 2)->default(0);
            $table->decimal('cost_price', 15, 2)->default(0);
            $table->decimal('commission', 15, 2)->default(0);
            $table->decimal('partner_price', 15, 2)->nullable();
            $table->integer('shared_users')->default(1);
            $table->integer('active_period')->default(30);
            $table->integer('quota_limit_mb')->nullable();
            $table->double('fup_limit_gb')->nullable();
            $table->string('fup_speed')->nullable();
            $table->boolean('fup_enabled')->default(false);
            $table->string('color')->nullable();
            $table->text('description')->nullable();
            $table->string('status')->default('active');
            $table->json('features')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // 2. Partners (Resellers)
        Schema::create('isp_partners', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('name');
            $table->enum('category', ['reseller', 'biller'])->default('reseller');
            $table->string('phone')->nullable();
            $table->text('address')->nullable();
            $table->decimal('saldo', 15, 2)->default(0);
            $table->decimal('limit_hutang', 15, 2)->default(0);
            $table->decimal('commission_rate', 5, 2)->default(0);
            $table->string('status')->default('active');
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // 3. Customers
        Schema::create('isp_customers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('identity_number')->nullable();
            $table->string('identity_type')->default('KTP');
            $table->string('address_street')->nullable();
            $table->string('address_village')->nullable();
            $table->string('address_district')->nullable();
            $table->string('address_city')->nullable();
            $table->string('address_province')->nullable();
            $table->string('address_postal_code')->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->string('coordinates')->nullable();
            $table->foreignId('billing_plan_id')->nullable()->constrained('isp_plans')->nullOnDelete();
            $table->integer('billing_cycle_start')->default(1);
            $table->date('installation_date')->nullable();
            $table->string('status')->default('active');
            $table->unsignedBigInteger('current_usage_bytes')->default(0);
            $table->timestamp('last_usage_reset_at')->nullable();
            $table->boolean('is_fup_active')->default(false);
            $table->decimal('saldo', 15, 2)->default(0);
            $table->decimal('limit_hutang', 15, 2)->default(0);
            $table->boolean('is_taxed')->default(false);
            $table->integer('unique_code')->nullable();
            $table->date('billing_due_date')->nullable();
            $table->text('billing_notes')->nullable();
            $table->foreignId('partner_id')->nullable()->constrained('isp_partners')->nullOnDelete();
            $table->foreignId('router_id')->nullable()->constrained('isp_service_nodes')->nullOnDelete();
            $table->foreignId('server_id')->nullable()->constrained('isp_service_zones')->nullOnDelete();
            $table->foreignId('olt_id')->nullable()->constrained('isp_service_nodes')->nullOnDelete();
            $table->string('olt_port')->nullable();
            $table->string('olt_onu_index')->nullable();
            $table->foreignId('odp_id')->nullable()->constrained('isp_odps')->nullOnDelete();
            $table->integer('odp_port')->nullable();
            $table->string('service_category')->nullable();
            $table->string('mikrotik_login')->nullable()->index();
            $table->string('mikrotik_password')->nullable();
            $table->string('telegram_id')->nullable();
            $table->string('onu_serial')->nullable();
            $table->string('onu_model')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // 4. Customer Devices
        Schema::create('isp_customer_devices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('isp_customers')->onDelete('cascade');
            $table->string('device_name')->nullable();
            $table->string('mac_address')->nullable();
            $table->string('serial_number')->nullable();
            $table->string('status')->default('active');
            $table->timestamp('activated_at')->nullable();
            $table->timestamp('expiration_date')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // 5. Contracts
        Schema::create('isp_contracts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('isp_customers')->onDelete('cascade');
            $table->string('contract_number')->unique();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('file_path')->nullable();
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->string('status')->default('active');
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // Add FK back to OLT signals now that customer table exists
        Schema::table('isp_olt_signals', function (Blueprint $table) {
            $table->foreign('customer_id')->references('id')->on('isp_customers')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('isp_olt_signals', function (Blueprint $table) {
            $table->dropForeign(['customer_id']);
        });
        Schema::dropIfExists('isp_contracts');
        Schema::dropIfExists('isp_customer_devices');
        Schema::dropIfExists('isp_customers');
        Schema::dropIfExists('isp_partners');
        Schema::dropIfExists('isp_plans');
    }
};
