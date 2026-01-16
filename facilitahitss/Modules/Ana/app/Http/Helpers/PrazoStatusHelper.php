<?php

namespace Modules\Ana\Http\Helpers;

use Carbon\Carbon;
use Illuminate\Support\Facades\Http;

class PrazoStatusHelper
{
    /**
     * Obtém a lista de feriados nacionais do Brasil via API.
     *
     * @param int $ano O ano para buscar os feriados.
     * @return array Lista de feriados no formato ['YYYY-MM-DD', ...]
     */
    private static function getFeriados($ano)
    {
        try {
            $response = Http::get("https://brasilapi.com.br/api/feriados/v1/{$ano}");

            if ($response->successful()) {
                return collect($response->json())->pluck('date')->toArray(); // Retorna apenas as datas dos feriados
            }
        } catch (\Exception $e) {
            // Caso a API falhe, retorna um array vazio (o sistema continua funcionando sem feriados)
            return [];
        }

        return [];
    }

    /**
     * Calcula o prazo final de X dias úteis após uma data base, considerando feriados nacionais.
     *
     * @param string|Carbon $dataBase Data base para o cálculo
     * @param int $diasUteis Número de dias úteis a adicionar
     * @return Carbon Objeto Carbon com o prazo final
     */
    public static function calcularPrazoFinal($dataBase, $diasUteis)
    {
        $dataBase = Carbon::parse($dataBase);
        $ano = $dataBase->year;
        $feriados = self::getFeriados($ano); // Obtém feriados do Brasil via API

        $diasAdicionados = 0;
        while ($diasAdicionados < $diasUteis) {
            $dataBase->addDay();

            // Verifica se é um dia útil (segunda a sexta) e não é feriado
            if ($dataBase->isWeekday() && !in_array($dataBase->toDateString(), $feriados)) {
                $diasAdicionados++;
            }
        }

        return $dataBase->endOfDay();
    }

    /**
     * Verifica se o prazo já expirou com base na data atual.
     *
     * @param Carbon|string $prazoFinal Objeto Carbon ou string com o prazo final
     * @return bool True se o prazo expirou, False caso contrário
     */
    public static function prazoExpirado($prazoFinal)
    {
        // Converte para Carbon se necessário e ajusta para o final do dia
        $prazoFinal = $prazoFinal instanceof Carbon ? $prazoFinal : Carbon::parse($prazoFinal)->endOfDay();

        // Compara a data atual com o prazo final
        return now()->greaterThan($prazoFinal);
    }
}
