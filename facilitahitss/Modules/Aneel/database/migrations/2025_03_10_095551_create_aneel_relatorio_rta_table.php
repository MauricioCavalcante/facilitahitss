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
        Schema::create('aneel_relatorio_rta', function (Blueprint $table) {
            $table->id();
            $table->string('nome');
            $table->binary('arquivo')->nullable();
            $table->integer('tamanho');
            $table->enum('status', ['Rascunho', 'Finalizado'])->nullable();
            $table->date('periodo_inicio')->nullable();
            $table->date('periodo_fim')->nullable();
            $table->timestamps();
        });

        // Modificar a coluna 'arquivo' para LONGBLOB
        DB::statement('ALTER TABLE aneel_relatorio_rta MODIFY arquivo LONGBLOB');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('aneel_relatorio_rta');
    }
};
