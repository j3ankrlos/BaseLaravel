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
use Livewire\Attributes\Computed;

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
    public $area_id, $assigned_post_id, $unit_id, $position_id, $shift_id, $status = 'Activo';

    // Datos exclusivos para Veterinarios
    public $medical_college_code, $ministry_code, $registration_status, $vet_initials;
    public $showVetSection = false;

    public $activeTab = 'personal'; 
    public $isModalOpen = false;
    public $importFile;

    public function mount()
    {
        \App\Models\ModuleUsage::track('employees', 'Gestión de Personal', '/employees', 'ph-briefcase', 'text-secondary');
    }

    // Sincronización con la URL
    protected $queryString = [
        'search' => ['except' => ''],
        'perPage' => ['except' => 10],
    ];

    /**
     * Resetea la página cuando se actualiza la búsqueda.
     */
    public function updatingSearch()
    {
        $this->resetPage();
    }

    /**
     * Cambia la pestaña activa.
     */
    public function setActiveTab($tab)
    {
        $this->activeTab = $tab;
    }

    /**
     * Sincroniza el Centro de Costo automáticamente cuando cambia el Área.
     */
    public function updatedAreaId($value)
    {
        if ($value) {
            $area = Area::find($value);
            $this->cost_center_code = $area ? $area->cost_center : '';
        }
        $this->activeTab = 'work'; // Seguir en la pestaña de trabajo al actualizar el centro de costo
    }

    public function updatedStateId($value)
    {
        $this->municipality_id = null;
        $this->parish_id = null;
        $this->activeTab = 'location'; // stay on location tab
    }

    public function updatedMunicipalityId($value)
    {
        $this->parish_id = null;
        $this->activeTab = 'location';
    }

    /**
     * Reacciona cuando cambia el Cargo (Position)
     */
    public function updatedPositionId($value)
    {
        if ($value) {
            $position = \App\Models\Position::find($value);
            // Only update if not manually toggled or if it matches the specific role
            if ($position && $position->name === 'MEDICO VETERINARIO') {
                $this->showVetSection = true;
            }
        }
        $this->activeTab = 'work';
    }



    #[Computed]
    public function areas() { return Area::all(); }
    #[Computed]
    public function assignedPosts() { return \App\Models\AssignedPost::all(); }
    #[Computed]
    public function positions() { return \App\Models\Position::where('active', true)->get(); }
    #[Computed]
    public function units() { return Unit::all(); }
    #[Computed]
    public function states() { return State::all(); }
    #[Computed]
    public function shifts() { return \App\Models\Shift::all(); }
    #[Computed]
    public function municipalities() { return $this->state_id ? Municipality::where('state_id', $this->state_id)->get() : []; }
    #[Computed]
    public function parishes() { return $this->municipality_id ? Parish::where('municipality_id', $this->municipality_id)->get() : []; }

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
            'areas' => $this->areas,
            'assignedPosts' => $this->assignedPosts,
            'positions' => $this->positions,
            'units' => $this->units,
            'states' => $this->states,
            'shifts' => $this->shifts,
            'municipalities' => $this->municipalities,
            'parishes' => $this->parishes,
        ])->title('Gestión de Personal');
    }

    /**
     * Prepara el formulario para crear un nuevo empleado.
     */
    public function create()
    {
        $this->resetFields();
        $this->activeTab = 'personal';
        $this->isModalOpen = true;
        $this->dispatch('open-modal', ['id' => 'employeeModal']);
    }

    /**
     * Carga los datos de un empleado para editar.
     */
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
        $this->status = $employee->status;

        // Cargar datos de veterinario si aplica
        $position = \App\Models\Position::find($this->position_id);
        $this->showVetSection = ($position && $position->name === 'MEDICO VETERINARIO');
        
        if ($this->showVetSection) {
            $vet = Veterinarian::where('employee_id', $employee->id)->first();
            // Si no existe pero el cargo es vet, inicializar
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

    /**
     * Guarda o actualiza un empleado.
     */

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
            'medical_college_code' => 'código del colegio médico',
            'ministry_code' => 'código del ministerio',
        ];
    }

    public function save()
    {
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
            'entry_date' => 'nullable|date',
        ];

        if ($this->showVetSection) {
            $rules['medical_college_code'] = 'nullable';
            $rules['ministry_code'] = 'nullable';
        }

        $this->validate($rules);

        \Illuminate\Support\Facades\DB::transaction(function () {
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
                'status' => $this->status,
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
                // Si ya no es veterinario, limpiar registro si existía
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

    /**
     * Dispara la confirmación de eliminación.
     */
    public function delete($id)
    {
        $this->dispatch('confirm-delete', [
            'id' => $id,
            'title' => '¿Eliminar empleado?',
            'text' => 'Esta acción no se puede deshacer.',
            'target' => 'delete-employee-confirmed'
        ]);
    }

    /**
     * Escucha el evento de confirmación para borrar definitivamente.
     */
    #[On('delete-employee-confirmed')]
    public function deleteConfirmed($id)
    {
        Employee::find($id)->delete();
        $this->dispatch('notify', ['icon' => 'success', 'title' => 'Empleado eliminado']);
    }

    /**
     * Limpia los campos del formulario.
     */
    public function resetFields()
    {
        $this->reset([
            'employeeId', 'first_names', 'last_names', 'national_id', 'phone_fixed', 
            'phone_mobile', 'state_id', 'municipality_id', 'parish_id', 'city', 'address',
            'entry_date', 'file_number', 'cost_center_code', 'area_id', 'assigned_post_id', 'unit_id', 
            'position_id', 'status', 'medical_college_code', 'ministry_code', 'registration_status', 'vet_initials', 'showVetSection', 'importFile'
        ]);
        $this->status = 'Activo';
    }

    /**
     * Importación masiva desde CSV
     */
    /**
     * Se ejecuta automáticamente cuando el archivo termina de subirse
     */
    public function updatedImportFile()
    {
        $this->importEmployees();
    }

    /**
     * Importación masiva desde CSV
     */
    public function importEmployees()
    {
        try {
            $this->validate([
                'importFile' => 'required|max:2048' // Quitamos mimes temporalmente para evitar bloqueos por tipo de archivo
            ]);

            $path = $this->importFile->getRealPath();
            
            // Detectar codificación y convertir a UTF-8 si es necesario
            $csvContent = file_get_contents($path);
            $encoding = mb_detect_encoding($csvContent, ['UTF-8', 'ISO-8859-1', 'Windows-1252'], true);
            if ($encoding && $encoding !== 'UTF-8') {
                $csvContent = mb_convert_encoding($csvContent, 'UTF-8', $encoding);
                file_put_contents($path, $csvContent);
            }

            // Detectar delimitador
            $firstLine = strtok($csvContent, "\n");
            $delimiter = (strpos($firstLine, ';') !== false) ? ';' : ',';
            
            $handle = fopen($path, 'r');
            
            // Omitir cabecera
            fgetcsv($handle, 0, $delimiter); 

            $count = 0;
            $errors = 0;
            $rowNumber = 1;

            \Illuminate\Support\Facades\DB::beginTransaction();

            while (($data = fgetcsv($handle, 0, $delimiter)) !== FALSE) {
                $rowNumber++;
                
                // Limpiar espacios y manejar nulos
                $data = array_map(function($item) {
                    return $item === "" ? null : trim($item);
                }, $data);
                
                if (count($data) < 4 || empty($data[3])) {
                    $errors++;
                    continue; 
                }

                $positionName = $data[13] ?? '';
                $areaName = $data[14] ?? '';
                $postName = $data[16] ?? '';
                
                $position = \App\Models\Position::where('name', $positionName)->first();
                $area = \App\Models\Area::where('name', $areaName)->first();
                $assignedPost = \App\Models\AssignedPost::where('name', $postName)->first();

                $entryDate = null;
                if (!empty($data[10])) {
                    try {
                        if (strpos($data[10], '/') !== false) {
                            $entryDate = \Carbon\Carbon::createFromFormat('d/m/Y', $data[10])->format('Y-m-d');
                        } else {
                            $entryDate = \Carbon\Carbon::parse($data[10])->format('Y-m-d');
                        }
                    } catch (\Exception $e) {
                        $entryDate = null;
                    }
                }

                Employee::updateOrCreate(
                    ['national_id' => $data[3]],
                    [
                        'first_names'      => $data[1] ?? 'S/N',
                        'last_names'       => $data[2] ?? 'S/N',
                        'phone_mobile'     => $data[4],
                        'state_id'         => is_numeric($data[5]) ? $data[5] : null,
                        'municipality_id'  => is_numeric($data[6]) ? $data[6] : null,
                        'parish_id'        => is_numeric($data[7]) ? $data[7] : null,
                        'city'             => $data[8],
                        'address'          => $data[9],
                        'entry_date'       => $entryDate,
                        'file_number'      => $data[11],
                        'payroll_type_id'  => is_numeric($data[12]) ? $data[12] : null,
                        'position_id'      => $position ? $position->id : null,
                        'area_id'          => $area ? $area->id : null,
                        'cost_center_code' => $data[15] ?? ($area ? $area->cost_center : null),
                        'assigned_post_id' => $assignedPost ? $assignedPost->id : null,
                        'status'           => 'Activo'
                    ]
                );
                $count++;
            }

            \Illuminate\Support\Facades\DB::commit();
            fclose($handle);
            
            $this->reset('importFile');
            $this->dispatch('notify', [
                'icon' => $errors > 0 ? 'warning' : 'success', 
                'title' => "Carga completa: $count procesados",
                'text' => $errors > 0 ? "Se omitieron $errors filas por datos inválidos." : ""
            ]);

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            if (isset($handle)) fclose($handle);
            
            \Illuminate\Support\Facades\Log::error("Error importando empleados: " . $e->getMessage());
            
            $this->dispatch('notify', [
                'icon' => 'error',
                'title' => 'Error en la importación',
                'text' => 'Ocurrió un error al procesar el archivo. Revisa el formato CSV.'
            ]);
        }
    }
}
