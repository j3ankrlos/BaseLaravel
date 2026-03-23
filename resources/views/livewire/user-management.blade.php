<div>
    {{-- Listado de Usuarios --}}
    <div class="card card-body mb-4 shadow-sm border-0">
        <div class="row g-3 d-flex align-items-center justify-content-between">
            <div class="col-12 col-md-5">
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0"><i class="ph ph-magnifying-glass text-muted"></i></span>
                    <input wire:model.live.debounce.300ms="search" type="search" class="form-control border-start-0 ps-0" placeholder="Buscar por usuario, nombre o email...">
                </div>
            </div>
            <div class="col-12 col-md-auto text-end">
                <button wire:click="create" class="btn btn-primary shadow-sm px-4" data-bs-toggle="modal" data-bs-target="#userFormModal">
                    <i class="ph ph-plus-circle me-1"></i> Nuevo Usuario
                </button>
            </div>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th scope="col" wire:click="$set('orderBy', 'name')" class="cursor-pointer ps-4">Nombre 
                            <i class="ph {{ $orderBy == 'name' ? ($isAsc ? 'ph-caret-up' : 'ph-caret-down') : 'ph-caret-double-up-down text-muted' }}"></i>
                        </th>
                        <th scope="col">Usuario</th>
                        <th scope="col">Email</th>
                        <th scope="col">Rol</th>
                        <th scope="col">Estatus</th>
                        <th scope="col" class="text-end pe-4">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($users as $user)
                        <tr>
                            <td class="ps-4">
                                <div class="d-flex align-items-center">
                                    <div class="avatar-sm bg-primary-subtle text-primary rounded-circle d-flex align-items-center justify-content-center fw-bold me-2" style="width: 32px; height: 32px; font-size: 0.8rem;">
                                        {{ substr($user->name, 0, 1) }}
                                    </div>
                                    <div>
                                        <div class="fw-bold">{{ $user->name }}</div>
                                        <div class="text-muted small">{{ $user->short_name }}</div>
                                    </div>
                                </div>
                            </td>
                            <td><span class="badge bg-light text-dark fw-normal border text-uppercase">{{ $user->username }}</span></td>
                            <td>{{ $user->email }}</td>
                            <td>
                                <span class="badge {{ $user->roles->first()?->name == 'Super Admin' ? 'bg-danger-subtle text-danger' : 'bg-info-subtle text-info' }} px-2">
                                    {{ $user->roles->first()?->name ?? 'Sin Rol' }}
                                </span>
                            </td>
                            <td>
                                @if($user->status_id == 1)
                                    <span class="badge bg-success-subtle text-success"><i class="ph ph-check-circle me-1"></i> Activo</span>
                                @else
                                    <span class="badge bg-secondary-subtle text-secondary"><i class="ph ph-minus-circle me-1"></i> Inactivo</span>
                                @endif
                            </td>
                            <td class="text-end pe-4">
                                <div class="btn-group shadow-sm">
                                    <button wire:click="edit({{ $user->id }})" class="btn btn-sm btn-white border" data-bs-toggle="modal" data-bs-target="#userFormModal">
                                        <i class="ph ph-pencil-simple"></i>
                                    </button>
                                    <button wire:click="$dispatch('confirm-delete', { id: {{ $user->id }}, method: 'delete-user-confirmed' })" class="btn btn-sm btn-white border text-danger">
                                        <i class="ph ph-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                <i class="ph ph-magnifying-glass fs-1 d-block mb-3 opacity-25"></i> 
                                <p class="mb-0">No se encontraron usuarios que coincidan con la búsqueda.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($users->hasPages())
        <div class="card-footer bg-white py-3">
            {{ $users->links(data: ['scrollTo' => false]) }}
        </div>
        @endif
    </div>

    {{-- MODAL DE USUARIO --}}
    <div wire:ignore.self class="modal fade" id="userFormModal" tabindex="-1" aria-labelledby="userFormModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 12px;">
                <div class="modal-header bg-white border-0 pt-4 px-4 pb-0 d-flex align-items-center gap-2">
                    <i class="ph ph-user-circle-plus fs-4 text-primary"></i>
                    <div>
                        <h5 class="modal-title fw-bold mb-0 text-dark" id="userFormModalLabel">{{ $userId ? 'EDITAR' : 'CREAR' }} USUARIO</h5>
                        <p class="text-muted small mb-0">Complete la información para gestionar los accesos al sistema.</p>
                    </div>
                    <button type="button" class="btn-close ms-auto" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                
                <div class="modal-body p-4">
                    <form wire:submit.prevent="save">
                        {{-- Búsqueda de Personal --}}
                        <div class="mb-4 position-relative">
                            <label class="form-label fw-bold small text-uppercase text-primary">Seleccionar Personal</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"><i class="ph ph-user-focus"></i></span>
                                <input wire:model.live.debounce.300ms="employeeSearch" type="text" class="form-control bg-light border-start-0" placeholder="Escriba nombre o cédula para buscar...">
                            </div>
                            
                            @if(!empty($employeeResults))
                                <div class="position-absolute w-100 mt-1 shadow-lg z-3 border border-light rounded overflow-hidden bg-white" style="z-index: 1060;">
                                    @foreach ($employeeResults as $emp)
                                        <button type="button" wire:click="selectEmployee({{ $emp->id }})" class="btn btn-link text-start text-dark w-100 p-3 border-bottom border-light text-decoration-none dropdown-item-hover">
                                            <div class="fw-bold text-dark">{{ $emp->first_names }} {{ $emp->last_names }}</div>
                                            <div class="text-muted small">Cód: {{ $emp->national_id }} | {{ $emp->position?->name ?? 'Sin Cargo' }}</div>
                                        </button>
                                    @endforeach
                                </div>
                            @endif
                        </div>

                        <div class="row g-4">
                            {{-- Nombre Corto --}}
                            <div class="col-md-6">
                                <label class="form-label fw-semibold small text-uppercase">Nombre Corto</label>
                                <input wire:model="short_name" type="text" class="form-control bg-light fw-bold" readonly placeholder="Auto-completado">
                            </div>

                            {{-- Usuario --}}
                            <div class="col-md-6">
                                <label class="form-label fw-semibold small text-uppercase">Usuario</label>
                                <input wire:model="username" type="text" class="form-control bg-light fw-bold" readonly placeholder="Auto-completado">
                            </div>

                            {{-- Rol del Sistema --}}
                            <div class="col-md-6">
                                <label class="form-label fw-semibold small text-uppercase">Rol de Sistema</label>
                                <select wire:model="role" class="form-select bg-light @error('role') is-invalid @enderror">
                                    <option value="">Seleccione Rol</option>
                                    @foreach ($roles as $rol)
                                        <option value="{{ $rol->name }}">{{ $rol->name }}</option>
                                    @endforeach
                                </select>
                                @error('role') <div class="invalid-feedback small">{{ $message }}</div> @enderror
                            </div>

                            {{-- Estatus --}}
                            <div class="col-md-6">
                                <label class="form-label fw-semibold small text-uppercase d-block">Estatus</label>
                                <div class="form-check form-switch pt-2">
                                    <input wire:model="status" class="form-check-input cursor-pointer" type="checkbox" role="switch" id="statusSwitch">
                                    <label class="form-check-label ms-2 cursor-pointer" for="statusSwitch">
                                        {{ $status ? 'Usuario Activo' : 'Usuario Inactivo' }}
                                    </label>
                                </div>
                            </div>
                            
                            {{-- Email --}}
                            <div class="col-md-12">
                                <label class="form-label fw-semibold small text-uppercase">Correo Electrónico</label>
                                <input wire:model="email" type="email" class="form-control bg-light @error('email') is-invalid @enderror" placeholder="ejemplo@granja.com">
                                @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            {{-- Contraseña --}}
                            <div class="col-md-6">
                                <label class="form-label fw-semibold small text-uppercase">Contraseña</label>
                                <input wire:model="password" type="password" class="form-control bg-light @error('password') is-invalid @enderror" placeholder="{{ $userId ? 'Dejar vacío para no cambiar' : 'Ingresar contraseña' }}">
                                @error('password') <div class="invalid-feedback text-danger small">{{ $message }}</div> @enderror
                            </div>

                            {{-- Confirmar Contraseña --}}
                            <div class="col-md-6">
                                <label class="form-label fw-semibold small text-uppercase">Confirmar Contraseña</label>
                                <input wire:model="password_confirmation" type="password" class="form-control bg-light" placeholder="Repetir contraseña">
                            </div>
                        </div>

                        <div class="d-flex justify-content-end align-items-center mt-5 gap-2">
                            <button type="button" class="btn btn-outline-secondary px-4 shadow-sm" data-bs-dismiss="modal">
                                <i class="ph ph-x me-1"></i> Cancelar
                            </button>
                            <button type="submit" class="btn btn-primary px-5 shadow-sm fw-bold" wire:loading.attr="disabled">
                                <i class="ph ph-floppy-disk me-1"></i> {{ $userId ? 'GUARDAR CAMBIOS' : 'CREAR USUARIO' }}
                            </button>
                        </div>
                    </form>
                </div>
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

            // Livewire re-renders the DOM after save, which can leave Bootstrap's
            // backdrop orphaned. We clean it up manually to avoid a frozen/opaque screen.
            setTimeout(() => {
                document.querySelectorAll('.modal-backdrop').forEach(el => el.remove());
                document.body.classList.remove('modal-open');
                document.body.style.removeProperty('overflow');
                document.body.style.removeProperty('padding-right');
            }, 300);
        });
    </script>
    @endscript

    <style>
        .dropdown-item-hover:hover {
            background-color: #f8f9fa !important;
        }
        .form-check-input:checked {
            background-color: #0d6efd;
            border-color: #0d6efd;
        }
        .form-control:focus, .form-select:focus {
            background-color: #fff;
            border-color: #0d6efd;
            box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.15);
        }
        .form-control[readonly] {
            background-color: #e9ecef !important;
            opacity: 1;
            border-style: solid;
        }
    </style>
</div>
