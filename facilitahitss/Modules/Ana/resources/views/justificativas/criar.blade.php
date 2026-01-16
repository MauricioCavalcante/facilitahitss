@extends('ana::layouts.app')

@section('content')
<div class="container mt-5">
    <!-- Cabeçalho -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="mb-0">Registrar Justificativa</h3>
        <a href="{{ route('ana::index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Voltar
        </a>
    </div>

    <!-- Descrição -->
    <p class="text-muted">Informe o motivo do atraso na criação do relatório e envie para análise do gestor.</p>

    <!-- Formulário -->
    <form action="{{ route('ana::justificativas.salvar') }}" method="POST">
        @csrf
        <!-- Campo oculto para enviar o ID da Ordem de Serviço -->
        <input type="hidden" name="os_id" value="{{ $ordemServico->id }}">

        <div class="form-group mb-2">
            <textarea
                id="justificativa"
                name="justificativa"
                class="form-control"
                rows="5"
                required
                maxlength="2000"
                placeholder="Descreva sua justificativa aqui"
                oninput="atualizarContador()"></textarea>
            <small id="contador" class="text-muted">0 / 2000 caracteres</small>
        </div>

        <div class="d-flex justify-content-end">
            <a href="{{ route('ana::index') }}" class="btn btn-secondary me-2">Cancelar</a>
            <button type="submit" class="btn btn-success">Enviar Justificativa</button>
        </div>
    </form>
</div>

<script>
    function atualizarContador() {
        let textarea = document.getElementById('justificativa');
        let contador = document.getElementById('contador');
        let maxLength = 2000;
        let currentLength = textarea.value.length;

        contador.textContent = `${currentLength} / ${maxLength} caracteres`;

        if (currentLength >= maxLength) {
            contador.classList.add('text-danger');
        } else {
            contador.classList.remove('text-danger');
        }
    }
</script>
@endsection
