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
        Schema::create('aneel_relatorio_rta_anexos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('relatorio_id')->constrained('aneel_relatorio_rta')->onDelete('cascade');
            $table->string('nome_arquivo');
            $table->string('tipo', 50);
            $table->binary('arquivo');
            $table->timestamps();
        });

        // Modificar a coluna 'arquivo' para LONGBLOB
        DB::statement('ALTER TABLE aneel_relatorio_rta_anexos MODIFY arquivo LONGBLOB');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('aneel_relatorio_rta_anexos');
    }
};
