@extends('aneel::layouts.app')

@section('head')
@endsection

@section('content')
    <div class="container mt-4">
        <div class="row justify-content-between align-items-center mb-4">
            <div class="col">
                <h2>Criar Novo Relatório RTA</h2>
            </div>
        </div>

        <form class="needs-validation" action="{{ route('aneel::reportsRTA.store') }}" method="POST"
            enctype="multipart/form-data" novalidate>
            @csrf

            {{-- PERÍODO --}}
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Período</h5>
                </div>
                <div class="card-body" id="validation">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="period_start" class="form-label">Data de Início</label>
                            <input type="date" class="form-control @error('period_start') is-invalid @enderror"
                                id="period_start" name="period_start" value="{{ old('period_start') }}" required>
                            <div class="invalid-feedback">
                                @error('period_start')
                                    {{ $message }}
                                @else
                                    Informe a data inicial.
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="period_end" class="form-label">Data de Fim</label>
                            <input type="date" class="form-control @error('period_end') is-invalid @enderror"
                                id="period_end" name="period_end" value="{{ old('period_end') }}" required>
                            <div class="invalid-feedback">
                                @error('period_end')
                                    {{ $message }}
                                @else
                                    Informe a data final.
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>
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
                            <input type="file" id="{{ $group['pdfId'] }}" accept="application/pdf" name="pdfs[]"
                                required />
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
                                </div>
                            </div>
                        @endforeach

                    </div>
                </div>
            @endforeach


            {{-- INDICADORES (Opcional) --}}
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Indicadores</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        @foreach ($indicators as $indicator)
                            @php
                                $inputs = is_string($indicator->inputs)
                                    ? json_decode($indicator->inputs, true)
                                    : $indicator->inputs;
                                $requiresFile = in_array($indicator->id, [1, 3, 4, 7, 9, 10]);
                            @endphp

                            <div class="col-lg-4 col-md-6 mb-4">
                                <div class="card shadow-sm">
                                    <div class="card-header bg-secondary text-white">
                                        <h6 class="mb-0">{{ $indicator->id }} - {{ $indicator->name }}
                                            ({{ $indicator->code }})
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <span class="text-muted">{{ $indicator->description }}</span>
                                        @if (!empty($inputs))
                                            @foreach ($inputs as $input)
                                                <div class="my-3">
                                                    <label for="input_{{ $indicator->id }}_{{ $input }}"
                                                        class="form-label">
                                                        {{ strtoupper($input) }}
                                                    </label>
                                                    <input type="number" step="any" class="form-control"
                                                        id="input_{{ $indicator->id }}_{{ $input }}"
                                                        name="inputs[{{ $indicator->id }}][{{ $input }}]"
                                                        value="{{ old("inputs.$indicator->id.$input") }}">
                                                </div>
                                            @endforeach
                                        @else
                                            <p class="text-muted">Nenhum dado disponível.</p>
                                        @endif

                                        @if ($requiresFile)
                                            <div class="mt-3">
                                                <label for="file_{{ $indicator->id }}">Anexar Arquivo:</label>
                                                <input type="file" class="form-control"
                                                    name="files[{{ $indicator->id }}]" id="file_{{ $indicator->id }}">
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- JUSTIFICATIVAS (Opcional) --}}
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Justificativas</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="justification1" class="form-label">Detalhamento dos atendimentos com indicadores de
                            desempenho não cumpridos</label>
                        <textarea class="form-control" id="justification1" name="justification1" rows="3"
                            placeholder="Digite a justificativa...">{{ old('justification1') }}</textarea>
                    </div>

                    <div class="mb-3">
                        <label for="justification2" class="form-label">Recomendações técnicas, administrativas e
                            gerenciais para o próximo período</label>
                        <textarea class="form-control" id="justification2" name="justification2" rows="3"
                            placeholder="Digite a justificativa...">{{ old('justification2') }}</textarea>
                    </div>
                </div>
            </div>

            {{-- BOTÕES --}}
            <div class="d-flex justify-content-end">
                <a href="{{ route('aneel::reportsRTA.index') }}" class="btn btn-secondary me-2">Cancelar</a>
                <button type="submit" class="btn btn-success">Salvar Relatório</button>
            </div>
        </form>
    </div>

    @push('scripts')
        <script src="{{ asset('js/Aneel/validation.js') }}"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.4.120/pdf.min.js"></script>
        <script src="{{ asset('js/Aneel/pdf-recort.js') }}"></script>
        <script src="{{ asset('js/Aneel/csrf-refresh.js') }}"></script>
    @endpush
@endsection
