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
        Schema::create('modules', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('description')->nullable();
            $table->timestamps();
        });

        DB::table('modules')->insert([
            [
                'name' => 'ANA',
                'description' => 'Módulo para gerenciamento dos relatórios da ANA',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'ANEEL',
                'description' => 'Módulo para gerenciamento dos relatórios da ANEEL',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('modules');
    }
};
