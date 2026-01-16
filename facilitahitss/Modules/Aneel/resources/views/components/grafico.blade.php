<script>
    Highcharts.chart('chart-{{ $indicator->id }}', {
        title: { text: '' },
        xAxis: {
            categories: {!! json_encode($reports->pluck('period_start')->map(fn($d) => \Carbon\Carbon::parse($d)->format('M/Y'))) !!}
        },
        yAxis: { title: { text: '{{ $indicator->unit ?? "%" }}' }},
        series: [{
            name: '{{ $indicator->code }}',
            data: {!! json_encode($indicator->reports->pluck('value')) !!}
        }]
    });
    </script>
    