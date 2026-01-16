@extends('ana::layouts.app')

@section('content')
    <div class="container">
        <div class="card mb-4">
            <div class="card-header">
                <h5>Detalhes</h5>
            </div>
            <div class="card-body">
                <div class="form-group mb-3">
                    <p class="mb-1">Email Hitss</p>
                    <p class="form-control-plaintext bg-light">{{ $user->email }}</p>
                </div>

                <div class="form-group mb-3">
                    <p class="mb-1">Nome de usuário</p>
                    <p class="form-control-plaintext bg-light">{{ $user->username }}</p>
                </div>
            </div>
        </div>

        <form class="form-group" method="POST" action="{{ route('ana::usuarios.update', $usuario->user_id) }}">
            @csrf
            <div class="card mb-4">
                <div class="card-header">
                    <h5>Detalhes</h5>
                </div>
                <div class="card-body">
                    <input type="hidden" name="user_id" value="{{ $usuario->user_id }}">

                    <div class="form-group mb-3">
                        <label class="form-label" for="email_ana">Email ANA:</label>
                        <input class="form-control" type="email" name="email_ana" id="email_ana"
                            value="{{ old('email_ana', $usuario->email_ana) }}">
                    </div>

                    <div class="form-group mb-3">
                        <label class="form-label" for="coordenacao_id">Coordenação:</label>
                        <select class="form-select" id="coordenacao_id" name="coordenacao_id">
                            <option value="">Selecione a Coordenação</option>
                            @foreach ($coordenacoes as $coordenacao)
                                <option value="{{ $coordenacao->id }}"
                                    {{ old('coordenacao_id', $usuario->coordenacao_id) == $coordenacao->id ? 'selected' : '' }}>
                                    {{ $coordenacao->nome }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group mb-3">
                        <label class="form-label">Escopo:</label>
                        <div id="escopo_select">
                            @foreach ($escopos as $escopo)
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="escopo_id"
                                        id="escopo_{{ $escopo->id }}" value="{{ $escopo->id }}"
                                        {{ old('escopo_id', $usuario->escopo_id) == $escopo->id ? 'checked' : '' }}>
                                    <label class="form-check-label" for="escopo_{{ $escopo->id }}">
                                        {{ $escopo->escopo }}
                                    </label>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-end">
                <button class="btn btn-success" type="submit">Atualizar</button>
            </div>
        </form>
    </div>

    <script>
        document.getElementById('coordenacao_id').addEventListener('change', function() {
            let coordenacaoId = this.value;
            let escopoSelect = document.getElementById('escopo_select');

            if (coordenacaoId) {
                fetch("{{ route('ana::usuarios.getEscoposByCoordenacao') }}", {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/json",
                            "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify({
                            coordenacao_id: coordenacaoId
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        escopoSelect.innerHTML = '';

                        if (data.escopos && data.escopos.length > 0) {
                            data.escopos.forEach(escopo => {
                                let div = document.createElement('div');
                                div.classList.add('form-check');

                                let input = document.createElement('input');
                                input.type = 'radio';
                                input.classList.add('form-check-input');
                                input.name = 'escopo_id';
                                input.id = 'escopo_' + escopo.id;
                                input.value = escopo.id;

                                let label = document.createElement('label');
                                label.classList.add('form-check-label');
                                label.htmlFor = 'escopo_' + escopo.id;
                                label.textContent = escopo.escopo;

                                div.appendChild(input);
                                div.appendChild(label);
                                escopoSelect.appendChild(div);

                                if (escopo.id == '{{ old('escopo_id', $usuario->escopo_id) }}') {
                                    input.checked = true;
                                }
                            });
                        } else {
                            escopoSelect.innerHTML = "<p>Não há escopos disponíveis para esta coordenação.</p>";
                        }
                    })
                    .catch(error => {
                        console.log(error);
                        escopoSelect.innerHTML = "<p>Erro ao carregar os escopos.</p>";
                    });
            } else {
                escopoSelect.innerHTML = '';
            }
        });
        window.addEventListener('DOMContentLoaded', function() {
            let coordenacaoId = document.getElementById('coordenacao_id').value;
            if (coordenacaoId) {
                document.getElementById('coordenacao_id').dispatchEvent(new Event('change'));
            }
        });
    </script>
@endsection
