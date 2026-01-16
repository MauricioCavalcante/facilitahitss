@props(['title', 'codes', 'labels' => [], 'indicators', 'reports', 'startDate', 'endDate'])

@php
    use Carbon\Carbon;
    use Illuminate\Support\Facades\Log;
    use Modules\Aneel\Services\IndicatorCalculatorService;

    $start = Carbon::parse($startDate)->startOfDay();
    $end   = Carbon::parse($endDate)->endOfDay();
    $lastRange = $start->translatedFormat('d/m/Y') . ' a ' . $end->translatedFormat('d/m/Y');

    Log::debug('📆 Período do filtro:', [
        'start' => $start->toDateTimeString(),
        'end'   => $end->toDateTimeString(),
    ]);
@endphp

<style>
    .card-grid {
        display: grid;
        gap: 1rem;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        margin: 0 auto;
    }
</style>

<div class="container-fluid mb-4">
    <h5 class="mb-3">{{ $title ?? 'Indicadores' }} ({{ $lastRange }})</h5>

    <div class="card-grid">
        @foreach ($indicators->whereIn('code', $codes) as $indicator)
            @php
                $label = $labels[$indicator->code] ?? $indicator->code;

                // 1️⃣ Filtrar relatórios dentro do período
                $relevant = collect($reports)
                    ->filter(fn($r) => Carbon::parse(
                        is_array($r) ? $r['report']->period_start : $r->period_start
                    )->between($start, $end));

                // 2️⃣ Obter entries do report_indicators
                $entries = $relevant->flatMap(function($r) use ($indicator) {
                    $list = is_array($r) ? $r['indicators'] : $r->indicators;
                    return collect($list)
                        ->filter(fn($e) => $e->indicator_id === $indicator->id);
                });

                Log::debug('📊 Indicador encontrado', [
                    'code'          => $indicator->code,
                    'related_count' => $entries->count(),
                ]);

                // 3️⃣ Somar os campos do JSON inputs
                $totals = [];

                // Protege: já pode ser array ou string
                $keys = is_string($indicator->inputs)
                    ? json_decode($indicator->inputs, true)
                    : $indicator->inputs;

                $keys = is_array($keys) ? $keys : [];

                foreach ($entries as $entry) {
                    // também protege aqui
                    $data = is_string($entry->inputs)
                        ? json_decode($entry->inputs, true)
                        : $entry->inputs;

                    if (!is_array($data)) {
                        Log::error("Inputs inválido para {$indicator->code}: " . json_encode($entry->inputs));
                        continue;
                    }

                    foreach ($keys as $key) {
                        $totals[$key] = ($totals[$key] ?? 0) + (is_numeric($data[$key] ?? null) ? $data[$key] : 0);
                    }
                }

                // 4️⃣ Calcular com o service
                try {
                    $value = !empty($totals)
                        ? IndicatorCalculatorService::calculate($indicator->id, $totals)
                        : null;

                    Log::debug("✅ Valor calculado para {$indicator->code}: {$value}");
                } catch (\Throwable $e) {
                    $value = null;
                    Log::error("❌ Erro ao calcular indicador {$indicator->code}: " . $e->getMessage());
                }

                // 5️⃣ Avaliar meta dinamicamente
                preg_match('/(>=|<=|>|<|=)?\s*([\d.]+)/', $indicator->service_level, $m);
                $op        = $m[1] ?? '>=';
                $threshold = floatval($m[2] ?? 0);
                $status    = 'Sem dados';
                if (!is_null($value)) {
                    $status = match($op) {
                        '>=' => $value >= $threshold ? 'Atingiu' : 'Não atingiu',
                        '<=' => $value <= $threshold ? 'Atingiu' : 'Não atingiu',
                        '>'  => $value >  $threshold ? 'Atingiu' : 'Não atingiu',
                        '<'  => $value <  $threshold ? 'Atingiu' : 'Não atingiu',
                        '='  => $value == $threshold? 'Atingiu' : 'Não atingiu',
                        default => 'Indefinido',
                    };
                }
            @endphp

            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <h6 class="card-subtitle mb-1 text-muted">{{ $indicator->code }} – {{ $label }}</h6>
                    @if (!is_null($value))
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-muted">Meta: {{ $indicator->service_level }}</span>
                            <span class="fw-bold fs-5">{{ number_format($value, 2) }}%</span>
                            <span>{{ $status === 'Atingiu' ? '✅' : '❌' }}</span>
                        </div>
                    @else
                        <span class="text-muted">Sem dados</span>
                    @endif
                </div>
            </div>
        @endforeach
    </div>
</div>
