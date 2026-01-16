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
        Schema::table('aneel_reports', function (Blueprint $table) {
            $table->string('xlsx_name')->nullable()->after('justification2');
            $table->binary('xlsx_attachment')->nullable()->after('xlsx_name');
            $table->integer('xlsx_attachment_size')->nullable()->after('xlsx_attachment');
            $table->string('xlsx_mime_type')->nullable()->after('xlsx_attachment_size');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('aneel_reports', function (Blueprint $table) {
            $table->dropColumn([
                'xlsx_name',
                'xlsx_attachment',
                'xlsx_attachment_size',
                'xlsx_mime_type',
            ]);
        });
    }
};
