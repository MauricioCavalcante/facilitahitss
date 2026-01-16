@extends('ana::layouts.app')


@section('content')
    <div class="container">
        <table class="table table-hover table-responsive">
            <thead>
                <tr>
                    <th>Nome</th>
                    <th>Email Hitss</th>
                    <th>Email ANA</th>
                    <th>Coordenação</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($usuarios as $usuario)
                    <tr>
                        <td>{{ $usuario->name }}</td>
                        <td>{{ $usuario->email }}</td>
                        <td>
                            @if ($usuario->anaUser && $usuario->anaUser->email_ana)
                                {{ $usuario->anaUser->email_ana }}
                            @endif
                        </td>
                        <!-- Exibindo a coordenação associada ao usuário -->
                        <td>
                            @if ($usuario->anaUser && $usuario->anaUser->coordenacao)
                                {{ $usuario->anaUser->coordenacao->codigo }}
                            @else
                                Nenhuma coordenação atribuída
                            @endif
                        </td>

                        <td>
                            <a class="btn btn-primary" href="{{ route('ana::usuarios.edit', ['id' => $usuario->id]) }}">
                                <i class="bi bi-pencil-square"></i>
                            </a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection
