<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dependency_vulnerabilities', function (Blueprint $table) {
            $table->id();
            $table->string('package_name')->index();
            $table->string('version');
            $table->enum('severity', ['low', 'medium', 'high', 'critical'])->index();
            $table->string('cve')->nullable()->index();
            $table->string('fixed_in')->nullable();
            $table->enum('status', ['new', 'acknowledged', 'patched', 'ignored'])->default('new')->index();
            $table->enum('source', ['composer', 'npm'])->index();
            $table->text('description')->nullable();
            $table->timestamps();

            $table->unique(['package_name', 'version', 'cve']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dependency_vulnerabilities');
    }
};
