<div>
    <div class="card card-body mb-4">
        <div class="row g-3 d-flex align-items-center justify-content-between">
            <div class="col-12 col-md-5">
                <input wire:model.live.debounce.300ms="search" type="search" class="form-control" placeholder="Buscar usuarios...">
            </div>
            <div class="col-12 col-md-auto text-end">
                <button wire:click="create" class="btn btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#userModal">
                    <i class="ph ph-plus-circle me-1"></i> Nuevo Usuario
                </button>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover table-striped mb-0 align-middle">
                <thead class="table-dark">
                    <tr>
                        <th scope="col" wire:click="$set('orderBy', 'name')" class="cursor-pointer">Nombre 
                            <i class="ph {{ $orderBy == 'name' ? ($isAsc ? 'ph-sort-ascending' : 'ph-sort-descending') : 'ph-arrows-down-up text-muted' }}"></i>
                        </th>
                        <th scope="col">Email</th>
                        <th scope="col">Rol</th>
                        <th scope="col" class="text-center">Acción</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <img src="https://ui-avatars.com/api/?name={{ urlencode($user->name) }}&background=EBF4FF&color=7F9CF5" class="rounded-circle me-2" width="32">
                                    <span class="fw-medium">{{ $user->name }}</span>
                                </div>
                            </td>
                            <td>{{ $user->email }}</td>
                            <td>
                                <span class="badge bg-{{ $user->roles->first()?->name == 'Super Admin' ? 'danger' : 'info' }}">
                                    {{ $user->roles->first()?->name ?? 'Sin Rol' }}
                                </span>
                            </td>
                            <td class="text-center">
                                <button wire:click="edit({{ $user->id }})" class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#userModal">
                                    <i class="ph ph-pencil-simple"></i>
                                </button>
                                <button wire:click="$dispatch('confirm-delete', { id: {{ $user->id }}, method: 'delete-user-confirmed' })" class="btn btn-sm btn-outline-danger ms-1">
                                    <i class="ph ph-trash"></i>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center py-4 text-muted">
                                <i class="ph ph-magnifying-glass fs-1 d-block mb-3"></i> No se encontraron usuarios.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer bg-white mt-1 pt-3 pb-0">
            {{ $users->links(data: ['scrollTo' => false]) }}
        </div>
    </div>

    <!-- Modal Form -->
    <div wire:ignore.self class="modal fade" id="userModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">{{ $userId ? 'Editar Usuario' : 'Crear Nuevo Usuario' }}</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" wire:click="$set('showModal', false)"></button>
                </div>
                <div class="modal-body p-4">
                    <form wire:submit.prevent="save">
                        <div class="mb-3">
                            <label class="form-label">Nombre Completo</label>
                            <input wire:model="name" type="text" class="form-control @error('name') is-invalid @enderror">
                            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Correo Electrónico</label>
                            <input wire:model="email" type="email" class="form-control @error('email') is-invalid @enderror">
                            @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Rol de Sistema</label>
                            <select wire:model="role" class="form-select @error('role') is-invalid @enderror">
                                <option value="">Seleccione Rol</option>
                                @foreach($roles as $rol)
                                    <option value="{{ $rol->name }}">{{ $rol->name }}</option>
                                @endforeach
                            </select>
                            @error('role') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-4">
                            <label class="form-label">Contraseña</label>
                            <input wire:model="password" type="password" class="form-control @error('password') is-invalid @enderror" placeholder="{{ $userId ? 'Dejar vacío para no cambiar' : 'Ej: Seguro123$' }}">
                            @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn btn-primary" wire:loading.attr="disabled">
                                <span wire:loading.remove wire:target="save">Guardar Cambios</span>
                                <span wire:loading wire:target="save"><i class="ph ph-spinner fs-6 fa-spin me-1 text-white"></i> Guardando...</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
