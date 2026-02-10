<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Customers (Consolidated with all fields from fragments)
        Schema::create('isp_customers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('identity_number')->nullable();
            $table->string('identity_type')->default('KTP');

            // Address
            $table->string('address_street')->nullable();
            $table->string('address_village')->nullable();
            $table->string('address_district')->nullable();
            $table->string('address_city')->nullable();
            $table->string('address_province')->nullable();
            $table->string('address_postal_code')->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->string('coordinates')->nullable(); // Legacy lat,lng string

            // Billing & Usage
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

            // Infrastructure Links
            $table->foreignId('partner_id')->nullable()->constrained('isp_partners')->nullOnDelete();
            $table->foreignId('router_id')->nullable()->constrained('isp_service_nodes')->nullOnDelete();
            $table->foreignId('server_id')->nullable()->constrained('isp_data_servers')->nullOnDelete();
            $table->foreignId('olt_id')->nullable()->constrained('olts')->nullOnDelete();
            $table->foreignId('odp_id')->nullable()->constrained('odps')->nullOnDelete();
            $table->integer('odp_port')->nullable();
            $table->string('service_category')->nullable();

            // Mikrotik Auth
            $table->string('mikrotik_login')->nullable()->index();
            $table->string('mikrotik_password')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });

        // 2. Customer Devices (ONT/Routers)
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

        // 3. Contracts
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

        // 4. Invoices
        Schema::create('isp_invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->decimal('tax', 15, 2)->default(0);
            $table->integer('unique_code')->default(0);
            $table->decimal('amount', 15, 2);
            $table->date('due_date');
            $table->enum('status', ['paid', 'unpaid', 'cancelled'])->default('unpaid');
            $table->string('billing_period');
            $table->timestamps();
        });

        // 5. Invoice Items
        Schema::create('isp_invoice_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained('isp_invoices')->onDelete('cascade');
            $table->string('name');
            $table->decimal('price', 15, 2);
            $table->integer('qty')->default(1);
            $table->decimal('total', 15, 2);
            $table->timestamps();
        });

        // 6. Polymorphic Transactions (Replaces CustomerTransaction and PartnerTransaction)
        Schema::create('isp_transactions', function (Blueprint $table) {
            $table->id();
            $table->morphs('parent'); // parent_id, parent_type
            $table->enum('type', ['credit', 'debit']);
            $table->decimal('amount', 15, 2);
            $table->decimal('saldo_before', 15, 2);
            $table->decimal('saldo_after', 15, 2);
            $table->string('category');
            $table->string('description')->nullable();
            $table->string('reference_type')->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->index(['parent_type', 'parent_id', 'created_at']);
        });

        // 7. Payment Gateways
        Schema::create('isp_payment_gateways', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('driver');
            $table->json('config');
            $table->boolean('is_active')->default(false);
            $table->timestamps();
        });

        // 8. Coupon Usages
        Schema::create('isp_coupon_usages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('coupon_id')->constrained('isp_coupons')->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained('isp_customers')->cascadeOnDelete();
            $table->foreignId('invoice_id')->nullable()->constrained('isp_invoices')->nullOnDelete();
            $table->decimal('discount_amount', 15, 2);
            $table->timestamps();
            $table->index(['coupon_id', 'customer_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('isp_coupon_usages');
        Schema::dropIfExists('isp_payment_gateways');
        Schema::dropIfExists('isp_transactions');
        Schema::dropIfExists('isp_invoice_items');
        Schema::dropIfExists('isp_invoices');
        Schema::dropIfExists('isp_contracts');
        Schema::dropIfExists('isp_customer_devices');
        Schema::dropIfExists('isp_customers');
    }
};
