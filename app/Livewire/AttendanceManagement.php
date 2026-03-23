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
        if ($this->hasModifications && $value != $this->assigned_post_id) {
            $this->dispatch('confirm-filter-change', [
                'type' => 'assigned_post_id',
                'value' => $value
            ]);
            // Bloqueamos la actualización inmediata hasta que el usuario confirme o descarte
            throw new \Exception('Confirmación pendiente'); 
        }
        $this->resetPage();
    }

    public function updatingShiftId($value)
    {
        if ($this->hasModifications && $value != $this->shift_id) {
            $this->dispatch('confirm-filter-change', [
                'type' => 'shift_id',
                'value' => $value
            ]);
            throw new \Exception('Confirmación pendiente');
        }
        $this->resetPage();
    }

    /**
     * Forzar el cambio de filtro desde JS tras confirmar
     */
    #[On('force-filter-change')]
    public function forceFilterChange($type, $value)
    {
        $this->hasModifications = false;
        $this->$type = $value;
        $this->resetPage();
    }

    /**
     * Alternar asistencia (Check rápido)
     */
    public function toggleAttendance($employeeId)
    {
        // Secuencia principal solicitada por el usuario
        $mainCodes = ['T1', 'L', 'V', 'R', 'FINR', 'FJ'];
        
        $attendance = Attendance::where('employee_id', $employeeId)
            ->where('attendance_date', $this->attendance_date)
            ->first();

        $currentCode = 'T1';
        if ($attendance && $attendance->status) {
            $currentCode = $attendance->status->code;
        }

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
            Attendance::updateOrCreate(
                ['employee_id' => $employeeId, 'attendance_date' => $this->attendance_date],
                ['attendance_status_id' => $nextStatus->id]
            );
            $this->hasModifications = true;
        }
    }

    /**
     * Guardar estado específico (Retardo, Permiso, etc)
     */
    public function setStatus($employeeId, $statusId)
    {
        // Si el statusId es T1 (Asistido), borramos el registro para mantener la db limpia según filosofía previa
        // O lo guardamos. El usuario pidió que se guarde. 
        // Vamos a guardarlo para tener el reporte completo.
        Attendance::updateOrCreate(
            ['employee_id' => $employeeId, 'attendance_date' => $this->attendance_date],
            ['attendance_status_id' => $statusId]
        );
        $this->hasModifications = true;
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

        // Obtener empleados según los filtros actuales
        $query = Employee::query();
        if ($this->assigned_post_id) $query->where('assigned_post_id', $this->assigned_post_id);
        if ($this->shift_id) $query->where('shift_id', $this->shift_id);
        
        $employeesToMark = $query->get();
        $count = 0;
        $statusAsistio = AttendanceStatus::where('code', 'T1')->first();

        foreach ($employeesToMark as $emp) {
            Attendance::firstOrCreate(
                ['employee_id' => $emp->id, 'attendance_date' => $this->attendance_date],
                ['attendance_status_id' => $statusAsistio ? $statusAsistio->id : null]
            );
            $count++;
        }

        $this->hasModifications = false;
        $this->dispatch('notify', [
            'icon' => 'success', 
            'title' => 'Asistencias guardadas',
            'text' => "Se registraron $count asistencias para la selección actual."
        ]);
    }
    
    public function resetFilters()
    {
        $this->reset(['search', 'assigned_post_id', 'shift_id', 'hasModifications']);
        $this->resetPage();
    }

    #[Title('Gestión de Asistencias')]
    public function render()
    {
        $employees = Employee::query()
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
            })
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

        return view('livewire.attendance-management', [
            'employees' => $employees,
            'assignedPosts' => AssignedPost::all(),
            'shifts' => Shift::all(),
            'attendanceStatuses' => AttendanceStatus::where('active', true)->orderBy('id')->get(),
            'dailyAttendances' => $dailyAttendances,
        ]);
    }
}
