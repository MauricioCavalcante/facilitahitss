<?php

namespace Modules\Ana\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AnaCoordenacoesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Define os dados a serem inseridos
        $coordenacoes = [
            [
                'codigo' => 'COGED', 
                'nome' => 'Coordenação de Gestão de Dados', 
                'created_at' => '2025-03-05 12:27:21', 
                'updated_at' => '2025-03-05 12:27:21'
            ],
            [
                'codigo' => 'COGTI', 
                'nome' => 'Coordenação de Governança de Tecnologia da Informação', 
                'created_at' => '2025-03-05 12:27:21', 
                'updated_at' => '2025-03-05 12:27:21'
            ],
            [
                'codigo' => 'COOPI', 
                'nome' => 'Coordenação de Operação de Infraestrutura de Tecnologia da Informação', 
                'created_at' => '2025-03-05 12:27:21', 
                'updated_at' => '2025-03-05 12:27:21'
            ],
            [
                'codigo' => 'COSIC', 
                'nome' => 'Coordenação de Segurança da Informação e Comunicações', 
                'created_at' => '2025-03-05 12:27:21', 
                'updated_at' => '2025-03-05 12:27:21'
            ],
            [
                'codigo' => 'COSIS', 
                'nome' => 'Coordenação de Sistemas e Soluções', 
                'created_at' => '2025-03-05 12:27:21', 
                'updated_at' => '2025-03-05 12:27:21'
            ],
            [
                'codigo' => 'CPLAC', 
                'nome' => 'Coordenação de Planejamento e Contratos', 
                'created_at' => '2025-03-05 12:27:21', 
                'updated_at' => '2025-03-20 22:31:18'
            ],
            [
                'codigo' => 'ASGOV', 
                'nome' => 'Assessoria de Governança', 
                'created_at' => '2025-03-05 12:27:21', 
                'updated_at' => '2025-03-05 12:27:21'
            ],
        ];

        DB::table('ana_coordenacoes')->upsert($coordenacoes, ['codigo']);
    }
}
