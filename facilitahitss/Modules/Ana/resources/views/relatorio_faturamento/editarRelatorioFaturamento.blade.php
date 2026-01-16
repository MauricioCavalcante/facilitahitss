@extends('ana::layouts.app')


@section('content')
    <div class="container mt-4">
        <!-- Título da seção -->
        <div class="row justify-content-between align-items-center mb-4">
            <div class="col">
                <h2>Editar Relatório de Faturamento</h2>
            </div>
        </div>

        <!-- Formulário para edição do relatório -->
        <form action="{{ route('ana::relatorio_faturamento.atualizar', $relatorio->id) }}" method="POST">
            @csrf
            @method('PUT')

            <!-- Informações da Nota Fiscal e Datas -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5>Informações do Relatório</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="numero_nota_fiscal" class="form-label">Número da Nota Fiscal</label>
                        <input type="text" class="form-control" id="numero_nota_fiscal" name="numero_nota_fiscal"
                            value="{{ old('numero_nota_fiscal', $relatorio->numero_nota_fiscal) }}" placeholder="Ex: 12345"
                            required>
                    </div>

                    <div class="mb-3">
                        <label for="data_vencimento" class="form-label">Data de Vencimento</label>
                        <input type="date" class="form-control" id="data_vencimento" name="data_vencimento"
                            value="{{ old('data_vencimento', $relatorio->data_vencimento) }}" required>
                    </div>
                </div>
            </div>

            <!-- Seleção das OS's -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5>Selecione as Ordens de Serviço</h5>
                </div>
                <div class="card-body">
                    <label for="ordens_servico" class="form-label">Ordens de Serviço (máximo 5)</label>
                    <select class="form-control select2" id="ordens_servico" name="ordens_servico[]" multiple="multiple" data-placeholder="Selecione até 5 OS's" required>
                        @foreach ($ordens_servico as $os)
                            <option value="{{ $os->id }}"
                                @if (in_array($os->id, old('ordens_servico', $ordens_servico_selecionadas))) selected @endif>
                                {{ $os->numero }} - {{ \Carbon\Carbon::parse($os->data_inicio)->format('d/m/Y') }} até {{ \Carbon\Carbon::parse($os->data_fim)->format('d/m/Y') }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Valores Correspondentes -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5>Valores das OS's Selecionadas</h5>
                </div>
                <div class="card-body" id="valores-os-container">
                    @foreach ($ordens_servico_selecionadas as $osId)
                        <div class="mb-3">
                            <label for="valor_os_{{ $osId }}" class="form-label">Valor da OS #{{ $osId }}</label>
                            <input type="number" class="form-control" id="valor_os_{{ $osId }}" name="valores[]" step="0.01"
                                value="{{ old('valores.' . $loop->index, $valores_os[$osId] ?? '') }}" placeholder="Ex: 1000,00" required>
                            <input type="hidden" name="ordens_servico[]" value="{{ $osId }}">
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Desconto -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5>Desconto</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="desconto" class="form-label">Desconto (se houver)</label>
                        <input type="number" class="form-control" id="desconto" name="desconto"
                            value="{{ old('desconto', $relatorio->desconto) }}" step="0.01" placeholder="Ex: 100.00">
                    </div>
                </div>
            </div>

            <!-- Botão de submissão -->
            <div class="d-flex justify-content-end">
                <a href="{{ route('ana::relatorio_faturamento.index') }}" class="btn btn-secondary me-2">Cancelar</a>
                <button type="submit" class="btn btn-success">Atualizar Relatório</button>
            </div>
        </form>
    </div>

    <script>
        $(document).ready(function() {
            // Configuração do Select2
            $('#ordens_servico').select2({
                maximumSelectionLength: 5,
                placeholder: $(this).data('placeholder'),
                allowClear: true
            });

            // Objeto para mapear IDs para os números das OS
            const osMap = @json($ordens_servico->pluck('numero', 'id'));

            // Objeto para mapear IDs das OS aos seus valores atuais
            const valoresOs = @json($valores_os);

            // Evento para popular os valores dinamicamente
            $('#ordens_servico').on('change', function() {
                const selectedOS = $(this).val() || [];
                const container = $('#valores-os-container');
                container.empty();

                if (selectedOS.length > 0) {
                    selectedOS.forEach(id => {
                        const osNumero = osMap[id]; // Recupera o número da OS pelo ID
                        const valorOS = valoresOs[id] !== undefined ? valoresOs[id] : '';
                        container.append(`
                            <div class="mb-3">
                                <label for="valor_os_${id}" class="form-label">Valor da OS #${osNumero}</label>
                                <input type="number" class="form-control" id="valor_os_${id}" name="valores[]" step="0.01"
                                    value="${valorOS}" placeholder="Ex: 1000,00" required>
                                <input type="hidden" name="ordens_servico[]" value="${id}">
                            </div>
                        `);
                    });
                } else {
                    container.html('<p class="text-muted">Selecione as OS\'s acima para inserir os valores correspondentes.</p>');
                }
            });

            // Dispara o evento 'change' ao carregar a página para preencher os campos existentes
            $('#ordens_servico').trigger('change');
        });
    </script>
@endsection
