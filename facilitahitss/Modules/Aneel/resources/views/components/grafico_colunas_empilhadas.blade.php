@props(['categories', 'indicators', 'selectedReport'])

@php
    $chartId = 'grafico-categorias-empilhado';

    $atingiuSeries = [];
    $naoAtingiuSeries = [];
    $categoryLabels = [];

    $exibindoHistorico = !$selectedReport;

    foreach ($categories as $categoria => $codigos) {
        $categoryLabels[] = $categoria;

        $atingiu = 0;
        $naoAtingiu = 0;

        foreach ($codigos as $codigo) {
            $indicator = $indicators->firstWhere('code', $codigo);
            $relatorios = $indicator?->reports ?? collect();

            if ($selectedReport) {
                $relatorios = $relatorios->where('report_id', $selectedReport->id);
            }

            foreach ($relatorios as $report) {
                if ($report->status === 'Atingiu') {
                    $atingiu++;
                } elseif ($report->status === 'Não Atingiu') {
                    $naoAtingiu++;
                }
            }
        }

        $total = $atingiu + $naoAtingiu;

        $atingiuSeries[] = [
            'y' => $atingiu,
            'customLabel' => $total > 0 ? "$atingiu (" . round(($atingiu / $total) * 100) . '%)' : '0',
        ];

        $naoAtingiuSeries[] = [
            'y' => $naoAtingiu,
            'customLabel' => $total > 0 ? "$naoAtingiu (" . round(($naoAtingiu / $total) * 100) . '%)' : '0',
        ];
    }
@endphp

<div class="col-12 col-lg-8 my-2">
    <div id="{{ $chartId }}" class="shadow-sm" style="height: 400px;"></div>
</div>

@push('scripts')
    <script>
        Highcharts.chart('{{ $chartId }}', {
            chart: {
                type: 'column'
            },
            title: {
                text: 'Indicadores por Categoria (Total e %)'
            },
            subtitle: {
                text: @json(
                    $exibindoHistorico
                        ? 'Exibindo dados acumulados de todos os meses disponíveis'
                        : 'Dados referentes ao mês selecionado'),
                align: 'center'
            },
            xAxis: {
                categories: {!! json_encode($categoryLabels) !!},
                labels: {
                    style: {
                        fontSize: '14px', 
                        fontWeight: 'bold'
                    }
                }
            },
            yAxis: {
                min: 0,
                title: {
                    text: 'Quantidade de Indicadores'
                },
                allowDecimals: false,
                stackLabels: {
                    enabled: true,
                    formatter: function() {
                        return this.total;
                    }
                }
            },
            legend: {
                reversed: true
            },
            plotOptions: {
                column: {
                    stacking: 'normal',
                    dataLabels: {
                        enabled: true,
                        formatter: function() {
                            return this.point.customLabel;
                        }
                    }
                }
            },
            series: [{
                    name: 'Não atingiu o nível de serviço esperado',
                    data: {!! json_encode($naoAtingiuSeries) !!},
                    color: '#dc3545'
                },
                {
                    name: 'Atingiu o nível de serviço esperado',
                    data: {!! json_encode($atingiuSeries) !!},
                    color: '#28a745'
                }
            ],
            credits: {
                enabled: false
            }
        });
    </script>
@endpush
