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
        Schema::create('ana_relatorio_executivo_detalhes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('relatorio_id')->constrained('ana_relatorio_executivos')->onDelete('cascade');
            $table->text('titulo');
            $table->text('referencias');
            $table->text('atividades');
            $table->text('tarefas');
            $table->text('evidencias');
            $table->text('sei')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ana_relatorio_executivo_detalhes');
    }
};
