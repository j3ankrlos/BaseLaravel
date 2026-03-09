<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Employee;
use App\Models\EmployeeIncident;
use App\Models\AttendanceStatus;
use App\Models\Attendance;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class IncidentManagement extends Component
{
    use WithPagination;

    // Filtros y búsqueda
    public $search = '';
    public $perPage = 10;

    public function mount()
    {
        \App\Models\ModuleUsage::track('incidents', 'Incidencias', '/incidents', 'ph-file-text', 'text-warning');
    }
    
    
    // Propiedades del formulario
    public $employee_id;
    public $attendance_status_id;
    public $start_date;
    public $end_date;
    public $observation;
    public $total_days = 1;

    public $isModalOpen = false;
    public $selectedEmployee = null;
    public $employeeSearch = ''; // Buscador dentro del modal

    protected $rules = [
        'employee_id' => 'required|exists:employees,id',
        'attendance_status_id' => 'required|exists:attendance_statuses,id',
        'start_date' => 'required|date',
        'end_date' => 'required|date|after_or_equal:start_date',
        'observation' => 'nullable|string|max:500',
    ];

    public function updatedStartDate()
    {
        $this->calculateTotalDays();
    }

    public function updatedEndDate()
    {
        $this->calculateTotalDays();
    }

    public function calculateTotalDays()
    {
        if ($this->start_date && $this->end_date) {
            $start = Carbon::parse($this->start_date);
            $end = Carbon::parse($this->end_date);
            $this->total_days = $start->diffInDays($end) + 1;
        }
    }

    public function openModal()
    {
        $this->resetFields();
        $this->isModalOpen = true;
    }

    public function deselectEmployee()
    {
        $this->employee_id = null;
        $this->selectedEmployee = null;
        $this->employeeSearch = '';
    }

    public function resetFields()
    {
        $this->reset(['employee_id', 'attendance_status_id', 'start_date', 'end_date', 'observation', 'total_days', 'selectedEmployee', 'employeeSearch']);
    }

    public function selectEmployee($id)
    {
        $this->employee_id = $id;
        $this->selectedEmployee = Employee::find($id);
        $this->employeeSearch = ''; // Limpiar búsqueda tras selección
    }

    public function save()
    {
        $this->validate();

        DB::transaction(function () {
            // Guardar la incidencia
            EmployeeIncident::create([
                'employee_id' => $this->employee_id,
                'attendance_status_id' => $this->attendance_status_id,
                'start_date' => $this->start_date,
                'end_date' => $this->end_date,
                'total_days' => $this->total_days,
                'observation' => $this->observation,
                'created_by' => auth()->id(),
            ]);

            // Sincronizar con la tabla de asistencias para el rango de fechas
            $start = Carbon::parse($this->start_date);
            $end = Carbon::parse($this->end_date);

            for ($date = $start->copy(); $date->lte($end); $date->addDay()) {
                Attendance::updateOrCreate(
                    [
                        'employee_id' => $this->employee_id,
                        'attendance_date' => $date->format('Y-m-d')
                    ],
                    [
                        'attendance_status_id' => $this->attendance_status_id,
                        'observation' => 'Generado por Incidencia: ' . $this->observation
                    ]
                );
            }
        });

        $this->isModalOpen = false;
        $this->dispatch('notify', [
            'icon' => 'success',
            'title' => 'Incidencia registrada',
            'text' => 'El historial y las asistencias han sido actualizados.'
        ]);
        $this->resetFields();
    }

    public function delete($id)
    {
        $incident = EmployeeIncident::findOrFail($id);
        
        DB::transaction(function () use ($incident) {
            // Opcionalmente borrar las asistencias generadas? 
            // Por seguridad, solo borramos la incidencia del historial por ahora.
            $incident->delete();
        });

        $this->dispatch('notify', ['icon' => 'success', 'title' => 'Incidencia eliminada']);
    }

    public function render()
    {
        $incidents = EmployeeIncident::with(['employee', 'attendanceStatus'])
            ->whereHas('employee', function($q) {
                $q->where('first_names', 'like', '%' . $this->search . '%')
                  ->orWhere('last_names', 'like', '%' . $this->search . '%')
                  ->orWhere('national_id', 'like', '%' . $this->search . '%');
            })
            ->orderBy('start_date', 'desc')
            ->paginate($this->perPage);

        $employees = [];
        if (strlen($this->employeeSearch) > 2) {
            $employees = Employee::where('status', 'Activo')
                ->where(function($q) {
                    $q->where('first_names', 'like', '%' . $this->employeeSearch . '%')
                      ->orWhere('last_names', 'like', '%' . $this->employeeSearch . '%')
                      ->orWhere('national_id', 'like', '%' . $this->employeeSearch . '%');
                })
                ->limit(5)
                ->get();
        }

        return view('livewire.incident-management', [
            'incidents' => $incidents,
            'employees' => $employees,
            'statuses' => AttendanceStatus::where('active', true)->where('code', '!=', 'T1')->orderBy('id')->get(),
        ])->title('Gestión de Incidencias');
    }
}
