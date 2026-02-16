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
        Schema::connection('mrtg')->create('isp_node_health_logs', function (Blueprint $table) {
            // Adapted to match App\Models\Isp\Network\NodeHealthLog
            $table->dateTime('time')->index(); // TimescaleDB hypertable candidate
            $table->integer('node_id');
            $table->string('status'); // up, down, degraded
            $table->integer('latency_ms')->default(0);
            $table->float('packet_loss')->default(0);
            
            // No primary key as per model definition (log data)
            $table->index(['node_id', 'time']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('isp_node_health_logs');
    }
};
