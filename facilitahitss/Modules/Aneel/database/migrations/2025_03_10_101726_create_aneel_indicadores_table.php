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
        // Criando tabelas para cada indicador com o novo prefixo "aneel_indicador_"
        Schema::create('aneel_indicador_iataa', function (Blueprint $table) {
            $table->id();
            $table->foreignId('relatorio_id')->nullable()->constrained('aneel_relatorio_rta')->onDelete('cascade');
            $table->integer('chamadas_abandonadas');
            $table->integer('chamadas_total');
            $table->decimal('resultado', 5, 2);
            $table->timestamps();
        });

        Schema::create('aneel_indicador_iet', function (Blueprint $table) {
            $table->id();
            $table->foreignId('relatorio_id')->nullable()->constrained('aneel_relatorio_rta')->onDelete('cascade');
            $table->integer('chamadas_espera_60s');
            $table->integer('chamadas_total');
            $table->decimal('resultado', 5, 2);
            $table->timestamps();
        });

        Schema::create('aneel_indicador_ita', function (Blueprint $table) {
            $table->id();
            $table->foreignId('relatorio_id')->nullable()->constrained('aneel_relatorio_rta')->onDelete('cascade');
            $table->integer('qt10');
            $table->integer('qtotal');
            $table->decimal('resultado', 5, 2);
            $table->timestamps();
        });

        Schema::create('aneel_indicador_icir', function (Blueprint $table) {
            $table->id();
            $table->foreignId('relatorio_id')->nullable()->constrained('aneel_relatorio_rta')->onDelete('cascade');
            $table->integer('qtrci');
            $table->integer('qtr');
            $table->decimal('resultado', 5, 2);
            $table->timestamps();
        });

        Schema::create('aneel_indicador_iaabc', function (Blueprint $table) {
            $table->id();
            $table->foreignId('relatorio_id')->nullable()->constrained('aneel_relatorio_rta')->onDelete('cascade');
            $table->integer('qtaa');
            $table->integer('qta');
            $table->decimal('resultado', 5, 2);
            $table->timestamps();
        });

        Schema::create('aneel_indicador_icabc', function (Blueprint $table) {
            $table->id();
            $table->foreignId('relatorio_id')->nullable()->constrained('aneel_relatorio_rta')->onDelete('cascade');
            $table->integer('qts_sbc');
            $table->integer('qts');
            $table->decimal('resultado', 5, 2);
            $table->timestamps();
        });

        Schema::create('aneel_indicador_iiap', function (Blueprint $table) {
            $table->id();
            $table->foreignId('relatorio_id')->nullable()->constrained('aneel_relatorio_rta')->onDelete('cascade');
            $table->integer('qtie');
            $table->integer('qc1');
            $table->integer('qc2');
            $table->integer('qc3');
            $table->integer('qc4');
            $table->decimal('resultado', 5, 2);
            $table->timestamps();
        });

        Schema::create('aneel_indicador_iiafpm', function (Blueprint $table) {
            $table->id();
            $table->foreignId('relatorio_id')->nullable()->constrained('aneel_relatorio_rta')->onDelete('cascade');
            $table->integer('qtie');
            $table->integer('qc1');
            $table->integer('qc2');
            $table->integer('qc3');
            $table->integer('qc4');
            $table->decimal('resultado', 5, 2);
            $table->timestamps();
        });

        Schema::create('aneel_indicador_irsap', function (Blueprint $table) {
            $table->id();
            $table->foreignId('relatorio_id')->nullable()->constrained('aneel_relatorio_rta')->onDelete('cascade');
            $table->integer('qtre');
            $table->integer('qc1');
            $table->integer('qc2');
            $table->integer('qc3');
            $table->integer('qc4');
            $table->decimal('resultado', 5, 2);
            $table->timestamps();
        });

        Schema::create('aneel_indicador_irsafpm', function (Blueprint $table) {
            $table->id();
            $table->foreignId('relatorio_id')->nullable()->constrained('aneel_relatorio_rta')->onDelete('cascade');
            $table->integer('qtre');
            $table->integer('qc1');
            $table->integer('qc2');
            $table->integer('qc3');
            $table->integer('qc4');
            $table->decimal('resultado', 5, 2);
            $table->timestamps();
        });

        Schema::create('aneel_indicador_irir', function (Blueprint $table) {
            $table->id();
            $table->foreignId('relatorio_id')->nullable()->constrained('aneel_relatorio_rta')->onDelete('cascade');
            $table->integer('qir');
            $table->integer('qrr');
            $table->integer('qtr');
            $table->integer('qti');
            $table->decimal('resultado', 5, 2);
            $table->timestamps();
        });

        Schema::create('aneel_indicador_isu', function (Blueprint $table) {
            $table->id();
            $table->foreignId('relatorio_id')->nullable()->constrained('aneel_relatorio_rta')->onDelete('cascade');
            $table->integer('qus');
            $table->integer('qunr');
            $table->integer('qtotal');
            $table->decimal('resultado', 5, 2);
            $table->timestamps();
        });

        Schema::create('aneel_indicador_idsp', function (Blueprint $table) {
            $table->id();
            $table->foreignId('relatorio_id')->nullable()->constrained('aneel_relatorio_rta')->onDelete('cascade');
            $table->integer('qtd');
            $table->integer('qtotal');
            $table->decimal('resultado', 5, 2);
            $table->timestamps();
        });

        Schema::create('aneel_indicador_idhw', function (Blueprint $table) {
            $table->id();
            $table->foreignId('relatorio_id')->nullable()->constrained('aneel_relatorio_rta')->onDelete('cascade');
            $table->integer('qtd');
            $table->integer('qtotal');
            $table->decimal('resultado', 5, 2);
            $table->timestamps();
        });

        Schema::create('aneel_indicador_iag', function (Blueprint $table) {
            $table->id();
            $table->foreignId('relatorio_id')->nullable()->constrained('aneel_relatorio_rta')->onDelete('cascade');
            $table->integer('qtga');
            $table->integer('qtgd');
            $table->decimal('resultado', 5, 2);
            $table->timestamps();
        });

        Schema::create('aneel_indicador_imsr', function (Blueprint $table) {
            $table->id();
            $table->foreignId('relatorio_id')->nullable()->constrained('aneel_relatorio_rta')->onDelete('cascade');
            $table->integer('qtf');
            $table->integer('qted');
            $table->integer('qtsr');
            $table->decimal('resultado', 5, 2);
            $table->timestamps();
        });

        Schema::create('aneel_indicador_iprm', function (Blueprint $table) {
            $table->id();
            $table->foreignId('relatorio_id')->nullable()->constrained('aneel_relatorio_rta')->onDelete('cascade');
            $table->integer('qtpr');
            $table->integer('qtr');
            $table->decimal('resultado', 5, 2);
            $table->timestamps();
        });

        Schema::create('aneel_indicador_iaeap', function (Blueprint $table) {
            $table->id();
            $table->foreignId('relatorio_id')->nullable()->constrained('aneel_relatorio_rta')->onDelete('cascade');
            $table->integer('qtaap');
            $table->integer('qtos');
            $table->decimal('resultado', 5, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop das tabelas seguindo o novo padrão de nomenclatura
        Schema::dropIfExists('aneel_indicador_iataa');
        Schema::dropIfExists('aneel_indicador_iet');
        Schema::dropIfExists('aneel_indicador_ita');
        Schema::dropIfExists('aneel_indicador_icir');
        Schema::dropIfExists('aneel_indicador_iaabc');
        Schema::dropIfExists('aneel_indicador_icabc');
        Schema::dropIfExists('aneel_indicador_iiap');
        Schema::dropIfExists('aneel_indicador_iiafpm');
        Schema::dropIfExists('aneel_indicador_irsap');
        Schema::dropIfExists('aneel_indicador_irsafpm');
        Schema::dropIfExists('aneel_indicador_irir');
        Schema::dropIfExists('aneel_indicador_isu');
        Schema::dropIfExists('aneel_indicador_idsp');
        Schema::dropIfExists('aneel_indicador_idhw');
        Schema::dropIfExists('aneel_indicador_iag');
        Schema::dropIfExists('aneel_indicador_imsr');
        Schema::dropIfExists('aneel_indicador_iprm');
        Schema::dropIfExists('aneel_indicador_iaeap');
    }
};
