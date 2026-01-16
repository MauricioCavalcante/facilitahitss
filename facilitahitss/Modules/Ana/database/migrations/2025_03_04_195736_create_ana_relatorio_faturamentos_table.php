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
        Schema::create('ana_relatorio_faturamentos', function (Blueprint $table) {
            $table->id();
            $table->string('nome');
            $table->binary('arquivo')->nullable();
            $table->bigInteger('tamanho');
            $table->string('numero_nota_fiscal');
            $table->date('data_vencimento');
            $table->date('data_inicio');
            $table->date('data_fim');
            $table->decimal('desconto', 10, 2)->default(0.00);
            $table->decimal('valor_final', 10, 2);
            $table->timestamps();
        });

        DB::statement('ALTER TABLE ana_relatorio_faturamentos MODIFY arquivo LONGBLOB');

        Schema::create('ana_relatorio_faturamento_os', function (Blueprint $table) {
            $table->id();
            $table->foreignId('relatorio_id')->constrained('ana_relatorio_faturamentos')->onDelete('cascade');
            $table->foreignId('os_id')->constrained('ana_ordem_servicos')->onDelete('cascade');
            $table->decimal('valor', 10, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ana_relatorio_faturamentos');
        Schema::dropIfExists('ana_relatorio_faturamentos_os');
    }
};
