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

    public $incident_id = null;
    public $incident_status = null;
    public $return_date = null; // Fecha real de regreso del trabajador

    public $isModalOpen = false;
    public $selectedEmployee = null;
    public $employeeSearch = '';

    // Validación dinámica según el modo (creación vs edición)
    protected function rules(): array
    {
        if ($this->incident_id) {
            // Modo edición: solo validamos lo que el usuario puede cambiar
            return [
                'incident_status' => 'required|in:En Curso,Pendiente,Cumplido',
                'observation'     => 'nullable|string|max:500',
                'return_date'     => 'nullable|date',
            ];
        }

        // Modo creación: validamos todo
        return [
            'employee_id'          => 'required|exists:employees,id',
            'attendance_status_id' => 'required|exists:attendance_statuses,id',
            'start_date'           => 'required|date',
            'end_date'             => 'required|date|after_or_equal:start_date',
            'observation'          => 'nullable|string|max:500',
        ];
    }

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
        $this->reset(['incident_id', 'incident_status', 'return_date', 'employee_id', 'attendance_status_id', 'start_date', 'end_date', 'observation', 'total_days', 'selectedEmployee', 'employeeSearch']);
    }

    public function edit($id)
    {
        $this->resetFields();
        $incident = EmployeeIncident::with(['employee', 'attendanceStatus'])->findOrFail($id);
        
        $this->incident_id = $incident->id;
        $this->employee_id = $incident->employee_id;
        $this->selectedEmployee = $incident->employee;
        $this->attendance_status_id = $incident->attendance_status_id;
        $this->start_date = $incident->start_date;
        $this->end_date = $incident->end_date;
        $this->total_days = $incident->total_days;
        $this->observation = $incident->observation;
        
        // Asignamos el estatus simulado calculado por tiempo para que el select inicie correctamente
        $this->incident_status = $incident->dynamic_status;

        $this->isModalOpen = true;
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

        $lateReturnData = null; // Para guardar datos del regreso tardío

        DB::transaction(function () use (&$lateReturnData) {
            if ($this->incident_id) {
                // =============== MODO EDICIÓN ===============
                $incident = EmployeeIncident::findOrFail($this->incident_id);
                $incident->update([
                    'status'      => $this->incident_status,
                    'observation' => $this->observation,
                ]);

                if ($this->incident_status === 'Cumplido') {
                    // 1. Limpiar estatus del empleado
                    Employee::where('id', $this->employee_id)->update(['current_status' => null]);

                    // 2. Revisar si regresó tarde
                    if ($this->return_date) {
                        $returnDay    = Carbon::parse($this->return_date);
                        $scheduledEnd = Carbon::parse($this->end_date);

                        // Si la fecha de regreso es POSTERIOR al fin programado,
                        // hay días injustificados que necesitan una nueva incidencia.
                        if ($returnDay->gt($scheduledEnd)) {
                            // El día siguiente al fin programado hasta el día ANTES del regreso son los injustificados
                            $extraStart = $scheduledEnd->copy()->addDay();
                            $extraEnd   = $returnDay->copy()->subDay();

                            if ($extraEnd->gte($extraStart)) {
                                $lateReturnData = [
                                    'employee_id'   => $this->employee_id,
                                    'employee_name' => ($this->selectedEmployee->last_names ?? '') . ' ' . ($this->selectedEmployee->first_names ?? ''),
                                    'national_id'   => $this->selectedEmployee->national_id ?? '',
                                    'extra_start'   => $extraStart->format('Y-m-d'),
                                    'extra_end'     => $extraEnd->format('Y-m-d'),
                                    'extra_days'    => $extraStart->diffInDays($extraEnd) + 1,
                                ];
                            }
                        }
                    }

                    $msgTitle = 'Regreso Confirmado';
                    $msgText  = 'Estatus del empleado liberado correctamente.';
                } else {
                    $statusObj = AttendanceStatus::find($this->attendance_status_id);
                    if ($statusObj) {
                        Employee::where('id', $this->employee_id)->update(['current_status' => $statusObj->description]);
                    }
                    $msgTitle = 'Incidencia Actualizada';
                    $msgText  = 'Estado y observación modificados.';
                }

            } else {
                // =============== MODO CREACIÓN ===============
                EmployeeIncident::create([
                    'employee_id'          => $this->employee_id,
                    'attendance_status_id' => $this->attendance_status_id,
                    'start_date'           => $this->start_date,
                    'end_date'             => $this->end_date,
                    'total_days'           => $this->total_days,
                    'observation'          => $this->observation,
                    'status'               => 'No Completado',
                    'created_by'           => auth()->id(),
                ]);

                $statusObj = AttendanceStatus::find($this->attendance_status_id);
                if ($statusObj) {
                    Employee::where('id', $this->employee_id)->update(['current_status' => $statusObj->description]);
                }

                $start = Carbon::parse($this->start_date);
                $end   = Carbon::parse($this->end_date);
                for ($date = $start->copy(); $date->lte($end); $date->addDay()) {
                    Attendance::updateOrCreate(
                        ['employee_id' => $this->employee_id, 'attendance_date' => $date->format('Y-m-d')],
                        ['attendance_status_id' => $this->attendance_status_id, 'observation' => 'Generado por Incidencia: ' . $this->observation]
                    );
                }

                $msgTitle = 'Incidencia registrada';
                $msgText  = 'El historial y las asistencias han sido actualizados.';
            }
        });

        $this->isModalOpen = false;
        $this->dispatch('close-modal', ['id' => 'incidentModal']);

        $this->dispatch('notify', [
            'icon'  => 'success',
            'title' => $msgTitle ?? 'Operación Exitosa',
            'text'  => $msgText ?? ''
        ]);

        // Si hay días injustificados, pre-cargamos el modal de creación
        if ($lateReturnData) {
            $this->resetFields();
            // Pre-cargamos los datos del empleado y las fechas extra
            $this->employee_id      = $lateReturnData['employee_id'];
            $this->selectedEmployee = Employee::find($lateReturnData['employee_id']);
            $this->start_date       = $lateReturnData['extra_start'];
            $this->end_date         = $lateReturnData['extra_end'];
            $this->total_days       = $lateReturnData['extra_days'];
            // Dispatchar evento para abrir el modal de creación
            $this->dispatch('open-modal', ['id' => 'incidentModal']);
            $this->dispatch('notify', [
                'icon'  => 'warning',
                'title' => '¡Regreso Tardío!',
                'text'  => 'El trabajador regresó con ' . $lateReturnData['extra_days'] . ' día(s) de retraso. Registre la incidencia injustificada.'
            ]);
        } else {
            $this->resetFields();
        }
    }

    public function delete($id)
    {
        $incident = EmployeeIncident::findOrFail($id);
        
        DB::transaction(function () use ($incident) {
            // Limpiar el estatus actual del empleado al borrar la incidencia
            Employee::where('id', $incident->employee_id)->update(['current_status' => null]);
            
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
