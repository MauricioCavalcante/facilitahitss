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
        Schema::create('aneel_relatorio_rta_detalhes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('relatorio_id')->constrained('aneel_relatorio_rta')->onDelete('cascade');
            $table->text('justificativa1')->nullable();
            $table->text('justificativa2')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('aneel_relatorio_rta_detalhes');
    }
};
