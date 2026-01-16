<?php

namespace Modules\Aneel\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AneelIndicatorsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()       
    {
        $indicators = [
            [
                'id' => 1,
                'code' => 'IATAA',
                'name' => 'Índice de Abandono Telefônico Antes do Atendimento',
                'description' => 'Apura o volume de chamadas telefônicas não atendidas.',
                'service_level' => '<= 5%',
                'inputs' => '["chamadas_abandonadas", "chamadas_total"]'
            ],
            [
                'id' => 2,
                'code' => 'IET',
                'name' => 'Índice de Espera Telefônica',
                'description' => 'Apura o volume de chamadas telefônicas com tempo de espera...',
                'service_level' => '<= 5%',
                'inputs' => '["chamadas_espera_60s", "chamadas_total"]'
            ],
            [
                'id' => 3,
                'code' => 'ITA',
                'name' => 'Índice de Tempo de Abertura de Chamado',
                'description' => 'Apura a quantidade de chamados com tempo de abertura...',
                'service_level' => '<= 5%',
                'inputs' => '["qt10", "qttotal"]'
            ],
            [
                'id' => 4,
                'code' => 'ICRR',
                'name' => 'Índice de Classificação Incorreta de Requisições',
                'description' => 'Apura a quantidade de Requisições classificadas incorretamente.',
                'service_level' => '<= 5%',
                'inputs' => '["qtic", "qttotal"]'
            ],
            [
                'id' => 5,
                'code' => 'IAABC',
                'name' => 'Índice de Atualização de Artigos da Base de Conhecimento',
                'description' => 'Apura o nível de atualização dos Artigos da Base de Conhecimento.',
                'service_level' => '>= 80%',
                'inputs' => '["qta", "qts"]'
            ],
            [
                'id' => 6,
                'code' => 'ICABC',
                'name' => 'Índice de Criação de Artigos da Base de Conhecimento',
                'description' => 'Apura a criação de Artigos da Base de Conhecimento.',
                'service_level' => '>= 95%',
                'inputs' => '["qts_sbc", "qts"]'
            ],
            [
                'id' => 7,
                'code' => 'IRSAP',
                'name' => 'Índice de Requisições de Serviços Atendidas no Prazo',
                'description' => 'Apura o nível de atendimento das Requisições de Serviço no prazo.',
                'service_level' => '>= 95%',
                'inputs' => '["qtc1", "qtc2", "qtc3", "qtc4"]'
            ],
            [
                'id' => 8,
                'code' => 'IRSAFM',
                'name' => 'Índice de Requisições de Serviço Atendidas Fora do Prazo Máximo',
                'description' => 'Apura o nível de atendimento de Requisições Atendidas fora do prazo máximo.',
                'service_level' => '<= 3%',
                'inputs' => '["qtc1", "qtc2", "qtc3", "qtc4"]'
            ],
            [
                'id' => 9,
                'code' => 'ISU',
                'name' => 'Índice de Satisfação dos Usuários',
                'description' => 'Apura o nível de satisfação dos Usuários de 1º e 2º nível.',
                'service_level' => '>= 95%',
                'inputs' => '["qus", "qmin"]'
            ],
            [
                'id' => 10,
                'code' => 'IAP',
                'name' => 'Índice de Incidentes Atendidos no Prazo',
                'description' => 'Apura o nível de atendimento dos Incidentes no prazo.',
                'service_level' => '<= 3%',
                'inputs' => '["qti1", "qti2", "qti3", "qti4"]'
            ],
            [
                'id' => 11,
                'code' => 'IRAFPM',
                'name' => 'Índice de Incidentes Atendidos Fora do Prazo Máximo',
                'description' => 'Apura o nível de atendimento de Incidentes Atendidos fora do prazo máximo.',
                'service_level' => '<= 3%',
                'inputs' => '["qti1", "qti2", "qti3", "qti4"]'
            ],
            [
                'id' => 12,
                'code' => 'IRR',
                'name' => 'Índice de Reabertura de Incidentes e Requisições',
                'description' => 'Apura o nível de reabertura de Incidentes e Requisições.',
                'service_level' => '<= 5%',
                'inputs' => '["qrir", "qtir"]'
            ],
            [
                'id' => 13,
                'code' => 'IDSP',
                'name' => 'Índice de Desconformidade com Sistema Patrimonial',
                'description' => 'Apura as discrepâncias entre equipamentos patrimoniais e sistema.',
                'service_level' => '<= 1%',
                'inputs' => '["qtd", "qttotal"]'
            ],
            [
                'id' => 14,
                'code' => 'IDHW',
                'name' => 'Índice de Desconformidade de Hardware',
                'description' => 'Apura as desconformidades com relação ao armazenamento do hardware.',
                'service_level' => '<= 1%',
                'inputs' => '["qtdhw", "qttotal"]'
            ],
            [
                'id' => 15,
                'code' => 'IAG',
                'name' => 'Índice de Acionamento de Garantias',
                'description' => 'Apura os níveis de serviço de acionamento de garantias.',
                'service_level' => '>= 90%',
                'inputs' => '["qtga", "qttotal"]'
            ],
            [
                'id' => 16,
                'code' => 'IMSR',
                'name' => 'Índice de Manutenção em Salas de Reunião',
                'description' => 'Apura os níveis de serviço referentes aos serviços em salas de reunião.',
                'service_level' => '>= 85%',
                'inputs' => '["qtm", "qttotal"]'
            ],
            [
                'id' => 17,
                'code' => 'IPRM',
                'name' => 'Índice de Participação em Reuniões de Mudança',
                'description' => 'Apura os níveis de serviço referentes à participação em reuniões de mudança.',
                'service_level' => '>= 100%',
                'inputs' => '["qtprm", "qttotal"]'
            ],
            [
                'id' => 18,
                'code' => 'IAEAP',
                'name' => 'Índice de Atividades Especiais Atendidas no Prazo',
                'description' => 'Apura os níveis de serviço referentes às atividades especiais atendidas no prazo.',
                'service_level' => '>= 90%',
                'inputs' => '["qtaap", "qttos"]'
            ],
        ];

        // Insere os dados na tabela, ignorando duplicatas se já existirem (upsert)
        DB::table('aneel_indicators')->upsert($indicators, ['id', 'code']);
    }
}