@props(['indicator', 'reports', 'lastSixMonths'])

@php
    use Modules\Aneel\Models\AneelReportIndicator;

    $seriesData = [];

    foreach ($lastSixMonths as $ym) {
        [$year, $month] = explode('-', $ym);

        $reportForMonth = $reports->first(function ($report) use ($year, $month) {
            return $report->period_start->format('Y') == $year && $report->period_start->format('m') == $month;
        });

        if ($reportForMonth) {
            $reportIndicator = $indicator->reports->first(fn($ri) => $ri->report_id == $reportForMonth->id);
            $value = $reportIndicator?->value ?? null;

            if (!is_null($value)) {
                $status = AneelReportIndicator::checkIndicatorStatus($value, $indicator->service_level);
                $color = $status === 'Atingiu' ? '#28a745' : '#dc3545';

                $seriesData[] = [
                    'y' => round($value, 4),
                    'status' => $status,
                    'color' => $color,
                ];
            } else {
                $seriesData[] = ['y' => null, 'status' => 'Sem dados', 'color' => null];
            }
        } else {
            $seriesData[] = ['y' => null, 'status' => 'Sem dados', 'color' => null];
        }
    }

$labels = $lastSixMonths
    ->map(function ($ym) {
        return \Carbon\Carbon::createFromFormat('Y-m-d', $ym . '-01')
            ->locale('pt_BR')
            ->translatedFormat('MY');
    })
    ->toArray();

    $serviceLevel = $indicator->service_level;

    preg_match('/(<=|>=|<|>|==|!=)?\s*(\d+(\.\d+)?)%?/', $serviceLevel, $matches);

    $serviceLevelOperator = $matches[1] ?? '';
    $serviceLevelNumber = isset($matches[2]) ? floatval($matches[2]) : null;

    $maxSeries = collect($seriesData)->pluck('y')->filter()->max();
    $maxY = max($maxSeries ?? 0, $serviceLevelNumber ?? 0);
    $yAxisMax = round($maxY * 1.2, 2);
@endphp

<div class="mb-2">
    <div id="chart-{{ $indicator->id }}" style="height: 250px;"></div>

    @push('scripts')
        <script>
            Highcharts.chart('chart-{{ $indicator->id }}', {
                chart: {
                    type: 'line'
                },
                title: {
                    text: '{{ $indicator->code }} - {{ $indicator->name }} ({{ $indicator->service_level }})',
                },
                xAxis: {
                    categories: @json($labels),
                    title: {
                        text: 'Período'
                    }
                },
                yAxis: {
                    min: 0,
                    max: {{ $yAxisMax }},
                    title: {
                        text: 'Nível de Serviço (%)'
                    },
                    plotLines: [
                        @if ($serviceLevelNumber !== null)
                            {
                                color: '#abb2b9',
                                dashStyle: 'Dash',
                                width: 2,
                                value: {{ $serviceLevelNumber }},
                                label: {
                                    text: 'Meta: {{ $serviceLevelOperator }} {{ $serviceLevelNumber }}%',
                                    align: 'right',
                                    style: {
                                        color: '#566573',
                                        fontWeight: 'bold'
                                    }
                                },
                                zIndex: 20
                            }
                        @endif
                    ]
                },
                tooltip: {
                    formatter: function() {
                        return this.point.y !== null ?
                            `<b>{{ $indicator->code }}</b><br/>
                             Valor: ${this.point.y}%<br/>
                             Status: ${this.point.status}` :
                            `<b>{{ $indicator->code }}</b><br/>Período: ${this.x}<br/>Sem dados`;
                    }
                },
                series: [{
                    name: '{{ $indicator->code }}',
                    data: @json($seriesData),
                    color: '#007bff',
                    connectNulls: true,
                    marker: {
                        enabled: true,
                        radius: 4
                    },
                    dataLabels: {
                        enabled: true,
                        formatter: function() {
                            return this.y !== null ? `${this.y.toFixed(2)}%` : '';
                        },
                        style: {
                            fontWeight: 'bold',
                            color: '#000000',
                            textOutline: 'none'
                        },
                        verticalAlign: 'bottom',
                        y: -5
                    }
                }],
                credits: {
                    enabled: false
                }
            });
        </script>
    @endpush
</div>
