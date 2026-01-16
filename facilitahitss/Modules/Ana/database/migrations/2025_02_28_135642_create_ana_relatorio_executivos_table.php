<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('ana_relatorio_executivos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('ordem_servico_id')->constrained('ana_ordem_servicos')->onDelete('cascade');
            $table->string('nome', 255);
            $table->binary('arquivo')->nullable();
            $table->string('tipo', 50);
            $table->bigInteger('tamanho');
            $table->timestamps();
        });

        // Alterando a coluna 'arquivo' para LONGBLOB via SQL na tabela 'ana_relatorio_executivos'
        DB::statement('ALTER TABLE ana_relatorio_executivos MODIFY arquivo LONGBLOB');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ana_relatorio_executivos');
    }
};
