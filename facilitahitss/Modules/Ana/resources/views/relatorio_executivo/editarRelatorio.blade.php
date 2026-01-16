@extends('ana::layouts.app')


@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <h2>Editar Relatório de Entrega</h2>

                <!-- Formulário para editar o relatório de entrega existente -->
                <form action="{{ route('ana::relatorio_executivo.atualizar', $relatorio->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <!-- Ordem de Serviço associada ao relatório -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5>Ordem de Serviço</h5>
                        </div>
                        <div class="card-body">
                            <div class="form-group my-3">
                                <small class="form-text text-muted">Campo preenchido automaticamente, não é necessário
                                    inserir nada.</small>
                                @if ($ordens_servico->count() === 1)
                                    <!-- Input travado quando há apenas uma OS disponível -->
                                    <input type="text" class="form-control"
                                        value="{{ $ordens_servico->first()->numero }}" readonly>
                                        <input type="hidden" name="ordem_servico_id"
                                        value="{{ old('ordem_servico_id', optional($relatorio->ordemServico)->id) }}">
                                @elseif ($ordens_servico->count() > 1)
                                    <!-- Quando há mais de uma OS, travando para edição -->
                                    <input type="text" class="form-control"
                                        value="{{ $relatorio->ordemServico->numero }}" readonly>
                                        <input type="hidden" name="ordem_servico_id"
                                        value="{{ old('ordem_servico_id', optional($relatorio->ordemServico)->id) }}">
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Campo para o título do relatório -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5>Título</h5>
                        </div>
                        <div class="card-body">
                            <div class="form-group my-3">
                                <small class="form-text text-muted">Descrição do escopo da OS</small>
                                <input type="text" class="form-control" id="titulo" name="titulo"
                                    value="{{ $relatorio->detalhes->titulo }}" required>
                            </div>
                        </div>
                    </div>

                    <!-- Campo para as referências do relatório -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5>Referências</h5>
                        </div>
                        <div class="card-body">
                            <div class="form-group my-3">
                                <small class="form-text text-muted">Descrição dos sistemas utilizados</small>
                                <textarea class="form-control auto-resize no-scroll" id="referencias" name="referencias" rows="3" required>{{ $relatorio->detalhes->referencias }}</textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Campo para a descrição das atividades executadas -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5>Descrição das Atividades Executadas</h5>
                        </div>
                        <div class="card-body">
                            <div class="form-group my-3">
                                <small class="form-text text-muted">Descrição das atividades e tarefas executadas</small>
                                <textarea class="form-control auto-resize no-scroll" id="atividades" name="atividades" rows="3" required>{{ $relatorio->detalhes->atividades }}</textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Card para a seção de evidências -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5>Evidências de Atividades Executadas</h5>
                        </div>
                        <div class="card-body" id="atividades-evidencias-sei">
                            @foreach (json_decode($relatorio->detalhes->tarefas, true) as $index => $tarefa)
                                <div class="row align-items-end my-3">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="tarefas_{{ $index }}">Atividades</label><br>
                                            <small class="form-text text-muted">Descrição das atividades (macro)</small>
                                            <textarea class="form-control auto-resize no-scroll" id="tarefas_{{ $index }}" name="tarefas[]" rows="3"
                                                required>{{ $tarefa }}</textarea>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="evidencias_{{ $index }}">Evidências</label><br>
                                            <small class="form-text text-muted">Descrição das evidências</small>
                                            <textarea class="form-control auto-resize no-scroll" id="evidencias_{{ $index }}" name="evidencias[]"
                                                rows="3" required>{{ json_decode($relatorio->detalhes->evidencias, true)[$index] }}</textarea>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="sei_{{ $index }}">Número do SEI</label><br>
                                            <small class="form-text text-muted">Insira o número do SEI (se não tiver,
                                                coloque N/A)</small>
                                            <textarea class="form-control auto-resize no-scroll" id="sei_{{ $index }}" name="sei[]" rows="3"
                                                required>{{ json_decode($relatorio->detalhes->sei, true)[$index] }}</textarea>
                                        </div>
                                    </div>
                                    <!-- Botões para adicionar e remover linhas -->
                                    <div class="col-md-12 text-end mt-2 d-flex justify-content-end">
                                        <button type="button" class="btn btn-danger me-2 remove-row"><i
                                                class="bi bi-trash"></i></button>
                                        @if ($loop->last)
                                            <button type="button" class="btn btn-primary add-more"><i
                                                    class="bi bi-plus-lg"></i></button>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- Botões para cancelar, salvar como rascunho ou gerar o relatório -->
                    <div class="d-flex justify-content-end">
                        <a href="{{ route('ana::relatorio_executivo.index') }}" class="btn btn-secondary me-2">Cancelar</a>
                        @if ($relatorio->validacao->status === 'Validado')
                            <button type="submit" name="salvar_como_rascunho" value="1"
                                class="btn btn-warning me-2">Salvar como Rascunho</button>
                        @endif
                        <button type="submit" class="btn btn-success">Gerar Relatório</button>
                    </div>
                </form>
            </div>
        </div>

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                var atividadesEvidenciasSEI = document.getElementById('atividades-evidencias-sei');
                var count = atividadesEvidenciasSEI.querySelectorAll('.row').length;

                // Função para ajustar a altura do textarea
                function autoResize() {
                    this.style.height = 'auto';
                    this.style.height = this.scrollHeight + 'px';
                }

                // Ajusta a altura inicial dos textareas ao carregar a página
                document.querySelectorAll('textarea').forEach(function(textarea) {
                    autoResize.call(textarea);
                    textarea.addEventListener('input', autoResize);
                });

                // Função para criar o botão "Incluir Mais"
                function createAddMoreButton() {
                    var addMoreBtn = document.createElement('button');
                    addMoreBtn.type = 'button';
                    addMoreBtn.classList.add('btn', 'btn-primary', 'add-more');
                    addMoreBtn.innerHTML = '<i class="bi bi-plus-lg"></i>';
                    addMoreBtn.addEventListener('click', function() {
                        addNewRow();
                    });
                    return addMoreBtn;
                }

                // Função para criar o botão "Remover"
                function createRemoveButton() {
                    var removeBtn = document.createElement('button');
                    removeBtn.type = 'button';
                    removeBtn.classList.add('btn', 'btn-danger', 'me-2', 'remove-row');
                    removeBtn.innerHTML = '<i class="bi bi-trash"></i>';
                    removeBtn.addEventListener('click', function() {
                        var row = removeBtn.closest('.row');
                        atividadesEvidenciasSEI.removeChild(row);
                        updateAddMoreButton();
                        toggleRemoveButtons();
                    });
                    return removeBtn;
                }

                // Função para adicionar novos pares de textarea
                function addNewRow() {
                    var newRow = document.createElement('div');
                    newRow.classList.add('row', 'align-items-end', 'my-3');

                    var newTarefas = document.createElement('div');
                    newTarefas.classList.add('col-md-4');
                    newTarefas.innerHTML = `
                        <div class="form-group">
                            <label for="tarefas_${count}">Atividades</label><br>
                            <small class="form-text text-muted">Descrição das atividades (macro)</small>
                            <textarea class="form-control auto-resize no-scroll" id="tarefas_${count}" name="tarefas[]" rows="3" required></textarea>
                        </div>
                    `;

                    var newEvidencias = document.createElement('div');
                    newEvidencias.classList.add('col-md-4');
                    newEvidencias.innerHTML = `
                        <div class="form-group">
                            <label for="evidencias_${count}">Evidências</label><br>
                            <small class="form-text text-muted">Descrição das evidências</small>
                            <textarea class="form-control auto-resize no-scroll" id="evidencias_${count}" name="evidencias[]" rows="3" required></textarea>
                        </div>
                    `;

                    var newSEI = document.createElement('div');
                    newSEI.classList.add('col-md-4');
                    newSEI.innerHTML = `
                        <div class="form-group">
                            <label for="sei_${count}">Número do SEI</label><br>
                            <small class="form-text text-muted">Insira o número do SEI (se não tiver, coloque N/A)</small>
                            <textarea class="form-control auto-resize no-scroll" id="sei_${count}" name="sei[]" rows="3" required></textarea>
                        </div>
                    `;

                    var buttonGroup = document.createElement('div');
                    buttonGroup.classList.add('col-md-12', 'text-end', 'mt-2', 'd-flex', 'justify-content-end');

                    if (count > 0 || atividadesEvidenciasSEI.querySelectorAll('.row').length > 1) {
                        buttonGroup.appendChild(createRemoveButton());
                    }
                    buttonGroup.appendChild(createAddMoreButton());

                    newRow.appendChild(newTarefas);
                    newRow.appendChild(newEvidencias);
                    newRow.appendChild(newSEI);
                    newRow.appendChild(buttonGroup);

                    atividadesEvidenciasSEI.appendChild(newRow);

                    // Ajusta a altura inicial do novo textarea
                    newRow.querySelectorAll('textarea').forEach(function(textarea) {
                        autoResize.call(textarea);
                        textarea.addEventListener('input', autoResize);
                    });

                    updateAddMoreButton();
                    toggleRemoveButtons();

                    count++;
                }

                // Função para atualizar o botão "Incluir Mais" para estar apenas na última linha
                function updateAddMoreButton() {
                    // Remover todos os botões "Incluir Mais"
                    document.querySelectorAll('.add-more').forEach(function(button) {
                        button.remove();
                    });

                    // Adicionar "Incluir Mais" à última linha
                    var lastRow = atividadesEvidenciasSEI.lastElementChild;
                    if (lastRow) {
                        var buttonGroup = lastRow.querySelector('.d-flex');
                        if (buttonGroup) {
                            buttonGroup.appendChild(createAddMoreButton());
                        }
                    }
                }

                // Função para mostrar ou ocultar os botões "Remover" com base no número de linhas
                function toggleRemoveButtons() {
                    var rows = atividadesEvidenciasSEI.querySelectorAll('.row');
                    if (rows.length === 1) {
                        rows[0].querySelector('.remove-row')?.remove();
                    } else {
                        document.querySelectorAll('.remove-row').forEach(function(button) {
                            button.style.display = 'inline-block';
                        });
                    }
                }

                // Adicionar funcionalidade de remoção às linhas existentes
                document.querySelectorAll('.remove-row').forEach(function(button) {
                    button.addEventListener('click', function() {
                        var row = button.closest('.row');
                        atividadesEvidenciasSEI.removeChild(row);
                        updateAddMoreButton();
                        toggleRemoveButtons();
                    });
                });

                // Garantir que o botão "Incluir Mais" esteja na última linha ao carregar a página
                updateAddMoreButton();
                // Verificar se o botão "Remover" deve estar visível ou não
                toggleRemoveButtons();
            });

            document.addEventListener('DOMContentLoaded', function() {
                // Obtém o status do relatório
                var statusRelatorio = @json($relatorio->validacao->status ?? '');

                // Elementos dos botões
                var botaoSalvarRascunho = document.querySelector('button[name="salvar_como_rascunho"]');
                var botaoGerarRelatorio = document.querySelector('button[type="submit"].btn-success');

                // Ajustar os botões com base no status
                if (statusRelatorio === 'Para Corrigir') {
                    // Esconde o botão "Salvar como Rascunho"
                    if (botaoSalvarRascunho) {
                        botaoSalvarRascunho.style.display = 'none';
                    }

                    // Renomeia o botão "Gerar Relatório" para "Atualizar"
                    if (botaoGerarRelatorio) {
                        botaoGerarRelatorio.textContent = 'Atualizar';
                    }
                }
            });
        </script>
    </div>
@endsection
