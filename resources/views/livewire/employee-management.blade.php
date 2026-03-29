<div>
    <div class="card card-body mb-4">
        <div class="row g-3 d-flex align-items-center justify-content-between">
            <div class="col-12 col-md-5">
                <input wire:model.live.debounce.300ms="search" type="search" class="form-control" placeholder="Buscar por nombre o cédula...">
            </div>
            <div class="col-12 col-md-auto text-end">
                <!-- Botón de Importar -->
                <button onclick="document.getElementById('importFile').click()" class="btn btn-outline-success shadow-sm me-1" title="Cargar empleados desde Excel o CSV">
                    <i class="ph ph-file-xls me-1"></i> <span class="d-none d-lg-inline">Importar</span>
                </button>
                <input type="file" id="importFile" wire:model.live="importFile" class="d-none" accept=".csv,.xlsx,.xls">
                
                @error('importFile') 
                    <script>
                        window.addEventListener('DOMContentLoaded', () => {
                            Livewire.dispatch('notify', [{icon: 'error', title: 'Error de archivo', text: '{{ $message }}'}]);
                        });
                    </script>
                @enderror

                <div wire:loading wire:target="importFile" class="spinner-border spinner-border-sm text-success me-2" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>

                <button wire:click="create" class="btn btn-primary shadow-sm">
                    <i class="ph ph-plus-circle me-1"></i> <span class="d-none d-lg-inline">Nuevo Empleado</span><span class="d-lg-none">Nuevo</span>
                </button>
            </div>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="table-responsive">
            <table class="table table-hover table-striped mb-0 align-middle">
                <thead class="table-dark">
                    <tr>
                        <th scope="col">Ficha</th>
                        <th scope="col">Nombre</th>
                        <th scope="col">Cédula</th>
                        <th scope="col">Cargo</th>
                        <th scope="col">Ubicación Laboral</th>
                        <th scope="col">Estatus</th>
                        <th scope="col" class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($employees as $emp)
                        <tr>
                            <td><span class="badge bg-light text-dark border">{{ $emp->file_number ?? 'N/A' }}</span></td>
                            <td>
                                <div class="fw-bold">{{ $emp->first_names }} {{ $emp->last_names }}</div>
                                <small class="text-muted">{{ $emp->phone_mobile }}</small>
                            </td>
                            <td>{{ $emp->national_id }}</td>
                            <td><span class="fw-medium text-dark">{{ $emp->position->name ?? 'N/A' }}</span></td>
                            <td>
                                <div class="small fw-bold text-primary" title="Puesto Asignado">
                                    <i class="ph ph-map-pin me-1"></i>{{ $emp->assignedPost->name ?? 'Sin Puesto' }}
                                </div>
                                <div class="small fw-semibold text-secondary" title="Área de Centro de Costo">
                                    <i class="ph ph-money me-1"></i>{{ $emp->area->name ?? 'Sin Área CC' }}
                                </div>
                                <div class="small text-muted" title="Unidad">
                                    <i class="ph ph-house me-1"></i>{{ $emp->unit->name ?? 'Sin Unidad' }}
                                </div>
                            </td>
                            <td>
                                <div class="d-flex flex-column gap-1">
                                    <span class="badge bg-info text-dark border-0" style="background-color: #e0f2fe !important;">
                                        <i class="ph ph-article me-1"></i>{{ $emp->estatus ?? 'Fijo' }}
                                    </span>
                                    <span class="badge bg-{{ $emp->estadonomina == 'Activo' ? 'success' : 'danger' }} shadow-sm">
                                        <i class="ph ph-circle-wavy-{{ $emp->estadonomina == 'Activo' ? 'check' : 'warning' }} me-1"></i>
                                        {{ $emp->estadonomina ?? 'Activo' }}
                                    </span>
                                </div>
                            </td>
                            <td class="text-center">
                                <div class="d-flex justify-content-center gap-1">
                                    <button wire:click="edit({{ $emp->id }})" class="btn btn-sm btn-outline-secondary" title="Editar" wire:loading.attr="disabled">
                                        <i class="ph ph-pencil-simple" wire:loading.remove wire:target="edit({{ $emp->id }})"></i>
                                        <span class="spinner-border spinner-border-sm" wire:loading wire:target="edit({{ $emp->id }})"></span>
                                    </button>
                                    <button wire:click="delete({{ $emp->id }})" class="btn btn-sm btn-outline-danger" title="Eliminar">
                                        <i class="ph ph-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="ph ph-users-three fs-1 d-block mb-3 opacity-25"></i>
                                No se encontraron empleados registrados.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($employees->hasPages())
            <div class="card-footer bg-white border-top-0 pt-3">
                {{ $employees->links(data: ['scrollTo' => false]) }}
            </div>
        @endif
    </div>

    <!-- Modal Form -->
    <div wire:ignore.self class="modal fade" id="employeeModal" tabindex="-1" aria-labelledby="employeeModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">
                        {{ $employeeId ? 'Editar Empleado' : 'Registro de Nuevo Empleado' }}
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" wire:click="resetFields"></button>
                </div>
                <div class="modal-body p-4">
                    <form wire:submit.prevent="save">
                        <!-- Tabs Navigation -->
                        <ul class="nav nav-pills mb-4 bg-light p-1 rounded-3" id="employeeTabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button wire:click="setActiveTab('personal')" class="nav-link {{ $activeTab === 'personal' ? 'active' : '' }}" type="button">Datos Personales</button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button wire:click="setActiveTab('location')" class="nav-link {{ $activeTab === 'location' ? 'active' : '' }}" type="button">Dirección completa</button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button wire:click="setActiveTab('work')" class="nav-link {{ $activeTab === 'work' ? 'active' : '' }}" type="button">Información Laboral</button>
                            </li>
                        </ul>

                        <div class="tab-content">
                            <!-- Datos Personales -->
                            <div class="tab-pane fade {{ $activeTab === 'personal' ? 'show active' : '' }}">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Nombres</label>
                                        <input wire:model="first_names" type="text" class="form-control @error('first_names') is-invalid @enderror">
                                        @error('first_names') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Apellidos</label>
                                        <input wire:model="last_names" type="text" class="form-control @error('last_names') is-invalid @enderror">
                                        @error('last_names') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold">Cédula</label>
                                        <input wire:model="national_id" type="text" class="form-control @error('national_id') is-invalid @enderror">
                                        @error('national_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold">Teléfono Fijo</label>
                                        <input wire:model="phone_fixed" type="text" class="form-control">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold">Teléfono Móvil</label>
                                        <input wire:model="phone_mobile" type="text" class="form-control">
                                    </div>
                                </div>
                            </div>

                            <!-- Ubicación -->
                            <div class="tab-pane fade {{ $activeTab === 'location' ? 'show active' : '' }}">
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold">Estado</label>
                                        <select wire:model.live="state_id" class="form-select @error('state_id') is-invalid @enderror">
                                            <option value="">Seleccione Estado</option>
                                            @foreach ($this->states as $st)
                                                <option value="{{ $st->id }}">{{ $st->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold">Municipio</label>
                                        <select wire:model.live="municipality_id" class="form-select @error('municipality_id') is-invalid @enderror" {{ !$state_id ? 'disabled' : '' }}>
                                            <option value="">Seleccione Municipio</option>
                                            @foreach ($this->municipalities as $mun)
                                                <option value="{{ $mun->id }}">{{ $mun->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold">Parroquia</label>
                                        <select wire:model="parish_id" class="form-select @error('parish_id') is-invalid @enderror" {{ !$municipality_id ? 'disabled' : '' }}>
                                            <option value="">Seleccione Parroquia</option>
                                            @foreach ($this->parishes as $par)
                                                <option value="{{ $par->id }}">{{ $par->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold">Ciudad</label>
                                        <input wire:model="city" type="text" class="form-control">
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label fw-semibold">Dirección Exacta</label>
                                        <textarea wire:model="address" class="form-control" rows="2"></textarea>
                                    </div>
                                </div>
                            </div>

                            <!-- Información Laboral -->
                            <div class="tab-pane fade {{ $activeTab === 'work' ? 'show active' : '' }}">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold text-primary">Área de Centro de Costo</label>
                                        <select wire:model.live="area_id" class="form-select border-primary @error('area_id') is-invalid @enderror">
                                            <option value="">Seleccione Área CC</option>
                                            @foreach ($this->areas as $area)
                                                <option value="{{ $area->id }}">{{ $area->name }}</option>
                                            @endforeach
                                        </select>
                                        <small class="text-primary small">Determina el presupuesto contable.</small>
                                        @error('area_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold text-secondary">Área Asignada (Puesto)</label>
                                        <select wire:model="assigned_post_id" class="form-select border-secondary @error('assigned_post_id') is-invalid @enderror">
                                            <option value="">Seleccione Puesto</option>
                                            @foreach ($this->assignedPosts as $post)
                                                <option value="{{ $post->id }}">{{ $post->name }}</option>
                                            @endforeach
                                        </select>
                                        <small class="text-secondary small">Lugar físico o función operativa.</small>
                                        @error('assigned_post_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Centro de Costo Asignado</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light text-primary fw-bold"><i class="ph ph-buildings"></i></span>
                                            <input wire:model="cost_center_code" type="text" class="form-control bg-light fw-bold text-primary" readonly placeholder="Automático...">
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Cargo</label>
                                        <select wire:model.live="position_id" class="form-select @error('position_id') is-invalid @enderror">
                                            <option value="">Seleccione Cargo</option>
                                            @foreach ($this->positions as $pos)
                                                <option value="{{ $pos->id }}">{{ $pos->name }}</option>
                                            @endforeach
                                        </select>
                                        @error('position_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                        <div class="form-check form-switch mt-1">
                                            <input class="form-check-input" type="checkbox" wire:model.live="showVetSection" id="manualVetToggle">
                                            <label class="form-check-label small text-muted" for="manualVetToggle">
                                                ¿Es Médico Veterinario? (Habilitar ficha médica)
                                            </label>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Unidad (Sitio)</label>
                                        <select wire:model="unit_id" class="form-select @error('unit_id') is-invalid @enderror">
                                            <option value="">Seleccione Unidad</option>
                                            @foreach ($this->units as $unit)
                                                <option value="{{ $unit->id }}">{{ $unit->name }}</option>
                                            @endforeach
                                        </select>
                                        @error('unit_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Fecha Ingreso</label>
                                        <input wire:model="entry_date" type="date" class="form-control @error('entry_date') is-invalid @enderror">
                                        @error('entry_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Número Ficha</label>
                                        <input wire:model="file_number" type="text" class="form-control">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold text-danger">Turno Asignado</label>
                                        <select wire:model="shift_id" class="form-select border-danger @error('shift_id') is-invalid @enderror">
                                            <option value="">Seleccione Turno</option>
                                            @foreach ($this->shifts as $shift)
                                                <option value="{{ $shift->id }}">{{ $shift->code }} - {{ $shift->name }}</option>
                                             @endforeach
                                        </select>
                                        @error('shift_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold text-success">Tipo de Nómina</label>
                                        <select wire:model="payroll_type_id" class="form-select border-success @error('payroll_type_id') is-invalid @enderror">
                                            <option value="">Seleccione Nómina</option>
                                            @foreach ($this->payrollTypes as $type)
                                                <option value="{{ $type->id }}">{{ $type->name }}</option>
                                            @endforeach
                                        </select>
                                        @error('payroll_type_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Estatus (Contrato)</label>
                                        <select wire:model="estatus" class="form-select @error('estatus') is-invalid @enderror">
                                            <option value="Fijo">Fijo</option>
                                            <option value="Contratado">Contratado</option>
                                        </select>
                                        @error('estatus') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Estado Nómina</label>
                                        <select wire:model="estadonomina" class="form-select @error('estadonomina') is-invalid @enderror">
                                            <option value="Activo">Activo</option>
                                            <option value="Inactivo">Inactivo</option>
                                        </select>
                                        @error('estadonomina') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>

                                    @if($showVetSection)
                                        <div class="col-12 mt-4">
                                            <h6 class="text-primary border-bottom pb-2"><i class="ph ph-stethoscope me-2"></i>Datos Exclusivos: Médico Veterinario</h6>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">Código Colegio Médico</label>
                                            <input wire:model="medical_college_code" type="text" class="form-control @error('medical_college_code') is-invalid @enderror" placeholder="Ej: 12345">
                                            @error('medical_college_code') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">Código Ministerio</label>
                                            <input wire:model="ministry_code" type="text" class="form-control @error('ministry_code') is-invalid @enderror" placeholder="Ej: MIN-6789">
                                            @error('ministry_code') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">Siglas Identificativas</label>
                                            <input wire:model="vet_initials" type="text" class="form-control text-uppercase" placeholder="Ej: AB">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">Estatus de Registro Médico</label>
                                            <select wire:model="registration_status" class="form-select">
                                                <option value="Activo">Activo</option>
                                                <option value="Suspendido">Suspendido</option>
                                                <option value="Vencido">Vencido</option>
                                            </select>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="mt-4 pt-3 border-top d-grid gap-2 d-md-flex justify-content-md-end">
                            <button type="button" class="btn btn-light shadow-sm" data-bs-dismiss="modal" wire:click="resetFields">Cancelar</button>
                            <button type="submit" class="btn btn-primary shadow-sm" wire:loading.attr="disabled">
                                <i class="ph ph-floppy-disk me-1"></i>
                                <span wire:loading.remove wire:target="save">Guardar Empleado</span>
                                <span wire:loading wire:target="save">Procesando...</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
