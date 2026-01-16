<?php

namespace Modules\Aneel\Services;

use Modules\Aneel\Models\AneelReport;
use Modules\Aneel\Services\ExcelReportService;
use Illuminate\Support\Facades\Log;

class PlanilhaService
{
    public static function gerarXlsx($report)
    {
        if (is_numeric($report)) {
            $report = AneelReport::find($report);
        }

        if (!$report instanceof AneelReport) {
            throw new \InvalidArgumentException('Parâmetro inválido: é esperado um ID ou um objeto AneelReport.');
        }

        $excelService = new ExcelReportService();
        $result = $excelService->gerarPlanilhaXlsx($report);

        if (!$result || !file_exists($result['filePath'])) {
            throw new \Exception('Arquivo XLSX não foi criado corretamente.');
        }

        $fileContent = file_get_contents($result['filePath']);
        $base64File = base64_encode($fileContent);

        $report->update([
            'xlsx_name' => $result['fileName'],
            'xlsx_attachment' => $base64File,
            'xlsx_attachment_size' => filesize($result['filePath']),
            'xlsx_mime_type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);

        unlink($result['filePath']);
    }
}
