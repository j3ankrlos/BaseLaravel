<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Livewire\Attributes\On;
use App\Models\Employee;
use App\Models\Area;
use App\Models\Unit;
use App\Models\Veterinarian;
use App\Models\State;
use App\Models\Municipality;
use App\Models\Parish;
use App\Models\Shift;
use App\Models\Position;
use App\Models\AssignedPost;
use App\Models\PayrollType;
use Livewire\Attributes\Computed;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Spatie\SimpleExcel\SimpleExcelReader;

class EmployeeManagement extends Component
{
    use WithPagination, WithFileUploads;

    // Propiedades de búsqueda y paginación
    public $search = '';
    public $perPage = 10;

    // Propiedades del formulario
    public $employeeId;
    public $first_names, $last_names, $national_id, $phone_fixed, $phone_mobile;
    public $state_id, $municipality_id, $parish_id, $city, $address;
    public $entry_date, $file_number, $cost_center_code;
    public $area_id, $assigned_post_id, $unit_id, $position_id, $shift_id, $payroll_type_id;
    public $status = 'Activo'; // Mantener por compatibilidad UI
    public $estatus = 'Fijo';
    public $estadonomina = 'Activo';

    // Datos exclusivos para Veterinarios
    public $medical_college_code, $ministry_code, $registration_status, $vet_initials;
    public $showVetSection = false;

    public $activeTab = 'personal'; 
    public $isModalOpen = false;

    public function mount()
    {
        \App\Models\ModuleUsage::track('employees', 'Gestión de Personal', '/employees', 'ph-briefcase', 'text-secondary');
    }

    protected $queryString = [
        'search' => ['except' => ''],
        'perPage' => ['except' => 10],
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function setActiveTab($tab)
    {
        $this->activeTab = $tab;
    }

    public function updatedAreaId($value)
    {
        if ($value) {
            $area = Area::find($value);
            $this->cost_center_code = $area ? $area->cost_center : '';
        }
        $this->activeTab = 'work';
    }

    public function updatedStateId($value)
    {
        $this->municipality_id = null;
        $this->parish_id = null;
        $this->activeTab = 'location';
    }

    public function updatedMunicipalityId($value)
    {
        $this->parish_id = null;
        $this->activeTab = 'location';
    }

    public function updatedPositionId($value)
    {
        if ($value) {
            $position = Position::find($value);
            if ($position && strtoupper($position->name) === 'MEDICO VETERINARIO') {
                $this->showVetSection = true;
            }
        }
        $this->activeTab = 'work';
    }

    #[Computed]
    public function areas() { return Area::all(); }
    #[Computed]
    public function assignedPosts() { return AssignedPost::all(); }
    #[Computed]
    public function positions() { return Position::where('active', true)->get(); }
    #[Computed]
    public function units() { return Unit::all(); }
    #[Computed]
    public function states() { return State::all(); }
    #[Computed]
    public function shifts() { return Shift::all(); }
    #[Computed]
    public function municipalities() { return $this->state_id ? Municipality::where('state_id', $this->state_id)->get() : []; }
    #[Computed]
    public function parishes() { return $this->municipality_id ? Parish::where('municipality_id', $this->municipality_id)->get() : []; }
    #[Computed]
    public function payrollTypes() { return PayrollType::all(); }

    public function render()
    {
        $employees = Employee::where(function($query) {
                $query->where('first_names', 'like', '%' . $this->search . '%')
                      ->orWhere('last_names', 'like', '%' . $this->search . '%')
                      ->orWhere('national_id', 'like', '%' . $this->search . '%');
            })
            ->with(['area', 'assignedPost', 'unit', 'position', 'shift'])
            ->orderBy('id', 'desc')
            ->paginate($this->perPage);

        return view('livewire.employee-management', [
            'employees' => $employees,
        ])->title('Gestión de Personal');
    }

    public function create()
    {
        $this->resetFields();
        $this->activeTab = 'personal';
        $this->isModalOpen = true;
        $this->dispatch('open-modal', ['id' => 'employeeModal']);
    }

    public function edit($id)
    {
        $employee = Employee::findOrFail($id);
        $this->employeeId = $id;
        $this->first_names = $employee->first_names;
        $this->last_names = $employee->last_names;
        $this->national_id = $employee->national_id;
        $this->phone_fixed = $employee->phone_fixed;
        $this->phone_mobile = $employee->phone_mobile;
        $this->state_id = $employee->state_id;
        $this->municipality_id = $employee->municipality_id;
        $this->parish_id = $employee->parish_id;
        $this->city = $employee->city;
        $this->address = $employee->address;
        $this->entry_date = $employee->entry_date;
        $this->file_number = $employee->file_number;
        $this->cost_center_code = $employee->cost_center_code;
        $this->area_id = $employee->area_id;
        $this->assigned_post_id = $employee->assigned_post_id;
        $this->unit_id = $employee->unit_id;
        $this->position_id = $employee->position_id;
        $this->shift_id = $employee->shift_id;
        $this->payroll_type_id = $employee->payroll_type_id;
        $this->status = $employee->status;
        $this->estatus = $employee->estatus ?? 'Fijo';
        $this->estadonomina = $employee->estadonomina ?? 'Activo';

        $position = Position::find($this->position_id);
        $this->showVetSection = ($position && strtoupper($position->name) === 'MEDICO VETERINARIO');
        
        if ($this->showVetSection) {
            $vet = Veterinarian::where('employee_id', $employee->id)->first();
            if ($vet) {
                $this->medical_college_code = $vet->medical_college_code;
                $this->ministry_code = $vet->ministry_code;
                $this->registration_status = $vet->registration_status;
                $this->vet_initials = $vet->initials;
            }
        }

        $this->activeTab = 'personal';
        $this->isModalOpen = true;
        $this->dispatch('open-modal', ['id' => 'employeeModal']);
    }

    public function validationAttributes()
    {
        return [
            'first_names' => 'nombres',
            'last_names' => 'apellidos',
            'national_id' => 'cédula',
            'status' => 'estatus',
            'area_id' => 'área',
            'assigned_post_id' => 'puesto asignado',
            'position_id' => 'cargo',
            'unit_id' => 'unidad',
            'shift_id' => 'turno',
            'entry_date' => 'fecha de ingreso',
            'estatus' => 'estatus de contrato',
            'estadonomina' => 'estado de nómina',
        ];
    }

    public function messages()
    {
        return [
            'required' => 'El campo :attribute es obligatorio.',
            'min' => 'El campo :attribute debe tener al menos :min caracteres.',
            'unique' => 'Esta :attribute ya se encuentra registrada en el sistema.',
            'in' => 'El valor seleccionado para :attribute no es válido.',
            'exists' => 'La :attribute seleccionada no es válida.',
            'date' => 'La :attribute debe ser una fecha válida.',
        ];
    }

    public function save()
    {
        // Normalización para evitar errores de validación 'in:...' por mayúsculas/minúsculas de importaciones
        $this->estatus = ucfirst(strtolower($this->estatus));
        $this->estadonomina = ucfirst(strtolower($this->estadonomina));

        $rules = [
            'first_names' => 'required|min:3',
            'last_names' => 'required|min:3',
            'national_id' => 'required|unique:employees,national_id,' . $this->employeeId,
            'status' => 'required',
            'area_id' => 'required|exists:areas,id',
            'assigned_post_id' => 'required|exists:assigned_posts,id',
            'position_id' => 'required|exists:positions,id',
            'unit_id' => 'nullable|exists:units,id',
            'shift_id' => 'nullable|exists:shifts,id',
            'payroll_type_id' => 'nullable|exists:payroll_types,id',
            'entry_date' => 'nullable|date',
            'estatus' => 'required|in:Fijo,Contratado',
            'estadonomina' => 'required|in:Activo,Inactivo',
        ];

        if ($this->showVetSection) {
            $rules['medical_college_code'] = 'nullable';
            $rules['ministry_code'] = 'nullable';
        }

        $this->validate($rules);

        DB::transaction(function () {
            $employee = Employee::updateOrCreate(['id' => $this->employeeId], [
                'first_names' => $this->first_names,
                'last_names' => $this->last_names,
                'national_id' => $this->national_id,
                'phone_fixed' => $this->phone_fixed,
                'phone_mobile' => $this->phone_mobile,
                'state_id' => $this->state_id,
                'municipality_id' => $this->municipality_id,
                'parish_id' => $this->parish_id,
                'city' => $this->city,
                'address' => $this->address,
                'entry_date' => $this->entry_date,
                'file_number' => $this->file_number,
                'cost_center_code' => $this->cost_center_code,
                'area_id' => $this->area_id,
                'assigned_post_id' => $this->assigned_post_id,
                'unit_id' => $this->unit_id,
                'position_id' => $this->position_id,
                'shift_id' => $this->shift_id,
                'payroll_type_id' => $this->payroll_type_id,
                'status' => $this->estadonomina, // Sincronizamos con el estado de nómina
                'estatus' => $this->estatus,
                'estadonomina' => $this->estadonomina,
            ]);

            if ($this->showVetSection) {
                Veterinarian::updateOrCreate(['employee_id' => $employee->id], [
                    'medical_college_code' => $this->medical_college_code,
                    'ministry_code' => $this->ministry_code,
                    'registration_status' => $this->registration_status ?? 'Activo',
                    'unit_id' => $this->unit_id,
                    'initials' => $this->vet_initials,
                ]);
            } else {
                Veterinarian::where('employee_id', $employee->id)->delete();
            }
        });

        $this->isModalOpen = false;
        $this->dispatch('notify', [
            'icon' => 'success',
            'title' => $this->employeeId ? 'Empleado actualizado' : 'Empleado creado con éxito'
        ]);
        $this->resetFields();
        $this->dispatch('close-modal', ['id' => 'employeeModal']);
    }

    public function delete($id)
    {
        $this->dispatch('confirm-delete', [
            'id' => $id,
            'title' => '¿Eliminar empleado?',
            'text' => 'Esta acción no se puede deshacer.',
            'target' => 'delete-employee-confirmed'
        ]);
    }

    #[On('delete-employee-confirmed')]
    public function deleteConfirmed($id)
    {
        $employee = Employee::find($id);
        if ($employee) {
            $employee->delete();
            $this->dispatch('notify', ['icon' => 'success', 'title' => 'Empleado eliminado']);
        } else {
            $this->dispatch('notify', ['icon' => 'warning', 'title' => 'Registro no encontrado', 'text' => 'El empleado ya no existe en el sistema.']);
        }
    }

    public function resetFields()
    {
        $this->reset([
            'employeeId', 'first_names', 'last_names', 'national_id', 'phone_fixed', 
            'phone_mobile', 'state_id', 'municipality_id', 'parish_id', 'city', 'address',
            'entry_date', 'file_number', 'cost_center_code', 'area_id', 'assigned_post_id', 'unit_id', 
            'position_id', 'shift_id', 'payroll_type_id', 'status', 'estatus', 'estadonomina', 'medical_college_code', 'ministry_code', 'registration_status', 'vet_initials', 'showVetSection'
        ]);
        $this->status = 'Activo';
        $this->estatus = 'Fijo';
        $this->estadonomina = 'Activo';
    }
}
