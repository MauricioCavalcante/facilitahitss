@extends('aneel::layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-between align-items-center mb-4">
            <div class="col">
                <h2 class="mb-0">Detalhes do Relatório</h2>
            </div>
            <div class="col-auto d-flex gap-2">
                <a href="{{ route('aneel::reportsRTA.index') }}" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Voltar
                </a>

                @if (in_array(Auth::user()->role, ['admin', 'editor']))
                    <a href="{{ route('aneel::reportsRTA.edit', ['id' => $reports->id]) }}" class="btn btn-primary">
                        <i class="bi bi-pencil-square"></i> Editar Relatório
                    </a>

                    <!-- Dropdown -->
                    <div class="btn-group">
                        <button type="button" class="btn btn-success dropdown-toggle" data-bs-toggle="dropdown"
                            aria-expanded="false">
                            <i class="bi bi-check2-circle"></i> Ações
                        </button>
                        <ul class="dropdown-menu">
                            <li>
                                <a class="dropdown-item"
                                    href="{{ route('aneel::reportsRTA.finalizeReport', ['id' => $reports->id]) }}">
                                    <i class="bi bi-check2-circle"></i> Finalizar Relatório
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item"
                                    href="{{ route('aneel::reportsRTA.finalizeGenerateReport', ['id' => $reports->id]) }}">
                                    <i class="bi bi-file-earmark-text"></i> Finalizar e gerar arquivos
                                </a>
                            </li>
                        </ul>
                    </div>
                @endif
            </div>
        </div>
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="mb-0">{{ $reports->name }}</h4>
                <a href="{{ route('aneel::archivesRTA', $reports->id) }}" class="btn btn-sm btn-secondary">
                    Baixar pasta completa
                </a>
            </div>

            <div class="card-body">
                <p><strong>Período:</strong>
                    {{ \Carbon\Carbon::parse($reports->period_start)->format('d/m/Y') }} a
                    {{ \Carbon\Carbon::parse($reports->period_end)->format('d/m/Y') }}
                </p>
                @php
                    $statusColor = match ($reports->status) {
                        'Em Andamento' => '#FFD700',
                        'Finalizado' => '#28a745',
                        default => '#6c757d',
                    };
                @endphp
                <div class="d-flex mb-3">
                    <span>
                        <strong>Status:</strong>
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="{{ $statusColor }}"
                            class="bi bi-circle-fill me-1" viewBox="0 0 18 16">
                            <circle cx="8" cy="8" r="8" />
                        </svg>
                    </span>
                    <span>
                        {{ $reports->status }}
                    </span>
                </div>

                @if ($reports->attachment && $reports->status == 'Finalizado')
                    <hr />
                    <div class="d-flex align-items-center gap-2">
                        <form action="{{ route('aneel::reportsRTA.deleteAttachmentRTA', $reports->id) }}" method="POST"
                            onsubmit="return confirm('Tem certeza que deseja excluir o arquivo gerado?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-outline-danger btn-sm">
                                <i class="bi bi-trash"></i>
                            </button>
                        </form>
                        <a href="{{ route('aneel::downloadReport', ['id' => $reports->id]) }}"
                            class="btn btn-outline-secondary btn-sm">
                            <i class="bi bi-download"></i>
                        </a>
                        <span>{{ $reports->name }}</span>
                    </div>
                @endif
                @if ($reports->xlsx_attachment)
                    <hr />
                    <div class="d-flex align-items-center gap-2">
                        <form action="{{ route('aneel::reportsRTA.deleteAttachmentXlsx', $reports->id) }}" method="POST"
                            onsubmit="return confirm('Tem certeza que deseja excluir o arquivo gerado?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-outline-danger btn-sm">
                                <i class="bi bi-trash"></i>
                            </button>
                        </form>
                        <a href="{{ route('aneel::downloadAneelXlsx', ['id' => $reports->id]) }}"
                            class="btn btn-outline-secondary btn-sm">
                            <i class="bi bi-download"></i>
                        </a>
                        <span>{{ $reports->xlsx_name }}</span>
                    </div>
                @endif
                @if (in_array(Auth::user()->role, ['admin', 'editor']) && $reports->status == 'Finalizado')
                    <hr />
                    <a href="{{ route('aneel::reportsRTA.generateRTAReport', ['id' => $reports->id]) }}"
                        class="btn {{ is_null($reports->attachment) ? 'btn-primary' : 'btn-warning' }}">
                        {{ is_null($reports->attachment) ? 'Gerar Relatório (Docx)' : 'Atualizar Relatório (Docx)' }}
                    </a>
                    <form action="{{ route('aneel::reportsRTA.updateXlsx', $reports->id) }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit"
                            class="btn {{ is_null($reports->xlsx_attachment) ? 'btn-primary' : 'btn-warning' }}">
                            {{ is_null($reports->xlsx_attachment) ? 'Gerar Planilha (Xlsx)' : 'Atualizar Planilha (Xlsx)' }}
                        </button>
                    </form>
                @endif
                <div class="mt-3">
                    <ol><strong>Justificativas:</strong>
                        <li>{{ $reports->justification1 ?? 'N/A' }}</li>
                        <li>{{ $reports->justification2 ?? 'N/A' }}</li>
                    </ol>
                </div>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header d-flex">
                <h4>Anexos</h4>
                <a href="{{ route('aneel::downloadImages', ['report_id' => $reports->id]) }}"
                    class="btn btn-secondary btn-sm ms-auto">
                    <i class="bi bi-download"></i> Baixar Imagens e Anexos
                </a>
            </div>
            <div class="card-body">

                <!-- PDFs Anexados -->
                <div class="mb-4">
                    <h5>PDFs Anexados</h5>
                    @if ($pdfAttachments->isNotEmpty())
                        <div class="row">
                            @foreach ($pdfAttachments as $attachment)
                                <div class="col-md-4 mb-3">
                                    <div class="d-flex align-items-center p-3 border">
                                        <div class="text-danger" style="font-size: 3rem; margin-right: 10px;">
                                            <i class="bi bi-filetype-pdf"></i>
                                        </div>
                                        <div class="me-auto">
                                            <p class="mb-0"><strong>{{ $attachment->label ?? 'Sem título' }}</strong></p>
                                            <p class="text-muted small mb-0">{{ $attachment->name }}</p>
                                        </div>
                                        <div class="d-flex gap-2">
                                            <a href="{{ route('aneel::downloadImagesById', ['id' => $attachment->id]) }}"
                                                class="btn btn-sm btn-outline-secondary" target="_blank"
                                                title="Download PDF">
                                                <i class="bi bi-download"></i>
                                            </a>
                                            @if (Auth::user()->role == 'admin')
                                                <form method="POST"
                                                    action="{{ route('aneel::reportsRTA.removeImageAttachment', ['id' => $attachment->id]) }}"
                                                    onsubmit="return confirm('Tem certeza que deseja remover este PDF?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button class="btn btn-sm btn-outline-danger" type="submit"
                                                        title="Excluir PDF">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p>Sem PDFs anexados.</p>
                    @endif
                </div>
                <hr />
                @if ($imageAttachments->isNotEmpty())
                    <div class="mb-4">
                        <h5>Imagens Anexadas</h5>
                        <div class="row">
                            @foreach ($imageAttachments as $attachment)
                                <div class="col-md-4 border p-2">
                                    <div>
                                        <p><strong>{{ $attachment->label ?? 'Sem título' }}</strong></p>
                                        <div>
                                            <img src="data:{{ $attachment->mime_type }};base64,{{ base64_encode($attachment->attachment ?? '') }}"
                                                class="img-fluid rounded shadow-sm mb-2 image-preview"
                                                alt="{{ $attachment->name ?? 'Imagem' }}"
                                                style="max-height: 200px; cursor: pointer;"
                                                data-index="{{ $loop->index }}"
                                                data-label="{{ $attachment->label ?? 'Sem título' }}"
                                                data-src="data:{{ $attachment->mime_type }};base64,{{ base64_encode($attachment->attachment ?? '') }}">
                                        </div>
                                        <div class="d-flex justify-content-end gap-2">
                                            <a href="{{ route('aneel::downloadImagesById', ['id' => $attachment->id]) }}"
                                                class="btn btn-sm btn-outline-secondary" target="_blank"
                                                title="Download Imagem">
                                                <i class="bi bi-download"></i>
                                            </a>
                                            @if (Auth::user()->role == 'admin')
                                                <form method="POST"
                                                    action="{{ route('aneel::reportsRTA.removeImageAttachment', ['id' => $attachment->id]) }}"
                                                    onsubmit="return confirm('Tem certeza que deseja remover esta imagem?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button class="btn btn-sm btn-outline-danger" type="submit"
                                                        title="Excluir Imagem">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @else
                    <p>Sem imagens anexadas.</p>
                @endif

            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h4>Indicadores</h4>
            </div>
            <div class="card-body row">
                @foreach ($reportIndicators as $indicator)
                    @php
                        $numero = $indicator->indicator->id;
                        $codigo = $indicator->indicator->code;
                        $nome = $indicator->indicator->name;
                        $imgPath = asset("img/aneel-indicadores/Indicador-{$numero}({$codigo}).png");
                    @endphp
                    <div class="col-lg-4 col-md-6 mb-4">
                        <div class="card shadow-sm">
                            <div
                                class="card-header bg-secondary text-white d-flex justify-content-between align-items-center">
                                <span>{{ $numero }} - {{ $nome }} ({{ $codigo }})</span>
                                <i class="bi bi-image fs-4" role="button" style="cursor: pointer;" title="Ver imagem"
                                    data-bs-toggle="modal" data-bs-target="#imageModal" data-src="{{ $imgPath }}">
                                </i>
                            </div>
                            <div class="card-body">
                                <p class="">
                                    <strong>Nível de Serviço:</strong> {{ $indicator->indicator->service_level }}
                                </p>


                                <div>
                                    <strong>Detalhes do Indicador</strong>
                                    @if (!empty($indicator->inputs))
                                        @php
                                            $inputs = is_array($indicator->inputs)
                                                ? $indicator->inputs
                                                : json_decode($indicator->inputs, true);
                                        @endphp
                                        @foreach ($inputs as $key => $value)
                                            <br><span>{{ strtoupper(str_replace('_', ' ', $key)) }}:
                                                <strong>{{ $value }}</strong></span>
                                        @endforeach
                                    @else
                                        <p class="text-muted">N/A</p>
                                    @endif
                                </div>
                                <p class="mt-2">Resultado:
                                    <strong>{{ number_format((float) ($indicator->value ?? 0), 2, ',', '.') }}%</strong>
                                </p>
                                @if ($indicator->status == 'Atingiu')
                                    <span>Status: </span><span class="text-success"><strong>O resultado atingiu o nível de
                                            serviço
                                            esperado.</strong></span>
                                @elseif($indicator->status == 'Não Atingiu')
                                    <span>Status: </span><span class="text-danger"><strong>O resultado não atingiu o nível
                                            de
                                            serviço
                                            esperado.</strong></span>
                                @elseif($indicator->status == 'Preencha todos os campos!')
                                    <span>Status: </span><span class="text-warning"><strong>Preencha todos os
                                            campos!</strong></span>
                                @endif
                                <div class="mt-3 text-nowrap overflow-hidden">
                                    @if ($indicator->name_attachment)
                                        <hr />
                                        <div class="d-flex align-items-center gap-2">
                                            <a href="{{ route('aneel::downloadIndicatorAttachment', ['id' => $indicator->id]) }}"
                                                class="btn btn-outline-secondary btn-sm">
                                                <i class="bi bi-download"></i> Anexo:
                                            </a>
                                            <span>{{ $indicator->name_attachment }}</span>
                                            @if (Auth::user()->role == 'admin')
                                                <form class="ms-auto" id="delete-attachment-{{ $indicator->id }}"
                                                    method="POST"
                                                    action="{{ route('aneel::reportsRTA.removeIndicatorAttachment', ['id' => $indicator->id]) }}"
                                                    onsubmit="return confirm('Tem certeza que deseja remover este anexo?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button class="btn btn-sm btn-outline-danger" type="submit">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
    <!-- Modal de Imagem -->
    <div class="modal fade" id="imageModal" tabindex="-1" aria-labelledby="imageModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-body text-center">
                    <img src="" id="modalImage" class="img-fluid rounded shadow" alt="Imagem do Indicador">
                </div>
            </div>
        </div>
    </div>

    <!-- Modal com Carrossel de Anexos -->
    <div class="modal fade" id="carouselImageModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-xl">
            <div class="modal-content">
                <div class="modal-header border-0">
                    <h5 class="modal-title" id="carouselImageLabel"></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>
                <div class="modal-body p-0">
                    <div id="carouselAttachments" class="carousel slide">
                        <div class="carousel-inner" id="carouselInner"></div>
                        <button class="carousel-control-prev" type="button" data-bs-target="#carouselAttachments"
                            data-bs-slide="prev">
                            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                            <span class="visually-hidden">Anterior</span>
                        </button>
                        <button class="carousel-control-next" type="button" data-bs-target="#carouselAttachments"
                            data-bs-slide="next">
                            <span class="carousel-control-next-icon" aria-hidden="true"></span>
                            <span class="visually-hidden">Próxima</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const modal = document.getElementById('imageModal');
            const modalImage = document.getElementById('modalImage');

            modal.addEventListener('show.bs.modal', function(event) {
                const trigger = event.relatedTarget;
                const imageSrc = trigger.getAttribute('data-src');
                modalImage.src = imageSrc;
            });

            modal.addEventListener('hidden.bs.modal', function() {
                modalImage.src = '';
            });
        });
        document.addEventListener("DOMContentLoaded", function() {
            const imageElements = document.querySelectorAll('.image-preview');
            const carouselInner = document.getElementById('carouselInner');
            const modalTitle = document.getElementById('carouselImageLabel');
            const carouselModal = new bootstrap.Modal(document.getElementById('carouselImageModal'));

            const images = Array.from(imageElements).map(img => ({
                src: img.dataset.src,
                label: img.dataset.label || 'Sem título',
            }));

            imageElements.forEach((img, index) => {
                img.addEventListener('click', () => {
                    carouselInner.innerHTML = '';

                    images.forEach((image, i) => {
                        const isActive = i === index ? 'active' : '';
                        const item = document.createElement('div');
                        item.className = `carousel-item ${isActive}`;
                        item.innerHTML = `
                        <div class="d-flex justify-content-center align-items-center" style="height:80vh;">
                            <img src="${image.src}" class="d-block img-fluid rounded shadow" style="max-height:100%; max-width:90%; object-fit:contain;" alt="${image.label}">
                        </div>
                    `;
                        carouselInner.appendChild(item);
                    });

                    modalTitle.textContent = images[index].label;
                    carouselModal.show();

                    const carouselInstance = new bootstrap.Carousel(document.getElementById(
                        'carouselAttachments'));
                    carouselInstance.to(index);
                });
            });

            document.getElementById('carouselAttachments').addEventListener('slid.bs.carousel', function(e) {
                const activeIndex = e.to;
                modalTitle.textContent = images[activeIndex]?.label || '';
            });
        });
    </script>
@endsection
