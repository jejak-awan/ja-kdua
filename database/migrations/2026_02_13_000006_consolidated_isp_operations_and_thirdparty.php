<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Network Outages
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

        // 2. Support Tickets
        Schema::create('isp_tickets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('subject');
            $table->string('category');
            $table->string('priority')->default('Medium');
            $table->foreignId('outage_id')->nullable()->constrained('isp_outages')->nullOnDelete();
            $table->string('status')->default('Open');
            $table->text('message');
            $table->timestamps();
            $table->softDeletes();
        });

        // 3. Service Requests
        Schema::create('isp_service_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('users')->onDelete('cascade');
            $table->string('type');
            $table->json('details')->nullable();
            $table->string('status')->default('Pending');
            $table->text('admin_notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // 4. WhatsApp Templates
        Schema::create('isp_whatsapp_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('category');
            $table->text('body');
            $table->json('variables')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
        });

        // 5. WhatsApp Blasts
        Schema::create('isp_whatsapp_blasts', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('template_id')->nullable()->constrained('isp_whatsapp_templates')->nullOnDelete();
            $table->text('message');
            $table->string('target_filter');
            $table->json('target_numbers')->nullable();
            $table->integer('total_targets')->default(0);
            $table->integer('sent_count')->default(0);
            $table->integer('failed_count')->default(0);
            $table->enum('status', ['draft', 'queued', 'processing', 'completed', 'failed'])->default('draft');
            $table->dateTime('scheduled_at')->nullable();
            $table->dateTime('started_at')->nullable();
            $table->dateTime('completed_at')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
        });

        // 6. WhatsApp Blast Logs
        Schema::create('isp_whatsapp_blast_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('blast_id')->constrained('isp_whatsapp_blasts')->cascadeOnDelete();
            $table->string('phone');
            $table->string('name')->nullable();
            $table->enum('status', ['pending', 'sent', 'failed'])->default('pending');
            $table->string('error_message')->nullable();
            $table->dateTime('sent_at')->nullable();
            $table->timestamps();
        });

        // 7. WhatsApp Schedules
        Schema::create('isp_whatsapp_schedules', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('template_id')->nullable()->constrained('isp_whatsapp_templates')->nullOnDelete();
            $table->string('target_filter');
            $table->string('frequency');
            $table->integer('day_offset')->default(0);
            $table->string('time')->default('08:00');
            $table->boolean('is_active')->default(true);
            $table->dateTime('last_run_at')->nullable();
            $table->dateTime('next_run_at')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
        });

        // 8. Print Templates
        Schema::create('isp_print_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('type', ['voucher', 'invoice'])->default('voucher');
            $table->string('paper_size')->default('A4');
            $table->enum('orientation', ['portrait', 'landscape'])->default('portrait');
            $table->unsignedTinyInteger('columns_per_row')->default(3);
            $table->text('html_content');
            $table->text('css_content')->nullable();
            $table->json('variables')->nullable();
            $table->boolean('is_default')->default(false);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        // 9. Activity Logs
        Schema::create('isp_activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('action', ['create', 'update', 'delete', 'login', 'export', 'other'])->default('other');
            $table->string('resource_type')->nullable();
            $table->unsignedBigInteger('resource_id')->nullable();
            $table->string('description');
            $table->json('properties')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamp('created_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('isp_activity_logs');
        Schema::dropIfExists('isp_print_templates');
        Schema::dropIfExists('isp_whatsapp_schedules');
        Schema::dropIfExists('isp_whatsapp_blast_logs');
        Schema::dropIfExists('isp_whatsapp_blasts');
        Schema::dropIfExists('isp_whatsapp_templates');
        Schema::dropIfExists('isp_service_requests');
        Schema::dropIfExists('isp_tickets');
        Schema::dropIfExists('isp_outages');
    }
};
