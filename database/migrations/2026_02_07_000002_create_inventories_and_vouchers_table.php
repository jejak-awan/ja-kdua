<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Inventory Items
        Schema::create('isp_inventories', function (Blueprint $row) {
            $row->id();
            $row->string('name');
            $row->string('sku')->unique()->nullable();
            $row->enum('category', ['ONU', 'Router', 'Cable', 'SFP', 'Splitter', 'Other']);
            $row->string('unit')->default('pcs'); // pcs, meters, etc
            $row->integer('stock')->default(0);
            $row->integer('min_stock')->default(5);
            $row->decimal('unit_price', 15, 2)->default(0);
            $row->timestamps();
        });

        // 2. Inventory Transactions (Stock In/Out)
        Schema::create('isp_inventory_transactions', function (Blueprint $row) {
            $row->id();
            $row->foreignId('inventory_id')->constrained('isp_inventories')->onDelete('cascade');
            $row->enum('type', ['In', 'Out', 'Adjustment', 'Return']);
            $row->integer('quantity');
            $row->foreignId('customer_id')->nullable()->constrained('users')->onDelete('set null'); // Linked to installation
            $row->foreignId('user_id')->constrained('users'); // Who performed transaction
            $row->text('notes')->nullable();
            $row->timestamps();
        });

        // 3. Hotspot Vouchers
        Schema::create('isp_vouchers', function (Blueprint $row) {
            $row->id();
            $row->string('code')->unique();
            $row->string('profile'); // Mikrotik profile name
            $row->decimal('price', 15, 2);
            $row->enum('status', ['Available', 'Used', 'Expired'])->default('Available');
            $row->timestamp('used_at')->nullable();
            $row->foreignId('used_by')->nullable()->constrained('users')->onDelete('set null');
            $row->string('batch_id')->nullable(); // For bulk printing/deletion
            $row->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('isp_vouchers');
        Schema::dropIfExists('isp_inventory_transactions');
        Schema::dropIfExists('isp_inventories');
    }
};
