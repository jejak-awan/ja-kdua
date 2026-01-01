<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('csp_reports', function (Blueprint $table) {
            $table->id();
            $table->string('document_uri')->index();
            $table->string('violated_directive')->index();
            $table->text('blocked_uri')->nullable();
            $table->string('source_file')->nullable();
            $table->integer('line_number')->nullable();
            $table->string('user_agent')->nullable();
            $table->ipAddress('ip_address')->index();
            $table->json('raw_report')->nullable();
            $table->enum('status', ['new', 'reviewed', 'false_positive'])->default('new')->index();
            $table->timestamps();
            
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('csp_reports');
    }
};
