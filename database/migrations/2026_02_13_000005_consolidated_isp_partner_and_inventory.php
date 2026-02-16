<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Voucher Batches
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

        // 2. Vouchers
        Schema::create('isp_vouchers', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('profile'); 
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
            $table->unsignedBigInteger('customer_id')->nullable(); 
            $table->unsignedBigInteger('outlet_id')->nullable();
            $table->timestamps();

            $table->index('partner_id');
        });

        // 3. Inventories
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

        // 4. Inventory Transactions
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
    }

    public function down(): void
    {
        Schema::dropIfExists('isp_inventory_transactions');
        Schema::dropIfExists('isp_inventories');
        Schema::dropIfExists('isp_vouchers');
        Schema::dropIfExists('isp_voucher_batches');
    }
};
