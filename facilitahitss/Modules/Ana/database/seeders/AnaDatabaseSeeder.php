<?php

namespace Modules\Ana\Database\Seeders;

use Illuminate\Database\Seeder;

class AnaDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->call(AnaCoordenacoesSeeder::class);
        $this->call(AnaEscoposSeeder::class);
    }
}
