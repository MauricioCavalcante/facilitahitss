@extends('ana::layouts.app')


@section('content')
    <div class="container mt-4">
        <!-- Título da seção -->
        <div class="row justify-content-between align-items-center mb-4">
            <div class="col">
                <h2>Criar Novo Relatório de Faturamento</h2>
            </div>
        </div>

        <!-- Formulário para criação do relatório -->
        <form action="{{ route('ana::relatorio_faturamento.salvar') }}" method="POST">
            @csrf

            <!-- Informações da Nota Fiscal e Datas -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5>Informações do Relatório</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="numero_nota_fiscal" class="form-label">Número da Nota Fiscal</label>
                        <input type="text" class="form-control" id="numero_nota_fiscal" name="numero_nota_fiscal"
                            value="{{ old('numero_nota_fiscal') }}"  required>
                        <small class="form-text text-muted">Informe o número da nota fiscal relacionada a este
                            relatório.</small>
                    </div>

                    <!-- Data de Vencimento -->
                    <div class="mb-3">
                        <label for="data_vencimento" class="form-label">Data de Vencimento</label>
                        <input type="date" class="form-control" id="data_vencimento" name="data_vencimento"
                            value="{{ old('data_vencimento') }}" required>
                        <small class="form-text text-muted">Escolha a data de vencimento da nota fiscal.</small>
                    </div>
                </div>
            </div>

            <!-- Seleção das OS's -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5>Selecione as Ordens de Serviço (máximo de 5)</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="ordens_servico" class="form-label">Ordens de Serviço</label>
                        <select class="form-control select2" id="ordens_servico" name="ordens_servico[]" multiple="multiple"
                            data-placeholder="Selecione até 5 OS's" required>
                            @foreach ($ordens_servico as $os)
                                <option value="{{ $os->id }}">
                                    {{ $os->numero }} - {{ \Carbon\Carbon::parse($os->data_inicio)->format('d/m/Y') }} até {{ \Carbon\Carbon::parse($os->data_fim)->format('d/m/Y') }}
                                </option>
                            @endforeach
                        </select>
                        <small class="form-text text-muted">Selecione as OS's que devem ser incluídas no relatório.</small>
                    </div>
                </div>
            </div>

            <!-- Campos para valores das OS's -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5>Valores das OS's</h5>
                </div>
                <div class="card-body">
                    <div id="valores-os-container">
                        <!-- Este container será preenchido dinamicamente -->
                        <p class="text-muted">Selecione as OS's acima para inserir os valores correspondentes.</p>
                    </div>
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
                            value="{{ old('desconto') }}" step="0.01" placeholder="Ex: 1000,00">
                        <small class="form-text text-muted">Informe o valor do desconto, se aplicável.</small>
                    </div>
                </div>
            </div>

            <!-- Botão de submissão -->
            <div class="d-flex justify-content-end">
                <a href="{{ route('ana::relatorio_faturamento.index') }}" class="btn btn-secondary me-2">Cancelar</a>
                <button type="submit" class="btn btn-success">Gerar Relatório</button>
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

            // Evento para capturar mudanças no Select2
            $('#ordens_servico').on('change', function() {
                const selectedOS = [...new Set($(this).val())]; // Remove duplicatas
                const container = $('#valores-os-container');
                container.empty(); // Limpa os campos existentes

                if (selectedOS.length > 0) {
                    // Adiciona um campo para cada OS selecionada
                    selectedOS.forEach(id => {
                        const osNumero = osMap[id]; // Recupera o número da OS pelo ID
                        const osElement = `
                            <div class="mb-3">
                                <label for="valor_os_${id}" class="form-label">Valor da OS #${osNumero}</label>
                                <input type="number" class="form-control" id="valor_os_${id}" name="valores[]" step="0.01"
                                    placeholder="Ex: 1000,00" required>
                                <input type="hidden" name="ordens_servico[]" value="${id}">
                            </div>
                        `;
                        container.append(osElement);
                    });
                } else {
                    container.html('<p class="text-muted">Selecione as OS\'s acima para inserir os valores correspondentes.</p>');
                }
            });
        });
    </script>
@endsection
