<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Invoices
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

        // 2. Invoice Items
        Schema::create('isp_invoice_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained('isp_invoices')->onDelete('cascade');
            $table->string('name');
            $table->decimal('price', 15, 2);
            $table->integer('qty')->default(1);
            $table->decimal('total', 15, 2);
            $table->timestamps();
        });

        // 3. Transactions 
        Schema::create('isp_transactions', function (Blueprint $table) {
            $table->id();
            $table->morphs('parent'); 
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

        // 4. Payment Gateways
        Schema::create('isp_payment_gateways', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('driver');
            $table->json('config');
            $table->boolean('is_active')->default(false);
            $table->timestamps();
        });

        // 5. Coupons
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
        });

        // 6. Coupon Usages
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
        Schema::dropIfExists('isp_coupons');
        Schema::dropIfExists('isp_payment_gateways');
        Schema::dropIfExists('isp_transactions');
        Schema::dropIfExists('isp_invoice_items');
        Schema::dropIfExists('isp_invoices');
    }
};
