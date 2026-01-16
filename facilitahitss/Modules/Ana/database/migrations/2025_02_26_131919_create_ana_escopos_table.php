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
        Schema::create('ana_escopos', function (Blueprint $table) {
            $table->id();
            $table->text('escopo');
            $table->unsignedBigInteger('coordenacao_id');
            $table->foreign('coordenacao_id')->references('id')->on('ana_coordenacoes')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ana_escopos');
    }
};
