@props(['categories', 'indicators', 'reports', 'selectedReport'])

@php
    $currentReport = $selectedReport ?? $reports->sortByDesc('period_start')->first();
@endphp

@foreach ($categories as $category => $codes)
    <div class="col-12 col-lg-6">
        <div class="card">
            <div class="card-header mt-1">
                <h4>{{ $category }}</h4>
            </div>    
            <div class="table-responsive">
                <table class="table table-sm table-bordered table-hover">
                    <thead>
                        <tr>
                            <th style="white-space: nowrap; width: 1%;">nº</th>
                            <th style="white-space: nowrap;">Indicador</th>
                            <th style="white-space: nowrap;">Meta</th>
                            <th style="white-space: nowrap;">
                                {{ \Carbon\Carbon::parse($currentReport->period_start)->locale('pt_BR')->translatedFormat('M/Y') }}
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($indicators->whereIn('code', $codes) as $indicator)
                            @php
                                $data = $indicator->reports->firstWhere('report_id', $currentReport->id);
                                $value = $data->value ?? null;
                                $status = $data->status ?? null;
                            @endphp
                            <tr>
                                <td>{{ $indicator->id }}</td>
                                <td>{{ $indicator->name }} - ({{ $indicator->code }})</td>
                                <td>{{ $indicator->service_level ?? '—' }}</td>
                                <td style="white-space: nowrap;">
                                    @if (!is_null($value))
                                        <div class="d-flex justify-content-between">
                                            <div>{{ number_format($value, 2) }}%</div>
                                            <div>{!! $status === 'Atingiu' ? '✅' : '❌' !!}</div>
                                        </div>
                                    @else
                                        —
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endforeach
