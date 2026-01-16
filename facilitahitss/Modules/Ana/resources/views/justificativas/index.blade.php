@extends('ana::layouts.app')


@section('content')
    <div class="container">

        @if (auth()->user()->role('user'))
            @if ($mensagemPrazo)
                <div class="alert alert-danger text-center">
                    {!! $mensagemPrazo !!}
                </div>
            @endif
        @endif

        <!-- Cabeçalho -->
        <div class="d-flex justify-content-between align-items-center my-4">
            <div>
                <h2 class="mb-4">Gerenciamento de Justificativas</h2>
            </div>
        </div>

        <!-- Visão do Administrador -->
        @if (Auth::user()->role('admin'))
            <div class="mb-5">
                <h3 class="mb-4">Justificativas Pendentes</h3>
                @if ($justificativasPendentes->isEmpty())
                    <p class="text-muted">Não há justificativas pendentes no momento.</p>
                @else
                    <div class="table-responsive">
                        <table class="table table-striped align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>OS</th>
                                    <th>Funcionário</th>
                                    <th>Data de Envio</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($justificativasPendentes as $justificativa)
                                    <tr>
                                        <td>{{ $justificativa->os->numero }}</td>
                                        <td>{{ $justificativa->user->name }}</td>
                                        <td>{{ $justificativa->created_at->format('d/m/Y H:i') }}</td>
                                        <td>
                                            <a href="{{ route('ana::justificativas.visualizar', $justificativa->id) }}"
                                               class="btn btn-primary btn-sm">
                                                <i class="bi bi-eye"></i> Visualizar
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>

            <div class="mb-5">
                <h3 class="mb-4">Justificativas Aprovadas</h3>
                @if ($justificativasAprovadas->isEmpty())
                    <p class="text-muted">Não há justificativas aprovadas no momento.</p>
                @else
                    <div class="table-responsive">
                        <table class="table table-striped align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>OS</th>
                                    <th>Funcionário</th>
                                    <th>Data de Envio</th>
                                    <th>Status</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($justificativasAprovadas as $justificativa)
                                    <tr>
                                        <td>{{ $justificativa->os->numero }}</td>
                                        <td>{{ $justificativa->user->name }}</td>
                                        <td>{{ $justificativa->created_at->format('d/m/Y H:i') }}</td>
                                        <td>
                                            <span class="badge bg-success">
                                                <i class="bi bi-check-circle-fill"></i> Aprovada
                                            </span>
                                        </td>
                                        <td>
                                            <a href="{{ route('ana::justificativas.visualizar', $justificativa->id) }}"
                                               class="btn btn-primary btn-sm">
                                                <i class="bi bi-eye"></i> Visualizar
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>

            <div class="mb-5">
                <h3 class="mb-4">Justificativas Sancionadas</h3>
                @if ($justificativasSancionadas->isEmpty())
                    <p class="text-muted">Não há justificativas sancionadas no momento.</p>
                @else
                    <div class="table-responsive">
                        <table class="table table-striped align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>OS</th>
                                    <th>Funcionário</th>
                                    <th>Data de Envio</th>
                                    <th>Status</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($justificativasSancionadas as $justificativa)
                                    <tr>
                                        <td>{{ $justificativa->os->numero }}</td>
                                        <td>{{ $justificativa->user->name }}</td>
                                        <td>{{ $justificativa->created_at->format('d/m/Y H:i') }}</td>
                                        <td>
                                            <span class="badge bg-warning">
                                                <i class="bi bi-exclamation-circle-fill"></i> Sancionada
                                            </span>
                                        </td>
                                        <td>
                                            <a href="{{ route('ana::justificativas.visualizar', $justificativa->id) }}"
                                               class="btn btn-primary btn-sm">
                                                <i class="bi bi-eye"></i> Visualizar
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        @endif

        <!-- Visão do Usuário -->
        @if (Auth::user()->role('user'))
            <div>
                <h3 class="mb-4">Suas Justificativas</h3>
                @if ($meusJustificativas->isEmpty())
                    <p class="text-muted">Você não possui justificativas enviadas no momento.</p>
                @else
                    <div class="table-responsive">
                        <table class="table table-striped align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>OS</th>
                                    <th>Data de Envio</th>
                                    <th>Status</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($meusJustificativas as $justificativa)
                                    <tr>
                                        <td>{{ $justificativa->os ? $justificativa->os->numero : 'OS não encontrada' }}</td>
                                        <td>{{ $justificativa->created_at->format('d/m/Y H:i') }}</td>
                                        <td>
                                            <span class="badge
                                                {{ $justificativa->status === 'Pendente' ? 'bg-warning' : ($justificativa->status === 'Aprovada' ? 'bg-success' : 'bg-warning') }} ">
                                                <i class="bi
                                                    {{ $justificativa->status === 'Pendente' ? 'bi-hourglass-split' : ($justificativa->status === 'Aprovada' ? 'bi-check-circle-fill' : 'bi-exclamation-circle-fill') }} ">
                                                </i> {{ ucfirst($justificativa->status) }}
                                            </span>
                                        </td>
                                        <td>
                                            <a href="{{ route('ana::justificativas.visualizar', $justificativa->id) }}"
                                               class="btn btn-primary btn-sm">
                                                <i class="bi bi-eye"></i> Visualizar
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        @endif
    </div>
@endsection
