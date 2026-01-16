@extends('ana::layouts.app')


@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <h2>Criar Relatório de Entrega</h2>

                <!-- Formulário para criar um novo relatório de entrega -->
                <form action="{{ route('ana::relatorio_executivo.salvar') }}" method="POST">
                    @csrf

                    <!-- Ordem de Serviço em andamento-->
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
                                        value="{{ $ordens_servico->first()->id }}">
                                @elseif ($ordens_servico->count() > 1)
                                    <!-- Quando há mais de uma OS, mas travada para edição -->
                                    <input type="text" class="form-control"
                                        value="{{ $relatorio->ordemServico->numero }}" readonly>
                                    <input type="hidden" name="ordem_servico_id"
                                        value="{{ $relatorio->ordem_servico_id }}">
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
                                <input type="text" class="form-control" id="titulo" name="titulo" required>
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
                                <textarea class="form-control auto-resize no-scroll" id="referencias" name="referencias" rows="3" required></textarea>
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
                                <textarea class="form-control auto-resize no-scroll" id="atividades" name="atividades" rows="3" required></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Card para a seção de evidências -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5>Evidências de Atividades Executadas</h5>
                        </div>
                        <div class="card-body" id="atividades-evidencias-sei">
                            <div class="row align-items-end my-3">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="tarefas_0">Atividades</label><br>
                                        <small class="form-text text-muted">Descrição das atividades (macro)</small>
                                        <textarea class="form-control auto-resize no-scroll" id="tarefas_0" name="tarefas[]" rows="3" required></textarea>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="evidencias_0">Evidências</label><br>
                                        <small class="form-text text-muted">Descrição das evidências</small>
                                        <textarea class="form-control auto-resize no-scroll" id="evidencias_0" name="evidencias[]" rows="3" required></textarea>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="sei_0">Número do SEI</label><br>
                                        <small class="form-text text-muted">Insira o número do SEI (se não tiver, coloque
                                            N/A)</small>
                                        <textarea class="form-control auto-resize no-scroll" id="sei_0" name="sei[]" rows="3" required></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Botões para cancelar, salvar como rascunho ou gerar o relatório -->
                    <div class="d-flex justify-content-end">
                        <a href="{{ route('ana::relatorio_executivo.index') }}" class="btn btn-secondary me-2">Cancelar</a>
                        <button type="submit" name="salvar_como_rascunho" value="1"
                            class="btn btn-warning me-2">Salvar como Rascunho</button>
                        <button type="submit" class="btn btn-success">Gerar Relatório</button>
                    </div>
                </form>
            </div>
        </div>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                var atividadesEvidenciasSEI = document.getElementById('atividades-evidencias-sei');
                var count = 1;

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
                    addMoreBtn.classList.add('btn', 'btn-primary');
                    addMoreBtn.innerHTML = '<i class="bi bi-plus-lg"></i>';
                    addMoreBtn.addEventListener('click', function() {
                        addNewRow();
                    });
                    return addMoreBtn;
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

                    var removeBtn = document.createElement('button');
                    removeBtn.type = 'button';
                    removeBtn.classList.add('btn', 'btn-danger', 'me-2');
                    removeBtn.innerHTML = '<i class="bi bi-trash"></i>';
                    removeBtn.addEventListener('click', function() {
                        atividadesEvidenciasSEI.removeChild(newRow);
                        // Mover o botão "Incluir Mais" para a última linha
                        var lastRow = atividadesEvidenciasSEI.lastElementChild;
                        if (lastRow) {
                            var buttonGroup = lastRow.querySelector('.d-flex');
                            if (buttonGroup && !buttonGroup.querySelector('.btn-primary')) {
                                buttonGroup.appendChild(createAddMoreButton());
                            }
                        }
                    });

                    buttonGroup.appendChild(removeBtn);
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

                    // Remover o botão "Incluir Mais" da linha anterior, se houver
                    if (atividadesEvidenciasSEI.children.length > 1) {
                        var previousRow = atividadesEvidenciasSEI.children[atividadesEvidenciasSEI.children
                            .length - 2];
                        var previousButtonGroup = previousRow.querySelector('.btn-primary');
                        if (previousButtonGroup) {
                            previousButtonGroup.remove();
                        }
                    }

                    count++;
                }

                // Adicionar o primeiro botão "Incluir Mais" ao carregar a página
                var initialButtonGroup = atividadesEvidenciasSEI.querySelector('.row .col-md-12');
                if (!initialButtonGroup) {
                    initialButtonGroup = document.createElement('div');
                    initialButtonGroup.classList.add('col-md-12', 'text-end', 'mt-2', 'd-flex', 'justify-content-end');
                    atividadesEvidenciasSEI.lastElementChild.appendChild(initialButtonGroup);
                }
                initialButtonGroup.appendChild(createAddMoreButton());
            });
        </script>
    </div>
@endsection
