@extends('ana::layouts.app')


@section('content')
    <div class="container mt-4">
        <!-- Título da seção e botão para adicionar novo relatório -->
        <div class="row justify-content-between align-items-center mb-4">
            <div class="col">
                <h2>Meus Relatórios Executivos</h2>
            </div>
            <div class="col-auto">
                <a href="{{ route('ana::relatorio_executivo.criar') }}" class="btn btn-success">
                    <i class="bi bi-plus-lg"></i> Criar Novo Relatório
                </a>
            </div>
        </div>

        <div class="row">
            <!-- Loop para exibir cada relatório em um card -->
            @forelse($relatorios as $relatorio)
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card shadow-sm border-0 h-100">
                        <!-- Imagem e cabeçalho do card com título do relatório -->
                        <div class="card-header d-flex justify-content-between align-items-center bg-white px-4 pt-4">
                            <div class="d-flex align-items-center mb-2">
                                <img src="{{ asset('img/docx_icon.png') }}" alt="Ícone DOCX" width="40"
                                    style='margin-right: 10px;'>
                                <div class="flex-grow-1">
                                    <h5 class="card-title mb-1">
                                        <!-- Nome do relatório com ajuste de quebra de linha para evitar transbordo -->
                                        Relatório da OS {{ $relatorio->ordemServico->numero }}
                                    </h5>
                                    <!-- Status do Relatório com cores -->
                                    @if (!empty($relatorio->validacao))
                                        <span
                                            class="badge
                                            @if ($relatorio->validacao->status === 'Validado') bg-success
                                            @elseif(in_array($relatorio->validacao->status, ['Para Corrigir', 'Corrigido'])) bg-warning
                                            @elseif($relatorio->validacao->status === 'Novo') bg-primary
                                            @elseif($relatorio->validacao->status === 'Rascunho') bg-secondary @endif">
                                            {{ $relatorio->validacao->status }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                            <div class="dropdown">
                                <!-- Botão de Dropdown com Ícone de Seta para Baixo -->
                                <a href="#" class="text-muted" id="dropdownMenuButton" data-bs-toggle="dropdown"
                                    aria-expanded="false">
                                    <i class="bi bi-chevron-down"></i>
                                </a>
                                <!-- Menu dropdown com opções de ação -->
                                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownMenuButton">
                                    <li><a class="dropdown-item"
                                            href="{{ route('ana::relatorio_executivo.baixar', $relatorio->id) }}">Baixar</a>
                                    </li>
                                    @if ($relatorio->validacao)
                                        @if ($relatorio->validacao->status !== 'Validado')
                                            <li><a class="dropdown-item"
                                                    href="{{ route('ana::relatorio_executivo.editar', $relatorio->id) }}">Editar</a>
                                            </li>
                                        @endif
                                    @endif
                                    <li>
                                        <hr class="dropdown-divider">
                                    </li>
                                    <li><a class="dropdown-item text-danger" href="#" data-bs-toggle="modal"
                                            data-bs-target="#deleteModal" data-id="{{ $relatorio->id }}"
                                            data-name="{{ $relatorio->nome }}"
                                            data-url="{{ route('ana::relatorio_executivo.excluir', $relatorio->id) }}">Excluir</a>
                                    </li>
                                </ul>
                            </div>
                        </div>

                        <!-- Corpo do card com detalhes do relatório -->
                        <div class="card-body px-4">
                            <!-- Informações do relatório: tipo, tamanho, data de criação e última edição -->
                            <p class="mb-2" style="font-size: 14px;"><strong>Coordenação:</strong>
                                {{ optional($relatorio->user->anauser->coordenacao)->codigo ?? 'Não definido' }}
                            </p>
                            <p class="mb-2" style="font-size: 14px;"><strong>Data de Criação:</strong>
                                {{ \Carbon\Carbon::parse($relatorio->data_criacao)->format('d/m/Y H:i') }}</p>
                            <p class="mb-2" style="font-size: 14px;"><strong>Última Edição:</strong>
                                {{ \Carbon\Carbon::parse($relatorio->data_edicao)->format('d/m/Y H:i') }}</p>
                        </div>

                        <!-- Rodapé do card -->
                        <div class="card-footer bg-light px-3 py-2">
                            <div class="d-flex justify-content-between align-items-center">
                                <small class="text-muted">Tamanho: {{ number_format($relatorio->tamanho / 1048576, 2) }}
                                    MB</small>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col">
                    <!-- @if (!$possuiOrdemServico)
                        <div class="alert alert-info">
                            Nenhuma Ordem de Serviço vinculada ao seu usuário.
                        </div>
                    @else
                        <div class="alert alert-warning">
                            Você ainda não criou nenhum relatório para suas Ordens de Serviço.
                        </div>
                    @endif -->
                </div>
            @endforelse
        </div>

        <!-- Paginação -->
        <div class="d-flex justify-content-center mt-4">
            {{ $relatorios->appends(request()->query())->links('pagination::bootstrap-4') }}
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
                    Você tem certeza que deseja excluir o relatório <strong id="modal-item-name"></strong>?
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
