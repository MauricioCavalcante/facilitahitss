@extends('ana::layouts.app')


@section('content')
    <div class="container mt-4">
        <div class="row justify-content-between align-items-center mb-4">
            <div class="col">
                <h2>Coordenações</h2>
            </div>
            <div class="col-auto">
                <!-- Botão para adicionar uma nova coordenação -->
                <a href="{{ route('ana::coordenacoes.criar') }}" class="btn btn-success float-end mb-3">
                    <i class="bi bi-plus-lg"></i> Adicionar Nova
                </a>
            </div>
        </div>

        <div class="row">
            <!-- Tabela para listar as coordenações -->
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Código</th>
                            <th>Nome</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Loop para exibir cada coordenação -->
                        @foreach ($coordenacoes as $coordenacao)
                            <tr>
                                <td>{{ $coordenacao->codigo }}</td>
                                <td>{{ $coordenacao->nome }}</td>
                                <td class="text-center">
                                    <div class="d-inline-flex gap-2">
                                        <!-- Botão para editar a coordenação -->
                                        <a href="{{ route('ana::coordenacoes.editar', $coordenacao->id) }}"
                                            class="btn btn-primary m-1">
                                            <i class="bi bi-pencil-square"></i>
                                        </a>
                                        <!-- Botão para excluir a coordenação (abre o modal de confirmação) -->
                                        <button type="button" class="btn btn-danger m-1" data-bs-toggle="modal"
                                            data-bs-target="#deleteModal" data-id="{{ $coordenacao->id }}"
                                            data-name="{{ $coordenacao->nome }}"
                                            data-url="{{ route('ana::coordenacoes.excluir', $coordenacao->id) }}">
                                            <i class="bi bi-trash3-fill"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal para confirmação de exclusão -->
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteModalLabel">Confirmar Exclusão</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Você tem certeza que deseja excluir <strong id="modal-item-name"></strong>?
                </div>
                <div class="modal-footer">
                    <!-- Formulário para confirmar a exclusão -->
                    <form id="deleteForm" method="POST" action="">
                        @csrf
                        @method('DELETE')
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-danger">Excluir</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Script para preencher o modal de exclusão com os dados corretos
        var deleteModal = document.getElementById('deleteModal');
        deleteModal.addEventListener('show.bs.modal', function(event) {
            var button = event.relatedTarget;
            var url = button.getAttribute('data-url');
            var name = button.getAttribute('data-name');

            var modalBody = deleteModal.querySelector('.modal-body #modal-item-name');
            var deleteForm = deleteModal.querySelector('#deleteForm');

            modalBody.textContent = name;
            deleteForm.action = url;
        });
    </script>
@endsection
