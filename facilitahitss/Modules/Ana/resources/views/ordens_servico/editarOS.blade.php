@extends('ana::layouts.app')


@section('content')
    <div class="container mt-4">
        <div class="row">
            <div class="col-md-12">
                <h2>Editar Ordem de Serviço</h2>

                <!-- Formulário para atualizar a Ordem de Serviço existente -->
                <form action="{{ route('ana::ordens_servico.atualizar', $ordensServico->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <!-- Detalhes da Ordem de Serviço -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5>Detalhes da Ordem de Serviço</h5>
                        </div>
                        <div class="card-body">
                            <!-- Campo para o número da Ordem de Serviço -->
                            <div class="form-group my-3">
                                <label for="numero">Número</label>
                                <input type="text" name="numero" id="numero" class="form-control"
                                    value="{{ $ordensServico->numero }}" required>
                            </div>
                            <!-- Campo para o documento da Ordem de Serviço -->
                            <div class="form-group my-3">
                                <label for="documento">Documento</label>
                                <input type="text" name="documento" id="documento" class="form-control"
                                    value="{{ $ordensServico->documento }}" required>
                            </div>
                            <!-- Campos para as datas de início, fim e o prazo da Ordem de Serviço -->
                            <div class="row my-3">
                                <div class="col-md-4">
                                    <label for="data_inicio">Data Início</label>
                                    <input type="date" name="data_inicio" id="data_inicio" class="form-control"
                                        value="{{ $ordensServico->data_inicio }}" required>
                                </div>
                                <div class="col-md-4">
                                    <label for="data_fim">Data Fim</label>
                                    <input type="date" name="data_fim" id="data_fim" class="form-control"
                                        value="{{ $ordensServico->data_fim }}" required>
                                </div>
                                <div class="col-md-4">
                                    <label for="prazo">Prazo para Entrega do Relatório (1 a 10 dias úteis)</label>
                                    <input type="number" name="prazo" id="prazo" class="form-control" min="1" max="10"
                                        value="{{ $ordensServico->prazo }}" required>
                                </div>
                            </div>
                            <!-- Campo para a quantidade de horas estimadas -->
                            <div class="form-group my-3">
                                <label for="horas">Quantidade de horas estimadas</label>
                                <input type="text" name="horas" id="horas" class="form-control"
                                    value="{{ $ordensServico->horas }}" required>
                            </div>
                            <!-- Campo para o dendereço do git -->
                            <div class="form-group my-3">
                                <label for="endereco">Endereço do Git</label>
                                <input type="text" name="endereco" id="endereco" class="form-control"
                                    value="{{ $ordensServico->endereco }}" required>
                            </div>
                        </div>
                    </div>

                    <!-- Seleção de Usuários -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5>Usuários Envolvidos</h5>
                        </div>
                        <div class="card-body">
                            <select name="users[]" id="users" class="form-control select2" multiple="multiple" required>
                                @foreach ($users as $user)
                                    <option value="{{ $user->user_id }}"
                                        {{ in_array($user->user_id, $usuariosSelecionados) ? 'selected' : '' }}>
                                        {{ $user->user->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <!-- Coordenações e Escopos -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5>Coordenações e Escopos</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Código</th>
                                            <th>Nome</th>
                                            <th>Ação</th>
                                        </tr>
                                    </thead>
                                    <tbody id="coordenacoes-table">
                                        @foreach ($coordenacoes as $coordenacao)
                                            <tr>
                                                <td>{{ $coordenacao->codigo }}</td>
                                                <td>{{ $coordenacao->nome }}</td>
                                                <td>
                                                    <button type="button"
                                                        class="btn {{ in_array($coordenacao->id, $coordenacoesSelecionadas) ? 'btn-danger' : 'btn-primary' }}"
                                                        onclick="toggleCoordenacao({{ $coordenacao->id }}, '{{ $coordenacao->codigo }}', this)">
                                                        {{ in_array($coordenacao->id, $coordenacoesSelecionadas) ? 'Excluir' : 'Incluir' }}
                                                    </button>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <div id="selected-coordenacoes-container" class="mt-4">
                                <!-- Coordenações e Escopos Selecionados -->
                                @foreach ($coordenacoesSelecionadas as $coordenacaoId)
                                    @php
                                        $coordenacao = $coordenacoes->find($coordenacaoId);
                                    @endphp
                                    <div class="form-group my-3" id="escopo_{{ $coordenacaoId }}">
                                        <label>Selecione o(s) Escopo(s) para Coordenação {{ $coordenacao->codigo }}</label>
                                        <div class="form-check">
                                            @foreach ($coordenacao->escopos as $escopo)
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox"
                                                        name="escopos[{{ $coordenacaoId }}][]" value="{{ $escopo->id }}"
                                                        id="escopo_{{ $escopo->id }}"
                                                        {{ in_array($escopo->id, $escoposSelecionados[$coordenacaoId] ?? []) ? 'checked' : '' }}>

                                                    <label class="form-check-label" for="escopo_{{ $escopo->id }}">
                                                        {{ $escopo->escopo }}
                                                    </label>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                    <input type="hidden" name="coordenacao_id[]" value="{{ $coordenacaoId }}"
                                        id="coordenacao_id_{{ $coordenacaoId }}">
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <!-- Botões de ação -->
                    <div class="d-flex justify-content-end">
                        <a href="{{ route('ana::ordens_servico.index') }}" class="btn btn-secondary me-2">Cancelar</a>
                        <button type="submit" class="btn btn-success">Atualizar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Script para manipulação de coordenações e escopos -->
    <script>
        let selectedCoordenacoes = @json($coordenacoesSelecionadas);

        function toggleCoordenacao(id, codigo, button) {
            const index = selectedCoordenacoes.indexOf(id);
            if (index === -1) {
                selectedCoordenacoes.push(id);
                button.classList.replace('btn-primary', 'btn-danger');
                button.innerHTML = 'Excluir';

                fetch(`{{ url('ana/ordens_servico/escopos') }}/${id}`)
                    .then(response => response.json())
                    .then(escopos => {
                        const container = document.getElementById('selected-coordenacoes-container');
                        const div = document.createElement('div');
                        div.className = 'form-group my-3';
                        div.id = `escopo_${id}`;

                        let escopoCheckboxes = `<label for="escopos_${id}">Selecione o(s) Escopo(s) para Coordenação ${codigo}</label>
                            <div class="form-check">`;

                        escopos.forEach(escopo => {
                            escopoCheckboxes += `
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="escopos[${id}][]" value="${escopo.id}" id="escopo_${escopo.id}">
                                    <label class="form-check-label" for="escopo_${escopo.id}">${escopo.escopo}</label>
                                </div>`;
                        });

                        escopoCheckboxes += `</div>`;
                        div.innerHTML = escopoCheckboxes;
                        container.appendChild(div);

                        const inputHidden = document.createElement('input');
                        inputHidden.type = 'hidden';
                        inputHidden.name = 'coordenacao_id[]';
                        inputHidden.value = id;
                        inputHidden.id = `coordenacao_id_${id}`;
                        container.appendChild(inputHidden);
                    })
                    .catch(error => console.error('Erro ao carregar os escopos:', error));
            } else {
                selectedCoordenacoes.splice(index, 1);
                button.classList.replace('btn-danger', 'btn-primary');
                button.innerHTML = 'Incluir';

                document.getElementById(`escopo_${id}`)?.remove();
                document.getElementById(`coordenacao_id_${id}`)?.remove();
            }
        }

        // Inicializar Select2 para o campo de usuários
        $(document).ready(function() {
            $('#users').select2({
                placeholder: 'Selecione os usuários',
                allowClear: true,
                width: '100%'
            });
        });
    </script>

@endsection
