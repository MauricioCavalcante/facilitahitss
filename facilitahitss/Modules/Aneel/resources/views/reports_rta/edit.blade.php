@extends('aneel::layouts.app')

@section('content')
    <div class="container mt-4">
        <!-- Cabeçalho -->
        <div class="row justify-content-between align-items-center mb-4">
            <div class="col">
                <h2 class="mb-0">Editar Relatório</h2>
            </div>
            <div class="col-auto d-flex">
                <a href="{{ route('aneel::reportsRTA.show', ['id' => $reports->id]) }}" class="btn btn-secondary me-2">
                    <i class="bi bi-arrow-left"></i> Voltar
                </a>
            </div>
        </div>

        <!-- FORM ÚNICO -->
        <form action="{{ route('aneel::reportsRTA.update', ['id' => $reports->id]) }}" method="POST"
            enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <input type="hidden" name="action_type" id="action_type" value="">

            <!-- Período -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5>Editar Período</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <label for="period_start" class="form-label fw-bold">Data de Início</label>
                            <input type="date" name="period_start" id="period_start" class="form-control"
                                value="{{ old('period_start', \Carbon\Carbon::parse($reports->period_start)->format('Y-m-d')) }}">
                        </div>
                        <div class="col-md-6">
                            <label for="period_end" class="form-label fw-bold">Data de Fim</label>
                            <input type="date" name="period_end" id="period_end" class="form-control"
                                value="{{ old('period_end', \Carbon\Carbon::parse($reports->period_end)->format('Y-m-d')) }}">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Anexos -->
            @php
                $groups = [
                    ['labelIndexes' => [0, 1], 'pdfId' => 'pdfUploadGroup1', 'pdfNamesId' => 'pdfNamesGroup1'],
                    ['labelIndexes' => [2, 3, 4], 'pdfId' => 'pdfUploadGroup2', 'pdfNamesId' => 'pdfNamesGroup2'],
                    ['labelIndexes' => [5], 'pdfId' => 'pdfUploadGroup3', 'pdfNamesId' => 'pdfNamesGroup3'],
                ];
            @endphp

            @foreach ($groups as $groupIndex => $group)
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">PDF {{ $groupIndex + 1 }}</h5>
                    </div>
                    <div class="card-body">

                        {{-- Upload específico para este grupo --}}
                        <div class="mb-4">
                            <label for="{{ $group['pdfId'] }}" class="form-label">Anexar PDF</label>
                            <input type="file" id="{{ $group['pdfId'] }}" accept="application/pdf" name="pdfs[]" />

                            @php
                                // Mapeia o índice do grupo com os nomes esperados
                                $baseNames = ['Anexo Base R1', 'Anexo Base R2', 'Anexo Base R3'];
                                $baseName = $baseNames[$groupIndex] ?? null;
                            @endphp

                            @if ($baseName && isset($baseAttachments[$baseName]))
                                <p class="mt-2 text-success">Atual: <strong>{{ $baseAttachments[$baseName]->name }}</strong>
                                </p>
                            @endif
                        </div>


                        {{-- Labels deste grupo --}}
                        @foreach ($group['labelIndexes'] as $index)
                            <div class="card mb-4">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <h5 class="mb-0">{{ $labels[$index] }}</h5>
                                    <button type="button" class="btn btn-sm btn-outline-secondary toggle-viewer"
                                        data-index="{{ $index }}" aria-label="Alternar visualização">
                                        <i class="bi bi-x-lg"></i>
                                    </button>
                                </div>
                                <div class="card-body">

                                    {{-- Local onde o PDF será exibido --}}
                                    <div id="pdfViewer{{ $index }}" class="mb-3"></div>

                                    <button type="button" id="cropLabel{{ $index }}"
                                        class="btn btn-primary">Selecionar Área</button>

                                    <input type="hidden" name="cropped_images[{{ $labels[$index] }}]"
                                        id="croppedImage{{ $index }}" />
                                    <img id="preview{{ $index }}" style="max-width: 100px;" />
                                    @php
                                        $existingImage = \Modules\Aneel\Models\AneelReportAttachment::where(
                                            'report_id',
                                            $reports->id,
                                        )
                                            ->where('label', $labels[$index])
                                            ->first();
                                    @endphp

                                    @if ($existingImage)
                                        <div class="mb-3">
                                            <h6>Imagem Atual</h6>
                                            <img src="data:{{ $existingImage->mime_type }};base64,{{ base64_encode($existingImage->attachment) }}"
                                                style="max-width: 100px;" />
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endforeach

                    </div>
                </div>
            @endforeach

            <!-- Justificativas -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5>Editar Justificativas</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="justification1" class="form-label fw-bold">Detalhamento dos atendimentos</label>
                        <textarea name="justification1" class="form-control" rows="4">{{ old('justification1', $reports->justification1) }}</textarea>
                    </div>
                    <div class="mb-3">
                        <label for="justification2" class="form-label fw-bold">Recomendações</label>
                        <textarea name="justification2" class="form-control" rows="4">{{ old('justification2', $reports->justification2) }}</textarea>
                    </div>
                </div>
            </div>

            <!-- Botão de salvar relatório -->
            <div class="d-flex">
                <button type="submit" class="btn btn-primary ms-auto me-3" onclick="setActionType('update_report')">
                    <i class="bi bi-save"></i> Atualizar Informações
                </button>
            </div>
            <hr />
            <!-- Indicadores -->
            <div class="card my-4">
                <div class="card-header">
                    <h5>Alterar Valores dos Indicadores</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        @php
                            $allIndicatorsMaster = \Modules\Aneel\Models\AneelIndicator::orderBy('id')->get();
                            $existingIndicators = $reportIndicators->keyBy('indicator_id');
                        @endphp
                        @foreach ($allIndicatorsMaster as $indicator)
                            @php
                                $reportIndicator = $existingIndicators->get($indicator->id);
                                
                                $savedInputs = [];
                                if ($reportIndicator) {
                                    $savedInputs = is_string($reportIndicator->inputs) 
                                        ? json_decode($reportIndicator->inputs, true) 
                                        : $reportIndicator->inputs;
                                }

                                $inputSchema = is_string($indicator->inputs) 
                                    ? json_decode($indicator->inputs, true) 
                                    : $indicator->inputs;

                                $requiresFile = in_array($indicator->id, [1, 3, 4, 7, 9, 10]);
                            @endphp

                            <div class="col-lg-4 col-md-6 mb-4">
                                <div class="card shadow-sm {{ !$reportIndicator ? 'border-warning' : '' }}">
                                    <div class="card-header {{ !$reportIndicator ? 'bg-warning text-dark' : 'bg-secondary text-white' }} d-flex justify-content-between align-items-center">
                                        <h6 class="mb-0">{{ $indicator->id }} - {{ $indicator->name }} ({{ $indicator->code }})</h6>
                                        @if(!$reportIndicator) 
                                            <span class="badge bg-light text-dark" title="Este indicador ainda não possui dados neste relatório">Novo</span> 
                                        @endif
                                    </div>
                                    <div class="card-body">
                                        <span class="text-muted small">{{ $indicator->description }}</span>
                                        
                                        @if (!empty($inputSchema))
                                            @foreach ($inputSchema as $key)
                                                <div class="my-3">
                                                    <label class="form-label">{{ strtoupper(str_replace('_', ' ', $key)) }}</label>
                                                    <input type="number" step="any" class="form-control"
                                                        name="inputs[{{ $indicator->id }}][{{ $key }}]"
                                                        value="{{ old("inputs.$indicator->id.$key", $savedInputs[$key] ?? '') }}">
                                                </div>
                                            @endforeach
                                        @else
                                            <p class="text-muted">Nenhum dado disponível.</p>
                                        @endif

                                        @if ($requiresFile)
                                            <div class="mt-3">
                                                <div id="file-container-{{ $indicator->id }}">
                                                    @if ($reportIndicator && $reportIndicator->name_attachment)
                                                        <div class="mb-2 small">
                                                            Anexo atual: <strong>{{ $reportIndicator->name_attachment }}</strong>
                                                        </div>
                                                    @endif
                                                    <label for="file_{{ $indicator->id }}" class="form-label mt-2">Substituir/Anexar Arquivo:</label>
                                                    <input type="file" class="form-control" name="files[{{ $indicator->id }}]" id="file_{{ $indicator->id }}">
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <button type="submit" class="btn btn-warning ms-auto me-3 mb-3"
                    onclick="setActionType('update_indicators')">
                    <i class="bi bi-bar-chart-line"></i> Atualizar Indicadores
                </button>
            </div>

            <div class="d-flex row gap-2">
                <button type="submit" class="btn btn-primary ms-auto col-auto" onclick="setActionType('update_all')">
                    <i class="bi bi-check2-circle"></i> Atualizar Tudo
                </button>
                <button type="submit" class="btn btn-success col-auto"
                    onclick="return validateAndFinalizeReport(event, 'finalize')">
                    <i class="bi bi-check2-square"></i> Finalizar Relatório
                </button>
            </div>
        </form>
    </div>
    <script>
        const pdfBase64FromDB = @json($attachments);
    </script>
    @push('scripts')
        <script src="{{ asset('js/Aneel/validation.js') }}"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.4.120/pdf.min.js"></script>
        <script src="{{ asset('js/Aneel/pdf-recort-edit.js') }}"></script>
    @endpush
@endsection
