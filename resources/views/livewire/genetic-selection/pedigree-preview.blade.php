<div>
    {{-- Header --}}
    <div class="d-flex align-items-center gap-3 mb-4">
        <a href="{{ route('genetics.births.index') }}" wire:navigate
           class="btn btn-sm btn-light border" title="Volver al Listado">
            <i class="ph ph-arrow-left"></i>
        </a>
        <div>
            <h4 class="mb-0 fw-bold text-dark d-flex align-items-center gap-2">
                <i class="ph ph-dna text-primary"></i>
                Vista Previa de Pedigree — Sala
                <span class="badge bg-primary bg-opacity-15 text-primary border border-primary border-opacity-25 px-3 py-2 fs-5 ms-1">{{ $room }}</span>
            </h4>
        </div>
    </div>

    {{-- Card de asignación de lote + botón único --}}
    <div class="card shadow-sm border-0 mb-4" style="border-left: 4px solid #0d6efd !important; background: linear-gradient(135deg, #f8fbff 0%, #ffffff 100%);">
        <div class="card-body py-3">
            <form wire:submit.prevent="generatePdf">
                <div class="row align-items-end g-3">
                    <div class="col-md-4">
                        <label class="form-label fw-bold text-dark mb-1">
                            <i class="ph ph-tag text-primary me-1"></i> Lote de Maternidad <span class="text-danger">*</span>
                        </label>
                        <div class="input-group shadow-sm">
                            <span class="input-group-text bg-primary bg-opacity-10 border-primary border-opacity-25">
                                <i class="ph ph-tag text-primary"></i>
                            </span>
                            <input type="text"
                                   wire:model.live="pedigreeLot"
                                   class="form-control border-primary border-opacity-25 fw-bold fs-6 @error('pedigreeLot') is-invalid @enderror"
                                   placeholder="Ej. 870"
                                   style="letter-spacing: 1px;">
                        </div>
                        @error('pedigreeLot')
                            <span class="text-danger small mt-1 d-block">
                                <i class="ph ph-warning-circle me-1"></i>{{ $message }}
                            </span>
                        @enderror
                    </div>
                    <div class="col-md-5 text-muted small lh-sm">
                        <i class="ph ph-info-circle me-1 text-info"></i>
                        El lote se asignará a todos los partos listados y quedará registrado en la base de datos.
                    </div>
                    <div class="col-md-3 text-end">
                        <button type="submit"
                                class="btn btn-primary fw-bold px-4 py-2 shadow-sm w-100"
                                wire:loading.attr="disabled"
                                wire:target="generatePdf">
                            <span wire:loading.remove wire:target="generatePdf">
                                <i class="ph ph-file-pdf me-2"></i> Generar PDF
                            </span>
                            <span wire:loading wire:target="generatePdf">
                                <i class="ph ph-spinner ph-spin me-2"></i> Generando...
                            </span>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Tabla de datos de la sala --}}
    <div class="card shadow-sm border-0 overflow-hidden bg-white">
        <div class="card-header bg-white border-bottom py-3 d-flex align-items-center gap-2">
            <i class="ph ph-list-bullets text-primary"></i>
            <span class="fw-bold text-dark">Partos registrados — Sala {{ $room }}</span>
            <div class="ms-auto d-flex gap-2">
                <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25">
                    {{ count($births) }} partos
                </span>
                <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25">
                    {{ collect($births)->sum(fn($b) => $b->details->count()) }} lechones
                </span>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive" style="max-height: 62vh; overflow-y: auto; overflow-x: auto;">
                <table class="table table-bordered table-hover mb-0 align-middle text-center" style="font-size: 0.82rem;">
                    <thead style="background: var(--sidebar-bg, #1a1f3c); color: #fff; position: sticky; top: 0; z-index: 2;">
                        <tr>
                            <th rowspan="2">#</th>
                            <th rowspan="2">SALA</th>
                            <th rowspan="2">JAULA</th>
                            <th rowspan="2">ID MADRE</th>
                            <th rowspan="2">N° PARTOS</th>
                            <th rowspan="2">ID PADRE</th>
                            <th rowspan="2">PIC<br>DÍA</th>
                            <th rowspan="2">LNV</th>
                            <th rowspan="2">N° LECHO<br>SELECC.</th>
                            <th rowspan="2">ID OREJA</th>
                            <th rowspan="2">ID ARETE</th>
                            <th rowspan="2">RAZA</th>
                            <th colspan="2">PESO</th>
                            <th rowspan="2">M.<br>PEZONES</th>
                            <th colspan="3">N° PEZONES</th>
                            <th rowspan="2">SEXO</th>
                        </tr>
                        <tr>
                            <th>P. C/UP.</th>
                            <th>PROM</th>
                            <th>IZQ</th>
                            <th style="min-width:70px;">DETRÁS<br>OMBLIGO<br>IZQ</th>
                            <th style="min-width:70px;">DETRÁS<br>OMBLIGO<br>DER.</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $index = 1; @endphp
                        @forelse ($births as $birth)
                            @php
                                $avgWeight = $birth->details->count() > 0
                                    ? number_format($birth->details->avg('weight'), 2, ',', '.')
                                    : '';
                            @endphp
                            @foreach ($birth->details as $detail)
                                <tr>
                                    <td class="fw-bold text-muted">{{ $index++ }}</td>
                                    <td><span class="badge bg-secondary opacity-75">{{ $birth->room }}</span></td>
                                    <td>{{ $birth->cage }}</td>
                                    <td class="font-monospace fw-bold text-dark">{{ $birth->mother_tag }}</td>
                                    <td>{{ $birth->parity }}</td>
                                    <td class="font-monospace text-muted small">{{ $birth->father_tag }}</td>
                                    <td class="fw-bold">{{ str_pad($birth->pic_day, 3, '0', STR_PAD_LEFT) }}</td>
                                    <td class="text-success fw-bold">{{ $birth->lnv }}</td>
                                    <td class="fw-bold">{{ $birth->quantity }}</td>
                                    <td class="font-monospace small">{{ $detail->ear_id }}</td>
                                    <td class="font-monospace small">
                                        <span class="badge bg-white text-primary border border-primary px-1">{{ $detail->generated_id }}</span>
                                    </td>
                                    <td class="text-primary small">{{ optional($birth->genetic)->name }}</td>
                                    <td>{{ $detail->weight ? number_format($detail->weight, 2, ',', '.') : '' }}</td>
                                    <td class="bg-light text-muted">{{ $avgWeight }}</td>
                                    <td>{{ $detail->teats_total }}</td>
                                    <td>{{ $detail->teats_left }}</td>
                                    <td>{{ $detail->teats_behind_shoulder_left }}</td>
                                    <td>{{ $detail->teats_behind_shoulder_right }}</td>
                                    <td>
                                        <span class="badge {{ strtolower($detail->sex) == 'hembra' ? 'bg-danger' : 'bg-primary' }} bg-opacity-10 text-{{ strtolower($detail->sex) == 'hembra' ? 'danger' : 'primary' }} rounded-pill">
                                            {{ strtoupper($detail->sex) }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        @empty
                            <tr>
                                <td colspan="19" class="text-center py-5 text-muted">
                                    <i class="ph ph-baby ph-xl d-block mb-2 opacity-25" style="font-size: 2.5rem;"></i>
                                    <p class="mb-1 fw-bold">No hay partos activos sin lote asignado en la Sala {{ $room }}</p>
                                    <p class="small">Todos los partos de esta sala ya tienen un lote asignado o están destetados.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
