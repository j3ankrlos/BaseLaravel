<div>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-0 fw-bold"><span class="text-primary"><i class="ph-fill ph-cylinder"></i></span> Gestión de Alimentos</h2>
            <div class="text-muted small">Administración del catálogo de tipos de alimento, proveedores y códigos</div>
        </div>
        <button wire:click="create" class="btn btn-primary px-4 shadow-sm rounded-3 fw-bold">
            <i class="ph ph-plus-circle me-1"></i> Nuevo Alimento
        </button>
    </div>

    <!-- Filtros -->
    <div class="card shadow-sm border-0 mb-4 rounded-4 overflow-hidden">
        <div class="card-body p-3">
            <div class="row g-3">
                <div class="col-md-9">
                    <div class="input-group">
                        <span class="input-group-text bg-light border-0"><i class="ph ph-magnifying-glass"></i></span>
                        <input wire:model.live.debounce.300ms="search" type="text" class="form-control bg-light border-0 fw-medium" placeholder="Buscar alimento por nombre, código o proveedor...">
                    </div>
                </div>
                <div class="col-md-3">
                    <select wire:model.live="perPage" class="form-select bg-light border-0 fw-medium">
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
                            TIPO DE ALIMENTO <i class="ph ph-caret-{{ $orderBy === 'name' ? ($isAsc ? 'up' : 'down') : 'up-down' }} small"></i>
                        </th>
                        <th class="cursor-pointer py-3" wire:click="sortBy('code')">
                            CÓDIGO <i class="ph ph-caret-{{ $orderBy === 'code' ? ($isAsc ? 'up' : 'down') : 'up-down' }} small"></i>
                        </th>
                        <th class="py-3">PROVEEDOR</th>
                        <th class="py-3">CENTRO COSTO</th>
                        <th class="text-end py-3 px-4">ACCIONES</th>
                    </tr>
                </thead>
                <tbody class="border-0">
                    @forelse ($feeds as $feed)
                        <tr wire:key="feed-{{ $feed->id }}" class="border-bottom border-light">
                            <td class="ps-4 text-muted small">#{{ $feed->id }}</td>
                            <td>
                                <div class="fw-bold text-dark">{{ $feed->name }}</div>
                            </td>
                            <td>
                                <span class="badge bg-secondary-subtle text-secondary px-2 py-1 fw-bold">
                                    {{ $feed->code }}
                                </span>
                            </td>
                            <td>
                                <div class="text-muted small fw-bold text-uppercase">{{ $feed->provider }}</div>
                            </td>
                            <td>
                                @if($feed->cost_center)
                                    <span class="badge bg-info-subtle text-info px-2 py-1">
                                        <i class="ph ph-hash me-1"></i> {{ $feed->cost_center }}
                                    </span>
                                @else
                                    <span class="text-muted smallest">---</span>
                                @endif
                            </td>
                            <td class="text-end px-4">
                                <div class="btn-group shadow-sm rounded-pill p-1 bg-white border">
                                    <button wire:click="edit({{ $feed->id }})" class="btn btn-sm btn-link text-primary border-0 rounded-pill px-3" title="Editar">
                                        <i class="ph-bold ph-pencil-simple fs-5"></i>
                                    </button>
                                    <button wire:click="confirmDelete({{ $feed->id }})" class="btn btn-sm btn-link text-danger border-0 rounded-pill px-3" title="Eliminar">
                                        <i class="ph-bold ph-trash fs-5"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-5">
                                <div class="py-4">
                                    <i class="ph ph-warning-circle fs-1 text-muted opacity-50 mb-3"></i>
                                    <h5 class="text-muted">No se encontraron alimentos registrados.</h5>
                                    <p class="text-muted small">Intenta ajustar tu búsqueda o agregar uno nuevo.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($feeds->hasPages())
            <div class="card-footer bg-white border-top border-light p-3">
                {{ $feeds->links() }}
            </div>
        @endif
    </div>

    <!-- Modal Formulario -->
    <div wire:ignore.self class="modal fade" id="feedFormModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4">
                <div class="modal-header border-0 pt-4 px-4 pb-0">
                    <h5 class="modal-title fw-bold">
                        <i class="ph {{ $feedId ? 'ph-pencil-simple' : 'ph-plus-circle' }} me-2 text-primary"></i>
                        {{ $feedId ? 'Editar Alimento' : 'Nuevo Alimento' }}
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form wire:submit.prevent="save">
                    <div class="modal-body p-4">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label fw-bold small text-muted text-uppercase mb-1">Nombre / Tipo de Alimento</label>
                                <input wire:model="name" type="text" class="form-control bg-light border-0 py-2 fw-bold" placeholder="ej: PRE INICIADOR FASE 0">
                                @error('name') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-muted text-uppercase mb-1">Código de Alimento</label>
                                <input wire:model="code" type="text" class="form-control bg-light border-0 py-2 fw-bold" placeholder="ej: 3206001">
                                @error('code') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-muted text-uppercase mb-1">Proveedor</label>
                                <input wire:model="provider" type="text" class="form-control bg-light border-0 py-2 fw-bold" placeholder="ej: TUNAL">
                                @error('provider') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-bold small text-muted text-uppercase mb-1">Centro de Costo</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-0"><i class="ph ph-hash"></i></span>
                                    <input wire:model="cost_center" type="text" class="form-control bg-light border-0 py-2 fw-bold text-uppercase" placeholder="ej: PRO7002020">
                                </div>
                                @error('cost_center') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-0 pb-4 px-4 pt-0">
                        <button type="button" class="btn btn-light px-4 fw-bold rounded-3" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary px-4 shadow-sm fw-bold rounded-3">
                            <i class="ph ph-floppy-disk me-1"></i> {{ $feedId ? 'Actualizar' : 'Registrar' }} Alimento
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
