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
        Schema::create('ana_ordem_servicos', function (Blueprint $table) {
            $table->id();
            $table->string('numero', 50);
            $table->string('documento', 50);
            $table->enum('status', ['Nova', 'Em andamento', 'Encerrada', 'Atualizado' ])->default('Nova');
            $table->date('data_inicio');
            $table->date('data_fim');
            $table->integer('horas');
            $table->text('endereco');
            $table->timestamps();
        });

        Schema::create('ana_ordem_servicos_coordenacoes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ordem_servico_id')->constrained('ana_ordem_servicos')->onDelete('cascade');
            $table->foreignId('coordenacao_id')->constrained('ana_coordenacoes')->onDelete('cascade');
            $table->unique(['ordem_servico_id', 'coordenacao_id'], 'ordem_servico_coordenacao_unique');
            $table->timestamps();
        });

        Schema::create('ana_ordem_servicos_users', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ordem_servico_id')->constrained('ana_ordem_servicos')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->unique(['ordem_servico_id', 'user_id'], 'ordem_servico_user_unique');
            $table->timestamps();
        });

        Schema::create('ana_ordem_servicos_escopos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ordem_servico_id');
            $table->unsignedBigInteger('coordenacao_id');
            $table->unsignedBigInteger('escopo_id');
            $table->foreign('ordem_servico_id')->references('id')->on('ana_ordem_servicos')->onDelete('cascade');
            $table->foreign('coordenacao_id')->references('id')->on('ana_coordenacoes')->onDelete('cascade');
            $table->foreign('escopo_id')->references('id')->on('ana_escopos')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ana_ordem_servicos');
        Schema::dropIfExists('ana_ordem_servicos_coordenacoes');
        Schema::dropIfExists('ana_ordem_servicos_users');
        Schema::dropIfExists('ana_ordem_servicos_escopos');
    }
};
