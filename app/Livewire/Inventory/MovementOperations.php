<?php

namespace App\Livewire\Inventory;

use App\Models\Animal;
use App\Models\Movement;
use App\Models\BarnSection;
use App\Models\Pen;
use App\Models\Stage;
use App\Models\Birth;
use App\Models\BirthDetail;
use App\Models\DeathCause;
use App\Models\Genetic;
use App\Models\Barn;
use Livewire\Component;
use Livewire\Attributes\Title;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use App\Traits\HandlesDecimals;

#[Title('Central de Operaciones')]
class MovementOperations extends Component
{
    use HandlesDecimals;

    public $activeTab = 'ingresos'; // ingresos, movimientos, celos, activaciones, venta, mortalidad
    public $ingresoType = 'RECRIA'; // RECRIA, LEVANTE, PUBERTAD
    public $operation_date;
    public $operation_pic;

    // Collections
    public $stages = [];
    public $barnSections = [];
    public $pens = [];
    public $activeInventory = [];
    public $births = [];
    public $rooms = []; // Lista de salas con partos pendientes
    public $deathCauses = [];
    public $genetics = [];
    public $barns = [];
    public $recriaAvailableSections = []; // Secciones de Recría con su estado

    // INGRESOS Form
    public $i_source_id; // ID de Birth (si Recria y es individual, o Inventory)
    public $i_genetic_id; // Genética seleccionada para filtrar
    public $i_management_lot = 'PROC'; // Lote de manejo, default PROC
    public $i_selected_room; // Sala seleccionada para filtrar partos
    public $i_birth_details = []; // Detalles de partos de la sala
    public $i_selected_details = []; // IDs de BirthDetail seleccionados
    public $i_all_selected = false;
    public $i_discard_remaining = false; // Opción para descartar lechones sobrantes

    // LEVANTE form (Recría → Levante)
    public $l_genetic_id;
    public $l_selected_lot;   // Management lot como "sala" para filtrar
    public $l_lots = [];      // Lotes únicos disponibles según genética
    public $l_animal_list = [];    // Animales individuales en Recría (para no-F1)
    public $l_selected_animals = []; // IDs de Animal seleccionados
    public $l_all_selected = false;
    
    public $i_target_stage_id;
    public $i_barn_id;
    public $i_barn_section_id;
    public $i_pen_id;
    public $i_pen_name;
    public $i_quantity;
    public $i_weight;
    public $i_feed_type;
    public $allFeedTypes = ['Pre-inic', 'Pre-inic-0', 'Iniciador', 'Lech-1', 'Lech-2'];
    // Individualization fields (for Pubertad)
    public $i_prefix_id = 'F1-T-';
    public $i_start_correlative = 1;

    // MOVIMIENTOS Form
    public $m_animal_id;
    public $m_barn_id;
    public $m_barn_section_id;
    public $m_pen_id;
    public $m_pen_name;
    public $m_quantity;

    // CELOS Form
    public $c_animal_id;
    public $c_note;

    // ACTIVACIONES Form
    public $act_animal_id;
    public $act_boar;

    // VENTA Form
    public $v_animal_id;
    public $v_quantity;
    public $v_weight;
    public $v_invoice;

    // MORTALIDAD Form
    public $d_animal_id;
    public $d_quantity;
    public $d_death_cause_id;
    public $d_note;

    // PROMOCIÓN MACHOS Form
    public $p_animal_id;
    public $p_target_role; // STUD, CELADOR_DM1, CELADOR_DM2

    public function mount()
    {
        $this->stages = Stage::orderBy('order')->get();
        $this->barns = Barn::orderBy('name')->get();
        $this->barnSections = BarnSection::with('barn')->get();
        $this->pens = Pen::with('barnSection.barn')->get();
        $this->deathCauses = DeathCause::orderBy('name')->get();
        $this->genetics = Genetic::all();
        $this->operation_date = now()->format('Y-m-d');
        $picData = \App\Services\PicDateService::fromDate(now());
        $this->operation_pic = $picData['vuelta'] . '-' . str_pad($picData['pic'], 3, '0', STR_PAD_LEFT);
        
        $this->i_genetic_id = 7; // Default F1
        $this->loadInventory();
        $this->loadBirths();

        // Inicializar Nave según el tipo de ingreso por defecto (RECRIA)
        $this->updatedIngresoType();
    }

    public function updatedOperationDate($value)
    {
        if ($value) {
            $date = Carbon::parse($value);
            $picData = \App\Services\PicDateService::fromDate($date);
            $this->operation_pic = $picData['vuelta'] . '-' . str_pad($picData['pic'], 3, '0', STR_PAD_LEFT);
        }
    }

    public function updatedOperationPic($value)
    {
        if ($value && str_contains($value, '-')) {
            $parts = explode('-', $value);
            if (count($parts) === 2 && is_numeric($parts[0]) && is_numeric($parts[1])) {
                $date = \App\Services\PicDateService::toDate((int)$parts[0], (int)$parts[1]);
                $this->operation_date = $date->format('Y-m-d');
            }
        }
    }

    public function loadInventory()
    {
        $this->activeInventory = Animal::with(['stage', 'barnSection', 'pen', 'genetic'])
            ->where('status', 'Activo')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function loadBirths()
    {
        // Traer partos que no se han destetado
        $birthQuery = Birth::where('estado', 2); // 2: Activa

        // Para la lista de animales individuales, siempre filtramos por genética
        $individualBirthQuery = (clone $birthQuery);
        if ($this->i_genetic_id) {
            $individualBirthQuery->where('genetic_id', $this->i_genetic_id);
        }
            
        $this->births = $individualBirthQuery->with(['genetic', 'responsible'])
            ->orderBy('room')
            ->get();
            
        // Lista de salas únicas para el selector
        // REGLA: Si es F1 (ID 7) o F1-T (ID 8), mostramos TODAS las salas activas.
        // Si es cualquier otra genética, filtramos salas que tengan esa genética.
        $roomQuery = (clone $birthQuery);
        if ($this->i_genetic_id && $this->i_genetic_id != 7) {
            $roomQuery->where('genetic_id', $this->i_genetic_id);
        }

        $this->rooms = $roomQuery->distinct()->orderBy('room')->pluck('room');
    }

    public function updatedIGeneticId()
    {
        if ($this->ingresoType == 'RECRIA') {
            $this->i_selected_room = null;
            $this->i_birth_details = [];
            $this->i_selected_details = [];
            $this->i_quantity = 0;
            $this->loadBirths();
        } elseif ($this->ingresoType == 'LEVANTE') {
            $this->l_genetic_id = $this->i_genetic_id;
            $this->l_selected_lot = null;
            $this->l_animal_list = [];
            $this->l_selected_animals = [];
            $this->l_all_selected = false;
            $this->i_quantity = 0;
            $this->loadLevanteAnimals();
        }
    }

    public function loadLevanteAnimals()
    {
        $stageRecria = Stage::where('name', 'Recría')->first();
        if (!$stageRecria) return;

        $query = Animal::where('status', 'Activo')
            ->where('stage_id', $stageRecria->id);

        if ($this->l_genetic_id) {
            $query->where('genetic_id', $this->l_genetic_id);
        }

        $animals = $query->with(['genetic'])->orderBy('management_lot')->get();

        // Lotes únicos con suma de cantidades
        $this->l_lots = $animals
            ->filter(fn($a) => !empty($a->management_lot))
            ->groupBy('management_lot')
            ->map(fn($group) => ['lot' => $group->first()->management_lot, 'total' => $group->sum('quantity')])
            ->values()
            ->toArray();

        // Si ya hay un lote seleccionado, cargar sus animales
        if ($this->l_selected_lot) {
            $this->updatedLSelectedLot($this->l_selected_lot);
        } else {
            $this->l_animal_list = [];
            $this->l_selected_animals = [];
        }
    }

    public function updatedLGeneticId()
    {
        $this->l_selected_lot = null;
        $this->l_animal_list = [];
        $this->l_selected_animals = [];
        $this->l_all_selected = false;
        $this->i_quantity = 0;
        $this->loadLevanteAnimals();
    }

    public function updatedLSelectedLot($value)
    {
        $this->l_selected_animals = [];
        $this->l_all_selected = false;

        if (!$value) {
            $this->l_animal_list = [];
            $this->i_quantity = 0;
            return;
        }

        $isF1 = $this->l_genetic_id == 7;

        if ($isF1) {
            // Modo lote: solo sumamos el total
            $stageRecria = Stage::where('name', 'Recría')->value('id');
            $this->i_quantity = Animal::where('management_lot', $value)
                ->where('stage_id', $stageRecria)
                ->where('status', 'Activo')
                ->where('genetic_id', $this->l_genetic_id)
                ->sum('quantity');
            $this->l_animal_list = [];
        } else {
            // Modo individual: cargar lista de animales
            $stageRecria = Stage::where('name', 'Recría')->value('id');
            $this->l_animal_list = Animal::where('management_lot', $value)
                ->where('stage_id', $stageRecria)
                ->where('status', 'Activo')
                ->where('genetic_id', $this->l_genetic_id)
                ->with(['genetic'])
                ->get()
                ->toArray();

            $this->i_quantity = 0;

            if (empty($this->l_animal_list)) {
                $this->dispatch('notify', ['icon' => 'info', 'title' => 'Sin animales', 'text' => 'No hay animales de esta genética en el lote seleccionado.']);
            }
        }
    }

    public function updatedLAllSelected($value)
    {
        if ($value) {
            $this->l_selected_animals = collect($this->l_animal_list)->pluck('id')->map(fn($id) => (string)$id)->toArray();
        } else {
            $this->l_selected_animals = [];
        }
        $this->i_quantity = count($this->l_selected_animals);
    }

    public function updatedLSelectedAnimals()
    {
        $this->i_quantity = count($this->l_selected_animals);
        $this->l_all_selected = count($this->l_selected_animals) === count($this->l_animal_list) && count($this->l_animal_list) > 0;
    }

    public function setTab($tab)
    {
        $this->activeTab = $tab;
        $this->resetValidation();
    }

    public function setIngresoType($type)
    {
        $this->ingresoType = $type;
        $this->i_source_id = null;
        $this->resetValidation();
    }

    public function updatedISourceId($value)
    {
        if ($value) {
            if ($this->ingresoType == 'RECRIA') {
                $birth = Birth::find($value);
                if ($birth) {
                    $this->i_quantity = $birth->quantity;
                }
            } elseif ($this->ingresoType == 'LEVANTE' || $this->ingresoType == 'PUBERTAD') {
                $stageName = $this->ingresoType == 'LEVANTE' ? 'Recría' : 'Levante';
                $stageId = Stage::where('name', $stageName)->value('id');
                
                $qty = Animal::where('management_lot', $value)
                            ->where('stage_id', $stageId)
                            ->where('status', 'Activo')
                            ->sum('quantity');
                $this->i_quantity = $qty;
            }
        } else {
            $this->i_quantity = null;
        }
    }

    public function updatedIngresoType()
    {
        $this->i_source_id = null;
        $this->i_management_lot = 'PROC';
        $this->i_quantity = 0;
        $this->i_selected_room = null;
        $this->i_birth_details = [];
        $this->i_selected_details = [];
        $this->i_all_selected = false;
        // Reset Levante
        $this->l_genetic_id = null;
        $this->l_selected_lot = null;
        $this->l_animal_list = [];
        $this->l_selected_animals = [];
        $this->l_all_selected = false;

        if ($this->ingresoType == 'RECRIA') {
            $this->i_genetic_id = 7;
            $this->loadBirths();
        } elseif ($this->ingresoType == 'LEVANTE') {
            $this->i_genetic_id = 7;
            $this->l_genetic_id = 7;
            $this->loadLevanteAnimals();
        } else {
            $this->i_genetic_id = null;
        }
        
        // Auto-seleccionar Nave basándose en el tipo de ingreso
        $barnMapping = [
            'RECRIA' => 'RECRIA',
            'LEVANTE' => 'LA',
            'PUBERTAD' => 'PUB1',
        ];

        $targetName = $barnMapping[$this->ingresoType] ?? $this->ingresoType;
        $targetBarn = Barn::where('name', $targetName)->first();

        if ($targetBarn) {
            $this->i_barn_id = $targetBarn->id;
            $this->i_pen_id = null;
            $this->i_pen_name = null;

            if ($this->ingresoType == 'RECRIA') {
                $this->loadRecriaSection($targetBarn->id);
            } else {
                $this->i_barn_section_id = null;
            }
        } else {
            $this->i_barn_id = null;
            $this->i_barn_section_id = null;
        }

        // Auto-asignar tipo de alimento según el destino
        $this->updateFeedType();
    }

    private function updateFeedType()
    {
        $barnName = null;
        if ($this->i_barn_id) {
            $barn = Barn::find($this->i_barn_id);
            $barnName = $barn?->name;
        }

        if ($this->ingresoType == 'RECRIA') {
            // Predeterminado para Recría
            $this->i_feed_type = 'Pre-inic'; 
        } else {
            $this->i_feed_type = $this->getFeedTypeByLocation($barnName);
        }

        // Asegurar que si el alimento actual no está en la lista disponible del área, se asigne el primero disponible
        $available = $this->getAvailableFeedTypes($barnName ?: ($this->ingresoType == 'RECRIA' ? 'RECRIA' : null));
        if (!in_array($this->i_feed_type, $available)) {
            $this->i_feed_type = $available[0] ?? null;
        }
    }

    public function getAvailableFeedTypes($location)
    {
        if (empty($location)) return $this->allFeedTypes;

        if ($location === 'RECRIA') {
            return ['Pre-inic', 'Pre-inic-0', 'Iniciador'];
        }

        if (in_array($location, ['LB', 'LA', 'LE', 'PUB3', 'PUB2-D'])) {
            return ['Lech-1'];
        }

        if (in_array($location, ['MP1', 'MP2', 'PUB1', 'PUB2'])) {
            return ['Lech-2'];
        }

        return $this->allFeedTypes;
    }

    private function getFeedTypeByLocation($location)
    {
        if (empty($location)) return null;

        $mapping = [
            'RECRIA' => 'Pre-inic', // Base, se ajusta según edad/peso en realidad
            'LB'     => 'Lech-1',
            'LA'     => 'Lech-1',
            'LE'     => 'Lech-1',
            'MP1'    => 'Lech-2',
            'PUB1'   => 'Lech-2',
            'MP2'    => 'Lech-2',
            'PUB2'   => 'Lech-2',
            'PUB3'   => 'Lech-1',
            'PUB2-D' => 'Lech-1',
        ];

        return $mapping[$location] ?? null;
    }

    /**
     * Calcula las secciones disponibles en Recría y pre-selecciona
     * la que está actualmente en proceso (PROC).
     */
    public function loadRecriaSection(int $barnId): void
    {
        $recria  = Stage::where('name', 'Recría')->first();
        $allSections = BarnSection::where('barn_id', $barnId)->orderBy('name')->get();

        // Secciones que tienen animales ACTIVOS en Recría
        $activeSectionIds = Animal::where('stage_id', $recria?->id)
            ->where('status', 'Activo')
            ->whereNotNull('barn_section_id')
            ->pluck('barn_section_id')
            ->unique();

        // Sección en PROC (lote abierto = en proceso)
        $procSectionId = Animal::where('stage_id', $recria?->id)
            ->where('status', 'Activo')
            ->where('management_lot', 'PROC')
            ->whereNotNull('barn_section_id')
            ->value('barn_section_id');

        // Construir lista enriquecida de secciones
        $this->recriaAvailableSections = $allSections->map(function ($section) use ($activeSectionIds, $procSectionId) {
            $isProc   = $section->id === $procSectionId;
            $isFull   = !$isProc && $activeSectionIds->contains($section->id);
            return [
                'id'        => $section->id,
                'name'      => $section->name,
                'is_proc'   => $isProc,    // Sección actualmente en proceso
                'is_full'   => $isFull,    // Sección llena con lote cerrado
                'available' => !$isFull,   // Se puede seleccionar
            ];
        })->values()->toArray();

        // Pre-seleccionar: primero PROC (si existe), luego la primera disponible
        if ($procSectionId) {
            $this->i_barn_section_id = $procSectionId;
        } else {
            $firstAvailable = collect($this->recriaAvailableSections)->first(fn($s) => $s['available']);
            $this->i_barn_section_id = $firstAvailable['id'] ?? null;
        }
    }

    public function updatedISelectedRoom($value)
    {
        if ($value && $this->ingresoType == 'RECRIA') {
            // Solo F1 (ID 7) entra por lote. F1-T (ID 8) entra por individuo.
            $isF1Batch = $this->i_genetic_id == 7;

            if ($isF1Batch) {
                // F1 no tiene partos registrados, el usuario ingresa la cantidad libremente
                $this->i_quantity = 0;
            } else {
                // Cargar individuos de partos en esa sala que no estén movidos (status MATERNIDAD)
                $this->i_birth_details = BirthDetail::whereHas('birth', function($q) use ($value) {
                    $q->where('room', $value)
                      ->where('genetic_id', $this->i_genetic_id)
                      ->where('estado', 2);
                })
                ->where('status', 'MATERNIDAD')
                ->with(['birth.genetic', 'birth.responsible'])
                ->get()
                ->toArray();

                if (empty($this->i_birth_details)) {
                    $this->dispatch('notify', ['icon' => 'info', 'title' => 'Sin animales', 'text' => 'No hay animales disponibles de esta genética en la sala seleccionada.']);
                }
            }
            
            $this->i_selected_details = [];
            $this->i_all_selected = false;
            $this->syncDiscardRemaining();
        } else {
            $this->i_birth_details = [];
            $this->i_quantity = 0;
            $this->i_discard_remaining = false;
        }
    }

    public function updatedIAllSelected($value)
    {
        if ($value) {
            $this->i_selected_details = collect($this->i_birth_details)->pluck('id')->map(fn($id) => (string)$id)->toArray();
        } else {
            $this->i_selected_details = [];
        }
        $this->i_quantity = count($this->i_selected_details);
        $this->syncDiscardRemaining();
    }

    public function updatedISelectedDetails()
    {
        $this->i_quantity = count($this->i_selected_details);
        $this->i_all_selected = count($this->i_selected_details) === count($this->i_birth_details) && count($this->i_birth_details) > 0;
        $this->syncDiscardRemaining();
    }

    public function updatedIQuantity()
    {
        if ($this->ingresoType == 'RECRIA' && in_array($this->i_genetic_id, [7, 8])) {
            $this->syncDiscardRemaining();
        }
    }

    private function syncDiscardRemaining()
    {
        if ($this->ingresoType !== 'RECRIA' || !$this->i_selected_room) {
            $this->i_discard_remaining = false;
            return;
        }

        $isF1Batch = $this->i_genetic_id == 7;

        if ($isF1Batch) {
            // F1 no maneja descartes ni cierres automáticos de sala
            $this->i_discard_remaining = false;
        } else {
            $totalInRoom = count($this->i_birth_details);
            $selected = count($this->i_selected_details);

            // Si selecciona algunos pero no todos, activar descarte automáticamente
            $this->i_discard_remaining = ($selected > 0 && $selected < $totalInRoom);
        }
    }

    // Handlers
    public function updatedIBarnId() 
    { 
        $this->i_barn_section_id = null; 
        $this->i_pen_id = null; 
        $this->i_pen_name = null; 

        if ($this->i_barn_id) {
            $barn = Barn::find($this->i_barn_id);
            if ($barn && in_array($barn->name, ['LA', 'LB', 'LE'])) {
                // firstOrCreate: si la sección 'C' no existe para esta nave, la crea automáticamente
                $section = BarnSection::firstOrCreate(
                    ['barn_id' => $this->i_barn_id, 'name' => 'C'],
                    ['description' => 'Sección C - ' . $barn->name]
                );
                $this->i_barn_section_id = $section->id;
            }
        }
        
        // Auto-asignar tipo de alimento según el destino
        $this->updateFeedType();
    }
    public function updatedIBarnSectionId() { $this->i_pen_id = null; $this->i_pen_name = null; }
    public function updatedMBarnId() { $this->m_barn_section_id = null; $this->m_pen_id = null; $this->m_pen_name = null; }
    public function updatedMBarnSectionId() { $this->m_pen_id = null; $this->m_pen_name = null; }

    private function resolvePenId($sectionId, $penName)
    {
        if (empty($penName)) return null;
        
        $pen = Pen::firstOrCreate([
            'barn_section_id' => $sectionId,
            'name' => trim($penName)
        ]);
        
        return $pen->id;
    }

    public function processIngreso()
    {
        if ($this->ingresoType == 'RECRIA') {
            $isF1Batch = $this->i_genetic_id == 7;

            // Reglas base para Recría
            $rules = [
                'i_genetic_id'      => 'required',
                'i_selected_room'   => 'required',
                'i_barn_section_id' => 'required',
                'i_pen_name'        => 'required',
                'i_management_lot'  => 'required',
                'i_feed_type'       => 'required',
            ];

            // Si es F1, validar campos de lote
            if ($isF1Batch) {
                $rules['i_quantity'] = 'required|numeric|min:1';
                $rules['i_weight']   = 'required';
            }

            $this->validate($rules, [
                'i_genetic_id.required'      => 'Seleccione la genética',
                'i_selected_room.required'   => 'Seleccione la sala de origen',
                'i_barn_section_id.required' => 'Seleccione la nave de destino',
                'i_pen_name.required'        => 'Ingrese el corral',
                'i_management_lot.required'  => 'Ingrese el lote de manejo',
                'i_feed_type.required'       => 'Seleccione el tipo de alimento',
                'i_quantity.required'        => 'Cargue la cantidad de F1',
                'i_quantity.min'             => 'La cantidad debe ser mayor a cero',
                'i_weight.required'          => 'Cargue el peso promedio',
            ]);

            // Si NO es F1, validar que haya selección en la tabla de partos
            if (!$isF1Batch && empty($this->i_selected_details)) {
                $this->dispatch('notify', [
                    'icon'  => 'error', 
                    'title' => 'Falta Selección', 
                    'text'  => 'Debe seleccionar al menos un animal de la tabla de partos para continuar.'
                ]);
                return;
            }

            $stage = Stage::where('name', 'Recría')->first();
            if (!$stage) {
                $this->dispatch('notify', ['icon' => 'error', 'title' => 'Error', 'text' => 'La etapa "Recría" no existe en la base de datos.']);
                return;
            }

            DB::beginTransaction();
            try {
                if ($isF1Batch) {
                    // Obtener datos de referencia (PIC Cycle/Day) del primer parto activo en esa sala
                    $refBirth = Birth::where('room', $this->i_selected_room)->where('estado', 2)->first();
                    $picCycle = $refBirth ? $refBirth->pic_cycle : 0;
                    $picDay = $refBirth ? $refBirth->pic_day : 0;

                    // Calcular edad Pic actual menos lote
                    $currentPicData = \App\Services\PicDateService::fromDate($this->operation_date);
                    $currentPicTotal = $currentPicData['total_days'];
                    $lotNum = is_numeric($this->i_management_lot) ? (int)$this->i_management_lot : 0;
                    $ageDays = $lotNum > 0 ? ($currentPicTotal - $lotNum) : 0;
                    
                    $sourcePrefix = 'S' . str_pad($this->i_selected_room, 2, '0', STR_PAD_LEFT);
                    $loteSap = $sourcePrefix . 'EXP' . $ageDays . '0'; // 0 para F1

                    $parsedWeight = $this->parseDecimal($this->i_weight);
                    $penId = $this->resolvePenId($this->i_barn_section_id, $this->i_pen_name);

                    // Crear nuevo lote F1 (Registros separados en DB por petición del usuario)
                    $animal = Animal::create([
                        'type'            => 'LOTE',
                        'primera'         => null,
                        'cola'            => null,
                        'saman'           => null,
                        'quantity'        => $this->i_quantity,
                        'entry_date'      => $this->operation_date,
                        'pic_cycle'       => $picCycle,
                        'pic_day'         => $picDay,
                        'source'          => $sourcePrefix,
                        'management_lot'  => $this->i_management_lot,
                        'genetic_id'      => $this->i_genetic_id,
                        'sex'             => 'Hembra',
                        'barn_id'         => $this->i_barn_id,
                        'barn_section_id' => $this->i_barn_section_id,
                        'pen_id'          => $penId,
                        'stage_id'        => $stage->id,
                        'status'          => 'Activo',
                        'lote_sap'        => $loteSap,
                        'age_days'        => $ageDays,
                        'farm'            => 'EXP',
                        'weight'          => $parsedWeight,
                        'feed_type'       => $this->i_feed_type,
                    ]);

                    Movement::create([
                        'user_id'            => auth()->id(),
                        'animal_id'          => $animal->id,
                        'movement_date'      => $this->operation_date,
                        'movement_type'      => 'INGRESO_RECRIA',
                        'quantity'           => $this->i_quantity,
                        'to_barn_section_id' => $this->i_barn_section_id,
                        'to_pen_id'          => $animal->pen_id,
                        'to_stage_id'        => $stage->id,
                        'pic_cycle'          => $picCycle,
                        'pic_day'            => $picDay,
                        'note'               => 'Ingreso Lote F1 - Sala ' . $this->i_selected_room,
                    ]);
                } else {
                    // Creación agrupada por genética (Tradicional)
                    $details = BirthDetail::whereIn('id', $this->i_selected_details)
                        ->with('birth.genetic')
                        ->get();

                    foreach ($details as $detail) {
                        $birth = $detail->birth;

                        $currentPicData = \App\Services\PicDateService::fromDate($this->operation_date);
                        $currentPicTotal = $currentPicData['total_days'];
                        $lotNum = is_numeric($this->i_management_lot) ? (int)$this->i_management_lot : 0;
                        $ageDays = $lotNum > 0 ? ($currentPicTotal - $lotNum) : 0;

                        $sourcePrefix = 'S' . str_pad($this->i_selected_room, 2, '0', STR_PAD_LEFT);
                        $loteSap = $sourcePrefix . 'EXP' . $ageDays . $detail->generated_id;

                        $parsedWeight = $this->parseDecimal($this->i_weight);

                        $animal = Animal::create([
                            'type'            => 'INDIVIDUO',
                            'primera'         => null,
                            'cola'            => null,
                            'saman'           => null,
                            'quantity'        => 1,
                            'entry_date'      => $this->operation_date,
                            'pic_cycle'       => $birth->pic_cycle,
                            'pic_day'         => $birth->pic_day,
                            'source'          => $sourcePrefix,
                            'management_lot'  => $this->i_management_lot,
                            'internal_id'     => $detail->generated_id, 
                            'genetic_id'      => $birth->genetic_id,
                            'sex'             => $detail->sex,
                            'barn_id'         => $this->i_barn_id,
                            'barn_section_id' => $this->i_barn_section_id,
                            'pen_id'          => $this->resolvePenId($this->i_barn_section_id, $this->i_pen_name),
                            'stage_id'        => $stage->id,
                            'status'          => 'Activo',
                            'lote_sap'        => $loteSap,
                            'age_days'        => $ageDays,
                            'farm'            => 'EXP',
                            'weight'          => $parsedWeight,
                            'feed_type'       => $this->i_feed_type,
                        ]);

                        Movement::create([
                            'user_id'            => auth()->id(),
                            'animal_id'          => $animal->id,
                            'movement_date'      => $this->operation_date,
                            'pic_cycle'          => $birth->pic_cycle,
                            'pic_day'            => $birth->pic_day,
                            'movement_type'      => 'INGRESO_RECRIA',
                            'quantity'           => 1,
                            'to_barn_section_id' => $this->i_barn_section_id,
                            'to_pen_id'          => $animal->pen_id,
                            'to_stage_id'        => $stage->id,
                            'note'               => 'Ingreso Individual - Arete: ' . $detail->generated_id,
                        ]);

                        // Actualizar status del lechón individual
                        $detail->update([
                            'status'    => 'RECRIA',
                            'animal_id' => $animal->id
                        ]);
                    }

                    // Check and update Birth status if all details moved or discard remaining
                    $birthIds = $details->pluck('birth_id')->unique();
                    foreach($birthIds as $bId) {
                        $remainingCount = BirthDetail::where('birth_id', $bId)->where('status', 'MATERNIDAD')->count();
                        
                        if ($remainingCount === 0) {
                            Birth::where('id', $bId)->update(['estado' => 1]); // 1: Destetada
                        } elseif ($this->i_discard_remaining) {
                            // Marcar lechones restantes como descartados
                            BirthDetail::where('birth_id', $bId)
                                ->where('status', 'MATERNIDAD')
                                ->update(['status' => 'DESCARTADO']);
                                
                            Birth::where('id', $bId)->update(['estado' => 3]); // 3: Descarte
                        }
                    }
                }

                DB::commit();

                // ============================================================
                // ACTUALIZACIÓN MASIVA DE LOTE (CIERRE SEMANAL)
                // Si el lote ingresado NO es PROC, significa que es el cierre
                // definitivo de la semana. Se actualizan TODOS los animales en
                // Recría que estaban marcados como PROC, sin importar la genética.
                // Esto unifica York, Duroc, Landrace, F1, F1-T bajo un mismo lote.
                // ============================================================
                if ($this->i_management_lot !== 'PROC') {
                    $updatedCount = Animal::where('stage_id', $stage->id)
                        ->where('management_lot', 'PROC')
                        ->where('status', 'Activo')
                        ->update(['management_lot' => $this->i_management_lot]);

                    $this->dispatch('notify', ['icon' => 'success', 'title' => 'Lote Cerrado', 'text' => 'Ingreso completado. ' . $updatedCount . ' registros actualizados al lote ' . $this->i_management_lot . '.']);
                } else {
                    $this->dispatch('notify', ['icon' => 'success', 'title' => 'Ingreso Exitoso', 'text' => 'Animales ingresados a Recría con lote PROC.']);
                }

                $this->resetFields();
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error($e->getMessage());
                $this->dispatch('notify', ['icon' => 'error', 'title' => 'Error', 'text' => $e->getMessage()]);
            }
        } elseif ($this->ingresoType == 'LEVANTE') {
            $isF1 = $this->l_genetic_id == 7;

            // Validación
            $rules = [
                'l_genetic_id'      => 'required',
                'l_selected_lot'    => 'required',
                'i_barn_section_id' => 'required',
                'i_pen_name'        => 'required',
                'i_weight'          => 'required',
                'i_feed_type'       => 'required',
                'i_quantity'        => 'required|numeric|min:1',
            ];
            if (!$isF1) {
                $rules['l_selected_animals'] = 'required|array|min:1';
            }
            $this->validate($rules);

            $stageRecria  = Stage::where('name', 'Recría')->firstOrFail();
            $stageLevante = Stage::where('name', 'Levante')->firstOrFail();
            $parsedWeight = $this->parseDecimal($this->i_weight);

            DB::beginTransaction();
            try {
                if ($isF1) {
                    // ── MODO LOTE (F1): consumir por cantidad ──────────────────
                    $items = Animal::where('management_lot', $this->l_selected_lot)
                        ->where('stage_id', $stageRecria->id)
                        ->where('status', 'Activo')
                        ->where('genetic_id', $this->l_genetic_id)
                        ->get();

                    $totalAvailable = $items->sum('quantity');
                    if ($this->i_quantity > $totalAvailable) {
                        $this->addError('i_quantity', 'Cantidad superior a la disponible (' . $totalAvailable . ').');
                        DB::rollBack();
                        return;
                    }

                    $qtyToMove = $this->i_quantity;
                    foreach ($items as $item) {
                        if ($qtyToMove <= 0) break;
                        $moveQty = min($item->quantity, $qtyToMove);

                        if ($moveQty < $item->quantity) {
                            $newItem = $item->replicate();
                            $newItem->quantity        = $moveQty;
                            $newItem->stage_id        = $stageLevante->id;
                            $newItem->barn_id         = $this->i_barn_id;
                            $newItem->barn_section_id = $this->i_barn_section_id;
                            $newItem->pen_id          = $this->resolvePenId($this->i_barn_section_id, $this->i_pen_name);
                            $newItem->feed_type       = $this->i_feed_type;
                            $newItem->weight          = $parsedWeight;
                            $newItem->save();
                            $item->quantity -= $moveQty;
                            $item->save();
                            $inventoryId = $newItem->id;
                        } else {
                            $item->stage_id        = $stageLevante->id;
                            $item->barn_id         = $this->i_barn_id;
                            $item->barn_section_id = $this->i_barn_section_id;
                            $item->pen_id          = $this->resolvePenId($this->i_barn_section_id, $this->i_pen_name);
                            $item->feed_type       = $this->i_feed_type;
                            $item->weight          = $parsedWeight;
                            $item->save();
                            $inventoryId = $item->id;
                        }

                        Movement::create([
                            'user_id'            => auth()->id(),
                            'animal_id'          => $inventoryId,
                            'movement_date'      => $this->operation_date,
                            'pic_cycle'          => $item->pic_cycle,
                            'pic_day'            => $item->pic_day,
                            'movement_type'      => 'INGRESO_LEVANTE',
                            'quantity'           => $moveQty,
                            'from_stage_id'      => $stageRecria->id,
                            'to_stage_id'        => $stageLevante->id,
                            'to_barn_section_id' => $this->i_barn_section_id,
                            'to_pen_id'          => $this->resolvePenId($this->i_barn_section_id, $this->i_pen_name),
                            'note'               => 'Ingreso Lote F1 - Lote de Manejo: ' . $this->l_selected_lot,
                        ]);

                        $qtyToMove -= $moveQty;
                    }

                } else {
                    // ── MODO INDIVIDUAL: mover animales seleccionados ──────────
                    $animals = Animal::whereIn('id', $this->l_selected_animals)
                        ->where('status', 'Activo')
                        ->get();

                    foreach ($animals as $animal) {
                        $animal->stage_id        = $stageLevante->id;
                        $animal->barn_id         = $this->i_barn_id;
                        $animal->barn_section_id = $this->i_barn_section_id;
                        $animal->pen_id          = $this->resolvePenId($this->i_barn_section_id, $this->i_pen_name);
                        $animal->feed_type       = $this->i_feed_type;
                        $animal->weight          = $parsedWeight;
                        $animal->save();

                        Movement::create([
                            'user_id'            => auth()->id(),
                            'animal_id'          => $animal->id,
                            'movement_date'      => $this->operation_date,
                            'pic_cycle'          => $animal->pic_cycle,
                            'pic_day'            => $animal->pic_day,
                            'movement_type'      => 'INGRESO_LEVANTE',
                            'quantity'           => $animal->quantity,
                            'from_stage_id'      => $stageRecria->id,
                            'to_stage_id'        => $stageLevante->id,
                            'to_barn_section_id' => $this->i_barn_section_id,
                            'to_pen_id'          => $this->resolvePenId($this->i_barn_section_id, $this->i_pen_name),
                            'note'               => 'Ingreso Individual - Lote: ' . $this->l_selected_lot,
                        ]);
                    }
                }

                DB::commit();
                $this->dispatch('notify', ['icon' => 'success', 'title' => 'Ingreso Exitoso', 'text' => 'Traslado a Levante completado correctamente.']);
                $this->resetFields();
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error($e->getMessage());
                $this->dispatch('notify', ['icon' => 'error', 'title' => 'Error', 'text' => 'Error al mover a Levante: ' . $e->getMessage()]);
            }

        } elseif ($this->ingresoType == 'PUBERTAD') {
            $this->validate([
                'i_source_id' => 'required',
                'i_quantity' => 'required|numeric|min:1',
                'i_barn_section_id' => 'required',
                'i_prefix_id' => 'required',
                'i_start_correlative' => 'required|numeric',
            ]);

            $stageLevante = Stage::where('name', 'Levante')->first();
            $stagePubertad = Stage::where('name', 'Pubertad')->first();

            $items = Animal::where('management_lot', $this->i_source_id)
                ->where('stage_id', $stageLevante->id)
                ->where('status', 'Activo')
                ->where('type', 'LOTE')
                ->get();

            $totalAvailable = $items->sum('quantity');

            if ($this->i_quantity > $totalAvailable) {
                $this->addError('i_quantity', 'Cantidad superior a la disponible.');
                return;
            }

            DB::beginTransaction();
            try {
                $qtyToIndividualize = $this->i_quantity;
                $correlative = $this->i_start_correlative;

                foreach ($items as $item) {
                    if ($qtyToIndividualize <= 0) break;

                    $takeQty = min($item->quantity, $qtyToIndividualize);
                    $item->quantity -= $takeQty;
                    if ($item->quantity == 0) $item->status = 'INACTIVO';
                    $item->save();

                    for ($i = 0; $i < $takeQty; $i++) {
                        $newIdStr = $this->i_prefix_id . str_pad($correlative, 3, '0', STR_PAD_LEFT);
                        $correlative++;

                        $ind = Animal::create([
                            'type' => 'INDIVIDUO',
                            'identifier' => $newIdStr,
                            'management_lot' => $item->management_lot,
                            'quantity' => 1,
                            'status' => 'ACTIVO',
                            'parent_animal_id' => $item->id,
                            'barn_section_id' => $this->i_barn_section_id,
                            'pen_id' => $this->resolvePenId($this->i_barn_section_id, $this->i_pen_name),
                            'stage_id' => $stagePubertad->id,
                            'genetic_id' => $item->genetic_id,
                            'sex' => 'Hembra',
                            'entry_date' => now(),
                            'pic_cycle' => $item->pic_cycle,
                            'pic_day' => $item->pic_day,
                        ]);

                        Movement::create([
                            'user_id' => auth()->id(),
                            'animal_id' => $ind->id,
                            'movement_date' => now(),
                            'pic_cycle' => $item->pic_cycle,
                            'pic_day' => $item->pic_day,
                            'movement_type' => 'INGRESO_PUBERTAD',
                            'quantity' => 1,
                            'to_stage_id' => $stagePubertad->id,
                            'to_barn_section_id' => $this->i_barn_section_id,
                            'to_pen_id' => $this->resolvePenId($this->i_barn_section_id, $this->i_pen_name),
                            'note' => 'Individualización desde Lote ' . ($item->management_lot ?? $item->id),
                        ]);
                    }

                    $qtyToIndividualize -= $takeQty;
                }

                DB::commit();
                $this->dispatch('notify', ['icon' => 'success', 'title' => 'Individualización Exitosa', 'text' => $this->i_quantity . ' cerdas ingresadas a Pubertad.']);
                $this->resetFields();
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error($e->getMessage());
                $this->dispatch('notify', ['icon' => 'error', 'title' => 'Error', 'text' => 'Falla en individualización.']);
            }
        }
    }

    public function processMovimiento()
    {
        $this->validate([
            'm_animal_id' => 'required',
            'm_barn_section_id' => 'required',
            'm_quantity' => 'required|numeric|min:1',
        ]);

        $item = Animal::findOrFail($this->m_animal_id);
        if ($this->m_quantity > $item->quantity) {
             $this->addError('m_quantity', 'Excede inventario.'); return;
        }

        DB::beginTransaction();
        try {
            if ($item->type == 'LOTE' && $this->m_quantity < $item->quantity) {
                $newItem = $item->replicate();
                $newItem->quantity = $this->m_quantity;
                $newItem->barn_section_id = $this->m_barn_section_id;
                $newItem->pen_id = $this->resolvePenId($this->m_barn_section_id, $this->m_pen_name);
                $newItem->save();
                $item->decrement('quantity', $this->m_quantity);
                $targetId = $newItem->id;
            } else {
                $item->update([
                    'barn_section_id' => $this->m_barn_section_id,
                    'pen_id' => $this->resolvePenId($this->m_barn_section_id, $this->m_pen_name)
                ]);
                $targetId = $item->id;
            }

            Movement::create([
                'user_id' => auth()->id(),
                'animal_id' => $targetId,
                'movement_date' => $this->operation_date,
                'pic_cycle' => $item->pic_cycle,
                'pic_day' => $item->pic_day,
                'movement_type' => 'TRASLADO',
                'quantity' => $this->m_quantity,
                'from_barn_section_id' => $item->barn_section_id,
                'to_barn_section_id' => $this->m_barn_section_id,
                'to_pen_id' => $this->resolvePenId($this->m_barn_section_id, $this->m_pen_name),
            ]);

            DB::commit();
            $this->dispatch('notify', ['icon' => 'success', 'title' => 'Movimiento Exitoso']);
            $this->resetFields();
        } catch (\Exception $e) {
            DB::rollBack();
            $this->dispatch('notify', ['icon' => 'error', 'title' => 'Error']);
        }
    }

    public function processCelo()
    {
        $this->validate([
            'c_animal_id' => 'required',
            'c_date' => 'required|date',
        ]);

        $item = Animal::findOrFail($this->c_animal_id);

        Movement::create([
            'user_id' => auth()->id(),
            'animal_id' => $item->id,
            'movement_date' => $this->operation_date,
            'pic_cycle' => $item->pic_cycle,
            'pic_day' => $item->pic_day,
            'movement_type' => 'CELO',
            'quantity' => 0,
            'note' => $this->c_note
        ]);

        $this->dispatch('notify', ['icon' => 'success', 'title' => 'Celo Registrado']);
        $this->reset(['c_animal_id', 'c_note']);
    }

    public function processActivacion()
    {
        $this->validate([
            'act_animal_id' => 'required',
            'act_date' => 'required|date',
            'act_boar' => 'required',
        ]);

        $item = Animal::findOrFail($this->act_animal_id);
        $stage = Stage::where('name', 'Gestación')->first();

        DB::beginTransaction();
        try {
            $item->update(['stage_id' => $stage->id]);

            Movement::create([
                'user_id' => auth()->id(),
                'animal_id' => $item->id,
                'movement_date' => $this->operation_date,
                'pic_cycle' => $item->pic_cycle,
                'pic_day' => $item->pic_day,
                'movement_type' => 'ACTIVACION',
                'quantity' => 0,
                'to_stage_id' => $stage->id,
                'boar_identifier' => $this->act_boar,
                'note' => 'Primera Inseminación (Activación)'
            ]);

            DB::commit();
            $this->dispatch('notify', ['icon' => 'success', 'title' => 'Activación Exitosa', 'text' => 'Hembra movida a Gestación.']);
            $this->resetFields();
        } catch (\Exception $e) {
            DB::rollBack();
            $this->dispatch('notify', ['icon' => 'error', 'title' => 'Error']);
        }
    }

    public function processVenta()
    {
        $this->validate([
            'v_animal_id' => 'required',
            'v_quantity' => 'required|numeric|min:1',
            'v_weight' => 'required',
        ]);

        $item = Animal::findOrFail($this->v_animal_id);
        if ($this->v_quantity > $item->quantity) {
            $this->addError('v_quantity', 'Excede inventario.'); return;
        }

        DB::beginTransaction();
        try {
            $item->decrement('quantity', $this->v_quantity);
            if ($item->quantity == 0) $item->update(['status' => 'INACTIVO']);

            $parsedWeight = $this->parseDecimal($this->v_weight);

            Movement::create([
                'user_id' => auth()->id(),
                'animal_id' => $item->id,
                'movement_date' => $this->operation_date,
                'movement_type' => 'VENTA',
                'quantity' => -$this->v_quantity,
                'weight' => $parsedWeight,
                'note' => 'Venta/Descarte. Fact: ' . $this->v_invoice
            ]);

            DB::commit();
            $this->dispatch('notify', ['icon' => 'success', 'title' => 'Venta Registrada']);
            $this->resetFields();
        } catch (\Exception $e) {
            DB::rollBack();
            $this->dispatch('notify', ['icon' => 'error', 'title' => 'Error']);
        }
    }

    public function processMortalidad()
    {
        $this->validate([
            'd_animal_id' => 'required',
            'd_quantity' => 'required|numeric|min:1',
            'd_death_cause_id' => 'required',
        ]);

        $item = Animal::findOrFail($this->d_animal_id);
        if ($this->d_quantity > $item->quantity) {
            $this->addError('d_quantity', 'Excede inventario.'); return;
        }

        DB::beginTransaction();
        try {
            $item->decrement('quantity', $this->d_quantity);
            if ($item->quantity == 0) $item->update(['status' => 'INACTIVO']);

            Movement::create([
                'user_id' => auth()->id(),
                'animal_id' => $item->id,
                'movement_date' => $this->operation_date,
                'movement_type' => 'MUERTE',
                'quantity' => -$this->d_quantity,
                'death_cause_id' => $this->d_death_cause_id,
                'note' => $this->d_note
            ]);

            DB::commit();
            $this->dispatch('notify', ['icon' => 'success', 'title' => 'Baja Registrada']);
            $this->resetFields();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error($e->getMessage());
            $this->dispatch('notify', ['icon' => 'error', 'title' => 'Error']);
        }
    }

    public function processPromocion()
    {
        $this->validate([
            'p_animal_id'   => 'required',
            'p_target_role' => 'required',
        ]);

        $item = Animal::findOrFail($this->p_animal_id);
        
        // Determinar Nave Destino según Rol
        $barnName = match($this->p_target_role) {
            'STUD'        => 'STUD',
            'CELADOR_DM1' => 'DM1',
            'CELADOR_DM2' => 'DM2',
            default       => null
        };

        if (!$barnName) return;

        $targetBarn = Barn::where('name', $barnName)->first();
        if (!$targetBarn) {
            $this->dispatch('notify', ['icon' => 'error', 'title' => 'Error', 'text' => 'El Galpón '.$barnName.' no está configurado.']);
            return;
        }

        DB::beginTransaction();
        try {
            // Un macho en STUD o DM suele considerarse "Reproductor" o similar
            // Por ahora mantenemos la etapa o si existe 'Reproductor' la cambiamos.
            $reproStage = Stage::where('name', 'Reproducción')->orWhere('name', 'Monta')->first();

            $item->update([
                'barn_id'  => $targetBarn->id,
                'status'   => 'Activo',
                'stage_id' => $reproStage ? $reproStage->id : $item->stage_id,
            ]);

            Movement::create([
                'user_id'       => auth()->id(),
                'animal_id'     => $item->id,
                'movement_date' => $this->operation_date,
                'movement_type' => 'PROMOCION_MACHO',
                'quantity'      => 0,
                'to_stage_id'   => $item->stage_id,
                'note'          => 'Promoción a ' . $this->p_target_role . ' en Nave ' . $barnName
            ]);

            DB::commit();
            $this->dispatch('notify', ['icon' => 'success', 'title' => 'Promoción Exitosa', 'text' => 'Macho movido a '.$barnName]);
            $this->resetFields();
        } catch (\Exception $e) {
            DB::rollBack();
            $this->dispatch('notify', ['icon' => 'error', 'title' => 'Error', 'text' => $e->getMessage()]);
        }
    }

    private function resetFields()
    {
        $this->reset([
            'i_source_id', 'i_quantity', 'i_barn_section_id', 'i_pen_id', 'i_weight',
            'm_animal_id', 'm_quantity', 'm_barn_section_id', 'm_pen_id',
            'v_animal_id', 'v_quantity', 'v_weight', 'v_invoice',
            'd_animal_id', 'd_quantity', 'd_death_cause_id', 'd_note',
            'act_animal_id', 'act_boar',
            'i_selected_room', 'i_birth_details', 'i_selected_details', 'i_all_selected', 'i_discard_remaining',
            'i_feed_type', 'p_animal_id', 'p_target_role'
        ]);

        if ($this->ingresoType == 'RECRIA') {
            $this->i_genetic_id = 7; // Mantener F1 por defecto
        }
        
        $this->loadInventory();
        $this->loadBirths();

        // Si estamos en Recría, refrescar el estado de las secciones PROC/Disponibles
        if ($this->ingresoType == 'RECRIA' && $this->i_barn_id) {
            $this->loadRecriaSection($this->i_barn_id);
        }

        // Volver a calcular alimento después del reset
        $this->updateFeedType();
    }

    public function truncateAllData()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Animal::truncate();
        Movement::truncate();
        BirthDetail::truncate();
        Birth::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $this->dispatch('notify', ['icon' => 'success', 'title' => 'Éxito', 'text' => 'Tablas de animales y partos vaciadas correctamente.']);
        $this->loadInventory();
        $this->loadBirths();
    }

    public function render()
    {
        return view('livewire.inventory.movement-operations');
    }
}
