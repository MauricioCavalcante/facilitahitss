<?php

namespace Modules\Aneel\Http\Helpers;

class IndicadoresHelper
{
    /**
     * Retorna a lista de tabelas de indicadores no banco de dados.
     */
    public static function getTabelasIndicadores()
    {
        return [
            'iataa' => 'aneel_indicador_iataa',
            'iet' => 'aneel_indicador_iet',
            'ita' => 'aneel_indicador_ita',
            'icir' => 'aneel_indicador_icir',
            'iaabc' => 'aneel_indicador_iaabc',
            'icabc' => 'aneel_indicador_icabc',
            'irsap' => 'aneel_indicador_irsap',
            'irsafpm' => 'aneel_indicador_irsafpm',
            'isu' => 'aneel_indicador_isu',
            'iiap' => 'aneel_indicador_iiap',
            'iiafpm' => 'aneel_indicador_iiafpm',
            'irir' => 'aneel_indicador_irir',
            'idsp' => 'aneel_indicador_idsp',
            'idhw' => 'aneel_indicador_idhw',
            'iag' => 'aneel_indicador_iag',
            'imsr' => 'aneel_indicador_imsr',
            'iprm' => 'aneel_indicador_iprm',
            'iaeap' => 'aneel_indicador_iaeap',
        ];
    }

    /**
     * Retorna os labels dos indicadores.
     */
    public static function getNomesIndicadores()
    {
        return [
            'iataa' => 'Índice de Abandono Telefônico Antes do Atendimento (IATAA)',
            'iet' => 'Índice de Espera Telefônica (IET)',
            'ita' => 'Índice de Tempo de Abertura de Chamado (ITA)',
            'icir' => 'Índice de Classificação Incorreta de Requisições (ICIR)',
            'iaabc' => 'Índice de Atualização de Artigos da Base de Conhecimento (IAABC)',
            'icabc' => 'Índice de Criação de Artigos da Base de Conhecimento (ICABC)',
            'irsap' => 'Índice de Requisições de Serviço Atendidas no Prazo (IRSAP)',
            'irsafpm' => 'Índice de Requisições de Serviço Atendidas Fora do Prazo Máximo (IRSAFPM)',
            'isu' => 'Índice de Satisfação dos Usuários (ISU)',
            'iiap' => 'Índice de Incidentes Atendidos no Prazo (IIAP)',
            'iiafpm' => 'Índice de Incidentes Atendidos Fora do Prazo Máximo (IIAFPM)',
            'irir' => 'Índice de Reabertura de Incidentes e Requisições (IRIR)',
            'idsp' => 'Índice de Desconformidade com Sistema Patrimonial (IDSP)',
            'idhw' => 'Índice de Desconformidade de Hardware (IDHW)',
            'iag' => 'Índice de Acionamento de Garantias (IAG)',
            'imsr' => 'Índice de Manutenção em Salas de Reunião (IMSR)',
            'iprm' => 'Índice de Participação em Reuniões de Mudança (IPRM)',
            'iaeap' => 'Índice de Atividades Especiais Atendidas no Prazo (IAEAP)',
        ];
    }

    /**
     * Retorna os labels dos inputs utilizados nos cálculos dos indicadores.
     */
    public static function getLabels()
    {
        return [
            'chamadas_abandonadas' => 'Chamadas Abandonadas',
            'chamadas_total' => 'Total de Chamadas',
            'chamadas_espera_60s' => 'Chamadas com Espera > 60s',
            'qt10' => 'Quantidade de Chamados com Tempo > 10 min',
            'qtotal' => 'Quantidade Total de Chamados',
            'qtrci' => 'Quantidade de Requisições Classificadas Incorretamente',
            'qtr' => 'Quantidade Total de Requisições',
            'qtaa' => 'Quantidade de Artigos Atualizados',
            'qta' => 'Quantidade Total de Artigos',
            'qts_sbc' => 'Quantidade de Serviços sem Artigos',
            'qts' => 'Quantidade Total de Serviços',
            'qtie' => 'Quantidade de Incidentes Encerrados',
            'qc1' => 'Incidentes Críticos Fora do Prazo',
            'qc2' => 'Incidentes Alta Prioridade Fora do Prazo',
            'qc3' => 'Incidentes Média Prioridade Fora do Prazo',
            'qc4' => 'Incidentes Baixa Prioridade Fora do Prazo',
            'qir' => 'Incidentes Reabertos',
            'qrr' => 'Requisições Reabertas',
            'qus' => 'Usuários Satisfeitos',
            'qunr' => 'Usuários que Não Responderam',
            'qtd' => 'Quantidade de Equipamentos com Discrepância',
            'qtga' => 'Quantidade de Garantias Acionadas',
            'qtgd' => 'Quantidade de Garantias Disponíveis',
            'qtf' => 'Quantidade Total de Falhas',
            'qted' => 'Quantidade Total de Equipamentos em Desconformidade',
            'qtsr' => 'Quantidade Total de Salas de Reunião',
            'qtpr' => 'Quantidade Total de Presenças em Reuniões',
            'qtos' => 'Quantidade de Atividades Demandadas por OS',
            'qtaap' => 'Quantidade de Atividades Atendidas no Prazo',
            'qtre' => 'Quantidade de Requisições de Serviços Encerradas',
            'qti' => 'Quantidade de Incidentes Encerrados',
        ];
    }

    /**
     * Retorna a lista de inputs necessários para cada indicador.
     */
    public static function getInputs()
    {
        return [
            'iataa' => ['chamadas_abandonadas', 'chamadas_total'],
            'iet' => ['chamadas_espera_60s', 'chamadas_total'],
            'ita' => ['qt10', 'qtotal'],
            'icir' => ['qtrci', 'qtr'],
            'iaabc' => ['qtaa', 'qta'],
            'icabc' => ['qts_sbc', 'qts'],
            'irsap' => ['qtre', 'qc1', 'qc2', 'qc3', 'qc4'],
            'irsafpm' => ['qtre', 'qc1', 'qc2', 'qc3', 'qc4'],
            'isu' => ['qus', 'qunr', 'qtotal'],
            'iiap' => ['qtie', 'qc1', 'qc2', 'qc3', 'qc4'],
            'iiafpm' => ['qtie', 'qc1', 'qc2', 'qc3', 'qc4'],
            'irir' => ['qir', 'qrr', 'qtr', 'qti'],
            'idsp' => ['qtd', 'qtotal'],
            'idhw' => ['qtd', 'qtotal'],
            'iag' => ['qtga', 'qtgd'],
            'imsr' => ['qtf', 'qted', 'qtsr'],
            'iprm' => ['qtpr', 'qtr'],
            'iaeap' => ['qtaap', 'qtos'],
        ];
    }

    /**
     * Retorna as regras de validação dos indicadores.
     */
    public static function getRegrasValidacao($indicador)
    {
        return [
            'iataa' => [
                'chamadas_abandonadas' => 'nullable|integer|min:0',
                'chamadas_total' => 'nullable|integer|min:0'
            ],
            'iet' => [
                'chamadas_espera_60s' => 'nullable|integer|min:0',
                'chamadas_total' => 'nullable|integer|min:0'
            ],
            'ita' => [
                'qt10' => 'nullable|integer|min:0',
                'qtotal' => 'nullable|integer|min:0'
            ],
            'icir' => [
                'qtrci' => 'nullable|integer|min:0',
                'qtr' => 'nullable|integer|min:0'
            ],
            'iaabc' => [
                'qtaa' => 'nullable|integer|min:0',
                'qta' => 'nullable|integer|min:0'
            ],
            'icabc' => [
                'qts_sbc' => 'nullable|integer|min:0',
                'qts' => 'nullable|integer|min:0'
            ],
            'irsap' => [
                'qtre' => 'nullable|integer|min:0',
                'qc1' => 'nullable|integer|min:0',
                'qc2' => 'nullable|integer|min:0',
                'qc3' => 'nullable|integer|min:0',
                'qc4' => 'nullable|integer|min:0'
            ],
            'irsafpm' => [
                'qtre' => 'nullable|integer|min:0',
                'qc1' => 'nullable|integer|min:0',
                'qc2' => 'nullable|integer|min:0',
                'qc3' => 'nullable|integer|min:0',
                'qc4' => 'nullable|integer|min:0'
            ],
            'isu' => [
                'qus' => 'nullable|integer|min:0',
                'qunr' => 'nullable|integer|min:0',
                'qtotal' => 'nullable|integer|min:0'
            ],
            'iiap' => [
                'qtie' => 'nullable|integer|min:0',
                'qc1' => 'nullable|integer|min:0',
                'qc2' => 'nullable|integer|min:0',
                'qc3' => 'nullable|integer|min:0',
                'qc4' => 'nullable|integer|min:0'
            ],
            'iiafpm' => [
                'qtie' => 'nullable|integer|min:0',
                'qc1' => 'nullable|integer|min:0',
                'qc2' => 'nullable|integer|min:0',
                'qc3' => 'nullable|integer|min:0',
                'qc4' => 'nullable|integer|min:0'
            ],
            'irir' => [
                'qir' => 'nullable|integer|min:0',
                'qrr' => 'nullable|integer|min:0',
                'qtr' => 'nullable|integer|min:0',
                'qti' => 'nullable|integer|min:0'
            ],
            'idsp' => [
                'qtd' => 'nullable|integer|min:0',
                'qtotal' => 'nullable|integer|min:0'
            ],
            'idhw' => [
                'qtd' => 'nullable|integer|min:0',
                'qtotal' => 'nullable|integer|min:0'
            ],
            'iag' => [
                'qtga' => 'nullable|integer|min:0',
                'qtgd' => 'nullable|integer|min:0'
            ],
            'imsr' => [
                'qtf' => 'nullable|integer|min:0',
                'qted' => 'nullable|integer|min:0',
                'qtsr' => 'nullable|integer|min:0'
            ],
            'iprm' => [
                'qtpr' => 'nullable|integer|min:0',
                'qtr' => 'nullable|integer|min:0'
            ],
            'iaeap' => [
                'qtaap' => 'nullable|integer|min:0',
                'qtos' => 'nullable|integer|min:0'
            ],
        ][$indicador] ?? [];
    }

    /**
     * Retorna o nível de serviço esperado para cada indicador.
     */
    public static function getNivelServico($indicador)
    {
        return [
            'iataa' => 5,
            'iet' => 5,
            'ita' => 5,
            'icir' => 5,
            'iaabc' => 80,
            'icabc' => 95,
            'irsap' => 95,
            'irsafpm' => 3,
            'isu' => 95,
            'iiap' => 3,
            'iiafpm' => 3,
            'irir' => 5,
            'idsp' => 1,
            'idhw' => 1,
            'iag' => 90,
            'imsr' => 85,
            'iprm' => 100,
            'iaeap' => 90,
        ][$indicador] ?? null;
    }

    /**
     * Calcula o resultado do indicador.
     */
    public static function calcularResultado($dados, $indicador)
    {
        return match ($indicador) {

        'iataa' => $dados['chamadas_total'] > 0
            ? round(($dados['chamadas_abandonadas'] / $dados['chamadas_total']) * 100, 1)
            : 0,

        'iet' => $dados['chamadas_total'] > 0
            ? round(($dados['chamadas_espera_60s'] / $dados['chamadas_total']) * 100, 1)
            : 0,

        'ita' => $dados['qtotal'] > 0
            ? round(($dados['qt10'] / $dados['qtotal']) * 100, 1)
            : 0,

        'icir' => $dados['qtr'] > 0
            ? round(($dados['qtrci'] / $dados['qtr']) * 100, 1)
            : 0,

        'iaabc' => $dados['qta'] > 0
            ? round(($dados['qtaa'] / $dados['qta']) * 100, 1)
            : 0,

        'icabc' => $dados['qts'] > 0
            ? round(($dados['qts_sbc'] / $dados['qts']) * 100, 1)
            : 0,

        'irsap' => $dados['qtre'] > 0
            ? round(($dados['qtre'] - (($dados['qc1'] * 2) + ($dados['qc2'] * 1.6) + ($dados['qc3'] * 1.3) + $dados['qc4'])) / $dados['qtre'] * 100, 1)
            : 0,

        'irsafpm' => $dados['qtre'] > 0
            ? round((($dados['qc1'] * 2) + ($dados['qc2'] * 1.6) + ($dados['qc3'] * 1.3) + $dados['qc4']) / $dados['qtre'] * 100, 1)
            : 0,

        'isu' => $dados['qtotal'] > 0
            ? round((($dados['qus'] + $dados['qunr']) / $dados['qtotal']) * 100, 1)
            : 0,

        'iiap' => $dados['qtie'] > 0
            ? round(($dados['qtie'] - (($dados['qc1'] * 2) + ($dados['qc2'] * 1.6) + ($dados['qc3'] * 1.3) + $dados['qc4'])) / $dados['qtie'] * 100, 1)
            : 0,

        'iiafpm' => $dados['qtie'] > 0
            ? round((($dados['qc1'] * 2) + ($dados['qc2'] * 1.6) + ($dados['qc3'] * 1.3) + $dados['qc4']) / $dados['qtie'] * 100, 1)
            : 0,

        'irir' => ($dados['qtr'] + $dados['qti']) > 0
            ? round((($dados['qir'] + $dados['qrr']) / ($dados['qtr'] + $dados['qti'])) * 100, 1)
            : 0,

        'idsp', 'idhw' => $dados['qtotal'] > 0
            ? round(($dados['qtd'] / $dados['qtotal']) * 100, 1)
            : 0,

        'iag' => $dados['qtgd'] > 0
            ? round(($dados['qtga'] / $dados['qtgd']) * 100, 1)
            : 0,

        'imsr' => $dados['qtsr'] > 0
            ? round((($dados['qtsr'] - (($dados['qtf'] * 1.5) + $dados['qted'])) / $dados['qtsr']) * 100, 1)
            : 0,

        'iprm' => $dados['qtr'] > 0
            ? round(($dados['qtpr'] / $dados['qtr']) * 100, 1)
            : 0,

        'iaeap' => $dados['qtos'] > 0
            ? round(($dados['qtaap'] / $dados['qtos']) * 100, 1)
            : 0,

        default => 0,
        };
    }

    /**
     * Gera mensagem de nível de serviço para um indicador.
     */
    public static function gerarMensagemNivelServico($indicador, $resultado)
    {
        $nivelEsperado = self::getNivelServico($indicador);

        if ($nivelEsperado === null) {
            return 'Nível de serviço não definido para este indicador.';
        }

        // Define o comparador com base no tipo do indicador
        $comparador = match ($indicador) {
            'iataa', 'iet', 'ita', 'icir', 'iiafpm', 'irsafpm', 'irir', 'idsp', 'idhw', 'iiap' => '<=',
            'iaabc', 'icabc', 'irsap', 'isu', 'iag', 'imsr', 'iaeap' => '>=',
            'iprm' => '==',
            default => null,
        };

        if (!$comparador) {
            return 'Comparação inválida.';
        }

        // Verifica se o resultado atinge o nível esperado
        $atingiu = match ($comparador) {
            '<=' => $resultado <= $nivelEsperado,
            '>=' => $resultado >= $nivelEsperado,
            '==' => $resultado == $nivelEsperado,
            default => false,
        };

        // Retorna a mensagem com o status
        return $atingiu
            ? "O resultado atingiu o nível de serviço esperado."
            : "O resultado não atingiu o nível de serviço esperado.";
    }

    /**
     * Retorna a descrição dos indicadores.
     */
    public static function getDescricao()
    {
        return [
            'iataa' => 'Apura o volume de chamadas telefônicas não atendidas pelo 1º Nível',
            'iet' => 'Apura o volume de chamadas telefônicas com tempo de espera superior a 60 segundos',
            'ita' => 'Apura a quantidade de chamados com tempo de abertura superior a 10 minutos, envolvendo o registro, classificação, categorização e análise',
            'icir' => 'Apura a quantidade de Requisições classificados incorretamente',
            'iaabc' => 'Apura o nível de atualização dos Artigos da Base de Conhecimento',
            'icabc' => 'Apura a criação de Artigos da Base de Conhecimento',
            'irsap' => 'Apura o nível de atendimento das Requisições de Serviço atendidas no prazo',
            'irsafpm' => 'Apura o nível de atendimento de Requisições atendidas fora do prazo máximo',
            'isu' => 'Apura o nível de satisfação dos usuários de 1° e 2° Níveis',
            'iiap' => 'Apura o nível de atendimento de Requisições atendidas fora do prazo máximo',
            'iiafpm' => 'Apura o nível de atendimento de Incidentes atendidos fora do prazo máximo',
            'irir' => 'Apura o nível de satisfação dos usuários de 1° e 2° Níveis',
            'idsp' => 'Apura as discrepâncias entre equipamentos patrimoniados no Sistema de Gestão Patrimonial da Contratante e o sistema de ITSM',
            'idhw' => 'Apura as desconformidades com relação ao armazenamento, classificação, organização, quantitativo e localização de ativos não patrimoniados, em Sala de Ativos sob responsabilidade da Superintendência de Gestão Técnica da Informação (SGI)',
            'iag' => 'Apura os níveis de serviço de acionamento de garantia de equipamentos de TI, referentes à plataforma Desktop, independente de patrimônio',
            'imsr' => 'Apura os níveis de serviço referentes aos serviços de manutenção em salas de reunião da Contratante',
            'iprm' => 'Apura os níveis de serviço referentes à participação em Reuniões de Mudanças',
            'iaeap' => 'Apura os níveis de serviço referentes às atividades executadas por cronograma acordado entre a CONTRATANTE e a CONTRATADA',
        ];
    }
}
