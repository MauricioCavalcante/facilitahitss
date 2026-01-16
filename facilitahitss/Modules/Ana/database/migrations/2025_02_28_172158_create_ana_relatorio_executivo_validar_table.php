<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('ana_relatorio_executivo_validar', function (Blueprint $table) {
            $table->id();
            $table->foreignId('relatorio_id')->constrained('ana_relatorio_executivos')->onDelete('cascade');
            $table->enum('status', ['Rascunho', 'Novo', 'Para Corrigir', 'Corrigido', 'Validado']);
            $table->text('comentario')->nullable();
            $table->foreignId('editado_por')->nullable()->constrained('users');
            $table->foreignId('validado_por')->nullable()->constrained('users');
            $table->timestamp('data_validacao')->useCurrent();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('ana_relatorio_executivo_validar');
    }
};
