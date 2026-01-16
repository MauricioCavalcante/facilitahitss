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
        Schema::create('ana_relatorio_executivo_justificativas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('os_id')->constrained('ana_ordem_servicos')->onDelete('cascade');
            $table->text('justificativa');
            $table->enum('status', ['Pendente', 'Aprovada', 'Sancionada'])->default('Pendente');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ana_relatorio_executivo_justificativas');
    }
};
