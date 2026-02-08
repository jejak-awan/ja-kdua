<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('isp_outages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('node_id')->nullable()->constrained('isp_service_nodes')->onDelete('cascade');
            $table->string('title');
            $table->text('description');
            $table->enum('type', ['Scheduled', 'Unscheduled'])->default('Unscheduled');
            $table->enum('status', ['Investigating', 'Identified', 'Monitoring', 'Resolved'])->default('Investigating');
            $table->dateTime('started_at');
            $table->dateTime('resolved_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('isp_outages');
    }
};
