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
        Schema::create('aneel_report_indicators', function (Blueprint $table) {
            $table->id();
            $table->foreignId('report_id')->constrained('aneel_reports')->onDelete('cascade');
            $table->foreignId('indicator_id')->constrained('aneel_indicators')->onDelete('cascade');
            $table->json('inputs');
            $table->decimal('value', 10, 4)->nullable();
            $table->string('status', 100)->nullable();
            $table->string('name_attachment')->nullable();
            $table->binary('attachment')->nullable();
            $table->string('mime')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('aneel_report_indicators');
    }
};
