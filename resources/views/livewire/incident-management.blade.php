<div class="incident-management">
    <div class="row mb-4">
        <div class="col-md-6">
            <h2 class="fw-bold"><i class="ph ph-file-text me-2 text-primary"></i> Gestión de Incidencias</h2>
            <p class="text-muted">Registro de vacaciones, reposos y novedades del personal.</p>
        </div>
        <div class="col-md-6 text-md-end">
            <button class="btn btn-primary shadow-sm rounded-pill px-4" wire:click="openModal" data-bs-toggle="modal" data-bs-target="#incidentModal">
                <i class="ph ph-plus-circle me-1"></i> Registrar Incidencia
            </button>
        </div>
    </div>

    <!-- Buscador y Filtros -->
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-3">
            <div class="row g-2">
                <div class="col-md-10">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0"><i class="ph ph-magnifying-glass text-muted"></i></span>
                        <input wire:model.live.debounce.300ms="search" type="text" class="form-control border-start-0" placeholder="Buscar por nombre, apellido o cédula...">
                    </div>
                </div>
                <div class="col-md-2">
                    <select wire:model.live="perPage" class="form-select">
                        <option value="10">10 por pág.</option>
                        <option value="25">25 por pág.</option>
                        <option value="50">50 por pág.</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <!-- Listado de Incidencias -->
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light text-dark text-uppercase small fw-bold">
                    <tr>
                        <th class="ps-4">Empleado</th>
                        <th>Tipo de Incidencia</th>
                        <th>Período</th>
                        <th>Días</th>
                        <th>Estatus</th>
                        <th class="text-end pe-4">Acciones</th>
                    </tr>
                </thead>
                <tbody class="border-top-0">
                    @forelse ($incidents as $incident)
                        @php
                            $typeColor = 'info';
                            $code = $incident->attendanceStatus->code;
                            if (in_array($code, ['FINR', 'FJ', 'FJNR'])) $typeColor = 'danger';
                            elseif (in_array($code, ['V', 'LP'])) $typeColor = 'primary';
                            elseif (in_array($code, ['R', 'X/R'])) $typeColor = 'warning';
                            elseif ($code === 'L') $typeColor = 'secondary';

                            // Badge dinámico del estatus de la incidencia
                            $dynStatus = $incident->dynamic_status;
                            $statusBadge = match($dynStatus) {
                                'En Curso'  => ['bg' => 'primary',  'icon' => 'ph-spinner',       'label' => 'En Curso'],
                                'Pendiente' => ['bg' => 'warning',  'icon' => 'ph-clock-countdown','label' => 'Pendiente'],
                                'Cumplido'  => ['bg' => 'success',  'icon' => 'ph-check-circle',  'label' => 'Cumplido'],
                                default     => ['bg' => 'secondary','icon' => 'ph-question',       'label' => $dynStatus],
                            };
                        @endphp
                        <tr>
                            <td class="ps-4 py-3">
                                <div class="d-flex align-items-center">
                                    <div class="avatar-circle me-3 bg-{{ $typeColor }}-subtle text-{{ $typeColor }} fw-bold rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                        {{ $code }}
                                    </div>
                                    <div>
                                        <div class="fw-bold text-dark">{{ $incident->employee->first_names }} {{ $incident->employee->last_names }}</div>
                                        <small class="text-muted">{{ $incident->employee->national_id }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="fw-medium text-dark">{{ $incident->attendanceStatus->description }}</span>
                                <div class="small text-muted text-truncate" style="max-width: 200px;" title="{{ $incident->observation }}">
                                    {{ $incident->observation }}
                                </div>
                            </td>
                            <td>
                                <div class="small fw-bold text-dark">
                                    <i class="ph ph-calendar-blank me-1 text-muted"></i>
                                    {{ \Carbon\Carbon::parse($incident->start_date)->format('d/m/Y') }}
                                </div>
                                <div class="small text-muted">
                                    al {{ \Carbon\Carbon::parse($incident->end_date)->format('d/m/Y') }}
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border px-3 rounded-pill">{{ $incident->total_days }} días</span>
                            </td>
                            <td>
                                <span class="badge bg-{{ $statusBadge['bg'] }}-subtle text-{{ $statusBadge['bg'] }} border border-{{ $statusBadge['bg'] }}-subtle px-3 rounded-pill d-inline-flex align-items-center gap-1">
                                    <i class="ph {{ $statusBadge['icon'] }}"></i>
                                    {{ $statusBadge['label'] }}
                                </span>
                            </td>
                            <td class="text-end pe-4">
                                <!-- Botón Editar (solo estatus y observación) -->
                                <button class="btn btn-light btn-sm rounded-circle me-1"
                                        wire:click="edit({{ $incident->id }})"
                                        data-bs-toggle="modal" data-bs-target="#incidentModal"
                                        title="Editar estatus de la incidencia">
                                    <i class="ph ph-pencil-simple text-warning"></i>
                                </button>
                                <!-- Botón Eliminar -->
                                <button class="btn btn-light btn-sm rounded-circle"
                                        wire:click="delete({{ $incident->id }})"
                                        title="Eliminar incidencia">
                                    <i class="ph ph-trash text-danger"></i>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-5 text-center text-muted">
                                <i class="ph ph-folder-open fs-1 d-block mb-3 opacity-25"></i>
                                No se encontraron incidencias registradas.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer bg-white border-top py-3">
            {{ $incidents->links() }}
        </div>
    </div>

    <!-- Modal Unificado: Registrar O Editar Incidencia -->
    <div wire:ignore.self class="modal fade" id="incidentModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow rounded-4">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold">
                        @if($incident_id)
                            <i class="ph ph-pencil-simple me-2 text-warning"></i> Confirmar / Actualizar Incidencia
                        @else
                            <i class="ph ph-plus-circle me-2 text-primary"></i> Registrar Nueva Incidencia
                        @endif
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" wire:click="resetFields"></button>
                </div>
                <div class="modal-body p-4">
                    <form wire:submit.prevent="save">
                        <div class="row g-3">

                            @if($incident_id)
                                {{-- ============ MODO EDICIÓN: Solo Status + Observación ============ --}}
                                {{-- Resumen de Solo Lectura --}}
                                <div class="col-12">
                                    <div class="d-flex align-items-center p-3 bg-light rounded-3 border">
                                        <div class="avatar-circle me-3 bg-warning-subtle text-warning fw-bold rounded-circle d-flex align-items-center justify-content-center" style="width: 45px; height: 45px;">
                                            <i class="ph ph-user"></i>
                                        </div>
                                        <div>
                                            <div class="fw-bold text-dark">{{ $selectedEmployee?->last_names }} {{ $selectedEmployee?->first_names }}</div>
                                            <small class="text-muted">
                                                Cédula: {{ $selectedEmployee?->national_id }}
                                                &nbsp;•&nbsp;
                                                Período: {{ $start_date ? \Carbon\Carbon::parse($start_date)->format('d/m/Y') : '–' }}
                                                al {{ $end_date ? \Carbon\Carbon::parse($end_date)->format('d/m/Y') : '–' }}
                                                ({{ $total_days }} días)
                                            </small>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-12">
                                    <label class="form-label fw-bold">Estado de la Incidencia</label>
                                    <select wire:model.live="incident_status" class="form-select @error('incident_status') is-invalid @enderror">
                                        <option value="En Curso">🔵 En Curso — La incidencia está vigente</option>
                                        <option value="Pendiente">🟠 Pendiente — Debió regresar, sin confirmar</option>
                                        <option value="Cumplido">🟢 Cumplido — El trabajador regresó</option>
                                    </select>
                                    @error('incident_status') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    <small class="text-muted mt-1 d-block">
                                        <i class="ph ph-info me-1"></i>
                                        Al marcar como <strong>Cumplido</strong>, el estatus del empleado se limpiará automáticamente.
                                    </small>
                                </div>

                                {{-- Campo de fecha real de regreso: solo si marca Cumplido --}}
                                @if($incident_status === 'Cumplido')
                                <div class="col-md-12">
                                    <div class="alert alert-warning-subtle border border-warning-subtle rounded-3 p-3 mb-0">
                                        <div class="fw-bold text-warning-emphasis mb-2">
                                            <i class="ph ph-calendar-check me-1"></i>
                                            ¿En qué fecha regresó el trabajador?
                                        </div>
                                        <input type="date" wire:model="return_date"
                                               class="form-control @error('return_date') is-invalid @enderror"
                                               max="{{ now()->format('Y-m-d') }}">
                                        @error('return_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                        <small class="text-muted mt-1 d-block">
                                            Dejar en blanco si regresó exactamente el
                                            <strong>{{ $end_date ? \Carbon\Carbon::parse($end_date)->format('d/m/Y') : '–' }}</strong>.
                                            Si ingresó una fecha posterior, se abrirá un formulario para registrar los días de ausencia injustificada.
                                        </small>
                                    </div>
                                </div>
                                @endif

                                <div class="col-md-12">
                                    <label class="form-label fw-bold">Observación / Justificación</label>
                                    <textarea wire:model="observation" class="form-control" rows="3" placeholder="Escriba aquí los detalles de la incidencia..."></textarea>
                                </div>

                            @else
                                {{-- ============ MODO CREACIÓN: Todos los campos ============ --}}
                                <div class="col-md-12">
                                    <label class="form-label fw-bold">Buscar Trabajador (Cédula o Nombre)</label>
                                    @if(!$selectedEmployee)
                                        <div class="input-group">
                                            <span class="input-group-text bg-white"><i class="ph ph-magnifying-glass"></i></span>
                                            <input type="text" wire:model.live.debounce.300ms="employeeSearch"
                                                   class="form-control border-start-0 @error('employee_id') is-invalid @enderror"
                                                   placeholder="Escriba al menos 3 caracteres para buscar...">
                                            @error('employee_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                        </div>

                                        @if(count($employees) > 0)
                                            <div class="list-group mt-2 shadow-sm">
                                                @foreach ($employees as $emp)
                                                    <button type="button" wire:click="selectEmployee({{ $emp->id }})"
                                                            class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                                                        <div>
                                                            <span class="fw-bold">{{ $emp->last_names }} {{ $emp->first_names }}</span><br>
                                                            <small class="text-muted">Cédula: {{ $emp->national_id }}</small>
                                                        </div>
                                                        <i class="ph ph-plus-circle text-primary fs-5"></i>
                                                    </button>
                                                @endforeach
                                            </div>
                                        @elseif(strlen($employeeSearch) > 2)
                                            <div class="alert alert-light border mt-2 py-2 small">
                                                <i class="ph ph-info me-1"></i> No se encontraron coincidencias.
                                            </div>
                                        @endif
                                    @else
                                        <div class="d-flex align-items-center p-3 bg-primary-subtle rounded-3 border border-primary-subtle">
                                            <div class="avatar-circle me-3 bg-primary text-white fw-bold rounded-circle d-flex align-items-center justify-content-center" style="width: 45px; height: 45px;">
                                                {{ substr($selectedEmployee->first_names, 0, 1) }}{{ substr($selectedEmployee->last_names, 0, 1) }}
                                            </div>
                                            <div class="flex-grow-1">
                                                <div class="fw-bold text-dark">{{ $selectedEmployee->last_names }} {{ $selectedEmployee->first_names }}</div>
                                                <small class="text-muted">Cédula: {{ $selectedEmployee->national_id }} • {{ $selectedEmployee->area->name ?? 'S/A' }}</small>
                                            </div>
                                            <button type="button" class="btn btn-sm btn-outline-danger border-0 rounded-circle" wire:click.prevent="deselectEmployee">
                                                <i class="ph ph-x-circle fs-4"></i>
                                            </button>
                                        </div>
                                    @endif
                                    <input type="hidden" wire:model="employee_id">
                                </div>

                                <div class="col-md-12">
                                    <label class="form-label fw-bold">Tipo de Incidencia</label>
                                    <select wire:model="attendance_status_id" class="form-select @error('attendance_status_id') is-invalid @enderror">
                                        <option value="">Seleccione el tipo...</option>
                                        @foreach ($statuses as $st)
                                            <option value="{{ $st->id }}">{{ $st->code }} - {{ $st->description }}</option>
                                        @endforeach
                                    </select>
                                    @error('attendance_status_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label fw-bold">Fecha de Inicio</label>
                                    <input type="date" wire:model.live="start_date" class="form-control @error('start_date') is-invalid @enderror">
                                    @error('start_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label fw-bold">Fecha de Fin</label>
                                    <input type="date" wire:model.live="end_date" class="form-control @error('end_date') is-invalid @enderror">
                                    @error('end_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label fw-bold">Total Días</label>
                                    <input type="text" class="form-control bg-light fw-bold text-primary" value="{{ $total_days }} días" readonly>
                                </div>

                                <div class="col-md-12">
                                    <label class="form-label fw-bold">Observación / Justificación</label>
                                    <textarea wire:model="observation" class="form-control" rows="3" placeholder="Escriba aquí los detalles de la incidencia..."></textarea>
                                    @error('observation') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            @endif

                        </div>

                        <div class="mt-4 pt-3 border-top d-flex justify-content-end gap-2">
                            <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal" wire:click="resetFields">Cancelar</button>
                            <button type="submit" class="btn btn-{{ $incident_id ? 'warning' : 'primary' }} px-4 shadow-sm" wire:loading.attr="disabled">
                                <span wire:loading.remove>
                                    @if($incident_id)
                                        <i class="ph ph-check-circle me-1"></i> Confirmar Cambio
                                    @else
                                        Guardar Incidencia
                                    @endif
                                </span>
                                <span wire:loading>Procesando...</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <style>
        .incident-management .avatar-circle { font-size: 0.8rem; }
        .incident-management .table hover tbody tr:hover { background-color: #f8fafc; }
        .incident-management .modal-content { overflow: hidden; }
    </style>
</div>
