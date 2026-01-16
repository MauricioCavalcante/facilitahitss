<?php

namespace Modules\Aneel\Http\Controllers;

use ZipArchive;
use Carbon\Carbon;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\Aneel\Models\AneelReport;
use Modules\Aneel\Models\AneelIndicator;
use Modules\Aneel\Models\AneelReportIndicator;
use Modules\Aneel\Models\AneelReportAttachment;
use Modules\Aneel\Services\PlanilhaService;
use Modules\Aneel\Http\Helpers\IndicatorCardHelper;

class AneelController extends Controller
{

    // Dashboard
    public function index(Request $request)
    {
        $latestReport = AneelReport::orderByDesc('period_start')->first();

        $lastPeriod = $latestReport
            ? $latestReport->period_start->copy()->startOfMonth()
            : now()->startOfMonth();

        $sixMonthsAgo = $lastPeriod->copy()->subMonths(5)->startOfMonth();

        $hasFilter = $request->filled('start') && $request->filled('end');

        $startDate = $hasFilter
            ? Carbon::parse($request->input('start'))->startOfMonth()
            : $lastPeriod;

        $endDate = $hasFilter
            ? Carbon::parse($request->input('end'))->endOfMonth()
            : $lastPeriod->copy()->endOfMonth();

        $lastMonthStart = $lastPeriod->copy()->startOfMonth()->format('Y-m-d');
        $lastMonthEnd = $lastPeriod->copy()->endOfMonth()->format('Y-m-d');

        if ($hasFilter) {
            $reportIds = AneelReport::whereBetween('period_start', [$startDate, $endDate])
                ->pluck('id');

            $indicators = AneelReportIndicator::with('indicator')
                ->whereIn('report_id', $reportIds)
                ->get()
                ->groupBy('report_id');

            $reports = AneelReport::whereIn('id', $reportIds)
                ->orderBy('period_start')
                ->get()
                ->map(function ($report) use ($indicators) {
                    return [
                        'report' => $report,
                        'indicators' => $indicators[$report->id] ?? collect(),
                    ];
                });
        } else {
            $reports = AneelReport::with(['indicators.indicator'])
                ->where('period_start', $lastPeriod)
                ->orderBy('period_start')
                ->get()
                ->map(function ($report) {
                    return [
                        'report' => $report,
                        'indicators' => $report->indicators,
                    ];
                });
        }

        $graphStart = $hasFilter ? $startDate : $sixMonthsAgo;
        $graphEnd = $hasFilter ? $endDate : $lastPeriod->copy()->endOfMonth();

        $graphReports = AneelReport::with(['indicators'])
            ->whereBetween('period_start', [$graphStart, $graphEnd])
            ->orderBy('period_start')
            ->get();

        $indicatorsBase = AneelIndicator::all();

        $categories = [
            'Item 1: Central de Serviços' => ['IATAA', 'IET', 'ITA', 'ICIR', 'IAABC', 'ICABC'],
            'Item 2: Atendimento ao Usuário de 1º e 2º Níveis' => ['IRSAP', 'IRSAFPM', 'ISU', 'IIAP', 'IIAFPM', 'IRIR'],
            'Item 3: Unidade de Controle e Conformidade de hardware' => ['IDSP', 'IDHW', 'IAG', 'IMSR', 'IPRM'],
            'Item 4: Unidade de Atividades Especiais' => ['IAEAP'],
        ];

        $periodMonths = collect();
        $current = $graphStart->copy();
        while ($current <= $graphEnd) {
            $periodMonths->push($current->format('Y-m'));
            $current->addMonth();
        }

        $graphData = [];


        return view('aneel::index', [
            'reports' => $reports,
            'graphReports' => $graphReports,
            'graphData' => $graphData,
            'indicators' => $indicatorsBase,
            'categories' => $categories,
            'periodMonths' => $periodMonths,
            'filterStart' => $startDate->format('Y-m-d'),
            'filterEnd' => $endDate->format('Y-m-d'),
            'hasFilter' => $hasFilter,
            'lastMonthStart' => $lastMonthStart,
            'lastMonthEnd' => $lastMonthEnd,
        ]);
    }


    // Baixa todos os arquivos "downloadAneelReport"
    public function downloadAneelReport($id)
    {
        $report = AneelReport::findOrFail($id);

        $zipBaseName = pathinfo($report->name, PATHINFO_FILENAME);
        $zipFileName = "{$zipBaseName}.zip";
        $tempFolder = public_path('temp');
        $zipPath = $tempFolder . '/' . $zipFileName;

        if (!File::exists($tempFolder)) {
            File::makeDirectory($tempFolder, 0755, true);
        }

        $arquivosRelatorio = AneelReportAttachment::where('report_id', $report->id)->get();
        $arquivosIndicadores = AneelReportIndicator::where('report_id', $report->id)->get();

        $tempFiles = [];
        $zip = new \ZipArchive;

        if ($zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) === TRUE) {

            // Relatório principal
            if ($report->attachment) {
                $relatorioTempPath = $tempFolder . '/' . $report->name;
                file_put_contents($relatorioTempPath, base64_decode($report->attachment)); // Adicionado base64_decode

                if (file_exists($relatorioTempPath)) {
                    $zip->addFile($relatorioTempPath, $report->name);
                    $tempFiles[] = $relatorioTempPath;
                } else {
                    return response()->json(['error' => 'Arquivo principal não pôde ser salvo.'], 500);
                }
            }

            // Anexos do relatório
            foreach ($arquivosRelatorio as $arquivo) {
                try {
                    $extension = pathinfo($arquivo->name, PATHINFO_EXTENSION);
                    $filename = $arquivo->label . '.' . $extension;
                    $tempPath = $tempFolder . '/' . $filename;

                    $fileContent = $arquivo->attachment;
                    file_put_contents($tempPath, $fileContent); // Sem base64_decode

                    if (file_exists($tempPath)) {
                        $zip->addFile($tempPath, 'relatorio/' . $filename);
                        $tempFiles[] = $tempPath;
                    }
                } catch (\Throwable $e) {
                    Log::error("Falha ao processar AneelReportAttachment ID: {$arquivo->id} - " . $e->getMessage());
                }
            }

            // Anexos dos indicadores
            foreach ($arquivosIndicadores as $indicador) {
                try {
                    if (!empty($indicador->attachment)) {
                        $nomeArquivo = $indicador->id . '_' . $indicador->name_attachment;
                        $tempPath = $tempFolder . '/' . $nomeArquivo;

                        $fileContent = $indicador->attachment;
                        file_put_contents($tempPath, $fileContent); // Sem base64_decode

                        if (file_exists($tempPath)) {
                            $zip->addFile($tempPath, 'indicadores/' . $nomeArquivo);
                            $tempFiles[] = $tempPath;
                        }
                    } else {
                        Log::warning("Anexo de indicador vazio: ID {$indicador->id}");
                    }
                } catch (\Throwable $e) {
                    Log::error("Falha ao processar AneelReportIndicator ID: {$indicador->id} - " . $e->getMessage());
                }
            }

            // XLSX principal (se existir)
            if (!empty($report->xlsx_attachment)) {
                $xlsxTempPath = $tempFolder . '/' . $report->xlsx_name;

                // XLSX ainda pode estar como base64, mantenha decode aqui
                file_put_contents($xlsxTempPath, base64_decode($report->xlsx_attachment));

                if (file_exists($xlsxTempPath)) {
                    $zip->addFile($xlsxTempPath, 'indicadores/' . $report->xlsx_name);
                    $tempFiles[] = $xlsxTempPath;
                }
            }

            $zip->close();
        } else {
            return response()->json(['error' => 'Falha ao criar o arquivo ZIP.'], 500);
        }

        // Exclusão automática
        $tempFiles[] = $zipPath;
        register_shutdown_function(function () use ($tempFiles) {
            foreach ($tempFiles as $file) {
                @unlink($file);
            }
        });

        return response()->download($zipPath)->deleteFileAfterSend(true);
    }



    // Baixa somente o RTA "downloadReport"
    public function downloadReport($id)
    {
        $report = AneelReport::findOrFail($id);

        if (!$report->attachment) {
            return response()->json(['error' => 'Nenhum anexo encontrado para este relatório.'], 404);
        }

        $fileName = $report->name;
        $fileContent = base64_decode($report->attachment);

        return response($fileContent)
            ->header('Content-Type', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document')
            ->header('Content-Disposition', "attachment; filename=\"{$fileName}\"");
    }

    public function deleteAttachmentRTA($id)
    {
        $report = AneelReport::findOrFail($id);

        $report->update([
            'attachment' => null,
            'attachment_size' => null,
            'mime_type' => null,
        ]);

        return redirect()->back()->with('success', 'Relatório ' . $report->name . ' removido com sucesso.');
    }

    // Cria e/ou atualiza o Excel "updateXlsx"
    public function updateXlsx($id)
    {
        DB::beginTransaction();

        try {
            $report = AneelReport::findOrFail($id);

            PlanilhaService::gerarXlsx($report);

            DB::commit();
            return redirect()->back()->with('success', 'Planilha gerada/atualizada com sucesso!');
        } catch (\Throwable $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Erro ao gerar/atualizar planilha: ' . $e->getMessage());
        }
    }
    // Baixa somente o Excel "downloadXlsx"
    public function downloadXlsx($id)
    {
        $report = AneelReport::findOrFail($id);

        if (!$report->xlsx_attachment || !$report->xlsx_name) {
            return redirect()->back()->with('error', 'Arquivo não disponível para download.');
        }

        $decodedFile = base64_decode($report->xlsx_attachment);
        $fileName = $report->xlsx_name;

        return response()->streamDownload(function () use ($decodedFile) {
            echo $decodedFile;
        }, $fileName, [
            'Content-Type' => $report->xlsx_mime_type ?? 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
        ]);
    }

    public function deleteAttachmentXlsx($id)
    {
        $report = AneelReport::findOrFail($id);

        $report->update([
            'xlsx_name' => null,
            'xlsx_attachment' => null,
            'xlsx_attachment_size' => null,
            'xlsx_mime_type' => null,
        ]);

        return redirect()->back()->with('success', 'Arquivo removido com sucesso.');
    }
}
