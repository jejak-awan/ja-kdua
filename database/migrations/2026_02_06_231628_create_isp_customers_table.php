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
        Schema::create('isp_customers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('identity_number')->nullable(); // KTP
            $table->string('identity_type')->default('KTP');

            // Address
            $table->string('address_street')->nullable();
            $table->string('address_village')->nullable();
            $table->string('address_district')->nullable();
            $table->string('address_city')->nullable();
            $table->string('address_province')->nullable();
            $table->string('address_postal_code')->nullable();
            $table->string('coordinates')->nullable(); // lat,lng

            // Billing
            $table->foreignId('billing_plan_id')->nullable()->constrained('isp_billing_plans')->nullOnDelete();
            $table->integer('billing_cycle_start')->default(1);
            $table->date('installation_date')->nullable();
            $table->string('status')->default('active'); // active, isolated, inactive

            // Mikrotik Auth (for PPPoE/Hotspot)
            $table->string('mikrotik_login')->nullable()->index();
            $table->string('mikrotik_password')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('isp_customers');
    }
};
