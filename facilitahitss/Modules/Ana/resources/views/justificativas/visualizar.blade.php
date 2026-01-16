@extends('ana::layouts.app')


@section('content')
    <div class="container mt-5">
        <!-- Cabeçalho -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="mb-0">Detalhes da Justificativa</h3>
            <a href="{{ route('ana::justificativas.index') }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Voltar
            </a>
        </div>

        <!-- Detalhes da Justificativa -->
        <div class="card shadow-sm">
            <div class="card-body">
                <p><strong>Nome:</strong> {{ $justificativa->user->name }}</p>
                <p><strong>OS:</strong> {{ $justificativa->os->numero }}</p>
                <p><strong>Enviada em:</strong> {{ $justificativa->created_at->format('d/m/Y H:i') }}</p>
                <hr>

                <h5 class="card-title mb-3">Justificativa</h5>
                <p class="bg-light p-3 rounded">{{ $justificativa->justificativa }}</p>
                <hr>

                @if (auth()->user()->role('admin'))
                    <!-- Ações disponíveis apenas para administradores -->
                    @if ($justificativa->status === 'Pendente')
                        <form action="{{ route('ana::justificativas.validar', $justificativa->id) }}" method="POST" class="mt-4">
                            @csrf
                            <div class="d-flex justify-content-end">
                                <button type="submit" name="acao" value="Aprovar" class="btn btn-success me-2">
                                    <i class="bi bi-check-circle"></i> Aprovar
                                </button>
                                <button type="submit" name="acao" value="Sancionar" class="btn btn-warning">
                                    <i class="bi bi-x-circle"></i> Sancionar
                                </button>
                            </div>
                        </form>
                    @else
                        <div class="alert alert-info mt-4">
                            Esta justificativa já foi {{ strtolower($justificativa->status) }}.
                        </div>
                    @endif
                @endif
            </div>
        </div>
    </div>
@endsection
