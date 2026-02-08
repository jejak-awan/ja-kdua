<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('odps', function (Blueprint $row) {
            $row->id();
            $row->string('name');
            $row->foreignId('olt_id')->constrained('olts')->onDelete('cascade');
            $row->integer('total_ports')->default(8);
            $row->string('location_address')->nullable();
            $row->decimal('latitude', 10, 8)->nullable();
            $row->decimal('longitude', 11, 8)->nullable();
            $row->enum('status', ['Active', 'Full', 'Maintenance', 'Inactive'])->default('Active');
            $row->text('description')->nullable();
            $row->timestamps();
        });

        Schema::table('isp_customers', function (Blueprint $table) {
            $table->foreignId('odp_id')->nullable()->constrained('odps')->onDelete('set null');
            $table->integer('odp_port')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('isp_customers', function (Blueprint $table) {
            $table->dropForeign(['odp_id']);
            $table->dropColumn(['odp_id', 'odp_port']);
        });
        Schema::dropIfExists('odps');
    }
};
