@extends('aneel::layouts.app')


@section('content')
    <div class="container mt-4">
        <!-- Cabeçalho -->
        <div class="row justify-content-between align-items-center mb-4">
            <div class="col">
                <h2 class="mb-0">Relatórios Técnicos de Atividades (RTA)</h2>
            </div>
            <div class="col text-end">
                @if (Auth::user()->role == 'admin' || Auth::user()->role === 'editor')
                    <a href="{{ route('aneel::reportsRTA.create') }}" class="btn btn-success">
                        <i class="bi bi-plus-circle"></i> Criar Novo Relatório
                    </a>
                @endif
            </div>
        </div>

        <div class="mb-4">
            <div class="row py-4">
                @forelse($reports as $report)
                    <div class="col-md-6 col-lg-4 mb-4">
                        <div class="card shadow-sm border-0 h-100">
                            <!-- Cabeçalho do card -->
                            <div class="card-header d-flex align-items-center bg-light px-3 py-2">
                                <img src="{{ asset('img/docx_icon.png') }}" alt="Ícone DOCX" width="40" class="me-3">
                                <h6 class="card-title mb-0 text-truncate">{{ $report->name }}</h6>
                                <div class="dropdown ms-auto">
                                    <a href="#" class="text-muted" id="dropdownMenuButton" data-bs-toggle="dropdown"
                                        aria-expanded="false">
                                        <i class="bi bi-chevron-down"></i>
                                    </a>
                                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownMenuButton">
                                        <li>
                                            <a class="dropdown-item" href="{{ route('aneel::downloadReport', ['id' => $report->id]) }}">
                                                Baixar Relatório
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item" href="{{ route('aneel::archivesRTA', $report->id) }}">
                                                Baixar Pasta Completa
                                            </a>
                                        </li>
                                        @if (Auth::user()->role == 'admin' || Auth::user()->role === 'editor')
                                            <li>
                                                <a class="dropdown-item"
                                                    href="{{ route('aneel::reportsRTA.edit', $report->id) }}">
                                                    Editar
                                                </a>
                                            </li>
                                        @endif
                                        <li>
                                            <hr class="dropdown-divider">
                                        </li>
                                        @if (Auth::user()->role == 'admin')
                                            <li>
                                                <a class="dropdown-item text-danger" href="#" data-bs-toggle="modal"
                                                    data-bs-target="#deleteModal" data-id="{{ $report->id }}"
                                                    data-name="{{ $report->nome }}"
                                                    data-url="{{ route('aneel::reportsRTA.destroy', $report->id) }}">
                                                    Excluir
                                                </a>
                                            </li>
                                        @endif
                                    </ul>
                                </div>
                            </div>

                            <div class="card-body px-3 py-3">
                                <p class="mb-2"><strong>Período:</strong>
                                    {{ \Carbon\Carbon::parse($report->period_start)->format('d/m/Y') }} a
                                    {{ \Carbon\Carbon::parse($report->period_end)->format('d/m/Y') }}</p>
                                @if ($report->attachment)
                                    <small class="text-muted">
                                        Tamanho do arquivo: {{ number_format($report->attachment_size / 1048576, 2) }} MB
                                    </small>
                                @else
                                    <small class="text-muted">
                                        Relatório não gerado.
                                    </small>
                                @endif
                            </div>

                            <div class="card-footer bg-light px-3 py-2">
                                <div class="d-flex justify-content-between align-items-center">
                                    @php
                                        $statusColor = match ($report->status) {
                                            'Em Andamento' => '#FFD700',
                                            'Finalizado' => '#28a745',
                                            default => '#6c757d',
                                        };
                                    @endphp

                                    <small class="d-flex align-items-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                            fill="{{ $statusColor }}" class="bi bi-circle-fill me-1" viewBox="0 0 16 16">
                                            <circle cx="8" cy="8" r="8" />
                                        </svg>
                                        <span>{{ $report->status }}</span>
                                    </small>


                                    <a href="{{ route('aneel::reportsRTA.show', ['id' => $report->id]) }}"
                                        class="btn btn-sm btn-primary">
                                        <i class="bi bi-eye"></i> Detalhes
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                @empty
                    <div class="col">
                        <div class="alert alert-warning text-center">
                            <i class="bi bi-exclamation-circle me-2"></i> Nenhum relatório foi gerado até o momento.
                        </div>
                    </div>
                @endforelse
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
                    Você tem certeza que deseja excluir o relatório <strong id="modal-item-name"></strong>?
                </div>
                <div class="modal-footer">
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

    <!-- Modal de alerta -->
    <div class="modal fade" id="bloqueioModal" tabindex="-1" aria-labelledby="bloqueioModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="bloqueioModalLabel">Ação Bloqueada</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Você precisa inserir novos indicadores para criar um novo relatório.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Entendido</button>
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
