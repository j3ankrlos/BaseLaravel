<div>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-0">Causas de Muerte</h2>
            <div class="text-muted small">Configuración de diagnósticos y sistemas biológicos involucrados</div>
        </div>
        <button wire:click="create" class="btn btn-primary px-4 shadow-sm">
            <i class="ph ph-plus-circle me-1"></i> Nueva Causa
        </button>
    </div>

    <!-- Filtros -->
    <div class="card shadow-sm border-0 mb-4 rounded-4 overflow-hidden">
        <div class="card-body p-3">
            <div class="row g-3">
                <div class="col-md-9">
                    <div class="input-group">
                        <span class="input-group-text bg-light border-0"><i class="ph ph-magnifying-glass"></i></span>
                        <input wire:model.live.debounce.300ms="search" type="text" class="form-control bg-light border-0" placeholder="Buscar causa por nombre...">
                    </div>
                </div>
                <div class="col-md-3">
                    <select wire:model.live="perPage" class="form-select bg-light border-0">
                        <option value="10">10 por página</option>
                        <option value="25">25 por página</option>
                        <option value="50">50 por página</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla -->
    <div class="card shadow-sm border-0 rounded-4 overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light border-bottom border-light">
                    <tr>
                        <th class="cursor-pointer py-3 ps-4" wire:click="sortBy('id')">
                            ID <i class="ph ph-caret-{{ $orderBy === 'id' ? ($isAsc ? 'up' : 'down') : 'up-down' }} small"></i>
                        </th>
                        <th class="cursor-pointer py-3" wire:click="sortBy('name')">
                            CAUSA <i class="ph ph-caret-{{ $orderBy === 'name' ? ($isAsc ? 'up' : 'down') : 'up-down' }} small"></i>
                        </th>
                        <th class="py-3">SISTEMA INVOLUCRADO</th>
                        <th class="text-end py-3 px-4">ACCIONES</th>
                    </tr>
                </thead>
                <tbody class="border-0">
                    @forelse($causes as $cause)
                        <tr wire:key="cause-{{ $cause->id }}" class="border-bottom border-light">
                            <td class="ps-4 text-muted small">#{{ $cause->id }}</td>
                            <td>
                                <div class="fw-bold text-dark">{{ $cause->name }}</div>
                            </td>
                            <td>
                                <span class="badge bg-info-subtle text-info p-2 border border-info-subtle">
                                    <i class="ph ph-activity me-1"></i> {{ $cause->system->name }}
                                </span>
                            </td>
                            <td class="text-end px-4">
                                <div class="btn-group shadow-sm rounded-pill p-1 bg-white border">
                                    <button wire:click="edit({{ $cause->id }})" class="btn btn-sm btn-link text-primary border-0 rounded-pill px-3">
                                        <i class="ph ph-pencil-simple-line fs-5"></i>
                                    </button>
                                    <button wire:click="confirmDelete({{ $cause->id }})" class="btn btn-sm btn-link text-danger border-0 rounded-pill px-3">
                                        <i class="ph ph-trash fs-5"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center py-5">
                                <i class="ph ph-warning-circle fs-1 text-muted opacity-50 mb-3"></i>
                                <h5 class="text-muted">No se encontraron causas registradas.</h5>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($causes->hasPages())
            <div class="card-footer bg-white border-top border-light p-3">
                {{ $causes->links() }}
            </div>
        @endif
    </div>

    <!-- Modal -->
    <div wire:ignore.self class="modal fade" id="causeFormModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4">
                <div class="modal-header border-0 pt-4 px-4 pb-0">
                    <h5 class="modal-title fw-bold">
                        {{ $causeId ? 'Editar Causa de Muerte' : 'Nueva Causa de Muerte' }}
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form wire:submit.prevent="save">
                    <div class="modal-body p-4">
                        <div class="mb-3">
                            <label class="form-label fw-bold small text-uppercase">Nombre de la Causa</label>
                            <input wire:model="name" type="text" class="form-control bg-light border-0 py-2" placeholder="ej: INFARTO AGUDO">
                            @error('name') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                        <div class="mb-0">
                            <label class="form-label fw-bold small text-uppercase">Sistema Involucrado</label>
                            <select wire:model="death_system_id" class="form-select bg-light border-0 py-2">
                                <option value="">Seleccione sistema...</option>
                                @foreach($this->deathSystems as $system)
                                    <option value="{{ $system->id }}">{{ $system->name }}</option>
                                @endforeach
                            </select>
                            @error('death_system_id') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="modal-footer border-0 pb-4 px-4 pt-0">
                        <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary px-4 shadow-sm">
                            <i class="ph ph-floppy-disk me-1"></i> Guardar Cambios
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
