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
        Schema::create('isp_contracts', function (Blueprint $blueprint) {
            $blueprint->id();
            $blueprint->unsignedBigInteger('customer_id');
            $blueprint->string('contract_number')->unique();
            $blueprint->string('title');
            $blueprint->text('description')->nullable();
            $blueprint->string('file_path')->nullable();
            $blueprint->date('start_date');
            $blueprint->date('end_date')->nullable();
            $blueprint->string('status')->default('active'); // active, expired, terminated
            $blueprint->json('metadata')->nullable();
            $blueprint->timestamps();
            $blueprint->softDeletes();

            $blueprint->foreign('customer_id')->references('id')->on('isp_customers')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('isp_contracts');
    }
};
