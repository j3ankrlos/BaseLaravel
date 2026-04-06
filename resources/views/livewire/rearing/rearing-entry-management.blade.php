<div class="container-fluid py-4">
    <div class="row mb-4 align-items-center">
        <div class="col-md-8">
            <h4 class="mb-0 fw-bold text-dark">
                <i class="ph ph-house-line me-2 text-primary"></i> Ingresos a Recría
            </h4>
            <p class="text-muted small mb-0">Gestión de inventario y movimientos desde genética hacia recría.</p>
        </div>
        <div class="col-md-4 text-end">
            <button wire:click="$set('isModalOpen', true)" class="btn btn-primary shadow-sm fw-bold">
                <i class="ph ph-plus-circle me-1"></i> Asignar a Recría
            </button>
        </div>
    </div>

    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-white py-3">
            <div class="row align-items-center">
                <div class="col-md-4">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-light border-end-0"><i class="ph ph-magnifying-glass"></i></span>
                        <input type="text" wire:model.live="search" class="form-control bg-light border-start-0" placeholder="Buscar Identifier o Lote...">
                    </div>
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light text-muted small text-uppercase">
                        <tr>
                            <th class="ps-4">Tipo</th>
                            <th>Identificador</th>
                            <th>Lote Asignado</th>
                            <th>Genética</th>
                            <th>Sexo</th>
                            <th class="text-center">Cant. Activa</th>
                            <th>Peso (kg)</th>
                            <th>Ubicación</th>
                            <th>Fecha de Ingreso</th>
                            <th>Día PIC</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($entries as $entry)
                            <tr>
                                <td class="ps-4">
                                    @if($entry->type == 'INDIVIDUO')
                                        <span class="badge bg-soft-info text-info"><i class="ph ph-user me-1"></i> INDIVIDUO</span>
                                    @else
                                        <span class="badge bg-soft-primary text-primary"><i class="ph ph-users-three me-1"></i> LOTE</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="fw-bold">{{ $entry->identifier ?? 'N/A' }}</div>
                                </td>
                                <td>
                                    @if($entry->management_lot)
                                        <span class="badge bg-soft-secondary text-secondary">{{ $entry->management_lot }}</span>
                                    @else
                                        <span class="text-muted small fst-italic">Pendiente</span>
                                    @endif
                                </td>
                                <td>{{ $entry->genetic->name ?? 'N/A' }}</td>
                                <td>{{ $entry->sex }}</td>
                                <td class="text-center fw-bold">{{ $entry->quantity }}</td>
                                <td class="fw-bold">{{ number_format($entry->current_weight, 2, ',', '.') }}</td>
                                <td>
                                    <span class="badge bg-soft-success text-success px-2 py-1">
                                        {{ $entry->barnSection->name ?? 'N/A' }}
                                    </span>
                                </td>
                                <td>{{ $entry->entry_date ? $entry->entry_date->format('d/m/Y') : 'N/A' }}</td>
                                <td>
                                    <span class="badge bg-light text-dark border">
                                        {{ $entry->entry_pic_cycle }}-{{ str_pad($entry->entry_pic_day, 3, '0', STR_PAD_LEFT) }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center py-5 text-muted">
                                    <i class="ph ph-magnifying-glass display-4 d-block mb-3 opacity-25"></i>
                                    No hay registros de inventario en Recría.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($entries->hasPages())
            <div class="card-footer bg-white py-3">
                {{ $entries->links() }}
            </div>
        @endif
    </div>

    <!-- Modal Form -->
    <div class="modal fade @if($isModalOpen) show d-block @endif" tabindex="-1" style="background: rgba(0,0,0,0.5)">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-dark text-white py-3 border-0">
                    <h5 class="modal-title fw-bold">
                        <i class="ph ph-file-plus me-2"></i> Nuevo Ingreso a Recría
                    </h5>
                    <button type="button" wire:click="$set('isModalOpen', false)" class="btn-close btn-close-white shadow-none"></button>
                </div>
                <form wire:submit="save">
                    <div class="modal-body p-4">
                        <div class="row g-3">
                            <!-- Header / Fecha -->
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Fecha de Ingreso</label>
                                <input type="date" wire:model.live="entry_date" class="form-control">
                                @error('entry_date') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Vuelta - PIC</label>
                                <div class="input-group">
                                    <input type="number" wire:model="pic_cycle" class="form-control text-center bg-light" readonly>
                                    <span class="input-group-text">-</span>
                                    <input type="number" wire:model="pic_day" class="form-control text-center bg-light" readonly>
                                </div>
                            </div>
                            <!-- Tipo de Ingreso Toggle -->
                            <div class="col-md-4 d-flex align-items-end">
                                <div class="form-check form-switch fs-5 mb-1">
                                    <input class="form-check-input" type="checkbox" role="switch" id="isIndividualToggle" wire:model.live="is_individual">
                                    <label class="form-check-label ms-2 fs-6 mt-1" for="isIndividualToggle">Es ingreso Individual (Con ID)</label>
                                </div>
                            </div>

                            <hr class="my-3 opacity-25">

                            @if($is_individual)
                                <!-- Campos para INDIVIDUO -->
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Identificación (ID / Arete)</label>
                                    <input type="text" wire:model="identifier" class="form-control" placeholder="Ej. YORK-902">
                                    @error('identifier') <span class="text-danger small">{{ $message }}</span> @enderror
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label fw-bold">Sexo</label>
                                    <select wire:model="sex" class="form-select">
                                        <option value="">...</option>
                                        <option value="Macho">Macho</option>
                                        <option value="Hembra">Hembra</option>
                                    </select>
                                    @error('sex') <span class="text-danger small">{{ $message }}</span> @enderror
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label fw-bold">Cantidad</label>
                                    <input type="number" wire:model="quantity" class="form-control text-dark text-center" value="1" disabled>
                                </div>
                            @else
                                <!-- Campos para LOTE -->
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Lote Semanal / Manejo</label>
                                    <input type="text" wire:model="management_lot" class="form-control" placeholder="Ej. Lote 800 (Puede quedar vacío hasta el viernes)">
                                    @error('management_lot') <span class="text-danger small">{{ $message }}</span> @enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-bold">Sexo del Grupo</label>
                                    <select wire:model="sex" class="form-select">
                                        <option value="">Seleccione...</option>
                                        <option value="Macho">Machos</option>
                                        <option value="Hembra">Hembras</option>
                                    </select>
                                    @error('sex') <span class="text-danger small">{{ $message }}</span> @enderror
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label fw-bold">Cantidad Vivos</label>
                                    <input type="number" wire:model="quantity" class="form-control text-center font-weight-bold" placeholder="0">
                                    @error('quantity') <span class="text-danger small">{{ $message }}</span> @enderror
                                </div>
                            @endif

                            <div class="col-md-4">
                                <label class="form-label fw-bold">Genética / Raza</label>
                                <select wire:model="genetic_id" class="form-select">
                                    <option value="">Seleccione...</option>
                                    @foreach($genetics as $g)
                                        <option value="{{ $g->id }}">{{ $g->name }}</option>
                                    @endforeach
                                </select>
                                @error('genetic_id') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-bold">Sala Origen (Maternidad)</label>
                                <input type="text" wire:model="source_room" class="form-control" placeholder="Ej. Sala 41">
                                <!-- Podría ser reemplazado después si registramos las salas de maternidad como locations -->
                                @error('source_room') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                            
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Peso Promedio/Total (kg)</label>
                                <input type="text" wire:model="weight" class="form-control decimal-mask" placeholder="0,00">
                            </div>

                            <!-- Destino Final (Location Catalogada) -->
                            <div class="col-md-12 mt-4">
                                <div class="p-3 bg-soft-primary rounded-3 border border-primary border-opacity-10">
                                    <label class="form-label fw-bold text-primary mb-2">
                                        <i class="ph ph-map-pin me-1"></i> Ubicación Destino (Recría)
                                    </label>
                                    <select wire:model="to_barn_section_id" class="form-select form-select-lg border-primary border-opacity-25">
                                        <option value="">Seleccione a dónde ingresan...</option>
                                        @foreach($barn_sections as $bs)
                                            <!-- En un futuro podríamos filtrar solo las que pertenecen al barn tipo "RECRIA" -->
                                            <option value="{{ $bs->id }}">{{ $bs->name }} ({{ $bs->barn->name ?? 'N/A' }})</option>
                                        @endforeach
                                    </select>
                                    @error('to_barn_section_id') <span class="text-danger small">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-light p-3 border-0 rounded-bottom-4">
                        <button type="button" wire:click="$set('isModalOpen', false)" class="btn btn-light px-4 fw-bold border">Cancelar</button>
                        <button type="submit" class="btn btn-primary px-5 fw-bold shadow-sm">
                            <i class="ph ph-floppy-disk me-1"></i> Confirmar Ingreso Oficial
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Styles for soft colors -->
    <style>
        .bg-soft-primary { background-color: rgba(13, 110, 253, 0.1); }
        .bg-soft-info { background-color: rgba(13, 202, 240, 0.1); }
        .bg-soft-success { background-color: rgba(25, 135, 84, 0.1); }
        .bg-soft-secondary { background-color: rgba(108, 117, 125, 0.1); }
        .avatar-xs { width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; }
        .btn-white { background: white; border: 1px solid #dee2e6; }
        .btn-white:hover { background: #f8f9fa; }
    </style>
</div>
