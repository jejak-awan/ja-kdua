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
        Schema::create('isp_invoice_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained('isp_invoices')->onDelete('cascade');
            $table->string('name');
            $table->decimal('price', 15, 2);
            $table->integer('qty')->default(1);
            $table->decimal('total', 15, 2);
            $table->timestamps();
        });

        Schema::table('isp_invoices', function (Blueprint $table) {
            if (! Schema::hasColumn('isp_invoices', 'subtotal')) {
                $table->decimal('subtotal', 15, 2)->after('user_id')->default(0);
            }
            if (! Schema::hasColumn('isp_invoices', 'tax')) {
                $table->decimal('tax', 15, 2)->after('subtotal')->default(0);
            }
            if (! Schema::hasColumn('isp_invoices', 'unique_code')) {
                $table->integer('unique_code')->after('tax')->default(0);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('isp_invoice_items');
        Schema::table('isp_invoices', function (Blueprint $table) {
            $table->dropColumn(['subtotal', 'tax', 'unique_code']);
        });
    }
};
