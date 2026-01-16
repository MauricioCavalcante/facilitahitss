@extends('ana::layouts.app')

@section('content')
    <div class="container">
        <!-- Cabeçalho -->
        <div class="card mb-4">
            <div class="card-header">
                <h5>Relatório de {{ $relatorio->user->name }}</h5>
            </div>
            <div class="card-body">
                <p><strong>Ordem de Serviço:</strong> {{ $relatorio->ordemServico->numero }}</p>
                <p><strong>Coordenação:</strong> {{ $relatorio->user->anaUser->coordenacao->codigo ?? 'Não se aplica' }}</p>
                <p><strong>Última Atualização:</strong> {{ $relatorio->updated_at ? $relatorio->updated_at->diffForHumans() : 'Data não disponível' }}</p>
            </div>
            <div class="card-footer d-flex justify-content-end">
                <a href="{{ route('ana::relatorio_executivo.baixarAtualizado', $relatorio->id) }}" class="btn btn-success"> <i class="bi bi-file-earmark-arrow-down"></i> Baixar Relatório </a>
            </div>
        </div>

        <!-- Timeline do Processo -->
        <div class="card mb-4">
            <div class="card-header">
                <h5>Status do Relatório</h5>
            </div>
            <div class="card-body">
                <div class="timeline">
                    <div class="timeline-step completed">
                        <div class="timeline-icon">
                            <i class="bi bi-file-earmark"></i>
                        </div>
                        <div class="timeline-title">Relatório Criado</div>
                    </div>

                    <div
                        class="timeline-step {{ in_array($validacao->status, ['Para Corrigir', 'Corrigido', 'Validado']) ? 'completed' : '' }}">
                        <div class="timeline-icon">
                            <i class="bi bi-pencil-square"></i>
                        </div>
                        <div class="timeline-title">Para Corrigir</div>
                    </div>

                    <div class="timeline-step {{ in_array($validacao->status, ['Corrigido', 'Validado']) ? 'completed' : '' }}">
                        <div class="timeline-icon">
                            <i class="bi bi-check-circle"></i>
                        </div>
                        <div class="timeline-title">Corrigido</div>
                    </div>

                    <div class="timeline-step {{ $validacao->status === 'Validado' ? 'completed' : '' }}">
                        <div class="timeline-icon">
                            <i class="bi bi-check2-all"></i>
                        </div>
                        <div class="timeline-title">Validado</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Seção de Comentários e Ações -->
        <div class="comments-section my-5">
            @if ($validacao->comentario)
                <div class="alert alert-secondary">
                    <p><strong>Comentário do Coordenador:</strong></p>
                    <pre class="bg-light p-3 rounded">{{ $validacao->comentario }}</pre>
                </div>
            @endif

            <!-- Visão do Admin (Coordenador) -->
            @if (Auth::user()->role === 'admin')
                @if ($validacao->status === 'Corrigido' || ($validacao->status !== 'Validado' && !$validacao->comentario))
                    <form action="{{ route('ana::relatorio_executivo.salvarValidacao', $relatorio->id) }}" method="POST">
                        @csrf
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5>Adicionar Comentário</h5>
                            </div>
                            <div class="card-body">
                                <textarea class="form-control auto-resize no-scroll" id="comentario" name="comentario" rows="3"
                                    placeholder="Adicione seu feedback ou solicite correções aqui.">{{ old('comentario') }}</textarea>
                                <div class="d-flex justify-content-end mt-3">
                                    <button type="submit" name="action" value="inserir_comentario"
                                        class="btn btn-primary">
                                        Inserir Comentário
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="d-flex justify-content-end">
                            <a href="{{ route('ana::index') }}" class="btn btn-secondary me-2">Cancelar</a>
                            <button type="submit" name="action" value="validar" class="btn btn-success">
                                Validar Relatório
                            </button>
                        </div>
                    </form>
                @elseif($validacao->status !== 'Validado' && $validacao->status !== 'Corrigido')
                    <div class="alert alert-info mt-4">
                        Comentário inserido. Aguardando correção do funcionário.
                    </div>
                @endif
            @endif

            <!-- Visão do Funcionário -->
            @if (Auth::user()->role === 'user')
                @if (
                    $validacao->status === 'Para Corrigir' &&
                        (is_null($validacao->editado_por) || $validacao->editado_por == Auth::user()->id))
                    <div class="d-flex justify-content-end">
                        <a href="{{ route('ana::index') }}" class="btn btn-light me-2">Cancelar</a>
                        <a href="{{ route('ana::relatorio_executivo.editar', $relatorio->id) }}" class="btn btn-primary">Editar Relatório</a>
                    </div>
                @elseif($validacao->status !== 'Validado' && $validacao->status !== 'Para Corrigir')
                    <div class="alert alert-info mt-4">
                        Relatório salvo e enviado para correção. Aguardando validação do coordenador.
                    </div>
                @endif
            @endif
        </div>

        @if ($validacao->status === 'Validado')
            <div class="alert alert-success mt-4">
                <i class="bi bi-check-circle"></i> Relatório Validado
            </div>
        @endif

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                function autoResize() {
                    this.style.height = 'auto';
                    this.style.height = this.scrollHeight + 'px';
                }
                document.querySelectorAll('textarea').forEach(function(textarea) {
                    autoResize.call(textarea);
                    textarea.addEventListener('input', autoResize);
                });
            });
        </script>
    </div>
@endsection
