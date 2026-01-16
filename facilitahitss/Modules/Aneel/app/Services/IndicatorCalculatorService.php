<?php

namespace Modules\Aneel\Services;

use Illuminate\Support\Collection;


class IndicatorCalculatorService
{
    public static function getIndicatorMap()
    {
        return [
            1  => 'iataa',
            2  => 'iet',
            3  => 'ita',
            4  => 'icir',
            5  => 'iaabc',
            6  => 'icabc',
            7  => 'irsap',
            8  => 'irsafpm',
            9  => 'isu',
            10 => 'iiap',
            11 => 'iiafpm',
            12 => 'irir',
            13 => 'idsp',
            14 => 'idhw',
            15 => 'iag',
            16 => 'imsr',
            17 => 'iprm',
            18 => 'iaeap',
        ];
    }

    public static function calculate($indicatorId, $dados)
    {
        $indicatorMap = self::getIndicatorMap();

        if (!isset($indicatorMap[$indicatorId])) {
            throw new \Exception("Chave do indicador inválida para ID: $indicatorId");
        }

        $indicatorKey = $indicatorMap[$indicatorId];

        $calculations = [
            'iataa' => fn() => $dados['chamadas_total'] > 0 ? round(($dados['chamadas_abandonadas'] / $dados['chamadas_total']) * 100, 2) : 0,
            'iet' => fn() => $dados['chamadas_total'] > 0 ? round(($dados['chamadas_espera_60s'] / $dados['chamadas_total']) * 100, 2) : 0,
            'ita' => fn() => $dados['qtotal'] > 0 ? round(($dados['qt10'] / $dados['qtotal']) * 100, 2) : 0,
            'icir' => fn() => $dados['qtr'] > 0 ? round(($dados['qtrci'] / $dados['qtr']) * 100, 2) : 0,
            'iaabc' => fn() => $dados['qta'] > 0 ? round(($dados['qtaa'] / $dados['qta']) * 100, 2) : 0,
            'icabc' => fn() => $dados['qts'] > 0 ? round(($dados['qts_sbc'] / $dados['qts']) * 100, 2) : 0,
            'irsap' => fn() => $dados['qtre'] > 0 ? round((($dados['qtre'] - (($dados['qc1'] * 2) + ($dados['qc2'] * 1.6) + ($dados['qc3'] * 1.3) + $dados['qc4'])) / $dados['qtre']) * 100, 2) : 0,
            'irsafpm' => fn() => $dados['qtre'] > 0 ? round(((($dados['qc1'] * 2) + ($dados['qc2'] * 1.6) + ($dados['qc3'] * 1.3) + $dados['qc4']) / $dados['qtre']) * 100, 2) : 0,
            'isu' => fn() => $dados['qtotal'] > 0 ? round((($dados['qus'] + $dados['qunr']) / $dados['qtotal']) * 100, 2) : 0,
            'iiap' => fn() => $dados['qtie'] > 0 ? round(((($dados['qc1'] * 2) + ($dados['qc2'] * 1.6) + ($dados['qc3'] * 1.3) + $dados['qc4']) / $dados['qtie']) * 100, 2) : 0,
            'iiafpm' => fn() => $dados['qtie'] > 0 ? round(((($dados['qc1'] * 2) + ($dados['qc2'] * 1.6) + ($dados['qc3'] * 1.3) + $dados['qc4']) / $dados['qtie']) * 100, 2) : 0,
            'irir' => fn() => ($dados['qtr'] + $dados['qti']) > 0 ? round((($dados['qir'] + $dados['qrr']) / ($dados['qtr'] + $dados['qti'])) * 100, 2) : 0,
            'idsp' => fn() => $dados['qtotal'] > 0 ? round(($dados['qtd'] / $dados['qtotal']) * 100, 2) : 0,
            'idhw' => fn() => $dados['qtotal'] > 0 ? round(($dados['qtd'] / $dados['qtotal']) * 100, 2) : 0,
            'iag' => fn() => $dados['qtgd'] > 0 ? round(($dados['qtga'] / $dados['qtgd']) * 100, 2) : 0,
            'imsr' => fn() => $dados['qtsr'] > 0 ? round((($dados['qtsr'] - (($dados['qtf'] * 1.5) + $dados['qted'])) / $dados['qtsr']) * 100, 2) : 0,
            'iprm' => fn() => $dados['qtr'] > 0 ? round(($dados['qtpr'] / $dados['qtr']) * 100, 2) : 0,
            'iaeap' => fn() => $dados['qtos'] > 0 ? round(($dados['qtaap'] / $dados['qtos']) * 100, 2) : 0,
        ];

        return $calculations[$indicatorKey]() ?? 0;
    }

    public static function calculateFromCollection(int $indicatorId, Collection $indicators)
    {
        if ($indicators->isEmpty()) {
            throw new \Exception("Nenhum dado encontrado para o indicador ID: $indicatorId");
        }

        $dados = [];

        foreach ($indicators as $indicator) {
            $inputs = $indicator->inputs ?? [];

            foreach ($inputs as $key => $value) {
                if (!isset($dados[$key])) {
                    $dados[$key] = 0;
                }

                // Se for numérico, soma; se null ou inválido, ignora
                $dados[$key] += is_numeric($value) ? floatval($value) : 0;
            }
        }

        return self::calculate($indicatorId, $dados);
    }
}
