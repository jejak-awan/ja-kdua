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
        Schema::table('forms', function (Blueprint $table) {
            $table->integer('view_count')->default(0)->after('submission_count');
            $table->integer('start_count')->default(0)->after('view_count');
        });

        Schema::create('form_analytics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('form_id')->constrained('forms')->onDelete('cascade');
            $table->date('date')->index();
            $table->integer('views')->default(0);
            $table->integer('starts')->default(0);
            $table->integer('submissions')->default(0);
            $table->timestamps();

            $table->unique(['form_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('form_analytics');
        Schema::table('forms', function (Blueprint $table) {
            $table->dropColumn(['view_count', 'start_count']);
        });
    }
};
