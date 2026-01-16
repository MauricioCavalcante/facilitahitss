@props(['indicators', 'reports', 'selectedReport' => null])

@php
    $atingiu = 0;
    $naoAtingiu = 0;

    $exibindoHistorico = !$selectedReport;

    foreach ($indicators as $indicator) {
        $relatorios = $indicator->reports ?? collect();

        if ($selectedReport) {
            $relatorios = $relatorios->where('report_id', $selectedReport->id);
        }

        foreach ($relatorios as $data) {
            if ($data->status === 'Atingiu') {
                $atingiu++;
            } elseif ($data->status === 'Não Atingiu') {
                $naoAtingiu++;
            }
        }
    }

    $chartId = 'grafico-pizza-indicadores';
    $tituloPeriodo = $selectedReport
        ? \Carbon\Carbon::parse($selectedReport->period_start)->format('F/Y')
        : 'Histórico Completo';
@endphp

<div class="col-12 col-lg-4 my-2">
    <div id="{{ $chartId }}" class="shadow-sm" style="height: 400px;"></div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        Highcharts.chart('{{ $chartId }}', {
            chart: {
                type: 'pie'
            },
            title: { text: 'Resumo Geral de Indicadores ({{ $tituloPeriodo }})' },
            subtitle: {
                text: @json(
                    $exibindoHistorico
                        ? 'Exibindo dados acumulados de todos os meses disponíveis'
                        : 'Dados referentes ao mês selecionado'),
                align: 'center'
            },
            tooltip: {
                pointFormat: '{series.name}: <b>{point.y} ({point.percentage:.1f}%)</b>'
            },
            plotOptions: {
                pie: {
                    allowPointSelect: true,
                    cursor: 'pointer',
                    dataLabels: {
                        enabled: true,
                        format: '<b>{point.name}</b>: {point.y} ({point.percentage:.1f}%)'
                    }
                }
            },
            series: [{
                name: 'Indicadores',
                colorByPoint: true,
                data: [
                    {
                        name: 'Dentro do nível esperado',
                        y: {{ $atingiu }},
                        color: '#28a745'
                    },
                    {
                        name: 'Não atingiu o nível esperado',
                        y: {{ $naoAtingiu }},
                        color: '#dc3545'
                    }
                ]
            }],
            credits: { enabled: false }
        });
    });
</script>
@endpush
