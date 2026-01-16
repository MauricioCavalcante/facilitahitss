@extends('ana::layouts.app')


@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <h2>Adicionar Coordenação</h2>

                <!-- Formulário para adicionar uma nova coordenação -->
                <form action="{{ route('ana::coordenacoes.salvar') }}" method="POST">
                    @csrf

                    <div class="card mb-4">
                        <div class="card-header">
                            <h5>Detalhes da Coordenação</h5>
                        </div>
                        <div class="card-body">
                            <!-- Campo para o código da coordenação -->
                            <div class="form-group my-3">
                                <label for="codigo">Código</label>
                                <input type="text" name="codigo" class="form-control" value="{{ old('codigo') }}" required>
                            </div>

                            <!-- Campo para o nome da coordenação -->
                            <div class="form-group my-3">
                                <label for="nome">Nome</label>
                                <input type="text" name="nome" class="form-control" value="{{ old('nome') }}" required>
                            </div>
                        </div>
                    </div>

                    <div class="card mb-4">
                        <div class="card-header">
                            <h5>Inserir Escopos</h5>
                        </div>
                        <div class="card-body">
                            <!-- Área dinâmica para adicionar/remover escopos -->
                            <div id="escopos-container">
                                <div class="row align-items-end my-3">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label for="escopos_0">Escopo</label>
                                            <textarea class="form-control auto-resize no-scroll" id="escopos_0" name="escopos[]" rows="3" required
                                                placeholder="Insira o escopo da coordenação"></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Botões para cancelar ou adicionar a coordenação -->
                    <div class="d-flex justify-content-end">
                        <a href="{{ route('ana::coordenacoes.index') }}" class="btn btn-secondary me-2">Cancelar</a>
                        <button type="submit" class="btn btn-success">Adicionar Coordenação</button>
                    </div>
                </form>
            </div>
        </div>

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                var escoposContainer = document.getElementById('escopos-container');
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

                    var newEscopo = document.createElement('div');
                    newEscopo.classList.add('col-md-12');
                    newEscopo.innerHTML = `
                        <div class="form-group">
                            <label for="escopos_${count}">Escopo</label>
                            <textarea class="form-control auto-resize no-scroll" id="escopos_${count}" name="escopos[]" rows="3" required
                            placeholder="Insira o escopo da coordenação"></textarea>
                        </div>
                    `;

                    var buttonGroup = document.createElement('div');
                    buttonGroup.classList.add('col-md-12', 'text-end', 'mt-2', 'd-flex', 'justify-content-end');

                    var removeBtn = document.createElement('button');
                    removeBtn.type = 'button';
                    removeBtn.classList.add('btn', 'btn-danger', 'me-2');
                    removeBtn.innerHTML = '<i class="bi bi-trash"></i>';
                    removeBtn.addEventListener('click', function() {
                        escoposContainer.removeChild(newRow);
                        // Mover o botão "Incluir Mais" para a última linha
                        var lastRow = escoposContainer.lastElementChild;
                        if (lastRow) {
                            var buttonGroup = lastRow.querySelector('.d-flex');
                            if (buttonGroup && !buttonGroup.querySelector('.btn-primary')) {
                                buttonGroup.appendChild(createAddMoreButton());
                            }
                        }
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

                    // Remover o botão "Incluir Mais" da linha anterior, se houver
                    if (escoposContainer.children.length > 1) {
                        var previousRow = escoposContainer.children[escoposContainer.children.length - 2];
                        var previousButtonGroup = previousRow.querySelector('.btn-primary');
                        if (previousButtonGroup) {
                            previousButtonGroup.remove();
                        }
                    }

                    count++;
                }

                // Adicionar o primeiro botão "Incluir Mais" ao carregar a página
                var initialButtonGroup = escoposContainer.querySelector('.row .col-md-12');
                if (initialButtonGroup) {
                    initialButtonGroup = document.createElement('div');
                    initialButtonGroup.classList.add('col-md-12', 'text-end', 'mt-2', 'd-flex', 'justify-content-end');
                    escoposContainer.lastElementChild.appendChild(initialButtonGroup);
                }
                initialButtonGroup.appendChild(createAddMoreButton());
            });
        </script>
    </div>
@endsection
