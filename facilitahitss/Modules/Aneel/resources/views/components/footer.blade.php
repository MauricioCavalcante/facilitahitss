@php
    $latestReport = $reports->sortByDesc('period_start')->first();
@endphp

<style>
    .aneel-footer {
        position: fixed;
        bottom: 0;
        left: 0;
        right: 0;
        background-color: white;
        color: black;
        padding: 10px 0;
        z-index: 9999;
        font-size: 14px;
        overflow: hidden;
    }

    .aneel-footer .marquee-container {
        display: flex;
        white-space: nowrap;
        animation: scroll-left 10s linear infinite;
    }

    @keyframes scroll-left {
        0% { transform: translateX(0); }
        100% { transform: translateX(-50%); }
    }

    .aneel-footer .item {
        margin-right: 2rem;
        display: inline-block;
    }
</style>

<div class="aneel-footer">
    <div class="marquee-container">
        @foreach ([$indicators, $indicators] as $group)
            @foreach ($group as $indicator)
                @php
                    $data = $indicator->reports->firstWhere('report_id', $latestReport->id);
                    $value = $data->value ?? null;
                    $status = $data->status ?? null;
                @endphp
                <span class="item">
                    <strong>{{ $indicator->code }}</strong>:
                    {{ !is_null($value) ? number_format($value, 2) . '%' : '--' }}
                    {!! $status === 'Atingiu' ? '✅' : ($status === 'Não Atingiu' ? '❌' : '') !!}
                </span>
            @endforeach
        @endforeach
    </div>
</div>
