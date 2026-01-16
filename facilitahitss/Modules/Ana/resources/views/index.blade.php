@extends('ana::layouts.app')

@section('content')
    <div class="container">

        @if (auth()->user()->role('user'))
            @if ($statusMensagem)
                <div class="alert alert-{{ $statusMensagem['type'] }} text-center">
                    {!! $statusMensagem['message'] !!}
                </div>
            @endif
        @endif

        @if (auth()->user()->role('admin'))
            @if ($mensagemJustificativas)
                <div class="alert alert-{{ $mensagemJustificativas['type'] }} text-center">
                    {!! $mensagemJustificativas['message'] !!}
                </div>
            @endif
        @endif

        <h2 class="my-4">Acompanhamento de Relatórios</h2>

        @php
            $isUser = Auth::user()->role('user');
        @endphp

        @if ($ordens_servico->isEmpty())
            <div class="col">
                <div class="alert alert-warning">Não há nenhuma OS em andamento.</div>
            </div>
        @else
            <div class="my-3">
                <a href="{{ route('ana::relatorio_executivo.baixarValidados') }}" class="btn btn-success">
                    Baixar Validados
                </a>
            </div>

            <div class="accordion" id="accordionExample">
                @foreach ($ordens_servico as $index => $os)
                    @php
                        $relatorios = $isUser ? $os->relatorios->where('user_id', Auth::id()) : $os->relatorios;

                        $usuarioCriouRelatorio = $relatorios->isNotEmpty();
                    @endphp
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="heading{{ $index }}">
                            <button class="accordion-button" type="button" data-bs-toggle="collapse"
                                data-bs-target="#collapse{{ $index }}" aria-expanded="true"
                                aria-controls="collapse{{ $index }}">
                                OS {{ $os->numero }} -
                                {{ \Carbon\Carbon::parse($os->data_inicio)->format('d/m/Y') }} até
                                {{ \Carbon\Carbon::parse($os->data_fim)->format('d/m/Y') }}
                            </button>
                        </h2>
                        <div id="collapse{{ $index }}" class="accordion-collapse collapse show"
                            aria-labelledby="heading{{ $index }}">
                            <div class="accordion-body">
                                <div class="row">
                                    @foreach ($relatorios as $relatorio)
                                        @php
                                            $relatorioUser = $relatorio->user;
                                            $validacao = $relatorio->validacao;
                                        @endphp
                                        <div class="col-12 col-sm-6 col-md-4 mb-3">
                                            <div class="card border h-100 text-decoration-none text-dark"
                                                style="border-radius: 10px; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);">
                                                <div class="card-body">
                                                    <h5 class="card-title">Relatório de
                                                        {{ $relatorioUser->name ?? 'Usuário não encontrado' }}</h5>
                                                    <p class="text-muted">Última atualização:
                                                        {{ $relatorio->updated_at->diffForHumans() }}</p>
                                                    @if ($validacao)
                                                        <span
                                                            class="badge bg-{{ $validacao->status === 'Validado' ? 'success' : ($validacao->status === 'Para Corrigir' || $validacao->status === 'Corrigido' ? 'warning' : 'primary') }}">
                                                            {{ $validacao->status }}
                                                        </span>
                                                        <div class="d-flex justify-content-end gap-2 mt-2">
                                                            <a href="{{ route('ana::relatorio_executivo.validarRelatorio', $relatorio->id) }}"
                                                                class="btn btn-primary"><i class="bi bi-eye"></i></a>
                                                            <a href="{{ route('ana::relatorio_executivo.baixarAtualizado', $relatorio->id) }}"
                                                                class="btn btn-success"><i
                                                                    class="bi bi-download"></i></a>
                                                        </div>
                                                    @else
                                                        <span class="badge bg-secondary">Sem status</span>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach

                                    @if (!$isUser && isset($os->usuariosSemRelatorio) && count($os->usuariosSemRelatorio) > 0)
                                        @foreach ($os->usuariosSemRelatorio as $usuario)
                                            <div class="col-12 col-sm-6 col-md-4 mb-3">
                                                <div class="card border h-100 text-decoration-none text-dark"
                                                    style="border-radius: 10px; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);">
                                                    <div class="card-body">
                                                        <h5 class="card-title text-danger">Relatório de
                                                            {{ $usuario->nome ?? 'Usuário não encontrado' }}</h5>
                                                        <small class="text-muted">Relatório não entregue</small>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    @endif
                                </div>

                                @if ($isUser && !$usuarioCriouRelatorio)
                                    <div class="my-3">
                                        <a href="{{ route('ana::relatorio_executivo.criar', ['os_id' => $os->id]) }}"
                                            class="btn btn-primary">
                                            Clique aqui para criar o relatório
                                        </a>
                                    </div>
                                @endif

                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
@endsection
