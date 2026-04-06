<div>
    {{-- Encabezado de Sección --}}
    <div class="card card-body mb-4 shadow-sm border-0 bg-primary text-white" style="background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%);">
        <div class="d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center">
                <div class="bg-white bg-opacity-25 rounded-circle p-3 me-3">
                    <i class="ph ph-shield-check fs-2 text-white"></i>
                </div>
                <div>
                    <h4 class="mb-0 fw-bold">Módulo de Cuarentena</h4>
                    <p class="mb-0 opacity-75 small">Gestión de lotes de importación y segregación individual.</p>
                </div>
            </div>
            <div>
                <button wire:click="create" class="btn btn-light shadow-sm px-4 fw-bold text-primary" data-bs-toggle="modal" data-bs-target="#batchFormModal">
                    <i class="ph ph-plus-circle me-1"></i> Nuevo Ingreso
                </button>
            </div>
        </div>
    </div>

    {{-- Filtros y Búsqueda --}}
    <div class="card card-body mb-4 shadow-sm border-0">
        <div class="row g-3 align-items-center">
            <div class="col-12 col-md-8">
                <div class="input-group drop-shadow">
                    <span class="input-group-text bg-white border-end-0"><i class="ph ph-magnifying-glass text-muted"></i></span>
                    <input wire:model.live.debounce.300ms="search" type="search" class="form-control border-start-0 ps-0" placeholder="Buscar por origen, documento o proveedor...">
                </div>
            </div>
            <div class="col-12 col-md-4 text-end">
                <span class="text-muted small">Mostrando {{ $batches->count() }} resultados</span>
            </div>
        </div>
    </div>

    {{-- Listado de Lotes --}}
    <div class="card shadow-sm border-0 overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light text-muted small text-uppercase fw-bold">
                    <tr>
                        <th style="width: 40px;"></th>
                        <th scope="col" wire:click="sortBy('entry_date')" class="cursor-pointer">
                            Fecha Ingreso <i class="ph {{ $orderBy == 'entry_date' ? ($isAsc ? 'ph-caret-up' : 'ph-caret-down') : 'ph-caret-double-up-down opacity-50' }}"></i>
                        </th>
                        <th scope="col">Documento</th>
                        <th scope="col" wire:click="sortBy('origin')" class="cursor-pointer">
                            Origen <i class="ph {{ $orderBy == 'origin' ? ($isAsc ? 'ph-caret-up' : 'ph-caret-down') : 'ph-caret-double-up-down opacity-50' }}"></i>
                        </th>
                        <th scope="col" class="text-center">Segregados</th>
                        <th scope="col" class="text-center">Incorporados</th>
                        <th scope="col" class="text-end pe-4">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($batches as $batch)
                        <tr>
                            <td class="text-center text-muted">
                                <button wire:click="toggleBatch({{ $batch->id }})" class="btn btn-link p-0 text-decoration-none transition-transform {{ in_array($batch->id, $expandedBatches) ? 'rotate-180' : '' }}">
                                    <i class="ph-bold ph-caret-down fs-5 text-muted"></i>
                                </button>
                            </td>
                            <td>
                                <div class="fw-bold">{{ $batch->entry_date->format('d/m/Y') }}</div>
                                <div class="text-muted small">{{ $batch->entry_date->diffForHumans() }}</div>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border fw-bold px-2 py-1">{{ $batch->document_number }}</span>
                                <div class="text-muted x-small mt-1">{{ $batch->provider }}</div>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <i class="ph ph-airplane-landing me-2 text-primary opacity-50"></i>
                                    <span class="fw-semibold text-dark">{{ $batch->origin }}</span>
                                </div>
                                <div class="text-muted small mt-1">
                                    <i class="ph ph-list-numbers me-1"></i> {{ $batch->total_quantity }} ítems totales
                                </div>
                            </td>
                            <td class="text-center" style="min-width: 180px;">
                                <div class="px-3">
                                    <div class="d-flex justify-content-between mb-1 small">
                                        <span class="fw-bold text-primary">{{ $batch->items_count }} / {{ $batch->total_quantity }}</span>
                                        <span class="text-muted">{{ round(($batch->items_count / $batch->total_quantity) * 100) }}%</span>
                                    </div>
                                    <div class="progress" style="height: 6px;">
                                        <div class="progress-bar bg-primary shadow-sm" role="progressbar" 
                                             style="width: {{ ($batch->items_count / $batch->total_quantity) * 100 }}%"></div>
                                    </div>
                                </div>
                            </td>
                            <td class="text-center" style="min-width: 180px;">
                                <div class="px-3">
                                    <div class="d-flex justify-content-between mb-1 small">
                                        <span class="fw-bold text-success">{{ $batch->incorporated_count }} / {{ $batch->items_count ?: 1 }}</span>
                                        <span class="text-muted">{{ $batch->items_count > 0 ? round(($batch->incorporated_count / $batch->items_count) * 100) : 0 }}%</span>
                                    </div>
                                    <div class="progress" style="height: 6px;">
                                        <div class="progress-bar bg-success shadow-sm" role="progressbar" 
                                             style="width: {{ $batch->items_count > 0 ? ($batch->incorporated_count / $batch->items_count) * 100 : 0 }}%"></div>
                                    </div>
                                </div>
                            </td>
                            <td class="text-end pe-4">
                                <div class="btn-group shadow-sm">
                                    <a href="{{ route('quarantine.segregation', $batch->id) }}" class="btn btn-sm btn-primary px-3" title="Segregar Individuos">
                                        <i class="ph ph-users-three me-1"></i> Segregar
                                    </a>
                                    <button wire:click="edit({{ $batch->id }})" class="btn btn-sm btn-white border border-start-0" title="Editar Lote">
                                        <i class="ph ph-pencil-simple"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>

                        {{-- FILA DE DETALLES EXPANDIBLE --}}
                        @if(in_array($batch->id, $expandedBatches))
                        <tr class="bg-light-subtle shadow-inner">
                            <td colspan="7" class="p-0">
                                <div class="p-4 border-start border-4 border-primary bg-white">
                                    <h6 class="fw-bold text-primary mb-3"><i class="ph ph-list-bullets me-1"></i> Detalle de Animales Importados</h6>
                                    <div class="table-responsive rounded border shadow-sm">
                                        <table class="table table-sm table-hover mb-0">
                                            <thead class="bg-light x-small text-uppercase text-muted">
                                                <tr>
                                                    <th class="ps-3 border-0 py-2">Animal / Detalle</th>
                                                    <th class="border-0 py-2">Estatus / Nacimiento</th>
                                                    <th class="border-0 py-2">Ubicación (Nave/S/C)</th>
                                                    <th class="border-0 py-2">Padres (P/M)</th>
                                                    <th class="border-0 py-2 text-center">Estado</th>
                                                    <th class="border-0 py-2 text-center">Acciones</th>
                                                </tr>
                                            </thead>
                                            <tbody class="small">
                                                @forelse($batch->items as $item)
                                                <tr>
                                                    <td class="ps-3">
                                                        <div class="fw-bold text-dark">{{ $item->official_id }}</div>
                                                        <div class="x-small text-muted">{{ $item->internal_id }}</div>
                                                        <div class="mt-1">
                                                            <span class="badge bg-light text-dark border-0 shadow-sm x-small">
                                                                {{ $item->genetic?->name ?? 'RAZA N/A' }} 
                                                                @if($item->sex == 'MACHO') <i class="ph ph-gender-male text-info ms-1"></i> @else <i class="ph ph-gender-female text-danger ms-1"></i> @endif
                                                            </span>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <div class="small fw-semibold text-uppercase text-primary">{{ $item->extra_status ?: 'ESTATUS N/A' }}</div>
                                                        <div class="x-small text-muted">
                                                            <i class="ph ph-calendar me-1"></i> {{ $item->birth_date?->format('d/m/Y') ?? 'S/F' }}
                                                            <span class="ms-1 fw-bold">Lote: {{ $item->lote }}</span>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <div class="d-flex align-items-center gap-1">
                                                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle x-small">{{ $item->barn?->name ?? 'CUARENTENA' }}</span>
                                                            <span class="text-muted small">/</span>
                                                            <span class="badge bg-light text-muted border x-small">{{ $item->section?->name ?? '-' }}</span>
                                                            <span class="text-muted small">/</span>
                                                            <span class="badge bg-light text-muted border x-small">{{ $item->pen?->name ?? '-' }}</span>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <div class="d-flex flex-column gap-1">
                                                            <div class="x-small d-flex align-items-center" title="Padre: {{ $item->fGenetic?->name }}">
                                                                <i class="ph ph-gender-male text-info me-1"></i>
                                                                <span class="fw-bold">{{ $item->f_tag ?: 'N/A' }}</span>
                                                                <span class="ms-1 opacity-50">({{ $item->fGenetic?->code ?? 'N/A' }})</span>
                                                            </div>
                                                            <div class="x-small d-flex align-items-center" title="Madre: {{ $item->mGenetic?->name }}">
                                                                <i class="ph ph-gender-female text-danger me-1"></i>
                                                                <span class="fw-bold">{{ $item->m_tag ?: 'N/A' }}</span>
                                                                <span class="ms-1 opacity-50">({{ $item->mGenetic?->code ?? 'N/A' }})</span>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td class="text-center">
                                                        @if($item->status == 'INCORPORADO')
                                                            <span class="badge bg-success text-white px-2">ACTIVO</span>
                                                        @else
                                                            <span class="badge bg-warning-subtle text-warning border border-warning-subtle px-2">PENDIENTE</span>
                                                        @endif
                                                    </td>
                                                    <td class="text-center">
                                                        <button wire:click="editItem({{ $item->id }})" class="btn btn-sm btn-icon btn-light-primary border-0 rounded-circle" title="Ver Detalle / Corregir">
                                                            <i class="ph ph-clipboard-text fs-6"></i>
                                                        </button>
                                                    </td>
                                                </tr>
                                                @empty
                                                <tr>
                                                    <td colspan="6" class="text-center py-3 text-muted">No se han segregado items para este lote.</td>
                                                </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        @endif
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-5">
                                <div class="py-4">
                                    <i class="ph ph-newspaper fs-1 opacity-25 d-block mb-3"></i>
                                    <h5 class="text-muted">No hay lotes de importación registrados</h5>
                                    <p class="text-muted small">Utilice el botón "Nueva Importación" para comenzar.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($batches->hasPages())
        <div class="card-footer bg-white py-3">
            {{ $batches->links() }}
        </div>
        @endif
    </div>

    {{-- MODAL DE FORMULARIO DE LOTE --}}
    <div wire:ignore.self class="modal fade" id="batchFormModal" tabindex="-1" aria-labelledby="batchFormModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg modal-premium">
                <div class="modal-header bg-white border-0 pt-4 px-4 pb-0 d-flex align-items-center gap-2">
                    <div class="bg-primary-subtle p-2 rounded-circle me-2">
                        <i class="ph ph-package fs-4 text-primary"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold mb-0 text-dark" id="batchFormModalLabel">{{ $batchId ? 'EDITAR' : 'NUEVO' }} LOTE DE CUARENTENA</h5>
                        <p class="text-muted small mb-0">Registre el ingreso de animales (Importación o Crecimiento Interno).</p>
                    </div>
                    <button type="button" class="btn-close ms-auto" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                
                <div class="modal-body p-4">
                    <form wire:submit.prevent="save">
                        {{-- Tipo de Ingreso --}}
                        <div class="border rounded-3 p-3 mb-4 d-flex align-items-center justify-content-between" style="background:#f8f9fa;">
                            <span class="fw-bold text-muted small text-uppercase">TIPO DE INGRESO</span>
                            <div class="d-flex gap-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" wire:model.live="batch_type" value="IMPORTACION" id="radioImp">
                                    <label class="form-check-label fw-bold small text-primary" for="radioImp">&#x2708; IMPORTACIÓN</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" wire:model.live="batch_type" value="CRECIMIENTO" id="radioCre">
                                    <label class="form-check-label fw-bold small text-info" for="radioCre">&#x1F33F; CRECIMIENTO (SITIO 3)</label>
                                </div>
                            </div>
                        </div>
                        <div class="row g-4">
                            {{-- Fecha de Ingreso --}}
                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-uppercase">Fecha de Ingreso</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0"><i class="ph ph-calendar"></i></span>
                                    <input wire:model="entry_date" type="date" class="form-control bg-light border-start-0 @error('entry_date') is-invalid @enderror">
                                </div>
                                @error('entry_date') <div class="invalid-feedback small">{{ $message }}</div> @enderror
                            </div>

                            {{-- Documento --}}
                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-uppercase">Nro de Documento / Guía</label>
                                <div class="input-group shadow-input">
                                    <span class="input-group-text bg-light border-end-0"><i class="ph ph-hash"></i></span>
                                    <input wire:model="document_number" type="text" class="form-control bg-light border-start-0 text-uppercase @error('document_number') is-invalid @enderror" placeholder="EJ: GEN-2026-X">
                                </div>
                                @error('document_number') <div class="invalid-feedback small">{{ $message }}</div> @enderror
                            </div>

                            {{-- Origen --}}
                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-uppercase">Origen / Genética</label>
                                <div class="input-group shadow-input">
                                    <span class="input-group-text bg-light border-end-0"><i class="ph ph-planet"></i></span>
                                    <input wire:model="origin" type="text" class="form-control bg-light border-start-0 text-uppercase @error('origin') is-invalid @enderror" placeholder="EJ: CANADA - GENESUS">
                                </div>
                                @error('origin') <div class="invalid-feedback small">{{ $message }}</div> @enderror
                            </div>

                            {{-- Proveedor --}}
                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-uppercase">Proveedor</label>
                                <div class="input-group shadow-input">
                                    <span class="input-group-text bg-light border-end-0"><i class="ph ph-buildings"></i></span>
                                    <input wire:model="provider" type="text" class="form-control bg-light border-start-0 text-uppercase @error('provider') is-invalid @enderror" placeholder="EJ: TOPIGS NORSVIN">
                                </div>
                                @error('provider') <div class="invalid-feedback small">{{ $message }}</div> @enderror
                            </div>

                            {{-- Cantidad --}}
                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-uppercase">Cantidad de Animales</label>
                                <div class="input-group shadow-input">
                                    <span class="input-group-text bg-light border-end-0"><i class="ph ph-number-square-four"></i></span>
                                    <input wire:model="total_quantity" type="number" class="form-control bg-light border-start-0 fw-bold fs-5 @error('total_quantity') is-invalid @enderror" placeholder="0">
                                </div>
                                @error('total_quantity') <div class="invalid-feedback small">{{ $message }}</div> @enderror
                            </div>

                            @if($batch_type == "CRECIMIENTO")
                            {{-- Genética del Lote --}}
                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-uppercase">Raza / Genética del Lote</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0"><i class="ph ph-dna"></i></span>
                                    <select wire:model="genetic_id" class="form-select bg-light border-start-0 @error('genetic_id') is-invalid @enderror">
                                        <option value="">Seleccione...</option>
                                        @foreach($genetics as $g)
                                            <option value="{{ $g->id }}">{{ $g->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                @error('genetic_id') <div class="invalid-feedback small">{{ $message }}</div> @enderror
                            </div>
                            @endif
                        </div>

                        <div class="d-flex justify-content-end align-items-center mt-5 gap-2">
                            <button type="button" class="btn btn-outline-secondary px-4 shadow-sm" data-bs-dismiss="modal">
                                <i class="ph ph-x me-1"></i> Cancelar
                            </button>
                            <button type="submit" class="btn btn-primary px-5 shadow-sm fw-bold py-2" wire:loading.attr="disabled">
                                <i class="ph ph-floppy-disk-back me-1"></i> {{ $batchId ? 'GUARDAR CAMBIOS' : 'REGISTRAR LOTE' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <style>
        .modal-premium { border-radius: 16px; overflow: hidden; }
        .shadow-input { transition: all 0.2s; }
        .shadow-input:focus-within { transform: translateY(-1px); box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
        .x-small { font-size: 0.75rem; }
        .custom-radio .form-check-input:checked + .form-check-label { outline: 2px solid currentColor; outline-offset: 2px; }
        .cursor-pointer { cursor: pointer; }
        .transition-transform { transition: transform 0.3s ease; display: inline-block; }
        .rotate-180 { transform: rotate(180deg); }
    </style>

    <!-- Modal para editar Animal Individual -->
    <div wire:ignore.self class="modal fade" id="itemEditModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-light border-bottom-0 py-3">
                    <h5 class="modal-title fw-bold text-dark">Corregir Datos de Animal</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form wire:submit.prevent="saveItem">
                    <div class="modal-body p-4">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label fw-semibold text-muted small">Raza / Genética</label>
                                <select wire:model.live="itemGeneticId" class="form-select">
                                    <option value="">Seleccione...</option>
                                    @foreach($genetics as $g) <option value="{{ $g->id }}">{{ $g->name }}</option> @endforeach
                                </select>
                                @error('itemGeneticId') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold text-muted small">ID Oficial (Arete)</label>
                                <input type="text" wire:model.live="itemOfficialId" class="form-control" placeholder="">
                                @error('itemOfficialId') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold text-muted small">ID Interno</label>
                                <input type="text" wire:model="itemInternalId" class="form-control" placeholder="">
                                @error('itemInternalId') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-muted small">Sexo</label>
                                <select wire:model="itemSex" class="form-select">
                                    <option value="MACHO">MACHO ♂</option>
                                    <option value="HEMBRA">HEMBRA ♀</option>
                                </select>
                                @error('itemSex') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-muted small">F. Nacimiento</label>
                                <input type="date" wire:model="itemBirthDate" class="form-control">
                                @error('itemBirthDate') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-light border-top-0 py-3">
                        <button type="button" class="btn btn-link text-muted fw-semibold text-decoration-none" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary px-4 fw-bold shadow-sm">Actualizar Animal</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @script
    <script>
        $wire.on('close-modal', (event) => {
            const modalElement = document.getElementById(event.id);
            const modal = bootstrap.Modal.getInstance(modalElement);
            if (modal) {
                modal.hide();
            }
            // Siempre limpiar el backdrop por si Livewire re-renderizó el DOM
            // y Bootstrap perdió la referencia a la instancia del modal
            setTimeout(() => {
                document.querySelectorAll('.modal-backdrop').forEach(el => el.remove());
                document.body.classList.remove('modal-open');
                document.body.style.overflow = '';
                document.body.style.paddingRight = '';
                if (modalElement) {
                    modalElement.classList.remove('show');
                    modalElement.style.display = 'none';
                }
            }, 300);
        });
    </script>
    @endscript
</div>
