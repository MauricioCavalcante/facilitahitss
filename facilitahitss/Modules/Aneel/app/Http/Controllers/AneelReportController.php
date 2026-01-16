<?php

namespace Modules\Aneel\Http\Controllers;

use ZipArchive;
use Carbon\Carbon;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Modules\Aneel\Http\Helpers\TemplateHelper;
use Modules\Aneel\Services\IndicatorCalculatorService;
use Modules\Aneel\Services\PlanilhaService;
use Modules\Aneel\Models\AneelReport;
use Modules\Aneel\Models\AneelIndicator;
use Modules\Aneel\Models\AneelReportIndicator;
use Modules\Aneel\Models\AneelReportAttachment;

class AneelReportController extends Controller
{

    private function isReportComplete(Request $request, $reportId = null): bool
    {
        $inputs = $request->input('inputs', []);
        $periodStart = $request->input('period_start');
        $periodEnd = $request->input('period_end');
        $requiredImageLabels = [
            'R1 - Quantidade geral por tipo',
            'R1 - Quantidade por grupo solucionador',
            'R2 - Chamados por prioridade geral',
            'R2 - Incidentes por prioridade',
            'R2 - Requisição por prioridade',
            'R3 - Chamados por Prioridade e Nível de Atendimento N1 e N2',
        ];

        if (empty($periodStart) || empty($periodEnd)) {
            return false;
        }

        foreach ($inputs as $indicatorId => $fields) {
            foreach ($fields as $value) {
                if ($value === null || $value === '') {
                    return false;
                }
            }
        }

        foreach ($requiredImageLabels as $label) {
            $hasImage = AneelReportAttachment::where('report_id', $reportId)->where('label', $label)->exists();
            $index = array_search($label, $request->input('labels', []));
            if (!$hasImage && (!isset($request->file('imagens')[$index]) || !$request->file('imagens')[$index]->isValid())) {
                return false;
            }
        }

        return true;
    }

    private function mapLabelToPlaceholder(string $label): ?string
    {
        $map = [
            'R1 - Quantidade geral por tipo' => 'img_tipo',
            'R1 - Quantidade por grupo solucionador' => 'img_solucionador',
            'R2 - Chamados por prioridade geral' => 'img_prioridade',
            'R2 - Incidentes por prioridade' => 'img_incidentes',
            'R2 - Requisição por prioridade' => 'img_requisicao',
            'R3 - Chamados por Prioridade e Nível de Atendimento N1 e N2' => 'img_nivel_atendimento',
        ];

        return $map[$label] ?? null;
    }

    public function index()
    {
        $reports = AneelReport::orderBy('period_start', 'desc')->get();

        return view('aneel::reports_rta.index', compact('reports'));
    }

    public function create()
    {
        $labels = [
            'R1 - Quantidade geral por tipo',
            'R1 - Quantidade por grupo solucionador',
            'R2 - Chamados por prioridade geral',
            'R2 - Incidentes por prioridade',
            'R2 - Requisição por prioridade',
            'R3 - Chamados por Prioridade e Nível de Atendimento N1 e N2',
        ];

        $indicators = AneelIndicator::all();
        return view('aneel::reports_rta.create', compact('indicators', 'labels'));
    }

    public function store(Request $request)
    {
        DB::beginTransaction();

        try {
            $action = $request->input('action_type');

            if (in_array($action, ['finalize', 'finalize_and_generate']) && !$this->isReportComplete($request)) {
                return redirect()->back()
                    ->with('error', 'Todos os campos e anexos obrigatórios devem ser preenchidos para finalizar o relatório.')
                    ->withInput();
            }

            $validator = Validator::make($request->all(), [
                'period_start' => 'required|date',
                'period_end' => 'required|date|after_or_equal:period_start',
                'justification1' => 'nullable|string',
                'justification2' => 'nullable|string',
                'imagens.*' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
                'labels' => 'nullable|array',
                'labels.*' => 'nullable|string',
                'inputs' => 'nullable|array',
                'inputs.*' => 'array',
                'files.*' => 'nullable|file|mimes:xlsx,csv|max:5120',
            ]);

            if ($validator->fails()) {
                return redirect()->back()->withErrors($validator)->withInput();
            }

            $validated = $validator->validated();
            $inputs = $validated['inputs'] ?? [];

            $reportName = "Relatorio_RTA_" . date('d', strtotime($validated['period_start'])) . "_a_" . date('d-m-Y', strtotime($validated['period_end'])) . ".docx";

            $report = AneelReport::create([
                'name' => $reportName,
                'period_start' => $validated['period_start'],
                'period_end' => $validated['period_end'],
                'justification1' => $validated['justification1'] ?? null,
                'justification2' => $validated['justification2'] ?? null,
                'status' => 'Em Andamento',
            ]);

            if ($request->hasFile('pdfs')) {
                foreach ($request->file('pdfs') as $pdfIndex => $pdfFile) {
                    if ($pdfFile && $pdfFile->isValid()) {
                        AneelReportAttachment::create([
                            'report_id'  => $report->id,
                            'label'      => "Anexo Base R" . ($pdfIndex + 1),
                            'name'       => $pdfFile->getClientOriginalName(),
                            'mime_type'  => $pdfFile->getMimeType(),
                            'size'       => $pdfFile->getSize(),
                            'attachment' => file_get_contents($pdfFile->getRealPath()),
                        ]);
                    }
                }
            }

            if ($request->has('cropped_images') && !empty($request->cropped_images)) {
                foreach ($request->cropped_images as $label => $base64Image) {
                    if ($base64Image) {
                        if (preg_match('/^data:image\/(\w+);base64,/', $base64Image, $matches)) {
                            $mime = 'image/' . $matches[1];
                            $data = substr($base64Image, strpos($base64Image, ',') + 1); // Remove a parte base64 do conteúdo
                            $data = base64_decode($data);

                            if ($data === false) {
                                Log::error("Erro ao decodificar imagem base64 para o label {$label}");
                                return response()->json(['error' => 'Imagem inválida (base64).'], 400);
                            }

                            $fileName = "imagem_{$label}." . $matches[1];

                            try {
                                AneelReportAttachment::create([
                                    'report_id'  => $report->id,
                                    'label'      => $label,
                                    'name'       => $fileName,
                                    'mime_type'  => $mime,
                                    'size'       => strlen($data),
                                    'attachment' => $data,
                                ]);
                            } catch (\Exception $e) {
                                Log::error("Erro ao salvar a imagem {$fileName} para o label {$label}: " . $e->getMessage());
                                return response()->json(['error' => 'Erro ao salvar a imagem.'], 500);
                            }
                        }
                    }
                }
            }

            $indicatorIds = array_keys($inputs);
            $indicators = AneelIndicator::whereIn('id', $indicatorIds)->get()->keyBy('id');

            foreach ($inputs as $indicatorId => $inputValues) {
                if (!isset($indicators[$indicatorId])) continue;

                $dados = [];
                $todosPreenchidos = true;

                foreach ($inputValues as $key => $value) {
                    if ($value === '' || $value === null) {
                        $dados[$key] = null;
                        $todosPreenchidos = false;
                    } else {
                        $dados[$key] = is_numeric($value) ? floatval($value) : null;
                    }
                }

                $calcResult = null;
                $status = 'Preencha todos os campos!';

                if ($todosPreenchidos) {
                    $calcResult = IndicatorCalculatorService::calculate($indicatorId, $dados);

                    $serviceLevel = $indicators[$indicatorId]->service_level;
                    preg_match('/(<=|>=|<|>|==|!=|=)?\s*([\d.,]+)%?/', $serviceLevel, $matches);

                    if ($matches && count($matches) >= 3) {
                        [$all, $operator, $targetValue] = $matches;
                        $targetValue = floatval(str_replace(',', '.', $targetValue));

                        if (!$operator || $operator === '=') {
                            $operator = '==';
                        }

                        switch ($operator) {
                            case '<=':
                                $atingiu = $calcResult <= $targetValue;
                                break;
                            case '>=':
                                $atingiu = $calcResult >= $targetValue;
                                break;
                            case '<':
                                $atingiu = $calcResult < $targetValue;
                                break;
                            case '>':
                                $atingiu = $calcResult > $targetValue;
                                break;
                            case '==':
                                $atingiu = $calcResult == $targetValue;
                                break;
                            case '!=':
                                $atingiu = $calcResult != $targetValue;
                                break;
                            default:
                                $atingiu = false;
                        }

                        $status = $atingiu ? 'Atingiu' : 'Não Atingiu';
                    }
                }

                $nameAttachment = null;
                $attachmentData = null;
                $mime = null;

                if ($request->hasFile("files.$indicatorId")) {
                    $file = $request->file("files.$indicatorId");
                    $nameAttachment = $file->getClientOriginalName();
                    $attachmentData = file_get_contents($file->getRealPath());
                    $mime = $file->getMimeType();
                }

                AneelReportIndicator::create([
                    'report_id'       => $report->id,
                    'indicator_id'    => $indicatorId,
                    'inputs'          => json_encode($dados, JSON_UNESCAPED_UNICODE),
                    'value'           => $calcResult,
                    'status'          => $status,
                    'name_attachment' => $nameAttachment,
                    'attachment'      => $attachmentData,
                    'mime'            => $mime,
                ]);
            }

            if (in_array($action, ['finalize', 'finalize_and_generate'])) {
                $report->update(['status' => 'Finalizado']);
            }

            DB::commit();

            return redirect()->route('aneel::reportsRTA.index')
                ->with('success', 'Relatório salvo com sucesso.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Erro ao salvar relatório: " . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Erro ao salvar o relatório: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function show($id)
    {
        $reports = AneelReport::findOrFail($id);
        $reportIndicators = AneelReportIndicator::where('report_id', $id)->with('indicator')->get();

        $allAttachments = AneelReportAttachment::where('report_id', $id)->get();

        $imageAttachments = $allAttachments->filter(function ($attachment) {
            return Str::contains($attachment->mime_type, 'image');
        });

        $pdfAttachments = $allAttachments->filter(function ($attachment) {
            return Str::contains($attachment->mime_type, 'pdf');
        });


        return view('aneel::reports_rta.details', compact(
            'reportIndicators',
            'reports',
            'imageAttachments',
            'pdfAttachments'
        ));
    }

    public function edit($reportId)
    {
        $reports = AneelReport::findOrFail($reportId);
        $reportIndicators = AneelReportIndicator::where('report_id', $reportId)->with('indicator')->get();
        $attachments = AneelReportAttachment::where('report_id', $reportId)->get()->mapWithKeys(function ($a) {
            return [$a->label => [
                'name' => $a->name,
                'base64' => 'data:' . $a->mime_type . ';base64,' . base64_encode($a->attachment),
            ]];
        });

        $baseAttachments = AneelReportAttachment::where('report_id', $reportId)
            ->whereIn('label', ['Anexo Base R1', 'Anexo Base R2', 'Anexo Base R3'])
            ->get()
            ->keyBy('label');


        $labels = [
            'R1 - Quantidade geral por tipo',
            'R1 - Quantidade por grupo solucionador',
            'R2 - Chamados por prioridade geral',
            'R2 - Incidentes por prioridade',
            'R2 - Requisição por prioridade',
            'R3 - Chamados por Prioridade e Nível de Atendimento N1 e N2',
        ];

        $groups = [
            ['labelIndexes' => [0, 1], 'pdfId' => 'pdfUploadGroup1'],
            ['labelIndexes' => [2, 3, 4], 'pdfId' => 'pdfUploadGroup2'],
            ['labelIndexes' => [5], 'pdfId' => 'pdfUploadGroup3'],
        ];

        return view('aneel::reports_rta.edit', compact(
            'reports',
            'reportIndicators',
            'attachments',
            'labels',
            'groups',
            'reportId',
            'baseAttachments'
        ));
    }
    public function update(Request $request, $reportId)
    {
        $action = $request->input('action_type');

        if (in_array($action, ['finalize', 'finalize_and_generate']) && !$this->isReportComplete($request, $reportId)) {
            return redirect()->back()
                ->with('error', 'Todos os campos e anexos obrigatórios devem estar preenchidos para finalizar o relatório.')
                ->withInput();
        }

        if ($action === 'update_report') {
            $this->updateReport($request, $reportId);
        } elseif ($action === 'update_indicators') {
            $this->updateIndicator($request, $reportId);
        } else {
            $this->updateReport($request, $reportId);
            $this->updateIndicator($request, $reportId);
        }

        if (in_array($action, ['finalize', 'finalize_and_generate'])) {
            AneelReport::where('id', $reportId)->update(['status' => 'Finalizado']);
        }

        return redirect()->route('aneel::reportsRTA.show', $reportId)
            ->with('success', 'Indicadores atualizados com sucesso!');
    }

    public function updateReport(Request $request, $reportId)
    {
        try {
            $report = AneelReport::findOrFail($reportId);

            $report->update([
                'name' => "Relatorio_RTA_" . date('d', strtotime($request->input('period_start'))) . "_a_" . date('d-m-Y', strtotime($request->input('period_end'))) . ".docx",
                'period_start' => $request->input('period_start'),
                'period_end' => $request->input('period_end'),
                'justification1' => $request->input('justification1'),
                'justification2' => $request->input('justification2'),
                'status' => "Em Andamento",
            ]);

            if ($request->hasFile('pdfs')) {
                foreach ($request->file('pdfs') as $pdfIndex => $pdfFile) {
                    if ($pdfFile && $pdfFile->isValid()) {
                        $existingPdfAttachment = AneelReportAttachment::where('report_id', $reportId)
                            ->where('label', "Anexo Base R" . ($pdfIndex + 1))
                            ->first();

                        if ($existingPdfAttachment) {
                            $existingPdfAttachment->update([
                                'name' => $pdfFile->getClientOriginalName(),
                                'mime_type' => $pdfFile->getMimeType(),
                                'size' => $pdfFile->getSize(),
                                'attachment' => file_get_contents($pdfFile->getRealPath()),
                            ]);
                        } else {
                            AneelReportAttachment::create([
                                'report_id' => $reportId,
                                'label' => "Anexo Base R" . ($pdfIndex + 1),
                                'name' => $pdfFile->getClientOriginalName(),
                                'mime_type' => $pdfFile->getMimeType(),
                                'size' => $pdfFile->getSize(),
                                'attachment' => file_get_contents($pdfFile->getRealPath()),
                            ]);
                        }
                    }
                }
            }

            if ($request->has('cropped_images') && !empty($request->cropped_images)) {
                foreach ($request->cropped_images as $label => $base64Image) {
                    if ($base64Image) {
                        if (preg_match('/^data:image\/(\w+);base64,/', $base64Image, $matches)) {
                            $mime = 'image/' . $matches[1];
                            $data = substr($base64Image, strpos($base64Image, ',') + 1); // Remove a parte base64 do conteúdo
                            $data = base64_decode($data);

                            if ($data === false) {
                                Log::error("Erro ao decodificar imagem base64 para o label {$label}");
                                return response()->json(['error' => 'Imagem inválida (base64).'], 400);
                            }

                            $fileName = "imagem_{$label}." . $matches[1];

                            try {
                                // Tenta encontrar um anexo com esse label
                                $attachment = AneelReportAttachment::where('report_id', $report->id)
                                    ->where('label', $label)
                                    ->first();

                                if ($attachment) {
                                    // Se o anexo já existe, realiza a atualização
                                    $attachment->update([
                                        'name' => $fileName,
                                        'mime_type' => $mime,
                                        'size' => strlen($data),
                                        'attachment' => $data,
                                        'label' => $label,
                                    ]);
                                    Log::info("Anexo de imagem {$fileName} para o label {$label} atualizado com sucesso.");
                                } else {
                                    // Se não existe, cria um novo anexo
                                    AneelReportAttachment::create([
                                        'report_id' => $report->id,
                                        'label' => $label,
                                        'name' => $fileName,
                                        'mime_type' => $mime,
                                        'size' => strlen($data),
                                        'attachment' => $data,
                                    ]);
                                    Log::info("Novo anexo de imagem {$fileName} para o label {$label} criado com sucesso.");
                                }
                            } catch (\Exception $e) {
                                Log::error("Erro ao salvar a imagem {$fileName} para o label {$label}: " . $e->getMessage());
                                return response()->json(['error' => 'Erro ao salvar a imagem.'], 500);
                            }
                        }
                    }
                }
            }


            return redirect()->route('aneel::reportsRTA.show', $reportId)
                ->with('success', 'Relatório atualizado com sucesso!');
        } catch (\Exception $e) {
            Log::error("Erro ao atualizar o relatório: " . $e->getMessage());
            return redirect()->back()->with('error', 'Erro ao atualizar o relatório: ' . $e->getMessage());
        }
    }

    public function updateIndicator(Request $request, $reportId)
    {
        DB::beginTransaction();

        try {
            $report = AneelReport::findOrFail($reportId);

            $reportIndicators = AneelReportIndicator::where('report_id', $reportId)
                ->with('indicator')
                ->get()
                ->keyBy('indicator_id');

            foreach ($request->input('inputs', []) as $indicatorId => $inputs) {
                if (!isset($reportIndicators[$indicatorId])) continue;

                $reportIndicator = $reportIndicators[$indicatorId];

                $dados = [];
                $todosPreenchidos = true;

                foreach ($inputs as $key => $value) {
                    if ($value === '' || $value === null) {
                        $dados[$key] = null;
                        $todosPreenchidos = false;
                    } else {
                        $dados[$key] = is_numeric($value) ? floatval($value) : null;
                    }
                }

                $calcResult = null;
                $status = 'Preencha todos os campos!';

                if ($todosPreenchidos) {
                    $calcResult = IndicatorCalculatorService::calculate($indicatorId, $dados);

                    $serviceLevel = $reportIndicator->indicator->service_level;
                    preg_match('/(<=|>=|<|>|==|!=|=)?\s*([\d.,]+)%?/', $serviceLevel, $matches);

                    if ($matches && count($matches) >= 3) {
                        [$all, $operator, $targetValue] = $matches;
                        $targetValue = floatval(str_replace(',', '.', $targetValue));

                        if (!$operator || $operator === '=') {
                            $operator = '==';
                        }

                        switch ($operator) {
                            case '<=':
                                $atingiu = $calcResult <= $targetValue;
                                break;
                            case '>=':
                                $atingiu = $calcResult >= $targetValue;
                                break;
                            case '<':
                                $atingiu = $calcResult < $targetValue;
                                break;
                            case '>':
                                $atingiu = $calcResult > $targetValue;
                                break;
                            case '==':
                                $atingiu = $calcResult == $targetValue;
                                break;
                            case '!=':
                                $atingiu = $calcResult != $targetValue;
                                break;
                            default:
                                $atingiu = false;
                        }

                        $status = $atingiu ? 'Atingiu' : 'Não Atingiu';
                    }
                }

                // Remoção de anexo, se solicitado
                if ($request->has('remove_attachments') && in_array($indicatorId, $request->input('remove_attachments', []))) {
                    $reportIndicator->name_attachment = null;
                    $reportIndicator->attachment = null;
                    $reportIndicator->mime = null;
                }

                // Substituição de anexo
                if ($request->hasFile("files.$indicatorId")) {
                    $file = $request->file("files.$indicatorId");
                    $reportIndicator->name_attachment = $file->getClientOriginalName();
                    $reportIndicator->attachment = file_get_contents($file->getRealPath());
                    $reportIndicator->mime = $file->getMimeType();
                }

                // Atualiza os dados no banco
                $reportIndicator->update([
                    'inputs' => json_encode($dados, JSON_UNESCAPED_UNICODE),
                    'value' => $calcResult,
                    'status' => $status,
                    'name_attachment' => $reportIndicator->name_attachment,
                    'attachment' => $reportIndicator->attachment,
                    'mime' => $reportIndicator->mime,
                ]);
            }

            DB::commit();

            return redirect()->route('aneel::reportsRTA.show', $reportId)
                ->with('success', 'Indicadores atualizados com sucesso!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Erro ao atualizar indicadores: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $report = AneelReport::find($id);

            if (!$report) {
                return redirect()->back()->with('error', 'Relatório não encontrado.');
            }
            AneelReportIndicator::where('report_id', $id)->delete();
            AneelReportAttachment::where('report_id', $id)->delete();
            $report->delete();

            DB::commit();

            return redirect()->route('aneel::reportsRTA.index')->with('success', 'Relatório excluído com sucesso.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Erro ao excluir o relatório: ' . $e->getMessage());
        }
    }

public function generateRTAReport($id)
{
    $report = AneelReport::findOrFail($id);

    // Verifica indicadores incompletos
    $incompleteIndicators = AneelReportIndicator::where('report_id', $id)
        ->whereNull('value')
        ->get();

    if ($incompleteIndicators->isNotEmpty()) {
        $indicatorIds = $incompleteIndicators->pluck('indicator_id')->toArray();
        $indicatorList = '<ul>';
        foreach ($indicatorIds as $indicatorId) {
            $indicatorList .= "<li>Indicador ID {$indicatorId}</li>";
        }
        $indicatorList .= '</ul>';

        return redirect()->back()->with('error_html', 'Não é possível gerar o relatório. Os seguintes indicadores estão incompletos:' . $indicatorList);
    }

    // Monta dados básicos
    $dados = [
        'data_inicio' => Carbon::parse($report->period_start)->format('d/m/Y'),
        'data_fim' => Carbon::parse($report->period_end)->format('d/m/Y'),
        'justificativa_1' => $report->justification1,
        'justificativa_2' => $report->justification2,
    ];

    // Busca indicadores completos
    $indicadores = AneelReportIndicator::where('report_id', $id)->get();
    foreach ($indicadores as $indicador) {
        $dados['indicadores'][$indicador->indicator->code] = $indicador->value;
    }

    // Labels obrigatórios para imagens
    $labels = [
        'R1 - Quantidade geral por tipo',
        'R1 - Quantidade por grupo solucionador',
        'R2 - Chamados por prioridade geral',
        'R2 - Incidentes por prioridade',
        'R2 - Requisição por prioridade',
        'R3 - Chamados por Prioridade e Nível de Atendimento N1 e N2',
    ];

    // Busca anexos de imagens
    $anexos = AneelReportAttachment::where('report_id', $id)
        ->where('mime_type', 'like', 'image/%')
        ->get();

    $imagens = [];
    foreach ($anexos as $anexo) {
        $placeholder = $this->mapLabelToPlaceholder($anexo->label);
        if ($placeholder) {
            $imagens[$placeholder] = (object)[
                'nome_arquivo' => $anexo->name,
                'arquivo' => $anexo->attachment
            ];
        }
    }

    // Verifica se todas imagens obrigatórias existem
    $faltantes = [];
    foreach ($labels as $label) {
        $placeholder = $this->mapLabelToPlaceholder($label);
        if (!isset($imagens[$placeholder])) {
            $faltantes[] = $label;
        }
    }

    if (!empty($faltantes)) {
        $faltantesList = '<ul>';
        foreach ($faltantes as $f) {
            $faltantesList .= "<li>{$f}</li>";
        }
        $faltantesList .= '</ul>';

        return redirect()->back()->with('error_html', 'Não é possível gerar o relatório. As seguintes imagens estão faltando:' . $faltantesList);
    }

    // Ordena imagens conforme labels
    $imagensOrdenadas = [];
    foreach ($labels as $label) {
        $placeholder = $this->mapLabelToPlaceholder($label);
        $imagensOrdenadas[$placeholder] = $imagens[$placeholder];
    }

    $templatePath = public_path('modelos/Modelo_Relatorio_RTA.docx');
    $templateProcessor = TemplateHelper::preencherTemplateRTA($templatePath, $dados, $imagensOrdenadas, $id);

    // Salvar em um arquivo temporário
    $tempFile = tempnam(sys_get_temp_dir(), 'rta_') . '.docx';
    $templateProcessor->saveAs($tempFile);

    // Codificar em base64
    $content = file_get_contents($tempFile);
    $base64 = base64_encode($content);

    // Salvar no banco
    $report->update([
        'attachment' => $base64,
        'attachment_size' => strlen($base64),
        'mime_type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    ]);

    unlink($tempFile); // Limpa o temporário

    return redirect()->back()->with('success', 'Relatório gerado com sucesso!');
}

    public function downloadImagesById($id)
    {
        $attachment = AneelReportAttachment::findOrFail($id);

        $mime = $attachment->mime_type ?? 'application/octet-stream';
        $extension = explode('/', $mime)[1] ?? 'bin';
        $filename = ($attachment->label ?? 'anexo') . '.' . $extension;

        return response($attachment->attachment)
            ->header('Content-Type', $mime)
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }
    public function downloadImages($reportId)
    {
        $attachments = AneelReportAttachment::where('report_id', $reportId)->get();

        if ($attachments->isEmpty()) {
            return redirect()->back()->with('error', 'Nenhum anexo disponível para download.');
        }

        $zip = new ZipArchive();
        $zipFileName = 'anexos_relatorio_' . $reportId . '.zip';
        $zipPath = storage_path("app/temp/{$zipFileName}");


        if (!file_exists(storage_path('app/temp'))) {
            mkdir(storage_path('app/temp'), 0755, true);
        }

        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            return redirect()->back()->with('error', 'Não foi possível criar o arquivo ZIP.');
        }

        foreach ($attachments as $attachment) {
            if ($attachment->attachment) {
                $fileContent = $attachment->attachment;
                $mime = $attachment->mime_type ?? 'application/octet-stream';
                $extension = explode('/', $mime)[1] ?? 'bin';

                $safeLabel = preg_replace('/[^A-Za-z0-9_\-]/', '_', $attachment->label ?? 'anexo');
                $fileName = $safeLabel . '.' . $extension;

                $zip->addFromString($fileName, $fileContent);
            }
        }

        $zip->close();

        return response()->download($zipPath)->deleteFileAfterSend(true);
    }
    public function downloadIndicatorAttachment($indicatorId)
    {
        $indicator = AneelReportIndicator::findOrFail($indicatorId);

        if (!$indicator->attachment) {
            return redirect()->back()->with('error', 'Anexo não encontrado.');
        }

        return response($indicator->attachment)
            ->header('Content-Type', $indicator->mime ?? 'application/octet-stream')
            ->header('Content-Disposition', 'attachment; filename="' . $indicator->name_attachment . '"');
    }

    public function deleteAttachment($id)
    {
        $attachment = AneelReportIndicator::findOrFail($id);
        $attachment->update([
            'attachment' => null,
            'name_attachment' => null,
            'mime' => null,
        ]);
        Log::info("Anexo removido do indicador ID: $id");
        return redirect()->back()->with('success', 'Anexo removido com sucesso.');
    }

    public function deleteImageAttachment($id)
    {
        try {
            $attachment = AneelReportAttachment::findOrFail($id);
            $reportId = $attachment->report_id;

            $attachment->delete();

            Log::info("Imagem de anexo removida com sucesso. Anexo ID: {$id}, Relatório ID: {$reportId}");

            return redirect()->back()->with('success', 'Imagem removida com sucesso.');
        } catch (\Exception $e) {
            Log::error("Erro ao remover anexo ID: {$id}. Erro: " . $e->getMessage());

            return redirect()->back()->with('error', 'Erro ao remover imagem.');
        }
    }

    private function handleXlsxGeneration($id)
    {
        DB::beginTransaction();

        try {
            $report = AneelReport::findOrFail($id);

            PlanilhaService::gerarXlsx($report);

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function updateXlsx($id)
    {
        try {
            $this->handleXlsxGeneration($id);

            return redirect()->back()->with('success', 'Planilha gerada/atualizada com sucesso!');
        } catch (\Throwable $e) {
            return redirect()->back()->with('error', 'Erro ao gerar/atualizar planilha: ' . $e->getMessage());
        }
    }

    public function finalizeReport($id)
    {
        $report = AneelReport::findOrFail($id);

        // Busca os indicadores com 'value' nulo
        $incompleteIndicators = DB::table('aneel_report_indicators')
            ->where('report_id', $id)
            ->whereNull('value')
            ->pluck('indicator_id')
            ->toArray();

        if (!empty($incompleteIndicators)) {
            $indicatorList = '<ul>';
            foreach ($incompleteIndicators as $indicatorId) {
                $indicatorList .= "<li>Indicador ID {$indicatorId}</li>";
            }
            $indicatorList .= '</ul>';

            return redirect()->route('aneel::reportsRTA.show', $id)
                ->with('error_html', 'Não é possível finalizar. Os seguintes indicadores estão incompletos:' . $indicatorList);
        }

        // Finaliza o relatório
        $report->update(['status' => 'Finalizado']);

        return redirect()->route('aneel::reportsRTA.show', $id)
            ->with('success', 'Relatório finalizado com sucesso.');
    }
    public function finalizeGenerateReport($id)
    {
        $report = AneelReport::findOrFail($id);

        $incompleteIndicators = DB::table('aneel_report_indicators')
            ->where('report_id', $id)
            ->whereNull('value')
            ->pluck('indicator_id')
            ->toArray();

        if (!empty($incompleteIndicators)) {
            $indicatorList = '<ul>';
            foreach ($incompleteIndicators as $indicatorId) {
                $indicatorList .= "<li>Indicador ID {$indicatorId}</li>";
            }
            $indicatorList .= '</ul>';

            return redirect()->route('aneel::reportsRTA.show', $id)
                ->with('error_html', 'Os seguintes indicadores precisam ser preenchidos para finalizar e gerar o relatório:' . $indicatorList);
        }

        try {
            $report->update(['status' => 'Finalizado']);

            $this->generateRTAReport($id);
            $this->handleXlsxGeneration($id); 

            return redirect()->route('aneel::reportsRTA.show', $id)
                ->with('success', 'Relatório finalizado e gerado com sucesso!');
        } catch (\Exception $e) {
            Log::error("Erro ao finalizar e gerar relatório RTA: " . $e->getMessage());

            return redirect()->route('aneel::reportsRTA.show', $id)
                ->with('error', 'Erro ao finalizar e gerar o relatório: ' . $e->getMessage());
        }
    }
}
