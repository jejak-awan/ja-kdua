<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('isp_technician_deployments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('technician_id')->constrained('users');
            $table->foreignId('customer_id')->constrained('isp_customers');
            $table->foreignId('service_request_id')->nullable()->constrained('isp_service_requests');
            $table->string('type'); // installation, repair, maintenance
            $table->string('status'); // scheduled, in_progress, completed, cancelled
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('isp_technician_deployments');
    }
};
