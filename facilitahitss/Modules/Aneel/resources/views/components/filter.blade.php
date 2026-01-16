@props(['reports'])

@php
    // Extrair apenas os objetos AneelReport da estrutura ['report' => ..., 'indicators' => ...]
    $reportList = collect($reports)->map(function ($item) {
        return is_array($item) && isset($item['report']) ? $item['report'] : $item;
    });

    $latestReport = $reportList->sortByDesc('period_start')->first();

    if ($latestReport) {
        $latestPeriod = $latestReport->period_start->copy()->startOfMonth();
        $defaultStart = request('start') ?? $latestPeriod->format('Y-m-d');
        $defaultEnd = request('end') ?? $latestPeriod->copy()->endOfMonth()->format('Y-m-d');
    } else {
        $defaultStart = request('start') ?? now()->startOfMonth()->format('Y-m-d');
        $defaultEnd = request('end') ?? now()->endOfMonth()->format('Y-m-d');
    }
@endphp

<div class="p-2 shadow rounded">
    <div class="d-flex justify-content-center mb-2">
        <h5>Filtro de Período</h5>
    </div>
    <form id="reportForm" method="GET" action="{{ route('aneel::index') }}">
        <div class="d-flex flex-wrap gap-3 align-items-center">
            <span>de:</span>
            <div class="d-flex align-items-center gap-2">
                <input type="date" name="start" id="start" class="form-control form-control-sm"
                       value="{{ $defaultStart }}">
            </div>

            <span>a</span>

            <div class="d-flex align-items-center gap-2">
                <input type="date" name="end" id="end" class="form-control form-control-sm"
                       value="{{ $defaultEnd }}">
            </div>

            <div>
                <button type="submit" class="btn btn-sm btn-primary">Filtrar</button>
                <a href="{{ route('aneel::index') }}" class="btn btn-sm btn-secondary">Limpar</a>
            </div>
        </div>
    </form>
</div>
