<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Title;
use Livewire\Attributes\On;
use App\Models\Employee;
use App\Models\AssignedPost;
use App\Models\Attendance;
use App\Models\AttendanceStatus;
use App\Models\Shift;
use Carbon\Carbon;

class AttendanceManagement extends Component
{
    use WithPagination;

    public $search = '';
    public $selectedEmployee = null;
    public $comment = '';
    public $assigned_post_id = '';
    public $shift_id = '';
    public $attendance_date;
    public $perPage = 25;
    public $hasModifications = false; // Rastrea si hubo cambios manuales
    public $isProcessed = false;     // Rastrea si el área ya está completa
    
    // Propiedades para el modal de edición
    public $editingAttendanceId = null;
    public $editingEmployeeId = null;
    public $editEmployeeName = '';
    public $editCheckIn = '';
    public $editLunchStart = '';
    public $editLunchEnd = '';
    public $editCheckOut = '';

    protected $queryString = [
        'search' => ['except' => ''],
        'assigned_post_id' => ['except' => ''],
        'shift_id' => ['except' => ''],
        'attendance_date' => ['except' => ''],
    ];

    public function mount()
    {
        if (!$this->attendance_date) {
            $this->attendance_date = Carbon::today()->format('Y-m-d');
        }

        \App\Models\ModuleUsage::track('attendance', 'Registrar Asistencia', '/attendance', 'ph-calendar-check', 'text-danger');
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingAssignedPostId($value)
    {
        $this->resetPage();
    }

    public function updatingShiftId($value)
    {
        $this->resetPage();
    }

    /**
     * Alternar asistencia (Check rápido)
     */
    public function toggleAttendance($employeeId)
    {
        $employee = Employee::with('shift')->find($employeeId);
        if (!$employee) return;

        // Secuencia principal solicitada por el usuario
        $mainCodes = ['T1', 'T2', 'T3', 'L', 'V', 'R', 'FINR', 'FJ'];
        
        $attendance = Attendance::where('employee_id', $employeeId)
            ->where('attendance_date', $this->attendance_date)
            ->first();

        // Determinar código actual (usando el turno del empleado como base si no hay asistencia)
        $currentCode = $attendance?->status?->code ?? ($employee->shift->code ?? 'T1');

        // Buscar el siguiente en la lista
        $currentIndex = array_search($currentCode, $mainCodes);
        
        // Si no está en la lista principal o es el último, volvemos al primero (T1)
        if ($currentIndex === false || $currentIndex === count($mainCodes) - 1) {
            $nextCode = $mainCodes[0];
        } else {
            $nextCode = $mainCodes[$currentIndex + 1];
        }

        $nextStatus = AttendanceStatus::where('code', $nextCode)->first();
        
        if ($nextStatus) {
            $data = ['attendance_status_id' => $nextStatus->id];
            
            // Si el código empieza por "T", es un turno de asistencia
            if (str_starts_with($nextCode, 'T')) {
                // Buscar el turno correspondiente al código seleccionado
                $newShift = Shift::where('code', $nextCode)->first();
                if ($newShift) {
                    // Actualizar permanentemente el turno del empleado si cambió
                    if ($employee->shift_id !== $newShift->id) {
                        $employee->update(['shift_id' => $newShift->id]);
                    }
                    
                    $data['check_in'] = $newShift->start_time;
                    $data['check_out'] = $newShift->end_time;
                    $data['lunch_break_start'] = '12:00:00';
                    $data['lunch_break_end'] = '13:30:00';
                }
            } else {
                // Si cambia a otro estado, limpiamos las horas
                $data['check_in'] = null;
                $data['check_out'] = null;
                $data['lunch_break_start'] = null;
                $data['lunch_break_end'] = null;
                $data['total_hours'] = 0;
            }

            $attendance = Attendance::updateOrCreate(
                ['employee_id' => $employeeId, 'attendance_date' => $this->attendance_date],
                $data
            );

            // Calcular y guardar total_hours si hay marcas de tiempo
            if ($attendance->check_in && $attendance->check_out) {
                $attendance->total_hours = $attendance->calculateWorkedHours();
                $attendance->save();
            }

            $this->hasModifications = true;
        }
    }

    /**
     * Guardar estado específico (Retardo, Permiso, etc)
     */
    public function setStatus($employeeId, $statusId)
    {
        $status = AttendanceStatus::find($statusId);
        $data = ['attendance_status_id' => $statusId];

        // Verificar si es un código de "Presente" (T1, T2, T3) e intentar actualizar empleado
        if ($status && str_starts_with($status->code, 'T')) {
            $employee = Employee::find($employeeId);
            $newShift = Shift::where('code', $status->code)->first();

            if ($employee && $newShift) {
                // Actualizar permanentemente el turno si es diferente
                if ($employee->shift_id !== $newShift->id) {
                    $employee->update(['shift_id' => $newShift->id]);
                }
                
                $data['check_in'] = $newShift->start_time;
                $data['check_out'] = $newShift->end_time;
                $data['lunch_break_start'] = '12:00:00';
                $data['lunch_break_end'] = '13:30:00';
            }
        } else {
            $data['check_in'] = null;
            $data['check_out'] = null;
            $data['lunch_break_start'] = null;
            $data['lunch_break_end'] = null;
            $data['total_hours'] = 0;
        }

        $attendance = Attendance::updateOrCreate(
            ['employee_id' => $employeeId, 'attendance_date' => $this->attendance_date],
            $data
        );

        if ($attendance->check_in && $attendance->check_out) {
            $attendance->total_hours = $attendance->calculateWorkedHours();
            $attendance->save();
        }

        $this->hasModifications = true;
    }

    /**
     * Carga los datos de una asistencia para editar en el modal
     */
    public function loadAttendanceForEdit($employeeId)
    {
        $employee = Employee::with('shift')->find($employeeId);
        if (!$employee) return;

        $this->editingEmployeeId = $employeeId;
        $this->editEmployeeName = $employee->first_names . ' ' . $employee->last_names;

        // Buscar si ya tiene asistencia hoy
        $attendance = Attendance::where('employee_id', $employeeId)
            ->where('attendance_date', $this->attendance_date)
            ->first();

        if ($attendance) {
            $this->editingAttendanceId = $attendance->id;
            $this->editCheckIn = $attendance->check_in ? Carbon::parse($attendance->check_in)->format('H:i') : '';
            $this->editLunchStart = $attendance->lunch_break_start ? Carbon::parse($attendance->lunch_break_start)->format('H:i') : '';
            $this->editLunchEnd = $attendance->lunch_break_end ? Carbon::parse($attendance->lunch_break_end)->format('H:i') : '';
            $this->editCheckOut = $attendance->check_out ? Carbon::parse($attendance->check_out)->format('H:i') : '';
        } else {
            // Cargar predeterminados del turno
            $this->editingAttendanceId = null;
            $this->editCheckIn = $employee->shift?->start_time ? Carbon::parse($employee->shift->start_time)->format('H:i') : '';
            $this->editCheckOut = $employee->shift?->end_time ? Carbon::parse($employee->shift->end_time)->format('H:i') : '';
            $this->editLunchStart = '12:00';
            $this->editLunchEnd = '13:30';
        }

        $this->dispatch('open-attendance-modal');
    }

    /**
     * Guarda los tiempos desde el modal y recalcula
     */
    public function saveAttendanceTimes()
    {
        $statusAsistio = AttendanceStatus::where('code', 'T1')->first();
        
        $attendance = Attendance::updateOrCreate(
            ['employee_id' => $this->editingEmployeeId, 'attendance_date' => $this->attendance_date],
            [
                'attendance_status_id' => Attendance::where('employee_id', $this->editingEmployeeId)
                    ->where('attendance_date', $this->attendance_date)
                    ->first()?->attendance_status_id ?? ($statusAsistio->id ?? 1),
                'check_in' => $this->editCheckIn ?: null,
                'lunch_break_start' => $this->editLunchStart ?: null,
                'lunch_break_end' => $this->editLunchEnd ?: null,
                'check_out' => $this->editCheckOut ?: null,
            ]
        );

        $attendance->total_hours = $attendance->calculateWorkedHours();
        $attendance->save();
        
        $this->dispatch('close-attendance-modal');
        $this->dispatch('notify', ['icon' => 'success', 'title' => 'Horario actualizado correctamente']);
    }

    /**
     * Guardar toda el área como 'Asistió' masivamente
     */
    public function saveBulkAttendance()
    {
        if (!$this->assigned_post_id && !$this->shift_id) {
            $this->dispatch('notify', ['icon' => 'warning', 'title' => 'Selecciona un Área o Turno primero']);
            return;
        }

        $statusAsistio = AttendanceStatus::where('code', 'T1')->first();
        if (!$statusAsistio) {
            $this->dispatch('notify', ['icon' => 'error', 'title' => 'Estado T1 no encontrado']);
            return;
        }

        // 1. Obtener IDs de todos los empleados que cumplen el filtro actual
        $employeeIds = Employee::query()
            ->when($this->assigned_post_id, fn($q) => $q->where('assigned_post_id', $this->assigned_post_id))
            ->when($this->shift_id, fn($q) => $q->where('shift_id', $this->shift_id))
            ->pluck('id');

        if ($employeeIds->isEmpty()) {
            $this->dispatch('notify', ['icon' => 'info', 'title' => 'No hay empleados en esta selección']);
            return;
        }

        // 2. Obtener IDs de empleados que YA tienen registro hoy (para este grupo)
        $existingAttendanceIds = Attendance::where('attendance_date', $this->attendance_date)
            ->whereIn('employee_id', $employeeIds)
            ->pluck('employee_id')
            ->toArray();

        // 3. Identificar quiénes faltan por registrar
        $missingIds = $employeeIds->diff($existingAttendanceIds);

        if ($missingIds->isNotEmpty()) {
            $now = now();
            // Para inserción masiva, necesitamos los datos de turnos y sus estados correspondientes
            $employees = Employee::whereIn('id', $missingIds)->with('shift')->get();
            $allStatuses = AttendanceStatus::whereIn('code', ['T1', 'T2', 'T3', 'T4', 'T5'])->get();

            foreach ($employees as $employee) {
                $shiftCode = $employee->shift?->code ?? 'T1';
                $statusId = $allStatuses->where('code', $shiftCode)->first()?->id ?? 1;

                $checkIn = $employee->shift?->start_time;
                $checkOut = $employee->shift?->end_time;
                $lStart = '12:00:00';
                $lEnd = '13:30:00';

                $attendance = Attendance::create([
                    'employee_id' => $employee->id,
                    'attendance_date' => $this->attendance_date,
                    'attendance_status_id' => $statusId,
                    'check_in' => $checkIn,
                    'check_out' => $checkOut,
                    'lunch_break_start' => $lStart,
                    'lunch_break_end' => $lEnd,
                ]);

                $attendance->total_hours = $attendance->calculateWorkedHours();
                $attendance->save();
            }
        }

        $this->hasModifications = false;
        $this->dispatch('notify', [
            'icon' => 'success', 
            'title' => 'Asistencias guardadas',
            'text' => "Se registraron " . $missingIds->count() . " asistencias nuevas."
        ]);
    }
    
    public function resetFilters()
    {
        $this->reset(['search', 'assigned_post_id', 'shift_id']);
        $this->resetPage();
    }

    #[Title('Gestión de Asistencias')]
    public function render()
    {
        $employeesQuery = Employee::query()
            ->when($this->search, function($query) {
                $query->where(function($q) {
                    $q->where('first_names', 'like', '%' . $this->search . '%')
                      ->orWhere('last_names', 'like', '%' . $this->search . '%')
                      ->orWhere('national_id', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->assigned_post_id, function($query) {
                $query->where('assigned_post_id', $this->assigned_post_id);
            })
            ->when($this->shift_id, function($query) {
                $query->where('shift_id', $this->shift_id);
            });

        $employees = (clone $employeesQuery)
            ->with(['assignedPost', 'area', 'shift'])
            ->orderBy('last_names', 'asc')
            ->paginate($this->perPage);

        $dailyAttendances = Attendance::where('attendance_date', $this->attendance_date)
            ->whereIn('employee_id', $employees->pluck('id'))
            ->get()
            ->keyBy('employee_id');

        // Verificar si el área filtrada ya está completa
        $this->isProcessed = false;
        if ($this->assigned_post_id || $this->shift_id) {
            $totalEmployees = Employee::query()
                ->when($this->assigned_post_id, fn($q) => $q->where('assigned_post_id', $this->assigned_post_id))
                ->when($this->shift_id, fn($q) => $q->where('shift_id', $this->shift_id))
                ->count();
            
            $totalAttendances = Attendance::where('attendance_date', $this->attendance_date)
                ->whereIn('employee_id', function($q) {
                    $q->select('id')->from('employees')
                        ->when($this->assigned_post_id, fn($sq) => $sq->where('assigned_post_id', $this->assigned_post_id))
                        ->when($this->shift_id, fn($sq) => $sq->where('shift_id', $this->shift_id));
                })
                ->count();

            if ($totalEmployees > 0 && $totalEmployees === $totalAttendances) {
                $this->isProcessed = true;
            }
        }

        // === Estadísticas Rápidas ===
        $statsEmployeesQuery = clone $employeesQuery;
        $totalEmpleadosStat = $statsEmployeesQuery->count();

        $attendancesBaseQuery = Attendance::where('attendance_date', $this->attendance_date)
            ->whereIn('employee_id', $statsEmployeesQuery->pluck('id'));

        $statusReposoIds = AttendanceStatus::whereIn('code', ['R', 'X/R'])->pluck('id');
        $repososCount = (clone $attendancesBaseQuery)->whereIn('attendance_status_id', $statusReposoIds)->count();

        $statusVacacionesIds = AttendanceStatus::whereIn('code', ['V', 'PV'])->pluck('id');
        $vacacionesCount = (clone $attendancesBaseQuery)->whereIn('attendance_status_id', $statusVacacionesIds)->count();

        $statusAsistenciaIds = AttendanceStatus::whereIn('code', ['T1', 'T2', 'T3', 'T4', 'T5'])->pluck('id');
        $presentesCount = (clone $attendancesBaseQuery)->whereIn('attendance_status_id', $statusAsistenciaIds)->count();

        return view('livewire.attendance-management', [
            'employees' => $employees,
            'assignedPosts' => AssignedPost::all(),
            'shifts' => Shift::all(),
            'attendanceStatuses' => AttendanceStatus::where('active', true)->orderBy('id')->get(),
            'dailyAttendances' => $dailyAttendances,
            'statTotal' => $totalEmpleadosStat,
            'statPresentes' => $presentesCount,
            'statReposos' => $repososCount,
            'statVacaciones' => $vacacionesCount,
        ]);
    }
}
