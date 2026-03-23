<div>
    {{-- Header --}}
    <div class="row mb-4 align-items-center">
        <div class="col-md-8">
            <a href="/genetic-selection/list" wire:navigate class="text-muted small d-block mb-1">
                <i class="ph ph-arrow-left me-1"></i> Volver al Listado
            </a>
            <h4 class="mb-0 fw-bold text-dark">
                <i class="ph ph-dna me-2 text-primary"></i> Pedigree del Parto
            </h4>
            <p class="text-muted small mt-1 mb-0">
                Lista de identificativos generados &bull; Madre: <strong>{{ $birth->mother_tag }}</strong> &bull;
                Raza: <strong>{{ $birth->genetic->name }}</strong> &bull;
                Fecha: <strong>{{ $birth->calendar_date->format('d/m/Y') }}</strong>
                <span class="badge bg-light text-dark border ms-1 font-monospace">PIC {{ $birth->pic_cycle }}-{{ str_pad($birth->pic_day, 3, '0', STR_PAD_LEFT) }}</span>
            </p>
        </div>
        <div class="col-md-4 text-end">
            <button onclick="window.print()" class="btn btn-outline-primary fw-bold px-4 py-2">
                <i class="ph ph-printer me-1"></i> Imprimir / PDF
            </button>
        </div>
    </div>

    {{-- Summary Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm text-center p-3">
                <div class="text-muted small mb-1">Sala / Jaula</div>
                <div class="fw-bold text-dark">{{ $birth->room }} / {{ $birth->cage }}</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm text-center p-3">
                <div class="text-muted small mb-1">Paridad</div>
                <div class="fw-bold text-dark fs-5">{{ $birth->parity }}</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm text-center p-3">
                <div class="text-muted small mb-1">LNV</div>
                <div class="fw-bold text-success fs-5">{{ $birth->lnv }}</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm text-center p-3">
                <div class="text-muted small mb-1">IDs Generados</div>
                <div class="fw-bold text-primary fs-5">{{ $birth->details->count() }}</div>
            </div>
        </div>
    </div>

    {{-- Details Table --}}
    <div class="card shadow-sm border-0" id="pedigree-table">
        <div class="card-header bg-white py-3">
            <h6 class="mb-0 fw-bold">Identificativos Individuales del Lote</h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-striped mb-0 align-middle text-sm">
                    <thead class="bg-light text-muted small text-uppercase fw-bold">
                        <tr>
                            <th class="ps-4">#</th>
                            <th>ID Oreja</th>
                            <th>ID Arete</th>
                            <th class="text-center">Peso (Kg)</th>
                            <th class="text-center" title="Nro de Pesones">N Pesones</th>
                            <th class="text-center" title="Izquierdos">IZQ</th>
                            <th class="text-center" title="Detrás Hombro Izq.">DTRZ IZQ.</th>
                            <th class="text-center" title="Detrás Hombro Der.">DTRZ DER.</th>
                            <th class="text-center">Sexo</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($birth->details as $index => $detail)
                            <tr>
                                <td class="ps-4 text-muted small">{{ $index + 1 }}</td>
                                <td>
                                    <span class="badge bg-white text-dark border py-2 px-3 font-monospace fw-bold">
                                        {{ $detail->ear_id ?? '—' }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-light text-primary border border-primary-subtle py-2 px-3 font-monospace fw-bold">
                                        {{ $detail->generated_id }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    @if($detail->weight)
                                        <span class="fw-bold">{{ number_format($detail->weight, 2) }}</span>
                                        <span class="text-muted small"> kg</span>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td class="text-center">{{ $detail->teats_total ?? '—' }}</td>
                                <td class="text-center">{{ $detail->teats_left ?? '—' }}</td>
                                <td class="text-center">{{ $detail->teats_behind_shoulder_left ?? '—' }}</td>
                                <td class="text-center">{{ $detail->teats_behind_shoulder_right ?? '—' }}</td>
                                <td class="text-center">
                                    @php
                                        $sexColor = match($detail->sex) {
                                            'Hembra' => 'badge bg-pink-subtle text-danger border border-danger-subtle',
                                            'Macho'  => 'badge bg-primary-subtle text-primary border border-primary-subtle',
                                            default  => 'badge bg-light text-dark border',
                                        };
                                    @endphp
                                    <span class="{{ $sexColor }} px-3 py-1">{{ $detail->sex ?? '—' }}</span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center py-5 text-muted">No hay identificativos en este parto.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <style>
        @media print {
            nav, .btn, .sidebar, .topbar, a[wire\:navigate] { display: none !important; }
            body { background: white; }
            .card { box-shadow: none !important; border: 1px solid #dee2e6 !important; }
        }
    </style>
</div>
