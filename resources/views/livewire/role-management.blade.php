<div>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-0">Gestión de Roles</h2>
            <div class="text-muted small">Configuración de perfiles de acceso y permisos asociados</div>
        </div>
        <button wire:click="create" class="btn btn-primary px-4 shadow-sm">
            <i class="ph ph-plus-circle me-1"></i> Nuevo Rol
        </button>
    </div>

    <!-- Filtros y Búsqueda -->
    <div class="card shadow-sm border-0 mb-4 rounded-4 overflow-hidden">
        <div class="card-body p-3">
            <div class="row g-3">
                <div class="col-md-8">
                    <div class="input-group">
                        <span class="input-group-text bg-light border-0"><i class="ph ph-magnifying-glass"></i></span>
                        <input wire:model.live.debounce.300ms="search" type="text" class="form-control bg-light border-0" placeholder="Buscar rol por nombre...">
                    </div>
                </div>
                <div class="col-md-4 text-end">
                    <select wire:model.live="perPage" class="form-select bg-light border-0">
                        <option value="10">10 por página</option>
                        <option value="25">25 por página</option>
                        <option value="50">50 por página</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla de Roles -->
    <div class="card shadow-sm border-0 rounded-4 overflow-hidden py-0">
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
                        <th class="py-3">PERMISOS ASOCIADOS</th>
                        <th class="text-end py-3 px-4">ACCIONES</th>
                    </tr>
                </thead>
                <tbody class="border-0">
                    @forelse ($roles as $role)
                        <tr wire:key="role-{{ $role->id }}" class="border-bottom border-light">
                            <td class="text-muted small fs-6">#{{ $role->id }}</td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="icon-shape bg-primary-subtle text-primary rounded-circle me-3 d-flex align-items-center justify-content-center" style="width: 38px; height: 38px;">
                                        <i class="ph ph-shield-check fs-5"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-0 fw-bold">{{ $role->name }}</h6>
                                        <span class="text-muted small" style="font-size: 0.75rem;">Protegiendo plataforma</span>
                                    </div>
                                </div>
                            </td>
                            <td>
                                @php $permissions = $role->permissions; @endphp
                                @if($permissions->count() > 0)
                                    <div class="d-flex flex-wrap gap-1">
                                        @foreach ($permissions->take(3) as $permission)
                                            <span class="badge bg-light text-dark border p-1 px-2" style="font-size: 0.7rem;">{{ $permission->name }}</span>
                                        @endforeach
                                        @if($permissions->count() > 3)
                                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle p-1 px-2" style="font-size: 0.7rem;">+{{ $permissions->count() - 3 }} más</span>
                                        @endif
                                    </div>
                                @else
                                    <span class="text-muted small italic">Sin permisos</span>
                                @endif
                            </td>
                            <td class="text-end px-4">
                                <div class="btn-group shadow-sm rounded-pill p-1 bg-white border">
                                    <button wire:click="edit({{ $role->id }})" class="btn btn-sm btn-link text-primary border-0 rounded-pill px-3" title="Editar">
                                        <i class="ph ph-pencil-simple-line fs-5"></i>
                                    </button>
                                    @if($role->name !== 'Super Admin')
                                    <button wire:click="confirmDelete({{ $role->id }})" class="btn btn-sm btn-link text-danger border-0 rounded-pill px-3" title="Eliminar">
                                        <i class="ph ph-trash fs-5"></i>
                                    </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center py-5">
                                <div class="opacity-50 mb-3"><i class="ph ph-warning-circle fs-1"></i></div>
                                <h5 class="text-muted">No se encontraron roles.</h5>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($roles->hasPages())
            <div class="card-footer bg-white border-top border-light p-3">
                {{ $roles->links() }}
            </div>
        @endif
    </div>

    <!-- Modal Formulario -->
    <div wire:ignore.self class="modal fade" id="roleFormModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 shadow-lg rounded-4">
                <div class="modal-header border-0 pt-4 px-4">
                    <h5 class="modal-title fw-bold">
                        {{ $roleId ? 'Editar Rol Administrativo' : 'Crear Nuevo Perfil' }}
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form wire:submit.prevent="save">
                    <div class="modal-body p-4 pt-0">
                        <div class="row g-4">
                            <!-- Datos del Rol -->
                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-uppercase mb-2 mt-3">Nombre del Rol</label>
                                <div class="input-group bg-light rounded-3 overflow-hidden border">
                                    <span class="input-group-text border-0 bg-transparent text-muted"><i class="ph ph-tag-chevron"></i></span>
                                    <input wire:model="name" type="text" class="form-control border-0 bg-transparent py-2 fs-6" placeholder="ej: Supervisor" {{ $name === 'Super Admin' ? 'readonly' : '' }}>
                                </div>
                                @error('name') <span class="text-danger small mt-1 d-block">{{ $message }}</span> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-uppercase mb-2 mt-3 text-muted">Protección (Guard)</label>
                                <div class="input-group bg-light rounded-3 overflow-hidden border">
                                    <span class="input-group-text border-0 bg-transparent text-muted"><i class="ph ph-shield-check"></i></span>
                                    <input wire:model="guard_name" type="text" class="form-control border-0 bg-transparent py-2 text-muted fs-6" readonly>
                                </div>
                                @error('guard_name') <span class="text-danger small mt-1 d-block">{{ $message }}</span> @enderror
                            </div>

                            <!-- Selector de Permisos -->
                            <div class="col-12">
                                <label class="form-label fw-bold small text-uppercase mb-3 mt-2 d-block border-bottom pb-2">Asignar Permisos Atomicós</label>
                                <div class="alert alert-info border-0 rounded-3 small py-2 d-flex align-items-center gap-2 mb-3">
                                    <i class="ph ph-info fs-5"></i> Los permisos seleccionados definirán las acciones permitidas para este rol.
                                </div>
                                <div class="row g-2 overflow-auto" style="max-height: 250px;">
                                    @foreach ($this->allPermissions as $perm)
                                        <div class="col-md-6 col-lg-4">
                                            <div class="form-check p-2 rounded-3 border bg-light h-100 d-flex align-items-center transition-all cursor-pointer permission-check-hover">
                                                <input wire:model="selectedPermissions" class="form-check-input ms-0 me-3 mt-0 flex-shrink-0" type="checkbox" value="{{ $perm->name }}" id="perm-{{ $perm->id }}">
                                                <label class="form-check-label small fw-semibold text-dark mb-0 flex-grow-1 cursor-pointer" for="perm-{{ $perm->id }}">
                                                    {{ $perm->name }}
                                                </label>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                                @error('selectedPermissions') <span class="text-danger small mt-2 d-block">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-0 pb-4 px-4 pt-3">
                        <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary px-4 shadow-sm">
                            <i class="ph ph-floppy-disk me-1"></i> {{ $roleId ? 'Actualizar Perfil' : 'Guardar y Activar' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <style>
        .permission-check-hover:hover {
            border-color: #f8b4b4 !important;
            background-color: #fff !important;
        }
        .icon-shape {
            transition: transform 0.2s;
        }
        tr:hover .icon-shape {
            transform: scale(1.1);
        }
        .transition-all {
            transition: all 0.2s ease;
        }
    </style>
</div>
