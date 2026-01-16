<?php

namespace Modules\Aneel\Http\Helpers;

use Illuminate\Support\Facades\DB;
use PhpOffice\PhpWord\TemplateProcessor;


class TemplateHelper
{
    public static function preencherTemplateRTA(string $templatePath, array $dados, array $imagens, int $reportId)
    {
        $template = new TemplateProcessor($templatePath);
        $placeholders = $template->getVariables();

        logger()->info('[RTA] Placeholders encontrados no documento:', $placeholders);

        $indicadores = DB::table('aneel_report_indicators')
            ->join('aneel_indicators', 'aneel_indicators.id', '=', 'aneel_report_indicators.indicator_id')
            ->where('report_id', $reportId)
            ->select('aneel_indicators.code', 'aneel_report_indicators.value')
            ->get();

        logger()->info('[RTA] Indicadores recuperados do banco:', $indicadores->toArray());

        foreach ($indicadores as $indicador) {
            $dados[strtolower($indicador->code)] = $indicador->value;
        }

        foreach ($placeholders as $placeholder) {
            if (array_key_exists($placeholder, $dados)) {
                $valor = $dados[$placeholder];
        
                if (is_numeric($valor)) {
                    $valor = number_format($valor, 2, ',', '');
                }
        
                $template->setValue('${' . $placeholder . '}', (string) $valor);
            }
        }
        

        foreach ($placeholders as $placeholder) {
            if (str_starts_with($placeholder, 'img_') && isset($imagens[$placeholder])) {
                $img = $imagens[$placeholder];
                $tempPath = storage_path("app/temp_{$img->nome_arquivo}");
                file_put_contents($tempPath, $img->arquivo);

                if (file_exists($tempPath)) {
                    $template->setImageValue($placeholder, [
                        'path' => $tempPath,
                        'width' => 600,
                        'height' => 400,
                    ]);
                    unlink($tempPath);
                } else {
                    $template->setValue($placeholder, 'Erro ao inserir imagem');
                }
            }
        }

        $naoPreenchidos = array_filter(
            $placeholders,
            fn($p) =>
            !array_key_exists($p, $dados) &&
                !array_key_exists($p, $imagens) &&
                !str_starts_with($p, 'img_')
        );

        if (!empty($naoPreenchidos)) {
            logger()->warning('Placeholders não preenchidos:', $naoPreenchidos);
        }

        return $template;
    }
}
