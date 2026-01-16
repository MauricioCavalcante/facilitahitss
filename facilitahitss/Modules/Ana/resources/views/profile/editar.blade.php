@extends('ana::layouts.app')

@section('content')
    <div class="container mt-4">
        <div class="col">
            <h2>Perfil</h2>
        </div>
        <div class="row">
            <form action="{{ route('ana::profile.atualizarPerfil', ['id' => $user->id]) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="card mb-4">
                    <div class="card-header">
                        <h5>Editar Perfil</h5>
                    </div>
                    <div class="card-body">
                        <div class="form-group mb-3">
                            <label for="email_ana">Email ANA</label>
                            <input type="email" class="form-control" name="email_ana" id="email_ana"
                                    value="{{ old('email_ana', $anaUser->email_ana) }}">
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-end">
                    <button type="submit" id="save-button" class="btn btn-success">Alterar Email</button>
                </div>
            </form>
        </div>
        <div class="card mb-2">
            <div class="card-header">
                <h5>Detalhes</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <p class="mb-1">Email Hitss</p>
                    <p class="form-control-plaintext bg-light">{{ $user->email }}</p>
                </div>

                <div class="mb-3">
                    <p class="mb-1">Nome de usuário</p>
                    <p class="form-control-plaintext bg-light">{{ $user->username }}</p>
                </div>
                <div class="mb-3">
                    <p class="mb-1">Coordenação</p>
                    @php
                        $coordenacao = $coordenacoes->firstWhere('id', $anaUser->coordenacao_id);
                    @endphp
                    <p class="form-control-plaintext bg-light">
                        {{ $coordenacao ? $coordenacao->nome : 'Nenhuma Coordenação' }}
                    </p>
                </div>
                <div class="mb-3">
                    <p class="mb-1">Escopo</p>
                    @php
                        $escopo = $escopos->firstWhere('id', $anaUser->escopo_id);
                    @endphp
                    <p class="form-control-plaintext bg-light">
                        {{ $escopo ? $escopo->escopo : 'Nenhum Escopo' }}
                    </p>
                </div>
            </div>
        </div>
    </div>
@endsection
