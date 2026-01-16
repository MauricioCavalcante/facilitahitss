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
        Schema::create('aneel_reports', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->binary('attachment')->nullable();
            $table->integer('attachment_size')->nullable();
            $table->string('mime_type')->nullable();
            $table->date('period_start')->nullable();
            $table->date('period_end')->nullable();
            $table->longText('justification1')->nullable();
            $table->longText('justification2')->nullable();
            $table->enum('status', ['Em Andamento', 'Finalizado']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('aneel_reports');
    }
};
