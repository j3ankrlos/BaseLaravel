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

    <!-- Alertas de Estado -->
    @if($isProcessed)
        <div class="alert alert-success border-0 shadow-sm rounded-4 d-flex align-items-center mb-4">
            <i class="ph ph-check-circle fs-3 me-3"></i>
            <div>
                <h6 class="mb-0 fw-bold">Asistencia Procesada</h6>
                <small>Todos los empleados de esta selección ya tienen su registro para el día {{ \Carbon\Carbon::parse($attendance_date)->format('d/m/Y') }}.</small>
            </div>
        </div>
    @elseif($hasModifications)
        <div class="alert alert-warning border-0 shadow-sm rounded-4 d-flex align-items-center mb-4">
            <i class="ph ph-warning-circle fs-3 me-3 text-warning"></i>
            <div>
                <h6 class="mb-0 fw-bold">Cambios Pendientes</h6>
                <small>Has modificado incidencias manualmente. Dale a "Guardar Asistencias" para completar el resto del grupo con T1.</small>
            </div>
        </div>
    @endif

    <div class="card border-0 shadow-sm overflow-hidden mb-4 rounded-4">
        <div class="card-body p-4 bg-white border-bottom">
            <div class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Área Asignada</label>
                    <select wire:model.live="assigned_post_id" class="form-select bg-light border-0 px-3 py-2 rounded-3">
                        <option value="">Todas las Áreas</option>
                        @foreach($assignedPosts as $post)
                            <option value="{{ $post->id }}">{{ $post->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label fw-semibold">Turno (Horario)</label>
                    <select wire:model.live="shift_id" class="form-select bg-light border-0 px-3 py-2 rounded-3">
                        <option value="">Todos los Turnos</option>
                        @foreach($shifts as $shift)
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
                    @forelse($employees as $employee)
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
                        <tr>
                            <td class="ps-4 py-3">
                                <div class="d-flex align-items-center">
                                    <div class="avatar-circle me-3 bg-{{ $color }}-subtle text-{{ $color }} fw-bold rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;" title="{{ $statusDesc }}">
                                        {{ $statusCode }}
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
                                <div class="small fw-bold">{{ $employee->shift->name ?? 'Sin turno' }}</div>
                                <div class="text-muted" style="font-size: 0.75rem;">
                                    {{ $employee->shift ? $employee->shift->start_time . ' - ' . $employee->shift->end_time : '' }}
                                    @if($employee->shift) <span class="badge bg-light text-dark border ms-1">{{ $employee->shift->total_hours }}h</span> @endif
                                </div>
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

                                    <!-- Menú de estados específicos -->
                                    <div class="dropdown">
                                        <button class="btn btn-light btn-sm rounded-circle p-1" type="button" data-bs-toggle="dropdown">
                                            <i class="ph ph-dots-three-vertical"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end shadow border-0 overflow-auto" style="max-height: 300px;">
                                            <li><h6 class="dropdown-header small text-muted">Incidencias ({{ count($attendanceStatuses) }})</h6></li>
                                            @foreach($attendanceStatuses as $st)
                                                <li>
                                                    <a class="dropdown-item py-2 d-flex justify-content-between align-items-center" href="#" wire:click.prevent="setStatus({{ $employee->id }}, {{ $st->id }})">
                                                        <span>{{ $st->description }}</span>
                                                        <span class="badge bg-light text-dark border ms-2">{{ $st->code }}</span>
                                                    </a>
                                                </li>
                                            @endforeach
                                        </ul>
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

    <script>
        document.addEventListener('livewire:init', () => {
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
