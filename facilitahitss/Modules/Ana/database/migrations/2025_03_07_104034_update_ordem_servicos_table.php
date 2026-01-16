<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('ana_ordem_servicos', function (Blueprint $table) {
            $table->integer('prazo')->after('data_fim')->default(6);
        });
    }

    public function down()
    {
        Schema::table('ana_ordem_servicos', function (Blueprint $table) {
            $table->dropColumn('prazo');
        });
    }
};
