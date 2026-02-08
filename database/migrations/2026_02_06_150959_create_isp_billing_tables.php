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
        Schema::create('isp_billing_plans', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('speed_limit');
            $table->decimal('price', 15, 2);
            $table->string('type')->default('prepaid'); // prepaid, postpaid
            $table->json('features')->nullable();
            $table->timestamps();
        });

        Schema::create('isp_invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->decimal('amount', 15, 2);
            $table->date('due_date');
            $table->enum('status', ['paid', 'unpaid', 'cancelled'])->default('unpaid');
            $table->string('billing_period');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('isp_invoices');
        Schema::dropIfExists('isp_billing_plans');
    }
};
