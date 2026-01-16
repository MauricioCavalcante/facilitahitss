@extends('aneel::layouts.app')

@section('content')
    <div class="container mt-4">
        <div class="mb-4">
            <div class="d-flex flex-column flex-md-row justify-content-between">
                <div class="mb-2 mb-md-0">
                    <h2>Dashboard Indicadores ANEEL</h2>
                </div>
                <div>
                    <x-aneel::filter :reports="$reports" :start="$filterStart" :end="$filterEnd" />
                </div>
            </div>
        </div>


        {{-- 🔹 Cards do último mês --}}
        <div>
            @php
                $groupsToShow = ['Item 1: Central de Serviços', 'Item 2: Atendimento ao Usuário de 1º e 2º Níveis'];

                $normalizedReports = $hasFilter
                    ? $reports
                    : collect($reports)->mapWithKeys(function ($item) {
                        $report = $item['report'];
                        $indicators = $item['indicators'];
                        return [
                            $report->id => (object) [
                                'period_start' => $report->period_start,
                                'indicators' => $indicators,
                            ],
                        ];
                    });
            @endphp

            @foreach ($groupsToShow as $groupName)
                @if (isset($categories[$groupName]))
                    <x-aneel::card_bloco :title="$groupName" :codes="$categories[$groupName]" :labels="[]" :indicators="$indicators"
                        :reports="$normalizedReports" :start-date="$hasFilter ? $filterStart : $lastMonthStart" :end-date="$hasFilter ? $filterEnd : $lastMonthEnd" />
                @endif
            @endforeach
        </div>

        {{-- Gráficos expandíveis --}}
        <div>
            @foreach ($categories as $groupName => $indicatorCodes)
                @php $groupId = Str::slug($groupName . '-' . uniqid()); @endphp

                <div class="card mb-1">
                    <div class="card-header h5" style="background-color: rgb(202, 202, 202); cursor: pointer"
                        data-bs-toggle="collapse" data-bs-target="#collapse-{{ $groupId }}" aria-expanded="true"
                        aria-controls="collapse-{{ $groupId }}" style="cursor: pointer;">
                        <div class="d-flex justify-content-between align-items-center">
                            <span>{{ $groupName }}</span>
                        </div>
                    </div>

                    <div id="collapse-{{ $groupId }}" class="collapse show"
                        aria-labelledby="heading-{{ $groupId }}">
                        <div class="card-body font-sans antialiased border" style="background-color: #ffffff;">
                            @foreach ($indicators->whereIn('code', $indicatorCodes) as $indicator)
                                <x-aneel::line-indicators-group :indicator="$indicator" :reports="$graphReports" :lastSixMonths="$periodMonths" />
                            @endforeach
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    {{-- 🔹 Tabela geral --}}
    <div style="width: 95%" class="container-fluid mt-4">
        <x-aneel::table :indicators="$indicators" :reports="$graphReports" />
    </div>
@endsection
