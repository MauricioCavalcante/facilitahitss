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

                $relevant = collect($reports)
                    ->filter(fn($r) => Carbon::parse(
                        is_array($r) ? $r['report']->period_start : $r->period_start
                    )->between($start, $end));

                $entries = $relevant->flatMap(function($r) use ($indicator) {
                    $list = is_array($r) ? $r['indicators'] : $r->indicators;
                    return collect($list)
                        ->filter(fn($e) => $e->indicator_id === $indicator->id);
                });

                Log::debug('📊 Indicador encontrado', [
                    'code'          => $indicator->code,
                    'related_count' => $entries->count(),
                ]);

                $totals = [];
                $keys = is_string($indicator->inputs)
                    ? json_decode($indicator->inputs, true)
                    : $indicator->inputs;
                $keys = is_array($keys) ? $keys : [];

                foreach ($entries as $entry) {
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

                try {
                    $value = !empty($totals) ? IndicatorCalculatorService::calculate($indicator->id, $totals) : null;
                } catch (\Throwable $e) {
                    $value = null;
                }


                $status = 'Sem dados';
                $serviceLevel = trim($indicator->service_level);
                $isInformativo = (strcasecmp($serviceLevel, 'Informativo') === 0);

                if (!is_null($value)) {
                    if ($isInformativo) {
                        $status = 'Informativo';
                    } else {
                        preg_match('/(>=|<=|>|<|=)?\s*([\d.]+)/', $serviceLevel, $m);
                        $op        = $m[1] ?? '>=';
                        $threshold = floatval($m[2] ?? 0);
                        
                        $status = match($op) {
                            '>=' => $value >= $threshold ? 'Atingiu' : 'Não atingiu',
                            '<=' => $value <= $threshold ? 'Atingiu' : 'Não atingiu',
                            '>'  => $value >  $threshold ? 'Atingiu' : 'Não atingiu',
                            '<'  => $value <  $threshold ? 'Atingiu' : 'Não atingiu',
                            '='  => $value == $threshold ? 'Atingiu' : 'Não atingiu',
                            default => 'Indefinido',
                        };
                    }
                }
            @endphp

            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <h6 class="card-subtitle mb-1 text-muted">{{ $indicator->code }}</h6>
                    @if (!is_null($value))
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-muted small">
                                {{ $isInformativo ? 'Valor Acumulado' : 'Meta: ' . $indicator->service_level }}
                            </span>

                            <span class="fw-bold fs-5">
                                {{ number_format($value, $isInformativo ? 0 : 2) }}{{ $isInformativo ? '' : '%' }}
                            </span>

                            <span>
                                @if($status === 'Atingiu') ✅ 
                                @elseif($status === 'Não atingiu') ❌ 
                                @elseif($status === 'Informativo') 📊 
                                @endif
                            </span>
                        </div>
                    @else
                        <span class="text-muted">Sem dados</span>
                    @endif
                </div>
            </div>
        @endforeach
    </div>
</div>
