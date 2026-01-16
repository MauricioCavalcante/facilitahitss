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
        Schema::create('aneel_report_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('report_id')->constrained('aneel_reports')->onDelete('cascade');
            $table->string('label');
            $table->string('name');
            $table->string('mime_type');
            $table->integer('size');
            $table->binary('attachment');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('aneel_report_attachments');
    }
};
