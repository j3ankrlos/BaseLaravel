<div>
    <div class="row mb-3 align-items-center">
        <div class="col-md-6">
            <h4 class="mb-0 fw-bold text-dark"><i class="ph ph-list-bullets me-2 text-primary"></i> Listado de Partos Registrados</h4>
            <p class="text-muted small mt-1 mb-0">Gestión y control de registros acumulados por sala.</p>
        </div>
        <div class="col-md-6 text-end d-flex justify-content-end gap-2">
            <a href="/genetic-selection/births" class="btn btn-primary shadow-sm fw-bold px-4 py-2" wire:navigate>
                <i class="ph ph-plus-circle me-1"></i> Registrar Nuevo Parto
            </a>
        </div>
    </div>

    @if (session()->has('message'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm border-0 mb-4" role="alert">
            <i class="ph ph-check-circle me-2"></i> {{ session('message') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card shadow-sm border-0 overflow-hidden">
        <div class="card-header bg-white py-3 border-bottom">
            <div class="row align-items-center">
                <div class="col-md-4">
                    <div class="input-group">
                        <span class="input-group-text bg-light border-0"><i class="ph ph-magnifying-glass text-muted"></i></span>
                        <input type="text" wire:model.live="search" class="form-control border-0 bg-light" placeholder="Buscar por sala o madre...">
                    </div>
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-striped mb-0 align-middle">
                    <thead class="bg-light text-muted small text-uppercase fw-bold">
                        <tr>
                            <th style="width: 50px;"></th>
                            <th class="ps-4">Sala</th>
                            <th class="text-center">Lote</th>
                            <th class="text-center">Rango de Fechas</th>
                            <th class="text-center">Total Partos (Madres)</th>
                            <th class="text-center">Total Lechones Seleccionados</th>
                            <th class="text-center">Estado</th>
                            <th class="text-end pe-4">Acciones</th>
                        </tr>
                    </thead>
                    <tbody x-data="{ expandedRow: null }">
                        @forelse ($rooms as $room)
                            @php 
                                $lotNorm = ($room->maternity_lot === null || $room->maternity_lot === '') ? 'sin_lote' : $room->maternity_lot;
                                $groupKey = $room->room . '__' . $lotNorm;
                                $thisGroupBirths = $birthsByGroup[$groupKey] ?? collect();
                            @endphp
                            <!-- Main Row -->
                            <tr @click="expandedRow = expandedRow === '{{ $groupKey }}' ? null : '{{ $groupKey }}'" class="cursor-pointer">
                                <td class="text-center text-muted">
                                    <i class="ph-bold ph-caret-down accordion-icon" :style="expandedRow === '{{ $groupKey }}' ? 'transform: rotate(180deg)' : ''"></i>
                                </td>
                                <td class="ps-4">
                                    <span class="badge bg-light text-dark border px-3 py-2 fs-6 mb-1 d-inline-block"><i class="ph ph-door text-primary me-1"></i> {{ $room->room }}</span>
                                </td>
                                <td class="text-center">
                                    @if($room->maternity_lot)
                                        <span class="badge bg-indigo-subtle text-indigo border border-indigo-subtle px-3 fs-6 rounded-pill fw-bold">{{ $room->maternity_lot }}</span>
                                    @else
                                        <span class="text-muted">Sin Lote</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <span class="small text-muted">
                                        {{ \Carbon\Carbon::parse($room->first_date)->format('d/m/Y') }}
                                        @if($room->first_date !== $room->last_date)
                                            → {{ \Carbon\Carbon::parse($room->last_date)->format('d/m/Y') }}
                                        @endif
                                    </span>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-3 fs-6">{{ $room->total_partos }}</span>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-indigo-subtle text-indigo border border-indigo-subtle rounded-pill px-3 fs-6 fw-bold">{{ $room->total_lechones }}</span>
                                </td>
                                <td class="text-center">
                                    @php
                                        // Since we group by room/lot, we can infer the state from any birth in the group
                                        $firstBirth = $thisGroupBirths->first();
                                        $estado = $firstBirth ? $firstBirth->estado : 2;
                                    @endphp
                                    @if($estado == 1)
                                        <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3"><i class="ph ph-check-circle me-1"></i> Destetada</span>
                                    @else
                                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-3"><i class="ph ph-activity me-1"></i> Activa</span>
                                    @endif
                                </td>
                                <td class="text-end pe-4">
                                    <div class="btn-group shadow-sm" role="group">
                                        @if($room->maternity_lot)
                                            @php
                                                $pdfPath = "pedigree_pdf/Pedigree_Sala_{$room->room}_Lote_{$room->maternity_lot}.pdf";
                                            @endphp
                                            @if(\Illuminate\Support\Facades\Storage::disk('public')->exists($pdfPath))
                                                <a href="{{ \Illuminate\Support\Facades\Storage::url($pdfPath) }}?v={{ time() }}" target="_blank" class="btn btn-sm btn-white border text-info" title="Ver Pedigree Generado" @click.stop>
                                                    <i class="ph ph-eye"></i>
                                                </a>
                                            @endif
                                        @endif
                                        <button class="btn btn-sm btn-light border" title="Desplegar Detalles">
                                            <i class="ph ph-list-plus"></i>
                                        </button>
                                        {{-- Pedigree Button por Sala: navega a la vista previa --}}
                                        <a href="{{ route('genetics.births.pedigree-preview', ['room' => $room->room]) }}"
                                           wire:navigate
                                           class="btn btn-sm btn-white border text-primary"
                                           title="Ver datos y Generar Pedigree"
                                           @click.stop>
                                            <i class="ph ph-dna"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            
                            <!-- Collapsible Details Row -->
                            <tr x-show="expandedRow === '{{ $groupKey }}'" x-cloak style="display: none;" class="bg-light border-bottom">
                                <td colspan="8" class="p-0 border-0">
                                    <div class="p-4 rounded-bottom" style="box-shadow: inset 0 3px 6px -3px rgba(0,0,0,.1);">
                                        <h6 class="fw-bold text-dark mb-3"><i class="ph ph-baby me-2"></i> Partos: Sala {{ $room->room }} — Lote {{ $room->maternity_lot ?: 'Sin Lote' }}</h6>
                                        <div class="table-responsive rounded border bg-white shadow-sm">
                                            <table class="table table-sm table-hover mb-0 align-middle">
                                                <thead class="bg-light text-muted small text-uppercase fw-bold">
                                                    <tr>
                                                        <th class="ps-3">Fechas / PIC</th>
                                                        <th>Jaula</th>
                                                        <th>Madre</th>
                                                        <th>Padre</th>
                                                        <th class="text-center">Lote</th>
                                                        <th>Raza</th>
                                                        <th class="text-center">LNV</th>
                                                        <th class="text-center">Seleccionados</th>
                                                        <th class="text-center">Estado</th>
                                                        <th class="text-end pe-3">Acciones</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach ($thisGroupBirths as $birth)
                                                        <tbody x-data="{ showPiglets: false }">
                                                        <tr @click="showPiglets = !showPiglets" class="cursor-pointer" :class="showPiglets ? 'table-active' : ''">
                                                            <td class="ps-3">
                                                                <div class="d-flex align-items-center gap-2">
                                                                    <i class="ph ph-caret-right text-muted" :style="showPiglets ? 'transform:rotate(90deg); transition:.15s' : 'transition:.15s'"></i>
                                                                    <div>
                                                                        <div class="fw-bold text-dark font-monospace">{{ $birth->pic_cycle }}-{{ str_pad($birth->pic_day, 3, '0', STR_PAD_LEFT) }}</div>
                                                                        <div class="small text-muted">{{ \Carbon\Carbon::parse($birth->calendar_date)->format('d/m/Y') }}</div>
                                                                    </div>
                                                                </div>
                                                            </td>
                                                            <td>
                                                                <span class="text-muted small"><i class="ph ph-grid-four text-secondary"></i> {{ $birth->cage }}</span>
                                                            </td>
                                                            <td><span class="badge bg-light text-dark border font-monospace">{{ $birth->mother_tag }}</span></td>
                                                            <td><span class="badge bg-light text-secondary border font-monospace">{{ $birth->father_tag }}</span></td>
                                                            <td class="text-center"><span class="fw-bold text-dark">{{ $birth->maternity_lot }}</span></td>
                                                            <td><span class="text-primary fw-medium small">{{ $birth->genetic->name }}</span></td>
                                                            <td class="text-center">
                                                                <span class="text-success fw-bold">{{ $birth->lnv }}</span>
                                                            </td>
                                                            <td class="text-center">
                                                                <span class="text-indigo fw-bold">{{ $birth->quantity }}</span>
                                                            </td>
                                                            <td class="text-center">
                                                                @if($birth->estado == 1)
                                                                    <span class="badge bg-success-subtle text-success border-0 small"><i class="ph ph-check-circle me-1"></i> Destetada</span>
                                                                @else
                                                                    <span class="badge bg-primary-subtle text-primary border-0 small"><i class="ph ph-activity me-1"></i> Activa</span>
                                                                @endif
                                                            </td>
                                                            <td class="text-end pe-3">
                                                                <div class="btn-group shadow-sm">
                                                                    <a href="/genetic-selection/edit/{{ $birth->id }}" wire:navigate class="btn btn-sm btn-white border text-warning" title="Editar" @click.stop>
                                                                        <i class="ph ph-pencil-simple"></i>
                                                                    </a>
                                                                    <button wire:click.stop="delete({{ $birth->id }})" wire:confirm="¿Eliminar parto y sus lechones de forma permanente?" class="btn btn-sm btn-white border text-danger" title="Borrar" @click.stop>
                                                                        <i class="ph ph-trash"></i>
                                                                    </button>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                        {{-- Piglet Details Sub-Row --}}
                                                        <tr x-show="showPiglets" x-cloak style="display:none;" class="table-light">
                                                            <td colspan="9" class="p-0 border-0">
                                                                <div class="px-4 pb-3 pt-2">
                                                                    <h6 class="text-muted small fw-bold mb-2"><i class="ph ph-baby me-1"></i> Lechones ({{ $birth->details->count() }}) — Madre: {{ $birth->mother_tag }} | Jaula: {{ $birth->cage }}</h6>
                                                                    <table class="table table-sm table-bordered mb-0 small">
                                                                        <thead class="table-secondary text-uppercase text-muted" style="font-size:0.7rem;">
                                                                            <tr>
                                                                                <th>#</th>
                                                                                <th>ID Oreja</th>
                                                                                <th>ID Arete (Generado)</th>
                                                                                <th class="text-center">Sexo</th>
                                                                                <th class="text-center">Peso (kg)</th>
                                                                                <th class="text-center">N° Pezones</th>
                                                                            </tr>
                                                                        </thead>
                                                                        <tbody>
                                                                            @forelse ($birth->details as $idx => $detail)
                                                                                <tr>
                                                                                    <td class="text-muted">{{ $idx + 1 }}</td>
                                                                                    <td class="font-monospace fw-bold">{{ $detail->ear_id ?: '—' }}</td>
                                                                                    <td class="font-monospace text-primary">{{ $detail->generated_id ?: '—' }}</td>
                                                                                    <td class="text-center">
                                                                                        @if(strtolower($detail->sex) === 'hembra')
                                                                                            <span class="badge border" style="background:#fef2f2;color:#dc3545;">♀ Hembra</span>
                                                                                        @else
                                                                                            <span class="badge bg-primary-subtle text-primary border">♂ Macho</span>
                                                                                        @endif
                                                                                    </td>
                                                                                    <td class="text-center">{{ number_format($detail->weight, 2, ',', '.') }}</td>
                                                                                    <td class="text-center">{{ $detail->teats_total }}</td>
                                                                                </tr>
                                                                            @empty
                                                                                <tr><td colspan="6" class="text-center text-muted py-2">Sin lechones registrados.</td></tr>
                                                                            @endforelse
                                                                        </tbody>
                                                                    </table>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                        </tbody>
                                                    @endforeach
                                                    @if($thisGroupBirths->isEmpty())
                                                        <tr><td colspan="9" class="text-muted text-center py-3">No hay registros detallados para este grupo.</td></tr>
                                                    @endif
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5">
                                    <div class="py-4">
                                        <i class="ph ph-ghost fs-1 text-muted mb-3"></i>
                                        <p class="text-muted">No se encontraron partos registrados.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-white py-3">
            {{ $rooms->links() }}
        </div>
    </div>
    
    <style>
        .text-indigo { color: #6366f1; }
        .bg-indigo-subtle { background-color: #eef2ff; border-color: #e0e7ff !important; }
        .cursor-pointer { cursor: pointer; transition: background-color 0.15s ease-in-out; }
        .cursor-pointer:hover { background-color: #f8f9fa; }
        [data-bs-toggle="collapse"][aria-expanded="true"] .accordion-icon { transform: rotate(180deg); }
        .accordion-icon { transition: transform 0.2s ease-in-out; display: inline-block; }
    </style>

    <!-- Modal Lote de Maternidad & Pedigree -->
    <div class="modal fade" id="pedigreeModal" tabindex="-1" wire:ignore.self
         x-data 
         x-on:show-pedigree-modal.window="new bootstrap.Modal($el).show()"
         x-on:hide-pedigree-modal.window="let m = bootstrap.Modal.getInstance($el); if(m){ m.hide(); }">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-primary text-white border-bottom-0 pb-2">
                    <h5 class="modal-title fw-bold"><i class="ph ph-dna me-2"></i>Generar Pedigree: Sala {{ $pedigreeRoom }}</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form wire:submit.prevent="exportPedigree">
                    <div class="modal-body bg-light rounded-bottom pb-4 pt-3">
                        <div class="alert alert-info border-0 shadow-sm small py-2 d-flex align-items-center mb-3">
                            <i class="ph ph-info me-2 fs-5"></i>
                            Ingrese el Lote de Maternidad. Se generará un PDF a partir de la plantilla original de Excel.
                        </div>

                        <label class="form-label fw-bold">Lote de Maternidad</label>
                        <div class="input-group mb-2">
                            <span class="input-group-text bg-white border-end-0"><i class="ph ph-tag text-muted"></i></span>
                            <input type="text" wire:model="pedigreeLot" class="form-control border-start-0 ps-0" placeholder="Ej. L-01, LT-2023..." required>
                        </div>
                        @error('pedigreeLot') <span class="text-danger small">{{ $message }}</span> @enderror
                        
                        <div class="mt-4 text-end">
                            <button type="button" class="btn btn-secondary border-0" data-bs-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn btn-primary d-inline-flex align-items-center border-0 ms-2" wire:loading.attr="disabled" wire:target="exportPedigree">
                                <span wire:loading.remove wire:target="exportPedigree"><i class="ph ph-file-pdf me-2"></i> Generar PDF</span>
                                <span wire:loading wire:target="exportPedigree"><i class="ph ph-spinner ph-spin me-2"></i> Procesando...</span>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Script to catch the event and open the PDF without blocking the DOM update -->
    @script
    <script>
        $wire.on('pedigree-generated', (event) => {
            window.open(event[0].url, '_blank');
        });
    </script>
    @endscript
</div>
```
