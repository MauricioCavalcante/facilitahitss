<h4 class="mt-5">Tabela Comparativa Mensal — Indicadores ANEEL</h4>
<div class="table-responsive">
    <table class="table table-responsive table-sm table-bordered table-hover text-nowrap mt-3">
        <thead>
            <tr>
                <th style="white-space: nowrap; width: 1%;" colspan="2">nº</th>
                <th style="white-space: nowrap;">Indicador</th>
                @foreach ($reports as $report)
                    <th>{{ \Carbon\Carbon::parse($report->period_start)->locale('pt_BR')->translatedFormat('M/Y') }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach ($indicators as $indicator)
                <tr>
                    <td>{{ $indicator->id }}</td>
                    @php
                        $imgPath = asset("img/aneel-indicadores/Indicador-{$indicator->id}({$indicator->code}).png");
                    @endphp
                    <td>
                        <i class="bi bi-image fs-6 text-secondary" style="cursor: pointer;" title="Ver imagem"
                            data-bs-toggle="modal" data-bs-target="#imageModal" data-src="{{ $imgPath }}">
                        </i>
                    </td>
                    <td>{{ $indicator->name }} ({{ $indicator->code }}) {{ $indicator->service_level ?? '—' }}</td>
                    @foreach ($reports as $report)
                        @php
                            $data = $indicator->reports->firstWhere('report_id', $report->id);
                            $value = $data->value ?? null;
                            $status = $data->status ?? null;
                        @endphp
                        <td>
                            @if (!is_null($value))
                                <div class="d-flex justify-content-between">
                                    <div>
                                        {{ number_format($value, 2) }}%
                                    </div>
                                    <div>
                                        {!! $status ? ($status === 'Atingiu' ? '✅' : '❌') : '' !!}
                                    </div>
                                </div>
                            @else
                                —
                            @endif
                        </td>
                    @endforeach
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

<div class="modal fade" id="imageModal" tabindex="-1" aria-labelledby="imageModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-body text-center">
                <img src="" id="modalImage" class="img-fluid rounded shadow" alt="Imagem do Indicador">
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        const modal = document.getElementById('imageModal');
        const modalImage = document.getElementById('modalImage');

        modal.addEventListener('show.bs.modal', function (event) {
            const trigger = event.relatedTarget;
            const imageSrc = trigger.getAttribute('data-src');
            modalImage.src = imageSrc;
        });

        modal.addEventListener('hidden.bs.modal', function () {
            modalImage.src = '';
        });
    });
</script>

