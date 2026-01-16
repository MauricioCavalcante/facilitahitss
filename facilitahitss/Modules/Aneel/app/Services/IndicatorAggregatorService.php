<?php

namespace Modules\Aneel\Services;

use Modules\Aneel\Models\AneelReportIndicator;
use Modules\Aneel\Models\AneelIndicator;

class IndicatorAggregatorService
{
    protected static function getRecordsByIndicatorCode(string $indicatorCode, string $startDate, string $endDate)
    {
        $indicator = AneelIndicator::where('code', strtoupper($indicatorCode))->firstOrFail();

return AneelReportIndicator::where('indicator_id', $indicator->id)
    ->whereBetween('period_start', [$startDate, $endDate])
    ->get();

    }

    protected static function getIndicadoresConfig(): array
    {
        return [
            'IATAA' => [
                'campos' => ['chamadas_total', 'chamadas_abandonadas'],
                'calculo' => fn($d) => $d['chamadas_total'] > 0 ? round(($d['chamadas_abandonadas'] / $d['chamadas_total']) * 100, 2) : 0,
            ],
            'IET' => [
                'campos' => ['chamadas_total', 'chamadas_espera_60s'],
                'calculo' => fn($d) => $d['chamadas_total'] > 0 ? round(($d['chamadas_espera_60s'] / $d['chamadas_total']) * 100, 2) : 0,
            ],
            'ITA' => [
                'campos' => ['qtotal', 'qt10'],
                'calculo' => fn($d) => $d['qtotal'] > 0 ? round(($d['qt10'] / $d['qtotal']) * 100, 2) : 0,
            ],
            'ICIR' => [
                'campos' => ['qtr', 'qtrci'],
                'calculo' => fn($d) => $d['qtr'] > 0 ? round(($d['qtrci'] / $d['qtr']) * 100, 2) : 0,
            ],
            'IAABC' => [
                'campos' => ['qta', 'qtaa'],
                'calculo' => fn($d) => $d['qta'] > 0 ? round(($d['qtaa'] / $d['qta']) * 100, 2) : 0,
            ],
            'ICABC' => [
                'campos' => ['qts', 'qts_sbc'],
                'calculo' => fn($d) => $d['qts'] > 0 ? round(($d['qts_sbc'] / $d['qts']) * 100, 2) : 0,
            ],
            'IRSAP' => [
                'campos' => ['qtre', 'qc1', 'qc2', 'qc3', 'qc4'],
                'calculo' => fn($d) => $d['qtre'] > 0 ? round((($d['qtre'] - ($d['qc1'] * 2 + $d['qc2'] * 1.6 + $d['qc3'] * 1.3 + $d['qc4'])) / $d['qtre']) * 100, 2) : 0,
            ],
            'IRSAFPM' => [
                'campos' => ['qtre', 'qc1', 'qc2', 'qc3', 'qc4'],
                'calculo' => fn($d) => $d['qtre'] > 0 ? round((($d['qc1'] * 2 + $d['qc2'] * 1.6 + $d['qc3'] * 1.3 + $d['qc4']) / $d['qtre']) * 100, 2) : 0,
            ],
            'ISU' => [
                'campos' => ['qtotal', 'qus', 'qunr'],
                'calculo' => fn($d) => $d['qtotal'] > 0 ? round((($d['qus'] + $d['qunr']) / $d['qtotal']) * 100, 2) : 0,
            ],
            'IIAP' => [
                'campos' => ['qtie', 'qc1', 'qc2', 'qc3', 'qc4'],
                'calculo' => fn($d) => $d['qtie'] > 0 ? round((($d['qc1'] * 2 + $d['qc2'] * 1.6 + $d['qc3'] * 1.3 + $d['qc4']) / $d['qtie']) * 100, 2) : 0,
            ],
            'IIAFPM' => [
                'campos' => ['qtie', 'qc1', 'qc2', 'qc3', 'qc4'],
                'calculo' => fn($d) => $d['qtie'] > 0 ? round((($d['qc1'] * 2 + $d['qc2'] * 1.6 + $d['qc3'] * 1.3 + $d['qc4']) / $d['qtie']) * 100, 2) : 0,
            ],
            'IRIR' => [
                'campos' => ['qtr', 'qti', 'qir', 'qrr'],
                'calculo' => fn($d) => ($d['qtr'] + $d['qti']) > 0 ? round((($d['qir'] + $d['qrr']) / ($d['qtr'] + $d['qti'])) * 100, 2) : 0,
            ],
            'IDSP' => [
                'campos' => ['qtd', 'qtotal'],
                'calculo' => fn($d) => $d['qtotal'] > 0 ? round(($d['qtd'] / $d['qtotal']) * 100, 2) : 0,
            ],
            'IDHW' => [
                'campos' => ['qtd', 'qtotal'],
                'calculo' => fn($d) => $d['qtotal'] > 0 ? round(($d['qtd'] / $d['qtotal']) * 100, 2) : 0,
            ],
            'IAG' => [
                'campos' => ['qtga', 'qtgd'],
                'calculo' => fn($d) => $d['qtgd'] > 0 ? round(($d['qtga'] / $d['qtgd']) * 100, 2) : 0,
            ],
            'IMSR' => [
                'campos' => ['qtf', 'qted', 'qtsr'],
                'calculo' => fn($d) => $d['qtsr'] > 0 ? round((($d['qtsr'] - (($d['qtf'] * 1.5) + $d['qted'])) / $d['qtsr']) * 100, 2) : 0,
            ],
            'IPRM' => [
                'campos' => ['qtpr', 'qtr'],
                'calculo' => fn($d) => $d['qtr'] > 0 ? round(($d['qtpr'] / $d['qtr']) * 100, 2) : 0,
            ],
            'IAEAP' => [
                'campos' => ['qtaap', 'qtos'],
                'calculo' => fn($d) => $d['qtos'] > 0 ? round(($d['qtaap'] / $d['qtos']) * 100, 2) : 0,
            ],
        ];
    }

    public static function calcular(string $codigo, string $startDate, string $endDate): float
    {
        $config = self::getIndicadoresConfig()[$codigo] ?? null;

        if (!$config) {
            throw new \InvalidArgumentException("Indicador '$codigo' não está configurado.");
        }

        $registros = self::getRecordsByIndicatorCode($codigo, $startDate, $endDate);
        $acumulados = array_fill_keys($config['campos'], 0);

        foreach ($registros as $registro) {
            $input = json_decode($registro->inputs, true);
            foreach ($config['campos'] as $campo) {
                $acumulados[$campo] += $input[$campo] ?? 0;
            }
        }

        return ($config['calculo'])($acumulados);
    }
}
