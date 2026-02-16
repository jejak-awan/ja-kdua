<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. OLT Command Logs
        Schema::create('isp_olt_command_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('olt_id')->constrained('isp_service_nodes')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('command');
            $table->text('response')->nullable();
            $table->boolean('is_success')->default(true);
            $table->integer('execution_time_ms')->nullable();
            $table->timestamps();
        });

        // 2. OLT Signals
        Schema::create('isp_olt_signals', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('customer_id')->nullable(); // Foreign key defined in Customer migration
            $table->foreignId('olt_id')->constrained('isp_service_nodes')->onDelete('cascade');
            $table->string('onu_index');
            $table->float('rx_power')->nullable();
            $table->float('tx_power')->nullable();
            $table->float('olt_rx_power')->nullable();
            $table->timestamp('collected_at')->useCurrent();
            $table->index(['olt_id', 'onu_index']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('isp_olt_signals');
        Schema::dropIfExists('isp_olt_command_logs');
    }
};
