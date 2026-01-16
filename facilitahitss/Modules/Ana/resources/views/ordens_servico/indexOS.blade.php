@extends('ana::layouts.app')


@section('content')
    <div class="container mt-4">
        <!-- Linha para o título e botão de adicionar nova OS -->
        <div class="row justify-content-between align-items-center mb-4">
            <div class="col">
                <h2>Ordens de Serviço</h2> <!-- Título da página -->
            </div>
            @if (Auth::user()->role == 'admin')
                <!-- Administradores e Coordenadores podem adicionar nova OS -->
                <div class="col-auto">
                    <a href="{{ route('ana::ordens_servico.criar') }}" class="btn btn-success">
                        <i class="bi bi-plus-lg"></i> Adicionar Nova
                    </a>
                </div>
            @endif
        </div>

        <div class="row">
            <!-- Loop para exibir cada Ordem de Serviço (OS) -->
            @forelse($ordensServico as $os)
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card shadow-sm border-0 h-100">
                        <!-- Cabeçalho do card com título e status da OS -->
                        <div class="card-header d-flex justify-content-between align-items-center bg-white px-4 pt-4">
                            <div>
                                <!-- Número da OS -->
                                <h5 class="card-title mb-1">OS {{ $os->numero }}</h5>
                                <!-- Badge indicando o status da OS -->
                                <span
                                    class="badge bg-{{ $os->status === 'Nova' ? 'success' : ($os->status === 'Em andamento' ? 'warning' : 'danger') }}
                                    {{ Auth::user()->role == 'admin' ? 'clickable' : '' }}"
                                    style="{{ Auth::user()->role == 'admin' ? 'cursor: pointer;' : 'cursor: default;' }}"
                                    id="status-badge-{{ $os->id }}"
                                    onclick="{{ Auth::user()->role == 'admin' ? 'toggleStatus(' . $os->id . ')' : '' }}">
                                    {{ $os->status }}
                                </span>

                            </div>
                            <div class="dropdown">
                                <!-- Botão de Dropdown com Ícone de Seta para Baixo -->
                                <a href="#" class="text-muted" id="dropdownMenuButton" data-bs-toggle="dropdown"
                                    aria-expanded="false">
                                    <i class="bi bi-chevron-down"></i>
                                </a>
                                <!-- Menu dropdown com opções de ação -->
                                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownMenuButton">
                                    <li><a class="dropdown-item" href="#" data-bs-toggle="modal"
                                            data-bs-target="#viewModal" data-os="{{ json_encode($os) }}">Visualizar
                                            escopo
                                        </a></li>
                                    @if (Auth::user()->role == 'admin')
                                        <!-- Dropdown de ações para Administradores e Coordenadores -->
                                        <li><a class="dropdown-item"
                                                href="{{ route('ana::ordens_servico.editar', $os->id) }}">Editar</a></li>
                                        <li><a class="dropdown-item" href="#" data-bs-toggle="modal"
                                                data-bs-target="#duplicateModal" data-os="{{ json_encode($os) }}">Duplicar
                                                OS</a></li>
                                        <li>
                                            <hr class="dropdown-divider">
                                        </li>
                                        <li><a class="dropdown-item text-danger" href="#" data-bs-toggle="modal"
                                                data-bs-target="#deleteModal" data-id="{{ $os->id }}"
                                                data-name="{{ $os->numero }}"
                                                data-url="{{ route('ana::ordens_servico.excluir', $os->id) }}">Excluir</a>
                                        </li>
                                    @endif
                                </ul>
                            </div>
                        </div>

                        <!-- Corpo do card com detalhes da OS -->
                        <div class="card-body px-4">
                            <!-- Informações da OS, como documento, datas, coordenações e usuários -->
                            <p class="mb-2" style="font-size: 14px;"><strong>Documento:</strong> {{ $os->documento }}</p>
                            <p class="mb-2" style="font-size: 14px;"><strong>Data Início:</strong>
                                {{ \Carbon\Carbon::parse($os->data_inicio)->format('d/m/Y') }}</p>
                            <p class="mb-2" style="font-size: 14px;"><strong>Data Fim:</strong>
                                {{ \Carbon\Carbon::parse($os->data_fim)->format('d/m/Y') }}</p>
                            <p class="mb-2" style="font-size: 14px;"><strong>Coordenações:</strong>
                                <!-- Loop para exibir cada coordenação associada -->
                                @foreach ($os->coordenacoes as $coordenacao)
                                    {{ $coordenacao->codigo }};
                                @endforeach
                            </p>
                            <p class="mb-0" style="font-size: 14px;"><strong>Participantes:</strong>
                                @if ($os->users->isNotEmpty())
                                    @foreach ($os->users as $user)
                                        {{ $user->name }};
                                    @endforeach
                                @else
                                    <span class="text-muted">Nenhum participante.</span>
                                @endif
                            </p>
                        </div>
                    </div>
                </div>
            @empty
                <!-- Exibição de mensagem caso não haja nenhuma OS cadastrada -->
                <div class="col">
                    <div class="alert alert-warning">Nenhuma ordem de serviço cadastrada.</div>
                </div>
            @endforelse
        </div>

        <!-- Paginação -->
        <div class="d-flex justify-content-center mt-4">
            {{ $ordensServico->appends(request()->query())->links('pagination::bootstrap-4') }}
        </div>
    </div>

    <!-- Modal para Visualização dos Escopos -->
    <div class="modal fade" id="viewModal" tabindex="-1" aria-labelledby="viewModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="viewModalLabel">Escopos da Ordem de Serviço</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- Lista para exibição dos escopos -->
                    <ul id="os-escopo" class="list-group">
                        <!-- Escopos serão preenchidos dinamicamente via JavaScript -->
                    </ul>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                </div>
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
                    Você tem certeza que deseja excluir a OS <strong id="modal-item-name"></strong>?
                </div>
                <div class="modal-footer">
                    <!-- Formulário para confirmar exclusão -->
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

    <!-- Modal para Duplicação da Ordem de Serviço -->
    <div class="modal fade" id="duplicateModal" tabindex="-1" aria-labelledby="duplicateModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="duplicateModalLabel">Duplicar Ordem de Serviço</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="duplicateForm" method="POST" action="">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="data_inicio" class="form-label">Data Início</label>
                            <input type="date" name="data_inicio" id="data_inicio" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="data_fim" class="form-label">Data Fim</label>
                            <input type="date" name="data_fim" id="data_fim" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="prazo" class="form-label">Prazo para Relatório (1 a 10 dias)</label>
                            <input type="number" name="prazo" id="prazo" class="form-control" min="1" max="10" required>
                        </div>
                        <div class="mb-3">
                            <label for="endereco" class="form-label">Novo Endereço</label>
                            <input type="text" name="endereco" id="endereco" class="form-control" required placeholder="Informe o novo endereço">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-success">Duplicar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function toggleStatus(osId) {
            // Verificar se o usuário não é um Funcionário
            @if (Auth::user()->role == 'admin') // Supondo que o ID do grupo Funcionário seja 3
                const badge = document.getElementById(`status-badge-${osId}`);

                // Define o próximo status baseado no status atual
                let currentStatus = badge.textContent.trim();
                let newStatus;

                if (currentStatus === 'Nova') {
                    newStatus = 'Em andamento';
                } else if (currentStatus === 'Em andamento') {
                    newStatus = 'Encerrada';
                } else if (currentStatus === 'Encerrada') {
                    newStatus = 'Nova';
                }

                // Chamada AJAX para atualizar o status da OS
                const url = `{{ route('ana::ordens_servico.atualizarStatus', ':osId') }}`.replace(':osId', osId);

                fetch(url, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            status: newStatus
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Atualiza o texto e a cor do badge
                            badge.textContent = newStatus;
                            badge.className =
                                `badge bg-${newStatus === 'Nova' ? 'success' : newStatus === 'Em andamento' ? 'warning' : 'danger'}`;
                        } else {
                            alert('Não foi possível atualizar o status. Tente novamente.');
                        }
                    })
                    .catch(error => {
                        console.error('Erro:', error);
                        alert('Ocorreu um erro ao atualizar o status.');
                    });
            @endif
        }

        var viewModal = document.getElementById('viewModal');
        viewModal.addEventListener('show.bs.modal', function(event) {
            var button = event.relatedTarget; // Botão que acionou o modal
            var os = JSON.parse(button.getAttribute('data-os')); // Obter dados da OS
            var escoposList = document.getElementById('os-escopo');
            escoposList.innerHTML = ''; // Limpar conteúdo anterior

            // Itera sobre cada coordenação associada à OS
            os.coordenacoes.forEach(function(coordenacao) {
                // Cria um título para a coordenação
                var coordTitle = document.createElement('li');
                coordTitle.innerHTML = `<strong>Coordenação: ${coordenacao.codigo}</strong>`;
                coordTitle.classList.add('mb-2', 'list-group-item', 'list-group-item-light');
                escoposList.appendChild(coordTitle);

                // Busca os escopos associados à coordenação na OS usando a tabela pivô
                const escoposAssociados = os.ordemservico_escopo.filter(function(associacao) {
                    return associacao.coordenacao_id == coordenacao.id;
                });

                if (escoposAssociados.length > 0) {
                    escoposAssociados.forEach(function(associacao) {
                        var li = document.createElement('li');
                        li.classList.add('list-group-item', 'list-group-item-action');

                        if (typeof associacao.escopo === 'object') {
                            li.innerHTML = associacao.escopo.escopo.replace(/\n/g, '<br>');
                        } else {
                            li.innerHTML = associacao.escopo.replace(/\n/g, '<br>');
                        }

                        escoposList.appendChild(li);
                    });
                } else {
                    var noEscopo = document.createElement('li');
                    noEscopo.classList.add('list-group-item', 'text-muted');
                    noEscopo.innerHTML = 'Nenhum escopo associado a esta coordenação.';
                    escoposList.appendChild(noEscopo);
                }
            });
        });

        // Script para preencher o modal de exclusão com os dados corretos
        var deleteModal = document.getElementById('deleteModal');
        deleteModal.addEventListener('show.bs.modal', function(event) {
            var button = event.relatedTarget; // Botão que acionou o modal
            var url = button.getAttribute('data-url'); // URL para exclusão
            var name = button.getAttribute('data-name'); // Nome da OS

            var modalBody = deleteModal.querySelector('.modal-body #modal-item-name');
            var deleteForm = deleteModal.querySelector('#deleteForm');

            modalBody.textContent = name; // Exibir nome no modal
            deleteForm.action = url; // Definir ação do formulário
        });

        var duplicateModal = document.getElementById('duplicateModal');
        duplicateModal.addEventListener('show.bs.modal', function(event) {
            var button = event.relatedTarget; // Botão que acionou o modal
            var os = JSON.parse(button.getAttribute('data-os')); // Obter dados da OS

            var duplicateForm = document.getElementById('duplicateForm');
            duplicateForm.action =
                `{{ url('ana/ordens_servico') }}/${os.id}/duplicar`; // Define a rota para duplicar a OS
        });
    </script>
@endsection
