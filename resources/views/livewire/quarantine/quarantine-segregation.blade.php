<div>
    {{-- ═══════════════════════════════════════════════════════════
         BARRA SUPERIOR: Resumen del Lote (horizontal, compacta)
    ═══════════════════════════════════════════════════════════ --}}
    <div class="card border-0 shadow-sm mb-4 overflow-hidden" style="border-radius: 12px;">
        <div class="card-body py-3 px-4">
            <div class="d-flex flex-wrap align-items-center gap-4">
                {{-- Ícono + Título --}}
                <div class="d-flex align-items-center gap-2 me-2">
                    <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center" style="width:38px;height:38px;">
                        <i class="ph ph-shield-check text-white fs-5"></i>
                    </div>
                    <div>
                        <div class="text-muted" style="font-size:0.68rem;text-transform:uppercase;font-weight:700;letter-spacing:.05em;">Lote en Proceso</div>
                        <div class="fw-bold fs-6 text-dark">{{ $batch->document_number }}</div>
                    </div>
                </div>

                <div class="vr d-none d-md-block opacity-25"></div>

                {{-- Origen --}}
                <div>
                    <div class="text-muted" style="font-size:0.68rem;text-transform:uppercase;font-weight:700;">Origen</div>
                    <div class="fw-semibold">{{ $batch->origin }}</div>
                </div>

                <div class="vr d-none d-md-block opacity-25"></div>

                {{-- Proveedor --}}
                <div>
                    <div class="text-muted" style="font-size:0.68rem;text-transform:uppercase;font-weight:700;">Proveedor</div>
                    <div class="fw-semibold">{{ $batch->provider }}</div>
                </div>

                <div class="vr d-none d-md-block opacity-25"></div>

                {{-- Fecha --}}
                <div>
                    <div class="text-muted" style="font-size:0.68rem;text-transform:uppercase;font-weight:700;">Fecha Ingreso</div>
                    <div class="fw-semibold">{{ $batch->entry_date->format('d/m/Y') }}</div>
                </div>

                <div class="vr d-none d-md-block opacity-25"></div>

                {{-- Sexo --}}
                <div>
                    <div class="text-muted" style="font-size:0.68rem;text-transform:uppercase;font-weight:700;">Sexo</div>
                    @if($batch->sex == 'MACHO')
                        <span class="badge bg-info-subtle text-info fw-bold"><i class="ph ph-gender-male me-1"></i>MACHO</span>
                    @else
                        <span class="badge bg-danger-subtle text-danger fw-bold"><i class="ph ph-gender-female me-1"></i>HEMBRA</span>
                    @endif
                </div>

                <div class="vr d-none d-md-block opacity-25"></div>

                {{-- Progreso --}}
                <div class="flex-grow-1" style="min-width: 160px;">
                    <div class="d-flex justify-content-between mb-1 small">
                        <span class="text-muted fw-bold" style="font-size:0.68rem;text-transform:uppercase;">Progreso</span>
                        <span class="fw-bold text-primary">{{ $batch->current_quantity }} / {{ $batch->total_quantity }}</span>
                    </div>
                    <div class="progress" style="height:8px;border-radius:4px;">
                        <div class="progress-bar bg-primary progress-bar-striped progress-bar-animated"
                             style="width:{{ $batch->total_quantity > 0 ? ($batch->current_quantity / $batch->total_quantity) * 100 : 0 }}%"></div>
                    </div>
                </div>

                {{-- Últimos segregados badge --}}
                @if($segregatedAnimals->count() > 0)
                <div class="ms-auto">
                    <div class="text-muted" style="font-size:0.68rem;text-transform:uppercase;font-weight:700;">Últimos Registrados</div>
                    <div class="d-flex gap-1 flex-wrap mt-1">
                        @foreach($segregatedAnimals->take(5) as $sa)
                            <span class="badge bg-light text-dark border fw-semibold" style="font-size:0.7rem;">
                                {{ $sa->internal_id }}
                            </span>
                        @endforeach
                        @if($segregatedAnimals->count() > 5)
                            <span class="badge bg-secondary-subtle text-secondary">+{{ $segregatedAnimals->count() - 5 }}</span>
                        @endif
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════════════════
         FORMULARIO PRINCIPAL (ancho completo)
    ═══════════════════════════════════════════════════════════ --}}
    <form wire:submit.prevent="save">
        <div class="card shadow-sm border-0 mb-4" style="border-radius: 12px;">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold"><i class="ph ph-identification-card me-2 text-primary"></i> DATOS DEL ANIMAL IMPORTADO</h5>
                <button type="submit" class="btn btn-primary px-4 shadow-sm fw-bold" wire:loading.attr="disabled">
                    <span wire:loading.remove><i class="ph ph-floppy-disk me-1"></i> GUARDAR ANIMAL</span>
                    <span wire:loading><i class="ph ph-spinner me-1"></i> Guardando...</span>
                </button>
            </div>
            <div class="card-body p-4">
                {{-- SECCIÓN 1: DATOS BÁSICOS --}}
                <div class="row g-2 mb-4 align-items-end">
                    <div class="col-md-1">
                        <label class="form-label fw-bold small text-uppercase mb-1">Cant.</label>
                        <input wire:model.live="quantity" type="number" class="form-control form-control-sm bg-white shadow-sm fw-bold">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-bold small text-uppercase mb-1">Raza / Genética</label>
                        <select wire:model.live="genetic_id" class="form-select form-select-sm bg-white shadow-sm @error('genetic_id') is-invalid @enderror">
                            <option value="">Seleccione...</option>
                            @foreach($genetics as $g) <option value="{{ $g->id }}">{{ $g->name }}</option> @endforeach
                        </select>
                        @error('genetic_id') <div class="invalid-feedback x-small">{{ $message }}</div> @enderror
                    </div>
                    
                    @if($quantity == 1)
                    <div class="col-md-3">
                        <label class="form-label fw-bold small text-uppercase mb-1">ID Oficial</label>
                        <input wire:model.live="official_id" type="text" class="form-control form-control-sm bg-white shadow-sm fw-bold text-uppercase @error('official_id') is-invalid @enderror" placeholder="">
                        @error('official_id') <div class="invalid-feedback x-small">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-bold small text-uppercase mb-1">ID Interno</label>
                        <input wire:model="internal_id" type="text" class="form-control form-control-sm bg-light fw-bold text-uppercase border-primary-subtle @error('internal_id') is-invalid @enderror" placeholder="">
                        @error('internal_id') <div class="invalid-feedback x-small">{{ $message }}</div> @enderror
                    </div>
                    @else
                    <div class="col-md-5">
                        <div class="alert alert-info py-1 px-2 mb-0 border-0 small d-flex align-items-center" style="height: 31px;">
                            <i class="ph ph-info me-2 fs-6"></i> Segregación por lote (IDs se asignarán en pubertad)
                        </div>
                    </div>
                    @endif

                    <div class="col-md-2">
                        <label class="form-label fw-bold small text-uppercase mb-1">Fecha Nac.</label>
                        <input wire:model="birth_date" type="date" class="form-control form-control-sm bg-white shadow-sm">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-bold small text-uppercase mb-1">Sexo</label>
                        <select wire:model="sex" class="form-select form-select-sm bg-white shadow-sm @error('sex') is-invalid @enderror">
                            <option value="">Seleccione...</option>
                            <option value="MACHO">MACHO</option>
                            <option value="HEMBRA">HEMBRA</option>
                        </select>
                        @error('sex') <div class="invalid-feedback x-small">{{ $message }}</div> @enderror
                    </div>
                </div>

                <hr class="my-4 opacity-25">

                {{-- SECCIÓN 2: UBICACIÓN --}}
                {{-- ... (sin cambios aquí) ... --}}
                <h6 class="fw-bold text-primary mb-3 text-uppercase">
                    <i class="ph ph-map-pin me-2"></i> Ubicación en Granja
                </h6>
                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <label class="form-label fw-bold small text-uppercase">Nave</label>
                        <select wire:model.live="barn_id" class="form-select bg-white shadow-sm @error('barn_id') is-invalid @enderror">
                            <option value="">Seleccione Nave...</option>
                            @foreach($barns as $b) <option value="{{ $b->id }}">{{ $b->name }}</option> @endforeach
                        </select>
                        @error('barn_id') <div class="invalid-feedback x-small">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold small text-uppercase">Sección</label>
                        <select wire:model.live="barn_section_id" class="form-select bg-white shadow-sm @error('barn_section_id') is-invalid @enderror" {{ empty($sections) ? 'disabled' : '' }}>
                            <option value="">Seleccione Sección...</option>
                            @foreach($sections as $s) <option value="{{ $s->id }}">{{ $s->name }}</option> @endforeach
                        </select>
                        @error('barn_section_id') <div class="invalid-feedback x-small">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold small text-uppercase">Corral / Jaula</label>
                        <select wire:model="pen_id" class="form-select bg-white shadow-sm @error('pen_id') is-invalid @enderror" {{ empty($pens) ? 'disabled' : '' }}>
                            <option value="">Seleccione Corral...</option>
                            @foreach($pens as $p) <option value="{{ $p->id }}">{{ $p->name }}</option> @endforeach
                        </select>
                        @error('pen_id') <div class="invalid-feedback x-small">{{ $message }}</div> @enderror
                    </div>
                </div>

                @if($quantity == 1)
                <hr class="my-4 opacity-25">

                <h6 class="fw-bold text-primary mb-3 text-uppercase">
                    <i class="ph ph-tree-structure me-2"></i> Árbol de Pedigrí (3 Generaciones)
                </h6>

                <div class="pedigree-tree-container bg-light p-4 rounded-4 position-relative">

                    {{-- NIVEL 1: PADRES --}}
                    <div class="row mb-4 g-4">
                        <div class="col-6">
                            <div class="card border-0 shadow-sm pedigree-card bg-info-subtle border-start border-4 border-info">
                                <div class="card-body p-3">
                                    <label class="d-block x-small fw-bold text-info text-uppercase mb-2"><i class="ph ph-gender-male"></i> PADRE</label>
                                    <div class="row g-2">
                                        <div class="col-7"><input wire:model="f_tag" type="text" class="form-control form-control-sm border-info border-opacity-25" placeholder="ID Padre"></div>
                                        <div class="col-5">
                                            <select wire:model="f_genetic" class="form-select form-select-sm border-info border-opacity-25">
                                                <option value="">Raza...</option>
                                                @foreach($genetics as $g) <option value="{{ $g->id }}">{{ $g->name }}</option> @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="card border-0 shadow-sm pedigree-card bg-danger-subtle border-start border-4 border-danger">
                                <div class="card-body p-3">
                                    <label class="d-block x-small fw-bold text-danger text-uppercase mb-2"><i class="ph ph-gender-female"></i> MADRE</label>
                                    <div class="row g-2">
                                        <div class="col-7"><input wire:model="m_tag" type="text" class="form-control form-control-sm border-danger border-opacity-25" placeholder="ID Madre"></div>
                                        <div class="col-5">
                                            <select wire:model="m_genetic" class="form-select form-select-sm border-danger border-opacity-25">
                                                <option value="">Raza...</option>
                                                @foreach($genetics as $g) <option value="{{ $g->id }}">{{ $g->name }}</option> @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- NIVEL 2: ABUELOS --}}
                    <div class="row mb-4 g-3">
                        @php
                            $abuelos = [
                                ['wire_tag' => 'ff_tag', 'wire_gen' => 'ff_genetic', 'label' => 'ABUELO P.', 'color' => 'info'],
                                ['wire_tag' => 'fm_tag', 'wire_gen' => 'fm_genetic', 'label' => 'ABUELA P.', 'color' => 'danger'],
                                ['wire_tag' => 'mf_tag', 'wire_gen' => 'mf_genetic', 'label' => 'ABUELO M.', 'color' => 'info'],
                                ['wire_tag' => 'mm_tag', 'wire_gen' => 'mm_genetic', 'label' => 'ABUELA M.', 'color' => 'danger'],
                            ];
                        @endphp
                        @foreach($abuelos as $a)
                        <div class="col-3">
                            <div class="card border-0 shadow-sm bg-white p-2 small border-top border-3 border-{{ $a['color'] }}">
                                <label class="x-small text-muted fw-bold mb-1">{{ $a['label'] }}</label>
                                <input wire:model="{{ $a['wire_tag'] }}" type="text" class="form-control form-control-sm border-0 bg-light mb-1" placeholder="Tag">
                                <select wire:model="{{ $a['wire_gen'] }}" class="form-select form-select-sm border-0 bg-light" style="font-size: 0.72rem;">
                                    <option value="">Raza...</option>
                                    @foreach($genetics as $g) <option value="{{ $g->id }}">{{ $g->name }}</option> @endforeach
                                </select>
                            </div>
                        </div>
                        @endforeach
                    </div>

                    {{-- NIVEL 3: BISABUELOS (Fila de 8) --}}
                    <div class="row g-2">
                        @php
                            $bisabuelos = [
                                ['key' => 'fff', 'label' => 'BIS. PP ♂', 'color' => 'info'],
                                ['key' => 'ffm', 'label' => 'BIS. PP ♀', 'color' => 'danger'],
                                ['key' => 'fmf', 'label' => 'BIS. PM ♂', 'color' => 'info'],
                                ['key' => 'fmm', 'label' => 'BIS. PM ♀', 'color' => 'danger'],
                                ['key' => 'mff', 'label' => 'BIS. MP ♂', 'color' => 'info'],
                                ['key' => 'mfm', 'label' => 'BIS. MP ♀', 'color' => 'danger'],
                                ['key' => 'mmf', 'label' => 'BIS. MM ♂', 'color' => 'info'],
                                ['key' => 'mmm', 'label' => 'BIS. MM ♀', 'color' => 'danger'],
                            ];
                        @endphp
                        @foreach($bisabuelos as $b)
                            <div class="col">
                                <div class="bg-white p-2 rounded shadow-sm hover-grow border-bottom border-3 border-{{ $b['color'] }}">
                                    <label class="d-block x-small text-muted fw-bold mb-1">{{ $b['label'] }}</label>
                                    <input wire:model="{{ $b['key'] }}_tag" type="text"
                                           class="form-control form-control-sm border-0 bg-light px-1 mb-1"
                                           placeholder="Tag" style="font-size: 0.65rem;">
                                    <select wire:model="{{ $b['key'] }}_genetic"
                                            class="form-select form-select-sm border-0 bg-light p-0 ps-1"
                                            style="font-size: 0.6rem; height: 20px;">
                                        <option value="">Raza...</option>
                                        @foreach($genetics as $g) <option value="{{ $g->id }}">{{ $g->name }}</option> @endforeach
                                    </select>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </form>

    {{-- ═══════════════════════════════════════════════════════════
         TABLA DE ÚLTIMOS REGISTRADOS
    ═══════════════════════════════════════════════════════════ --}}
    <div class="card shadow-sm border-0 mb-4" style="border-radius: 12px;">
        <div class="card-header bg-white py-3 border-0">
             <h5 class="mb-0 fw-bold"><i class="ph ph-list-bullets me-2 text-primary"></i> ÚLTIMOS ANIMALES REGISTRADOS</h5>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light small text-muted text-uppercase fw-bold">
                    <tr>
                        <th class="ps-4">ID Interno</th>
                        <th>ID Oficial</th>
                        <th>Lote (PIC)</th>
                        <th>Raza / Genética</th>
                        <th>Sexo</th>
                        <th>Fecha Nac.</th>
                        <th class="text-end pe-4">Acciones</th>
                    </tr>
                </thead>
                <tbody class="border-top-0">
                    @forelse($segregatedAnimals as $sa)
                    <tr class="border-bottom">
                        <td class="ps-4 fw-bold text-dark">{{ $sa->internal_id }}</td>
                        <td class="text-muted small fw-semibold">{{ $sa->official_id ?? '-' }}</td>
                        <td><span class="badge bg-secondary-subtle text-secondary fw-bold px-2 py-1">{{ $sa->lote ?? '-' }}</span></td>
                        <td>{{ $sa->genetic?->name ?? 'N/A' }}</td>
                        <td>
                             @if($sa->sex == 'MACHO')
                                <span class="badge bg-info-subtle text-info px-2 py-1"><i class="ph ph-gender-male me-1"></i>MACHO</span>
                             @else
                                <span class="badge bg-danger-subtle text-danger px-2 py-1"><i class="ph ph-gender-female me-1"></i>HEMBRA</span>
                             @endif
                        </td>
                        <td class="small">{{ $sa->birth_date?->format('d/m/Y') ?? '-' }}</td>
                        <td class="text-end pe-4">
                            <button onclick="confirmRecrotalar({{ $sa->id }}, '{{ $sa->internal_id }}')" 
                                    class="btn btn-sm btn-outline-primary fw-bold"
                                    title="Cambiar ID">
                                <i class="ph ph-pencil me-1"></i> EDITAR ID
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-5 text-muted">Aún no se han registrado animales en este lote.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <script>
        function confirmRecrotalar(animalId, currentId) {
            Swal.fire({
                title: 'Recrotalar Animal',
                text: `Ingrese el nuevo ID Interno para el animal ${currentId}:`,
                input: 'text',
                inputPlaceholder: 'Nuevo ID Interno',
                inputValue: currentId,
                showCancelButton: true,
                confirmButtonText: 'Actualizar ID',
                cancelButtonText: 'Cancelar',
                inputValidator: (value) => {
                    if (!value) {
                        return '¡Debes ingresar un nuevo ID!'
                    }
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    @this.recrotalar(animalId, result.value);
                }
            });
        }
    </script>

    <style>
        .x-small { font-size: 0.7rem; }
        .hover-grow { transition: transform 0.15s; }
        .hover-grow:hover { transform: scale(1.03); z-index: 10; }
        .pedigree-card { border-radius: 10px; }
    </style>
</div>
