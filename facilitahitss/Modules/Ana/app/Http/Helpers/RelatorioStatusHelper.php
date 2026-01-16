<?php

namespace Modules\Ana\Http\Helpers;

use Modules\Ana\Models\AnaRelatorioExecutivoJustificativa;
use Modules\Ana\Models\AnaRelatorioExecutivo;
use Modules\Ana\Http\Helpers\PrazoStatusHelper;

class RelatorioStatusHelper
{
    /**
     * Verifica o status de um relatório para um usuário em uma Ordem de Serviço em andamento.
     *
     * @param object $user Usuário autenticado.
     * @param object $osEmAndamento Ordem de Serviço em andamento.
     * @return array Retorna um array contendo informações sobre o prazo final, se está expirado e a mensagem de status.
     */
    public static function verificarStatus($user, $osEmAndamento)
    {
        // Obtém o prazo definido na OS (valor entre 1 e 10 dias)
        $prazoDias = $osEmAndamento->prazo ?? 6; // Usa 6 dias como padrão se não houver valor definido

        // Calcula o prazo final para entrega do relatório
        $prazoFinal = PrazoStatusHelper::calcularPrazoFinal($osEmAndamento->data_fim, $prazoDias);

        // Verifica se o prazo já expirou
        $prazoExpirado = PrazoStatusHelper::prazoExpirado($prazoFinal);

        // Formata a data final no formato "dd/mm/aaaa às 23:59"
        $prazoFinalFormatado = $prazoFinal->format('d/m/Y') . ' às 23:59';

        // Verifica se já existe um relatório para essa OS e usuário
        $relatorioExistente = AnaRelatorioExecutivo::where('user_id', $user->id)
            ->where('ordem_servico_id', $osEmAndamento->id)
            ->exists();

        // Busca a justificativa mais recente do usuário para essa OS
        $justificativa = AnaRelatorioExecutivoJustificativa::where('user_id', $user->id)
            ->where('os_id', $osEmAndamento->id)
            ->latest() // Pega a última justificativa cadastrada
            ->first();

        // Retorna um array com as informações calculadas e a mensagem de status
        return [
            'prazoFinalFormatado' => $prazoFinalFormatado,
            'prazoExpirado' => $prazoExpirado,
            'statusMensagem' => self::determinarMensagem($relatorioExistente, $prazoExpirado, $justificativa, $osEmAndamento, $prazoFinalFormatado)
        ];
    }

    /**
     * Determina a mensagem de status do relatório com base no prazo, existência de relatório e justificativas.
     *
     * @param bool $relatorioExistente Se o relatório já existe.
     * @param bool $prazoExpirado Se o prazo já expirou.
     * @param object|null $justificativa Última justificativa do usuário, se existir.
     * @param object $osEmAndamento Ordem de Serviço em andamento.
     * @param string $prazoFinalFormatado Data final formatada para exibição.
     * @return array|null Retorna um array com a mensagem e o tipo de alerta ou `null` se o relatório já existir.
     */
    private static function determinarMensagem($relatorioExistente, $prazoExpirado, $justificativa, $osEmAndamento, $prazoFinalFormatado)
    {
        // Se o relatório já existe, não há necessidade de exibir uma mensagem
        if ($relatorioExistente) {
            return null;
        }

        // Caso o prazo tenha expirado e não exista justificativa, exibe alerta para criar justificativa
        if ($prazoExpirado && !$justificativa) {
            return [
                'type' => 'danger',
                'message' => 'Você perdeu o prazo para criar o relatório. <a href="' . route('ana::justificativas.criar', $osEmAndamento->id) . '">Clique aqui</a> para justificar o atraso.'
            ];
        }

        // Se houver justificativa pendente de aprovação
        if ($justificativa && $justificativa->status === 'Pendente') {
            return [
                'type' => 'info',
                'message' => 'Sua justificativa foi enviada e está aguardando validação do gestor.'
            ];
        }

        // Se a justificativa foi aprovada ou sancionada, permite a criação do relatório
        if ($justificativa && in_array($justificativa->status, ['Aprovada', 'Sancionada'])) {
            return [
                'type' => 'success',
                'message' => 'Sua justificativa foi ' . strtolower($justificativa->status) . '! <a href="' . route('ana::relatorio_executivo.criar', ['os_id' => $osEmAndamento->id]) . '">Clique aqui</a> para criar seu relatório.'
            ];
        }

        // Caso contrário, exibe mensagem informando o prazo para criação do relatório
        return [
            'type' => 'warning',
            'message' => 'O prazo para a criação de relatórios termina em ' . $prazoFinalFormatado . '. O não cumprimento desse prazo deverá ser autorizado previamente pelo gestor.'
        ];
    }

    /**
     * Obtém a mensagem de alerta para administradores sobre justificativas pendentes.
     *
     * Este método verifica a quantidade de justificativas com status "Pendente"
     * e retorna uma mensagem formatada para exibição, caso existam pendências.
     *
     * @return array|null Retorna um array contendo o tipo de alerta e a mensagem, ou `null` se não houver justificativas pendentes.
     */
    public static function obterMensagemJustificativasPendentes()
    {
        // Contar quantas justificativas estão pendentes
        $justificativasPendentes = AnaRelatorioExecutivoJustificativa::where('status', 'Pendente')->count();

        // Se houver justificativas pendentes, retorna a mensagem
        if ($justificativasPendentes > 0) {
            return [
                'type' => 'warning',
                'message' => "Você tem <strong>{$justificativasPendentes}</strong> justificativa(s) aguardando análise. <a href='" . route('ana::justificativas.index') . "'>Clique aqui</a> para vê-las."
            ];
        }

        return null;
    }
}
