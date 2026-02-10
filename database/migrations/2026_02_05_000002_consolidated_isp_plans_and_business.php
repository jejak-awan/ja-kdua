<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. ISP Plans (Consolidated from ServiceProfile and BillingPlan)
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

        // 2. Partners (Resellers / Billers) - Renamed from Mitras
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

        // 3. Voucher Batches
        Schema::create('isp_voucher_batches', function (Blueprint $table) {
            $table->id();
            $table->string('batch_code')->unique();
            $table->foreignId('profile_id')->constrained('isp_plans')->onDelete('cascade');
            $table->foreignId('partner_id')->nullable()->constrained('isp_partners')->onDelete('set null');
            $table->integer('quantity');
            $table->decimal('unit_price', 15, 2);
            $table->decimal('total_value', 15, 2);
            $table->enum('status', ['active', 'sold', 'expired', 'cancelled'])->default('active');
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });

        // 4. Vouchers
        Schema::create('isp_vouchers', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('profile'); // Text-based for Mikrotik compat
            $table->foreignId('profile_id')->nullable()->constrained('isp_plans')->onDelete('set null');
            $table->foreignId('partner_id')->nullable()->constrained('isp_partners')->onDelete('set null');
            $table->foreignId('batch_id')->nullable()->constrained('isp_voucher_batches')->onDelete('set null');
            $table->string('batch_code')->nullable();
            $table->decimal('price', 15, 2);
            $table->decimal('commission', 15, 2)->default(0);
            $table->enum('status', ['available', 'sold', 'used', 'expired', 'disabled'])->default('available');
            $table->timestamp('used_at')->nullable();
            $table->foreignId('used_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('sold_at')->nullable();
            $table->foreignId('sold_by')->nullable()->constrained('users')->onDelete('set null');
            $table->unsignedBigInteger('customer_id')->nullable(); // To be linked to isp_customers if needed
            $table->unsignedBigInteger('outlet_id')->nullable();
            $table->timestamps();

            $table->index('batch_code');
            $table->index('partner_id');
        });

        // 6. Inventories
        Schema::create('isp_inventories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('sku')->unique()->nullable();
            $table->enum('category', ['ONU', 'Router', 'Cable', 'SFP', 'Splitter', 'Other']);
            $table->string('unit')->default('pcs');
            $table->integer('stock')->default(0);
            $table->integer('min_stock')->default(5);
            $table->decimal('unit_price', 15, 2)->default(0);
            $table->timestamps();
        });

        // 7. Inventory Transactions
        Schema::create('isp_inventory_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inventory_id')->constrained('isp_inventories')->onDelete('cascade');
            $table->enum('type', ['In', 'Out', 'Adjustment', 'Return']);
            $table->integer('quantity');
            $table->foreignId('customer_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('user_id')->constrained('users');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // 8. Coupons
        Schema::create('isp_coupons', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->string('description')->nullable();
            $table->enum('discount_type', ['percentage', 'fixed']);
            $table->decimal('discount_value', 15, 2);
            $table->decimal('min_transaction', 15, 2)->default(0);
            $table->decimal('max_discount', 15, 2)->nullable();
            $table->integer('max_usage')->nullable();
            $table->integer('used_count')->default(0);
            $table->integer('max_per_customer')->default(1);
            $table->dateTime('valid_from')->nullable();
            $table->dateTime('valid_until')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index('code');
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('isp_coupons');
        Schema::dropIfExists('isp_inventory_transactions');
        Schema::dropIfExists('isp_inventories');
        Schema::dropIfExists('isp_vouchers');
        Schema::dropIfExists('isp_voucher_batches');
        Schema::dropIfExists('isp_partners');
        Schema::dropIfExists('isp_plans');
    }
};
