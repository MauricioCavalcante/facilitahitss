<?php

namespace Modules\Aneel\Database\Seeders;

use Illuminate\Database\Seeder;

class AneelDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->call(AneelIndicatorsSeeder::class);
    }
}
