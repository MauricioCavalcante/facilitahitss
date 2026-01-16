@extends('ana::layouts.app')


@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <h2>Editar Coordenação</h2>

                <!-- Formulário para editar uma coordenação existente -->
                <form action="{{ route('ana::coordenacoes.atualizar', $coordenacao->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="card mb-4">
                        <div class="card-header">
                            <h5>Detalhes da Coordenação</h5>
                        </div>
                        <div class="card-body">
                        <!-- Campo para o código da coordenação -->
                            <div class="form-group my-3">
                                <label for="codigo">Código</label>
                                <input type="text" name="codigo" class="form-control" value="{{ $coordenacao->codigo }}" required>
                            </div>

                            <!-- Campo para o nome da coordenação -->
                            <div class="form-group my-3">
                                <label for="nome">Nome</label>
                                <input type="text" name="nome" class="form-control" value="{{ $coordenacao->nome }}" required>
                            </div>
                        </div>
                    </div>

                    <div class="card mb-4">
                        <div class="card-header">
                            <h5>Inserir Escopos</h5>
                        </div>
                        <div class="card-body">
                            <!-- Área dinâmica para editar/remover escopos existentes e adicionar novos -->
                            <div id="escopos-container">
                                @if ($coordenacao->escopos->isEmpty())
                                    <!-- Caso não existam escopos, exibe um campo vazio para adicionar um novo -->
                                    <div class="row align-items-end my-3">
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label for="escopos_0">Escopo</label>
                                                <textarea class="form-control auto-resize no-scroll" id="escopos_0" name="escopos[][escopo]" rows="3" required
                                                    placeholder="Insira o escopo da coordenação"></textarea>
                                            </div>
                                        </div>
                                        <div class="col-md-12 text-end mt-2 d-flex justify-content-end">
                                            <button type="button" class="btn btn-danger me-2 remove-row" style="display: none;">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                            <button type="button" class="btn btn-primary add-more">
                                                <i class="bi bi-plus-lg"></i>
                                            </button>
                                        </div>
                                    </div>
                                @else
                                    @foreach ($coordenacao->escopos as $index => $escopo)
                                        <div class="row align-items-end my-3">
                                            <div class="col-md-12">
                                                <div class="form-group">
                                                    <label for="escopos_{{ $index }}">Escopo</label>
                                                    <input type="hidden" name="escopos[{{ $index }}][id]"
                                                        value="{{ $escopo->id }}">
                                                    <textarea class="form-control auto-resize no-scroll" id="escopos_{{ $index }}"
                                                        name="escopos[{{ $index }}][escopo]" rows="3" required>{{ $escopo->escopo }}</textarea>
                                                </div>
                                            </div>
                                            <div class="col-md-12 text-end mt-2 d-flex justify-content-end">
                                                <button type="button" class="btn btn-danger me-2 remove-row">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                                @if ($loop->last)
                                                    <button type="button" class="btn btn-primary add-more">
                                                        <i class="bi bi-plus-lg"></i>
                                                    </button>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Botões para cancelar ou atualizar a coordenação -->
                    <div class="d-flex justify-content-end">
                        <a href="{{ route('ana::coordenacoes.index') }}" class="btn btn-secondary me-2">Cancelar</a>
                        <button type="submit" class="btn btn-success">Atualizar</button>
                    </div>
                </form>
            </div>
        </div>

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                var escoposContainer = document.getElementById('escopos-container');
                var count = escoposContainer.querySelectorAll('.row').length;

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

                // Função para adicionar novos pares de textarea
                function addNewRow() {
                    var newRow = document.createElement('div');
                    newRow.classList.add('row', 'align-items-end', 'my-3');

                    var newEscopo = document.createElement('div');
                    newEscopo.classList.add('col-md-12');
                    newEscopo.innerHTML = `
                        <div class="form-group">
                            <label for="escopos_${count}">Escopo</label>
                            <textarea class="form-control auto-resize no-scroll" id="escopos_${count}"
                                name="escopos[${count}][escopo]" rows="3" required></textarea>
                        </div>
                    `;

                    var buttonGroup = document.createElement('div');
                    buttonGroup.classList.add('col-md-12', 'text-end', 'mt-2', 'd-flex', 'justify-content-end');

                    var removeBtn = document.createElement('button');
                    removeBtn.type = 'button';
                    removeBtn.classList.add('btn', 'btn-danger', 'me-2', 'remove-row');
                    removeBtn.innerHTML = '<i class="bi bi-trash"></i>';
                    removeBtn.addEventListener('click', function() {
                        escoposContainer.removeChild(newRow);
                        updateAddMoreButton();
                    });

                    buttonGroup.appendChild(removeBtn);
                    buttonGroup.appendChild(createAddMoreButton());

                    newRow.appendChild(newEscopo);
                    newRow.appendChild(buttonGroup);

                    escoposContainer.appendChild(newRow);

                    // Ajusta a altura inicial do novo textarea
                    newRow.querySelectorAll('textarea').forEach(function(textarea) {
                        autoResize.call(textarea);
                        textarea.addEventListener('input', autoResize);
                    });

                    count++;
                }

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

                // Adiciona o botão "Incluir Mais" na última linha
                function updateAddMoreButton() {
                    document.querySelectorAll('.add-more').forEach(function(button) {
                        button.remove();
                    });
                    var lastRow = escoposContainer.lastElementChild;
                    if (lastRow) {
                        var buttonGroup = lastRow.querySelector('.d-flex');
                        if (buttonGroup) {
                            buttonGroup.appendChild(createAddMoreButton());
                        }
                    }
                }

                // Adicionar funcionalidade de remoção às linhas existentes
                document.querySelectorAll('.remove-row').forEach(function(button) {
                    button.addEventListener('click', function() {
                        var row = button.closest('.row');
                        escoposContainer.removeChild(row);
                        updateAddMoreButton();
                    });
                });

                // Garantir que o botão "Incluir Mais" esteja na última linha ao carregar a página
                updateAddMoreButton();
            });
        </script>
    </div>
@endsection
