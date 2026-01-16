<?php

namespace Modules\Ana\Http\Helpers;

use PhpOffice\PhpWord\TemplateProcessor;

class TemplateHelper
{
    /**
     * Converte texto para um formato compatível com o Word
     */
    public static function convertTextForWord($text): string
    {
        $text = strip_tags((string) $text);
        $text = htmlspecialchars($text, ENT_QUOTES | ENT_XML1, 'UTF-8');
        return str_replace("\n", '<w:br/>', $text);
    }

    public static function templateRelatorioExecutivo(string $templatePath, array $dados, bool $atualizacao = false): TemplateProcessor
    {
        $templateProcessor = new TemplateProcessor($templatePath);

        $os = $dados['ordem_servico'];
        $templateProcessor->setValue('escopo', self::convertTextForWord($dados['escopo'] ?? ''));
        $templateProcessor->setValue('os', $os->numero);
        $templateProcessor->setValue('data.inicio', (new \DateTime($os->data_inicio))->format('d/m/Y'));
        $templateProcessor->setValue('data.fim', (new \DateTime($os->data_fim))->format('d/m/Y'));
        $templateProcessor->setValue('documento', $os->documento);
        $templateProcessor->setValue('endereco', $os->endereco);
        $templateProcessor->setValue('data', now()->format('d/m/Y'));
        $templateProcessor->setValue('versao', $atualizacao ? '2' : '1');
        $templateProcessor->setValue('descricao', $atualizacao ? 'Atualização do documento' : 'Versão inicial do documento');
        $templateProcessor->setValue('responsavel', $dados['user']->name ?? '');

        $tarefas = $dados['tarefas'] ?? [];
        $evidencias = $dados['evidencias'] ?? [];
        $sei = $dados['sei'] ?? [];

        $linhas = [];
        foreach ($tarefas as $i => $tarefa) {
            $linhas[] = [
                'tarefas' => self::convertTextForWord($tarefa),
                'evidencias' => self::convertTextForWord($evidencias[$i] ?? ''),
                'sei' => self::convertTextForWord($sei[$i] ?? ''),
            ];
        }

        if (!empty($linhas)) {
            $templateProcessor->cloneRowAndSetValues('tarefas', $linhas);
        }

        $templateProcessor->setValue('titulo', self::convertTextForWord($dados['titulo'] ?? ''));
        $templateProcessor->setValue('referencias', self::convertTextForWord($dados['referencias'] ?? ''));
        $templateProcessor->setValue('atividades', self::convertTextForWord($dados['atividades'] ?? ''));

        return $templateProcessor;
    }
    

    /**
     * Preenche o template do Relatório de Faturamento com os dados fornecidos.
     */
    public static function templateRelatorioFaturamento(string $templatePath, array $dados): TemplateProcessor
    {
        $templateProcessor = new TemplateProcessor($templatePath);

        $templateProcessor->setValue('numero', $dados['numero_nota_fiscal']);
        $templateProcessor->setValue('data.hoje', now()->format('d/m/Y'));
        $templateProcessor->setValue('vencimento', (new \DateTime($dados['data_vencimento']))->format('d/m/Y'));
        $templateProcessor->setValue('data.inicio', (new \DateTime($dados['data_inicio']))->format('d/m/Y'));
        $templateProcessor->setValue('data.fim', (new \DateTime($dados['data_fim']))->format('d/m/Y'));

        foreach ($dados['ordens_servico'] as $index => $item) {
            $os = $item['modelo'];
            $valor = $item['valor'];

            $numKey = 'os' . ($index + 1);

            $templateProcessor->setValue("{$numKey}.numero", $os->numero);
            $templateProcessor->setValue("{$numKey}.valor", number_format($valor, 2, ',', '.'));

            // Concatenação dos escopos
            $escopos = $os->escopos->pluck('escopo')->map(function ($escopo) {
                return preg_replace("/\n+•/", "•", trim($escopo));
            })->implode("\n\n");

            $templateProcessor->setValue("{$numKey}.escopo", self::convertTextForWord($escopos ?: 'Escopo não definido'));
        }

        // Campos finais
        $templateProcessor->setValue('os.desconto', number_format($dados['desconto'], 2, ',', '.'));
        $templateProcessor->setValue('fatura.valor_final', number_format($dados['valor_final'], 2, ',', '.'));

        return $templateProcessor;
    }
}