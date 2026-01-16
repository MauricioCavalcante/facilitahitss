<?php

namespace Modules\Aneel\Services;

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Modules\Aneel\Models\AneelReport;
use Modules\Aneel\Models\AneelIndicator;
use Modules\Aneel\Models\AneelReportIndicator;

class ExcelReportService
{

    public function gerarPlanilhaXlsx(AneelReport $report)
    {
        $mes = ucfirst($report->period_start->locale('pt_BR')->translatedFormat('F'));
        $ano = $report->period_start->year;
        $templatePath = public_path("modelos/Calculo_dos_Indicadores.xlsx");

        if (!file_exists($templatePath)) {
            Log::error("[RTA] Template de planilha não encontrado em: {$templatePath}");
            return false;
        }

        try {
            $spreadsheet = IOFactory::load($templatePath);
        } catch (\Exception $e) {
            Log::error("[RTA] Erro ao carregar o template: " . $e->getMessage());
            return false;
        }

        $indicators = AneelReportIndicator::where('report_id', $report->id)->get();
        Log::info("[RTA] Indicadores recuperados: " . $indicators->count());

        foreach ($indicators as $indicador) {
            $indicadorDetalhes = AneelIndicator::find($indicador->indicator_id);

            if (!$indicadorDetalhes) {
                Log::warning("[RTA] Indicador não encontrado para o ID: {$indicador->indicator_id}");
                continue;
            }

            $codigo = strtoupper($indicadorDetalhes->code);
            $sheetName = "Indicador {$indicador->indicator_id} ({$codigo})";

            $sheet = $spreadsheet->getSheetByName($sheetName);

            if (!$sheet) {
                Log::warning("[RTA] Aba '{$sheetName}' não encontrada no template.");
                continue;
            }

            $inputs = json_decode($indicador->inputs, true);
            $linha = 3; // Começar da linha C3

            if (is_array($inputs) && count($inputs)) {
                foreach ($inputs as $value) {
                    $sheet->setCellValue("C{$linha}", $value);
                    $linha++;
                }
            } else {
                $sheet->setCellValue("C3", '-');
            }
        }

        $outputFileName = "Calculo_dos_Indicadores_{$mes}_{$ano}.xlsx";
        $outputPath = storage_path('app/temp/' . $outputFileName);

        if (!is_dir(storage_path('app/temp'))) {
            mkdir(storage_path('app/temp'), 0777, true);
        }

        try {
            $writer = new Xlsx($spreadsheet);
            $writer->save($outputPath);
            return [
                'filePath' => $outputPath,
                'fileName' => $outputFileName
            ];
        } catch (\Exception $e) {
            Log::error("[RTA] Erro ao salvar o arquivo gerado: " . $e->getMessage());
            return false;
        }
    }
}
