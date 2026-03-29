<div class="attendance-management">
    <div class="row mb-4">
        <div class="col-md-6">
            <h2 class="fw-bold"><i class="ph ph-calendar-check me-2 text-primary"></i> Gestión de Asistencias</h2>
            <p class="text-muted">Control de presencia diario por área asignada.</p>
        </div>
        <div class="col-md-6 text-md-end">
            <div class="bg-white p-2 rounded-4 shadow-sm d-inline-block border">
                <label class="small fw-bold text-muted px-2">FECHA DE ASISTENCIA</label>
                <input type="date" wire:model.live="attendance_date" class="form-control border-0 fw-bold text-primary" style="width: auto; display: inline-block;">
            </div>
        </div>
    </div>

    {{-- Alertas de Estado removidas por petición --}}
    @if($isProcessed)
        <div class="alert alert-success border-0 shadow-sm rounded-4 d-flex align-items-center mb-4">
            <i class="ph ph-check-circle fs-3 me-3"></i>
            <div>
                <h6 class="mb-0 fw-bold">Asistencia Procesada</h6>
                <small>Todos los empleados de esta selección ya tienen su registro para el día {{ \Carbon\Carbon::parse($attendance_date)->format('d/m/Y') }}.</small>
            </div>
        </div>
    @endif

    <!-- Tarjetas de Estadísticas Rápidas -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden h-100 bg-white">
                <div class="card-body p-3 d-flex align-items-center">
                    <div class="bg-primary-subtle text-primary rounded-circle p-3 me-3 d-flex align-items-center justify-content-center">
                        <i class="ph ph-users fs-3"></i>
                    </div>
                    <div>
                        <h6 class="text-muted mb-1 small fw-bold text-uppercase" style="font-size: 0.70rem; letter-spacing: 0.5px;">Total Empleados</h6>
                        <h3 class="mb-0 fw-bold text-dark lh-1">{{ $statTotal }}</h3>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden h-100 bg-white">
                <div class="card-body p-3 d-flex align-items-center">
                    <div class="bg-success-subtle text-success rounded-circle p-3 me-3 d-flex align-items-center justify-content-center">
                        <i class="ph ph-check-square-offset fs-3"></i>
                    </div>
                    <div>
                        <h6 class="text-muted mb-1 small fw-bold text-uppercase" style="font-size: 0.70rem; letter-spacing: 0.5px;">Asistencias Hoy</h6>
                        <h3 class="mb-0 fw-bold text-dark lh-1">{{ $statPresentes }}</h3>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden h-100 bg-white">
                <div class="card-body p-3 d-flex align-items-center">
                    <div class="bg-warning-subtle text-warning rounded-circle p-3 me-3 d-flex align-items-center justify-content-center">
                        <i class="ph ph-first-aid fs-3"></i>
                    </div>
                    <div>
                        <h6 class="text-muted mb-1 small fw-bold text-uppercase" style="font-size: 0.70rem; letter-spacing: 0.5px;">De Reposo</h6>
                        <h3 class="mb-0 fw-bold text-dark lh-1">{{ $statReposos }}</h3>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden h-100 bg-white">
                <div class="card-body p-3 d-flex align-items-center">
                    <div class="bg-info-subtle text-info rounded-circle p-3 me-3 d-flex align-items-center justify-content-center">
                        <i class="ph ph-airplane-tilt fs-3"></i>
                    </div>
                    <div>
                        <h6 class="text-muted mb-1 small fw-bold text-uppercase" style="font-size: 0.70rem; letter-spacing: 0.5px;">De Vacaciones</h6>
                        <h3 class="mb-0 fw-bold text-dark lh-1">{{ $statVacaciones }}</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm overflow-hidden mb-4 rounded-4">
        <div class="card-body p-4 bg-white border-bottom">
            <div class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Área Asignada</label>
                    <select wire:model.live="assigned_post_id" class="form-select bg-light border-0 px-3 py-2 rounded-3">
                        <option value="">Todas las Áreas</option>
                        @foreach ($assignedPosts as $post)
                            <option value="{{ $post->id }}">{{ $post->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label fw-semibold">Turno (Horario)</label>
                    <select wire:model.live="shift_id" class="form-select bg-light border-0 px-3 py-2 rounded-3">
                        <option value="">Todos los Turnos</option>
                        @foreach ($shifts as $shift)
                            <option value="{{ $shift->id }}">{{ $shift->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label fw-semibold">Buscar Empleado</label>
                    <input wire:model.live.debounce.300ms="search" type="text" class="form-control bg-light border-0 px-3 py-2 rounded-3" placeholder="Nombre o Cédula...">
                </div>

                <div class="col-md-3 text-md-end">
                    @if($assigned_post_id || $shift_id)
                        <button class="btn btn-success btn-sm w-100 mb-2 shadow-sm" wire:click="saveBulkAttendance">
                            <i class="ph ph-floppy-disk me-1"></i> Guardar Asistencias
                        </button>
                    @endif
                    <button class="btn btn-outline-secondary btn-sm w-100" wire:click="resetFilters">
                        Limpiar Filtros
                    </button>
                </div>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light text-dark text-uppercase small fw-bold">
                    <tr>
                        <th class="ps-4">Empleado</th>
                        <th>Cédula</th>
                        <th>Área Asignada</th>
                        <th>Turno / Horas</th>
                        <th class="text-center">Estado de Asistencia</th>
                    </tr>
                </thead>
                <tbody class="border-top-0">
                    @forelse ($employees as $employee)
                        @php
                            $att = $dailyAttendances[$employee->id] ?? null;
                            $statusId = $att ? $att->attendance_status_id : null;
                            $statusObj = $att ? $att->status : null;
                            $statusCode = $statusObj ? $statusObj->code : 'T1';
                            $statusDesc = $statusObj ? $statusObj->description : 'ASISTENCIA';
                            
                            // Determinar color
                            $color = 'success'; // Default T1
                            if ($statusCode === 'FINR' || $statusCode === 'FJ' || $statusCode === 'FJNR' || $statusCode === 'HFI' || $statusCode === 'HFJ') $color = 'danger';
                            elseif (in_array($statusCode, ['PR', 'PNR', 'PV', 'PCC', 'X/PNR'])) $color = 'info';
                            elseif (in_array($statusCode, ['V', 'LP'])) $color = 'primary';
                            elseif (in_array($statusCode, ['R', 'X/R'])) $color = 'warning';
                            elseif ($statusCode === 'L') $color = 'secondary';
                        @endphp
                        <tr wire:key="emp-{{ $employee->id }}" class="{{ !$att ? 'opacity-75' : '' }} transition-all">
                            <td class="ps-4 py-3">
                                <div class="d-flex align-items-center">
                                    <div class="avatar-circle me-3 bg-{{ $color }}-subtle text-{{ $color }} fw-bold rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;" title="{{ $statusDesc }}">
                                        {{ $loop->iteration }}
                                    </div>
                                    <div>
                                        <div class="fw-bold mb-0 text-dark">{{ $employee->first_names }} {{ $employee->last_names }}</div>
                                        <small class="text-muted">Ficha: {{ $employee->file_number ?? 'S/N' }}</small>
                                    </div>
                                </div>
                            </td>
                            <td><code class="text-dark">{{ $employee->national_id }}</code></td>
                            <td>
                                <span class="small fw-medium text-secondary">
                                    <i class="ph ph-map-pin me-1"></i> {{ $employee->assignedPost->name ?? 'No asignada' }}
                                </span>
                            </td>
                            <td>
                                <div class="small fw-bold">Turno</div>
                                <div class="text-muted" style="font-size: 0.75rem;">
                                    @if($employee->shift)
                                        @php
                                            $currentStatusCode = $att->status->code ?? ($employee->shift->code ?? 'T1');
                                            $h = $att ? $att->total_hours : ($employee->shift->total_hours > 0 ? $employee->shift->total_hours : 0);
                                            
                                            // Si el estado no empieza por T (No es asistencia), las horas deben ser 0
                                            if (!str_starts_with($currentStatusCode, 'T')) {
                                                $h = 0;
                                            }
                                            
                                            // El cálculo dinámico del turno solo aplica si es un estado de asistencia (T1-T5 o no hay registro yet)
                                            if (abs($h) <= 0.01 && str_starts_with($currentStatusCode, 'T') && $employee->shift->start_time && $employee->shift->end_time) {
                                                $s = \Carbon\Carbon::parse($employee->shift->start_time);
                                                $e = \Carbon\Carbon::parse($employee->shift->end_time);
                                                $diff = abs($e->diffInMinutes($s));
                                                // Descontar 90 min de almuerzo si es turno diurno (9h)
                                                $finalMin = ($diff > 500) ? ($diff - 90) : $diff;
                                                $h = round($finalMin / 60, 2);
                                            }
                                        @endphp
                                        
                                        @if(str_starts_with($currentStatusCode, 'T'))
                                            {{ \Carbon\Carbon::parse($employee->shift->start_time)->format('h:i A') }} - {{ \Carbon\Carbon::parse($employee->shift->end_time)->format('h:i A') }}
                                            <span class="badge bg-light text-dark border ms-1">{{ number_format(abs($h), 2) }}h</span>
                                        @endif
                                    @endif
                                </div>
                                
                                {{-- Columnas Ocultas para tiempos --}}
                                <input type="hidden" name="check_in_{{ $employee->id }}" value="{{ str_starts_with($currentStatusCode, 'T') ? ($att->check_in ?? $employee->shift->start_time) : '' }}">
                                <input type="hidden" name="lunch_start_{{ $employee->id }}" value="{{ str_starts_with($currentStatusCode, 'T') ? ($att->lunch_break_start ?? '12:00:00') : '' }}">
                                <input type="hidden" name="lunch_end_{{ $employee->id }}" value="{{ str_starts_with($currentStatusCode, 'T') ? ($att->lunch_break_end ?? '13:30:00') : '' }}">
                                <input type="hidden" name="check_out_{{ $employee->id }}" value="{{ str_starts_with($currentStatusCode, 'T') ? ($att->check_out ?? $employee->shift->end_time) : '' }}">
                            </td>
                            <td class="text-center">
                                <div class="d-flex justify-content-center align-items-center gap-2">
                                    <!-- Botón rápido Tildar/Quitar Falla -->
                                    <button wire:click="toggleAttendance({{ $employee->id }})" 
                                            class="btn btn-{{ $color }} rounded-pill px-3 btn-sm shadow-sm transition-all"
                                            title="Click para alternar Falta Injustificada">
                                        <i class="ph {{ $statusCode === 'T1' ? 'ph-check-circle' : 'ph-warning-circle' }} me-1"></i>
                                        {{ $statusCode }}
                                    </button>

                                    <!-- Botón para editar horarios detallados -->
                                    @if($employee->shift)
                                        <button wire:click="loadAttendanceForEdit({{ $employee->id }})" 
                                                class="btn btn-outline-primary btn-sm rounded-circle p-1 shadow-none border-0"
                                                title="Editar Horarios Detallados">
                                            <i class="ph ph-clock-countdown fs-5"></i>
                                        </button>
                                    @endif

                                    <!-- Menú de opciones (3 puntos) + Indicador de Procesado -->
                                    <div class="dropdown d-flex align-items-center">
                                        <button class="btn btn-light btn-sm rounded-circle p-1 shadow-none" type="button" data-bs-toggle="dropdown">
                                            <i class="ph ph-dots-three-vertical"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end shadow border-0 overflow-auto" style="max-height: 300px;">
                                            <li><h6 class="dropdown-header small text-muted">Incidencias ({{ count($attendanceStatuses) }})</h6></li>
                                            @foreach ($attendanceStatuses as $st)
                                                <li wire:key="st-{{ $st->id }}-{{ $employee->id }}">
                                                    <a class="dropdown-item py-2 d-flex justify-content-between align-items-center" href="#" wire:click.prevent="setStatus({{ $employee->id }}, {{ $st->id }})">
                                                        <span>{{ $st->description }}</span>
                                                        <span class="badge bg-light text-dark border ms-2">{{ $st->code }}</span>
                                                    </a>
                                                </li>
                                            @endforeach
                                        </ul>
                                        
                                        @if($att)
                                            <i class="ph-fill ph-check-circle text-success fs-5 ms-2" title="Registro guardado"></i>
                                        @else
                                            <i class="ph ph-circle text-muted fs-5 ms-2 opacity-25" title="Sin registro aún"></i>
                                        @endif
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="py-5 text-center text-muted">
                            <i class="ph ph-warning-circle fs-1 d-block mb-3 opacity-25"></i>
                            No se encontraron empleados.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="card-footer bg-white py-3 border-top">
            {{ $employees->links() }}
        </div>
    </div>

    <style>
        .transition-all { transition: all 0.2s ease-in-out; }
        .attendance-management .btn:active { transform: scale(0.95); }
        .table-hover tbody tr:hover { background-color: #f8fafc; }
        .avatar-circle { font-size: 0.85rem; }
    </style>

    <!-- Modal de Edición de Horarios -->
    <div wire:ignore.self class="modal fade" id="attendanceModal" tabindex="-1" aria-labelledby="attendanceModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold" id="attendanceModalLabel">
                        <i class="ph ph-clock-user me-2 text-primary"></i> Ajustar Horario
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-4 text-center">
                        <h6 class="text-primary fw-bold mb-1">{{ $editEmployeeName }}</h6>
                        <p class="text-muted small mb-0">{{ \Carbon\Carbon::parse($attendance_date)->translatedFormat('d \d\e F, Y') }}</p>
                    </div>

                    <div class="row g-3">
                        <div class="col-6">
                            <label class="form-label small fw-bold text-muted uppercase">Hora Entrada</label>
                            <input type="time" wire:model="editCheckIn" class="form-control bg-light border-0 py-2 rounded-3">
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-bold text-muted uppercase">Hora Salida Final</label>
                            <input type="time" wire:model="editCheckOut" class="form-control bg-light border-0 py-2 rounded-3">
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-bold text-muted uppercase">Salida Almuerzo</label>
                            <input type="time" wire:model="editLunchStart" class="form-control bg-light border-0 py-2 rounded-3">
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-bold text-muted uppercase">Entrada Almuerzo</label>
                            <input type="time" wire:model="editLunchEnd" class="form-control bg-light border-0 py-2 rounded-3">
                        </div>
                    </div>

                    <div class="mt-4 p-3 bg-primary-subtle rounded-3 text-center">
                        <div class="small text-primary fw-bold mb-1">TOTAL HORAS CALCULADAS</div>
                        <div class="h4 mb-0 text-primary fw-bold">
                            @php
                                // Cálculo visual rápido en el modal si los campos están llenos
                                $displayHours = 0;
                                if ($editCheckIn && $editCheckOut) {
                                    $in = \Carbon\Carbon::parse($editCheckIn);
                                    $out = \Carbon\Carbon::parse($editCheckOut);
                                    $totalMin = $out->diffInMinutes($in);
                                    $lunchMin = 0;
                                    if ($editLunchStart && $editLunchEnd) {
                                        $lS = \Carbon\Carbon::parse($editLunchStart);
                                        $lE = \Carbon\Carbon::parse($editLunchEnd);
                                        $lunchMin = $lE->diffInMinutes($lS);
                                    }
                                    $displayHours = round(($totalMin - $lunchMin) / 60, 1);
                                }
                            @endphp
                            {{ abs($displayHours) }}h
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0 p-4">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" wire:click="saveAttendanceTimes" class="btn btn-primary rounded-pill px-4 shadow-sm">
                        <i class="ph ph-floppy-disk me-1"></i> Guardar Cambios
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('livewire:init', () => {
            const attendanceModal = new bootstrap.Modal(document.getElementById('attendanceModal'));

            Livewire.on('open-attendance-modal', () => {
                attendanceModal.show();
            });

            Livewire.on('close-attendance-modal', () => {
                attendanceModal.hide();
            });

            Livewire.on('confirm-filter-change', (data) => {
                const eventData = data[0];
                Swal.fire({
                    title: '¿Cambiar de Área?',
                    text: 'Tienes cambios manuales sin "Guardar". Si cambias el área ahora, podrías dejar registros incompletos para los otros trabajadores de este grupo.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#10b981',
                    cancelButtonColor: '#6b7280',
                    confirmButtonText: 'Sí, cambiar área',
                    cancelButtonText: 'Seguir editando'
                }).then((result) => {
                    if (result.isConfirmed) {
                        Livewire.dispatch('force-filter-change', { type: eventData.type, value: eventData.value });
                    }
                });
            });
        });
    </script>
</div>
