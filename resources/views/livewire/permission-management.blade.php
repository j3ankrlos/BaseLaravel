<div>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-0">Gestión de Permisos</h2>
            <div class="text-muted small">Control granular de acciones técnicas en el sistema</div>
        </div>
        <button wire:click="create" class="btn btn-primary px-4 shadow-sm">
            <i class="ph ph-plus-circle me-1"></i> Nuevo Permiso
        </button>
    </div>

    <!-- Filtros y Búsqueda -->
    <div class="card shadow-sm border-0 mb-4 rounded-4 overflow-hidden">
        <div class="card-body p-3">
            <div class="row g-3">
                <div class="col-md-8">
                    <div class="input-group">
                        <span class="input-group-text bg-light border-0"><i class="ph ph-magnifying-glass"></i></span>
                        <input wire:model.live.debounce.300ms="search" type="text" class="form-control bg-light border-0" placeholder="Buscar permiso por nombre...">
                    </div>
                </div>
                <div class="col-md-4">
                    <select wire:model.live="perPage" class="form-select bg-light border-0">
                        <option value="10">10 por página</option>
                        <option value="25">25 por página</option>
                        <option value="50">50 por página</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla de Permisos -->
    <div class="card shadow-sm border-0 rounded-4 overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light border-bottom border-light">
                    <tr>
                        <th class="cursor-pointer py-3" wire:click="sortBy('id')">
                            ID <i class="ph ph-caret-{{ $orderBy === 'id' ? ($isAsc ? 'up' : 'down') : 'up-down' }} small"></i>
                        </th>
                        <th class="cursor-pointer py-3" wire:click="sortBy('name')">
                            NOMBRE <i class="ph ph-caret-{{ $orderBy === 'name' ? ($isAsc ? 'up' : 'down') : 'up-down' }} small"></i>
                        </th>
                        <th class="py-3">GUARD</th>
                        <th class="text-end py-3 px-4">ACCIONES</th>
                    </tr>
                </thead>
                <tbody class="border-0">
                    @forelse($permissions as $permission)
                        <tr wire:key="perm-{{ $permission->id }}" class="border-bottom border-light">
                            <td class="text-muted small fs-6">#{{ $permission->id }}</td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="icon-shape bg-info-subtle text-info rounded-circle me-3 d-flex align-items-center justify-content-center" style="width: 38px; height: 38px;">
                                        <i class="ph ph-key fs-5"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-0 fw-bold">{{ $permission->name }}</h6>
                                        <span class="text-muted small" style="font-size: 0.75rem;">Protegiendo recursos</span>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border p-2">{{ $permission->guard_name }}</span>
                            </td>
                            <td class="text-end px-4">
                                <div class="btn-group shadow-sm rounded-pill p-1 bg-white border">
                                    <button wire:click="edit({{ $permission->id }})" class="btn btn-sm btn-link text-primary border-0 rounded-pill px-3" title="Editar">
                                        <i class="ph ph-pencil-simple-line fs-5"></i>
                                    </button>
                                    <button wire:click="confirmDelete({{ $permission->id }})" class="btn btn-sm btn-link text-danger border-0 rounded-pill px-3" title="Eliminar">
                                        <i class="ph ph-trash fs-5"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center py-5">
                                <div class="opacity-50 mb-3"><i class="ph ph-empty fs-1"></i></div>
                                <h5 class="text-muted">No se encontraron permisos.</h5>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($permissions->hasPages())
            <div class="card-footer bg-white border-top border-light p-3">
                {{ $permissions->links() }}
            </div>
        @endif
    </div>

    <!-- Modal Formulario -->
    <div wire:ignore.self class="modal fade" id="permissionFormModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4">
                <div class="modal-header border-0 pt-4 px-4">
                    <h5 class="modal-title fw-bold">
                        {{ $permissionId ? 'Editar Permiso' : 'Registrar Nuevo Permiso' }}
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form wire:submit.prevent="save">
                    <div class="modal-body p-4">
                        <div class="mb-3">
                            <label class="form-label fw-bold small text-uppercase">Nombre del Permiso</label>
                            <div class="input-group bg-light rounded-3 overflow-hidden border">
                                <span class="input-group-text border-0 bg-transparent text-muted"><i class="ph ph-tag"></i></span>
                                <input wire:model="name" type="text" class="form-control border-0 bg-transparent py-2" placeholder="ej: edit users">
                            </div>
                            @error('name') <span class="text-danger small mt-1 d-block">{{ $message }}</span> @enderror
                        </div>
                        <div class="mb-0">
                            <label class="form-label fw-bold small text-uppercase">Guard Name</label>
                            <div class="input-group bg-light rounded-3 overflow-hidden border">
                                <span class="input-group-text border-0 bg-transparent text-muted"><i class="ph ph-shield-check"></i></span>
                                <input wire:model="guard_name" type="text" class="form-control border-0 bg-transparent py-2 text-muted" readonly>
                            </div>
                            @error('guard_name') <span class="text-danger small mt-1 d-block">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="modal-footer border-0 pb-4 px-4 pt-0">
                        <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary px-4 shadow-sm">
                            <i class="ph ph-floppy-disk me-1"></i> {{ $permissionId ? 'Actualizar Permiso' : 'Guardar Permiso' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
