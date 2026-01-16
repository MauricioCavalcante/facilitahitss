@props(['indicator', 'reports', 'lastSixMonths'])

@php
    use Modules\Aneel\Models\AneelReportIndicator;

    // Definição de comportamentos especiais
    $isN1 = $indicator->code === 'ATEND_N1';
    $isN2 = $indicator->code === 'ATEND_N2';
    $isInformativo = strcasecmp(trim($indicator->service_level), 'Informativo') === 0;

    // Se for o Nível 2, matamos a renderização aqui para não duplicar o gráfico da soma
    if ($isN2) { return; }

    $seriesData = [];

    foreach ($lastSixMonths as $ym) {
        [$year, $month] = explode('-', $ym);

        $reportForMonth = $reports->first(function ($report) use ($year, $month) {
            return $report->period_start->format('Y') == $year && $report->period_start->format('m') == $month;
        });

        if ($reportForMonth) {
            // Valor do indicador atual (N1 ou qualquer outro)
            $reportIndicator = $indicator->reports->first(fn($ri) => $ri->report_id == $reportForMonth->id);
            $value = $reportIndicator?->value ?? 0;

            // 🔹 LÓGICA DE SOMA: Se for N1, busca o N2 do mesmo mês e soma
            if ($isN1) {
                $valueN2 = AneelReportIndicator::where('report_id', $reportForMonth->id)
                    ->whereHas('indicator', fn($q) => $q->where('code', 'ATEND_N2'))
                    ->value('value') ?? 0;
                $value = (float)$value + (float)$valueN2;
            }

            $status = $isInformativo ? 'Calculado' : AneelReportIndicator::checkIndicatorStatus($value, $indicator->service_level);
            
            // Define cor: azul para informativo, verde/vermelho para metas
            $color = $isInformativo ? '#007bff' : ($status === 'Atingiu' ? '#28a745' : '#dc3545');

            $seriesData[] = [
                'y' => (float)$value,
                'status' => $status,
                'color' => $color,
            ];
        } else {
            $seriesData[] = ['y' => null, 'status' => 'Sem dados', 'color' => null];
        }
    }

    $labels = $lastSixMonths->map(function ($ym) {
        return \Carbon\Carbon::createFromFormat('Y-m', $ym)->locale('pt_BR')->translatedFormat('M/Y');
    })->toArray();

    // Ajuste de labels para o gráfico
    $unit = $isInformativo ? '' : '%';
    $yAxisTitle = $isInformativo ? 'Quantidade' : 'Nível de Serviço (%)';
    $chartTitle = $isN1 ? 'Total Atendimentos (N1 + N2)' : "{$indicator->code} - {$indicator->name}";

    // Cálculo de escala do Eixo Y
    $serviceLevel = $indicator->service_level;
    preg_match('/(<=|>=|<|>|==|!=)?\s*(\d+(\.\d+)?)%?/', $serviceLevel, $matches);
    $serviceLevelNumber = isset($matches[2]) ? floatval($matches[2]) : null;
    $maxSeries = collect($seriesData)->pluck('y')->filter()->max();
    $maxY = max($maxSeries ?? 0, $serviceLevelNumber ?? 0);
    $yAxisMax = $maxY > 0 ? round($maxY * 1.2, 2) : 100;
@endphp

<div class="mb-4">
    <div id="chart-{{ $indicator->id }}" style="height: 280px;"></div>

    @push('scripts')
        <script>
            Highcharts.chart('chart-{{ $indicator->id }}', {
                chart: { type: 'line' },
                title: { text: '{{ $chartTitle }}' },
                xAxis: {
                    categories: @json($labels),
                    title: { text: 'Período' }
                },
                yAxis: {
                    min: 0,
                    max: {{ $yAxisMax }},
                    title: { text: '{{ $yAxisTitle }}' },
                    plotLines: [
                        @if (!$isInformativo && $serviceLevelNumber !== null)
                            {
                                color: '#abb2b9',
                                dashStyle: 'Dash',
                                width: 2,
                                value: {{ $serviceLevelNumber }},
                                label: { text: 'Meta: {{ $serviceLevel }}', align: 'right' },
                                zIndex: 20
                            }
                        @endif
                    ]
                },
                tooltip: {
                    formatter: function() {
                        return this.point.y !== null ?
                            `<b>${this.series.name}</b><br/>Valor: ${this.point.y}{{ $unit }}<br/>Status: ${this.point.status}` :
                            `<b>${this.series.name}</b><br/>Sem dados`;
                    }
                },
                series: [{
                    name: '{{ $isN1 ? "Soma N1 + N2" : $indicator->code }}',
                    data: @json($seriesData),
                    color: '{{ $isN1 ? "#6f42c1" : "#007bff" }}',
                    connectNulls: true,
                    dataLabels: {
                        enabled: true,
                        formatter: function() {
                            return this.y !== null ? `${this.y}{{ $unit }}` : '';
                        }
                    }
                }],
                credits: { enabled: false }
            });
        </script>
    @endpush
</div>