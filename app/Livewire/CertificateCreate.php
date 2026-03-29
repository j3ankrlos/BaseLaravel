<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\Certificate;
use App\Models\Veterinarian;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

use App\Models\Employee;
use App\Models\DeathCause;
use App\Models\DeathSystem;
use App\Models\DeathType;
use App\Models\AnimalStatus;
use App\Models\Barn;
use App\Models\BarnSection;
use Livewire\Attributes\Computed;
use App\Traits\HandlesDecimals;

class CertificateCreate extends Component
{
    use WithFileUploads;
    use HandlesDecimals;

    // Secciones del Formulario
    public $fecha_registro;
    
    // Datos del Veterinario
    public $vet_cedula;
    public $vet_nombre;
    public $vet_apellido;
    public $vet_colegio_medico_codigo;
    public $vet_ministerio_codigo;
    public $vet_area_reproduccion = 'Reproducción'; 

    // Datos del Animal
    public $animal_id;
    public $lote;
    public $raza;
    public $estatus;
    public $peso;
    public $sexo;
    public $nave;
    public $seccion;
    public $corral;
    public $tipo_muerte;
    public $causa_muerte;
    public $sistema_involucrado;
    public $reportado_por;
    public $fecha_muerte;
    public $evaluacion_externa;
    public $evaluacion_interna;

    // Búsqueda de Reportado Por
    public $reportadoSearch = '';
    public $reportadoResults = [];

    // Búsqueda de Causa de Muerte
    public $causeSearch = '';
    public $causeResults = [];

    // Búsqueda de Nave
    public $naveSearch = '';
    public $naveResults = [];

    // Evidencia Fotográfica
    public $arete_photo;
    public $tatuaje_photo;
    public $otra_photo;

    public function mount()
    {
        // Verificar roles (Spatie Permission check)
        if (!Auth::user()->hasAnyRole(['Super Admin', 'Admin', 'Veterinario'])) {
            abort(403, 'No tienes permiso para acceder a este módulo.');
        }

        $this->fecha_registro = Carbon::today()->format('Y-m-d');
        
        // Intentar pre-cargar datos si el usuario tiene un registro de veterinario
        $user = Auth::user();
        if ($user->employee) {
            $this->vet_cedula = $user->employee->national_id;
            
            // Usar primer nombre y primer apellido (Nombre Corto)
            $this->vet_nombre = explode(' ', trim($user->employee->first_names ?? ''))[0];
            $this->vet_apellido = explode(' ', trim($user->employee->last_names ?? ''))[0];
            
            // Buscar en la tabla de veterinarios si tiene códigos de colegio/ministerio
            $vetRecord = Veterinarian::where('employee_id', $user->employee->id)->first();
            if ($vetRecord) {
                $this->vet_colegio_medico_codigo = $vetRecord->medical_college_code;
                $this->vet_ministerio_codigo = $vetRecord->ministry_code;
            }
        }
    }

    public function updatedReportadoSearch($value)
    {
        if (strlen($value) < 2) {
            $this->reportadoResults = [];
            return;
        }

        $this->reportadoResults = Employee::whereHas('position', function($q) {
            $q->where('name', 'like', '%SUPERVISOR%')
              ->orWhere('name', 'like', '%ENCARGADO%')
              ->orWhere('name', 'like', '%ENCARGADA%')
              ->orWhere('name', 'like', '%MANAGER%');
        })
        ->where(function($q) use ($value) {
            $q->where('first_names', 'like', "%{$value}%")
              ->orWhere('last_names', 'like', "%{$value}%");
        })
        ->get()
        ->map(function($emp) {
            $f = explode(' ', trim($emp->first_names ?? ''))[0];
            $l = explode(' ', trim($emp->last_names ?? ''))[0];
            return [
                'name' => "{$f} {$l}",
                'position' => $emp->position->name
            ];
        })
        ->take(5);
    }

    public function selectReportado($name)
    {
        $this->reportado_por = $name;
        $this->reportadoSearch = $name;
        $this->reportadoResults = [];
    }

    public function updatedCauseSearch($value)
    {
        if (strlen($value) < 2) {
            $this->causeResults = [];
            return;
        }

        $this->causeResults = DeathCause::with('system')
            ->where('name', 'like', "%{$value}%")
            ->take(5)
            ->get();
    }

    public function selectCause($id)
    {
        $cause = DeathCause::with('system')->find($id);
        if ($cause) {
            $this->causa_muerte = $cause->name;
            $this->sistema_involucrado = $cause->system->name;
            $this->causeSearch = $cause->name;
        }
        $this->causeResults = [];
    }

    public function updatedNaveSearch($value)
    {
        if (strlen($value) < 1) {
            $this->naveResults = [];
            $this->nave = '';
            return;
        }

        $this->naveResults = Barn::where('name', 'like', "%{$value}%")
            ->orderBy('name')
            ->take(5)
            ->get();

        // Si lo que escribió coincide exactamente con una nave, la seleccionamos automáticamente
        $exact = Barn::where('name', strtoupper(trim($value)))->first();
        if ($exact) {
            $this->nave = $exact->name;
            $this->handleNaveAutoSelection($exact->name);
        }
    }

    public function selectNave($name)
    {
        $this->nave = $name;
        $this->naveSearch = $name;
        $this->naveResults = [];
        
        $this->handleNaveAutoSelection($name);
    }

    private function handleNaveAutoSelection($naveName)
    {
        $v = strtoupper(trim($naveName));
        
        // Auto-seleccionar Sección C para Naves de Pubertad y Lactancia
        if (str_starts_with($v, 'PUB') || str_starts_with($v, 'LA') || str_starts_with($v, 'LB') || str_starts_with($v, 'LE')) {
            $this->seccion = 'C';
        } else {
            $this->seccion = '';
        }
    }

    #[Computed]
    public function deathTypes()
    {
        return DeathType::orderBy('name')->get();
    }

    #[Computed]
    public function animalStatuses()
    {
        return AnimalStatus::orderBy('name')->get();
    }

    #[Computed]
    public function barns()
    {
        return Barn::orderBy('name')->get();
    }

    #[Computed]
    public function barnSections()
    {
        if (!$this->nave) return collect();
        
        // Find the barn by name to get its sections (since nave is saved as string)
        $barn = Barn::where('name', $this->nave)->first();
        return $barn ? $barn->sections()->orderBy('name')->get() : collect();
    }

    public function updatedNave($value)
    {
        $this->handleNaveAutoSelection($value);
    }

    protected $rules = [
        'fecha_registro' => 'required|date',
        'vet_cedula' => 'required|string',
        'vet_nombre' => 'required|string',
        'vet_apellido' => 'required|string',
        'vet_colegio_medico_codigo' => 'nullable|string',
        'vet_ministerio_codigo' => 'nullable|string',
        'vet_area_reproduccion' => 'required|string',
        'animal_id' => 'required|string',
        'lote' => 'required|string',
        'raza' => 'required|string',
        'estatus' => 'required|string',
        'peso' => 'required',
        'sexo' => 'required|string',
        'nave' => 'required|string',
        'seccion' => 'required|string',
        'corral' => 'required|string',
        'tipo_muerte' => 'required|string',
        'causa_muerte' => 'required|string',
        'sistema_involucrado' => 'required|string',
        'reportado_por' => 'required|string',
        'fecha_muerte' => 'required|date',
        'evaluacion_externa' => 'nullable|string',
        'evaluacion_interna' => 'nullable|string',
        'arete_photo' => 'nullable|image|max:2048',
        'tatuaje_photo' => 'nullable|image|max:2048',
        'otra_photo' => 'nullable|image|max:2048',
    ];

    public function validationAttributes()
    {
        return [
            'fecha_registro' => 'fecha de registro',
            'vet_cedula' => 'cédula del veterinario',
            'vet_nombre' => 'nombre del veterinario',
            'vet_apellido' => 'apellido del veterinario',
            'vet_area_reproduccion' => 'área de reproducción',
            'animal_id' => 'ID del animal',
            'lote' => 'lote',
            'raza' => 'raza',
            'estatus' => 'estatus',
            'peso' => 'peso',
            'sexo' => 'sexo',
            'nave' => 'nave',
            'seccion' => 'sección',
            'corral' => 'corral',
            'tipo_muerte' => 'tipo de muerte',
            'causa_muerte' => 'causa de muerte',
            'sistema_involucrado' => 'sistema involucrado',
            'reportado_por' => 'reportado por',
            'fecha_muerte' => 'fecha de muerte',
            'arete_photo' => 'foto de arete',
            'tatuaje_photo' => 'foto de tatuaje',
            'otra_photo' => 'otra foto',
        ];
    }

    public function save()
    {
        $this->validate();

        $aretePath = $this->arete_photo ? $this->arete_photo->store('certificates/photos', 'public') : null;
        $tatuajePath = $this->tatuaje_photo ? $this->tatuaje_photo->store('certificates/photos', 'public') : null;
        $otraPath = $this->otra_photo ? $this->otra_photo->store('certificates/photos', 'public') : null;

        $parsedPeso = $this->parseDecimal($this->peso);

        Certificate::create([
            'user_id' => Auth::id(),
            'fecha_registro' => $this->fecha_registro,
            'vet_cedula' => $this->vet_cedula,
            'vet_nombre' => $this->vet_nombre,
            'vet_apellido' => $this->vet_apellido,
            'vet_colegio_medico_codigo' => $this->vet_colegio_medico_codigo,
            'vet_ministerio_codigo' => $this->vet_ministerio_codigo,
            'vet_area_reproduccion' => $this->vet_area_reproduccion,
            'animal_id' => $this->animal_id,
            'lote' => $this->lote,
            'raza' => $this->raza,
            'estatus' => $this->estatus,
            'peso' => $parsedPeso,
            'sexo' => $this->sexo,
            'nave' => $this->nave,
            'seccion' => $this->seccion,
            'corral' => $this->corral,
            'tipo_muerte' => $this->tipo_muerte,
            'causa_muerte' => $this->causa_muerte,
            'sistema_involucrado' => $this->sistema_involucrado,
            'reportado_por' => $this->reportado_por,
            'fecha_muerte' => $this->fecha_muerte,
            'evaluacion_externa' => $this->evaluacion_externa,
            'evaluacion_interna' => $this->evaluacion_interna,
            'arete_photo_path' => $aretePath,
            'tatuaje_photo_path' => $tatuajePath,
            'otra_photo_path' => $otraPath,
        ]);

        \App\Models\ModuleUsage::track('certificates_create', 'Crear Certificado', '/certificates/create', 'ph-plus-circle', 'text-success');

        return redirect()->to('/certificates')->with('notify', [
            'icon' => 'success',
            'title' => 'Éxito',
            'text' => 'Certificado creado correctamente.'
        ]);
    }

    public function render()
    {
        return view('livewire.certificate-create')->title('Crear Certificado');
    }
}
