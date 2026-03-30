<?php

namespace App\Livewire\Inventory;

use App\Models\Animal;
use App\Models\Movement;
use App\Models\BarnSection;
use App\Models\Stage;
use App\Models\Birth;
use App\Models\BirthDetail;
use App\Models\DeathCause;
use App\Models\Genetic;
use App\Models\Barn;
use App\Models\User;
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
    public $activeInventory = [];
    public $births = [];
    public $rooms = []; 
    public $deathCauses = [];
    public $genetics = [];
    public $barns = [];
    public $recriaAvailableSections = [];

    // INGRESOS Form
    public $i_source_id; 
    public $i_genetic_id; 
    public $i_management_lot = 'PROC'; 
    public $i_selected_room; 
    public $i_birth_details = []; 
    public $i_selected_details = []; 
    public $i_all_selected = false;
    public $i_discard_remaining = false; 

    // LEVANTE form
    public $l_genetic_id;
    public $l_selected_lot;   
    public $l_lots = [];      
    public $l_animal_list = [];    
    public $l_selected_animals = []; 
    public $l_all_selected = false;
    
    public $i_target_stage_id;
    public $i_nave_id;
    public $i_seccion_id;
    public $i_corral;
    public $i_quantity;
    public $i_weight;
    public $i_feed_type;
    public $allFeedTypes = ['Pre-inic', 'Pre-inic-0', 'Iniciador', 'Lech-1', 'Lech-2'];
    
    // Individualization fields
    public $i_prefix_id = 'F1-T-';
    public $i_start_correlative = 1;

    // MOVIMIENTOS Form
    public $m_animal_id;
    public $m_nave_id;
    public $m_seccion_id;
    public $m_corral;
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

    // PROMOCIÓN Form
    public $p_animal_id;
    public $p_target_role; 

    public function mount()
    {
        $this->stages = Stage::orderBy('order')->get();
        $this->barns = Barn::orderBy('name')->get();
        $this->barnSections = BarnSection::with('barn')->get();
        $this->deathCauses = DeathCause::orderBy('name')->get();
        $this->genetics = Genetic::all();
        $this->operation_date = now()->format('Y-m-d');
        $picData = \App\Services\PicDateService::fromDate(now());
        $this->operation_pic = $picData['vuelta'] . '-' . str_pad($picData['pic'], 3, '0', STR_PAD_LEFT);
        
        $this->i_genetic_id = 7; 
        $this->loadInventory();
        $this->loadBirths();
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
        $this->activeInventory = Animal::with(['stage', 'nave', 'seccion', 'genetic'])
            ->where('status', 'Activo')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function loadBirths()
    {
        $birthQuery = Birth::where('estado', 2);
        $individualBirthQuery = (clone $birthQuery);
        if ($this->i_genetic_id) {
            $individualBirthQuery->where('genetic_id', $this->i_genetic_id);
        }
        $this->births = $individualBirthQuery->with(['genetic', 'responsible'])->orderBy('room')->get();
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
        $query = Animal::where('status', 'Activo')->where('stage_id', $stageRecria->id);
        if ($this->l_genetic_id) $query->where('genetic_id', $this->l_genetic_id);
        $animals = $query->with(['genetic'])->orderBy('management_lot')->get();
        $this->l_lots = $animals->filter(fn($a) => !empty($a->management_lot))->groupBy('management_lot')->map(fn($group) => ['lot' => $group->first()->management_lot, 'total' => $group->sum('quantity')])->values()->toArray();
        if ($this->l_selected_lot) $this->updatedLSelectedLot($this->l_selected_lot); else { $this->l_animal_list = []; $this->l_selected_animals = []; }
    }

    public function updatedLGeneticId() { $this->l_selected_lot = null; $this->l_animal_list = []; $this->l_selected_animals = []; $this->l_all_selected = false; $this->i_quantity = 0; $this->loadLevanteAnimals(); }

    public function updatedLSelectedLot($value)
    {
        $this->l_selected_animals = []; $this->l_all_selected = false;
        if (!$value) { $this->l_animal_list = []; $this->i_quantity = 0; return; }
        $isF1 = $this->l_genetic_id == 7;
        $stageRecriaId = Stage::where('name', 'Recría')->value('id');
        if ($isF1) {
            $this->i_quantity = Animal::where('management_lot', $value)->where('stage_id', $stageRecriaId)->where('status', 'Activo')->where('genetic_id', $this->l_genetic_id)->sum('quantity');
            $this->l_animal_list = [];
        } else {
            $this->l_animal_list = Animal::where('management_lot', $value)->where('stage_id', $stageRecriaId)->where('status', 'Activo')->where('genetic_id', $this->l_genetic_id)->with(['genetic'])->get()->toArray();
            $this->i_quantity = 0;
        }
    }

    public function updatedLAllSelected($value) { $this->l_selected_animals = $value ? collect($this->l_animal_list)->pluck('id')->map(fn($id) => (string)$id)->toArray() : []; $this->i_quantity = count($this->l_selected_animals); }
    public function updatedLSelectedAnimals() { $this->i_quantity = count($this->l_selected_animals); $this->l_all_selected = count($this->l_selected_animals) === count($this->l_animal_list) && count($this->l_animal_list) > 0; }

    public function setTab($tab) { $this->activeTab = $tab; $this->resetValidation(); }
    public function setIngresoType($type) { $this->ingresoType = $type; $this->i_source_id = null; $this->resetValidation(); }

    public function updatedIngresoType()
    {
        $this->i_source_id = null; $this->i_management_lot = 'PROC'; $this->i_quantity = 0; $this->i_selected_room = null; $this->i_birth_details = []; $this->i_selected_details = []; $this->i_all_selected = false;
        $this->l_genetic_id = null; $this->l_selected_lot = null; $this->l_animal_list = []; $this->l_selected_animals = []; $this->l_all_selected = false;

        if ($this->ingresoType == 'RECRIA') {
            $this->i_genetic_id = 7; $this->loadBirths();
        } elseif ($this->ingresoType == 'LEVANTE') {
            $this->i_genetic_id = 7; $this->l_genetic_id = 7; $this->loadLevanteAnimals();
        }
        
        $barnMapping = ['RECRIA' => 'RECRIA', 'LEVANTE' => 'LA', 'PUBERTAD' => 'PUB1'];
        $targetName = $barnMapping[$this->ingresoType] ?? $this->ingresoType;
        $targetBarn = Barn::where('name', $targetName)->first();

        if ($targetBarn) {
            $this->i_nave_id = $targetBarn->id; $this->i_corral = null;
            if ($this->ingresoType == 'RECRIA') $this->loadRecriaSection($targetBarn->id); else $this->i_seccion_id = null;
        } else { $this->i_nave_id = null; $this->i_seccion_id = null; }

        $this->updateFeedType();
    }

    private function updateFeedType()
    {
        $barnName = $this->i_nave_id ? Barn::find($this->i_nave_id)?->name : null;
        if ($this->ingresoType == 'RECRIA') $this->i_feed_type = 'Pre-inic'; 
        else $this->i_feed_type = $this->getFeedTypeByLocation($barnName);

        $available = $this->getAvailableFeedTypes($barnName ?: ($this->ingresoType == 'RECRIA' ? 'RECRIA' : null));
        if (!in_array($this->i_feed_type, $available)) $this->i_feed_type = $available[0] ?? null;
    }

    public function getAvailableFeedTypes($location)
    {
        if (empty($location)) return $this->allFeedTypes;
        if ($location === 'RECRIA') return ['Pre-inic', 'Pre-inic-0', 'Iniciador'];
        if (in_array($location, ['LB', 'LA', 'LE', 'PUB3', 'PUB2-D'])) return ['Lech-1'];
        if (in_array($location, ['MP1', 'MP2', 'PUB1', 'PUB2'])) return ['Lech-2'];
        return $this->allFeedTypes;
    }

    private function getFeedTypeByLocation($location)
    {
        $mapping = ['RECRIA' => 'Pre-inic', 'LB' => 'Lech-1', 'LA' => 'Lech-1', 'LE' => 'Lech-1', 'MP1' => 'Lech-2', 'PUB1' => 'Lech-2', 'MP2' => 'Lech-2', 'PUB2' => 'Lech-2', 'PUB3' => 'Lech-1', 'PUB2-D' => 'Lech-1'];
        return $mapping[$location] ?? null;
    }

    public function loadRecriaSection(int $barnId): void
    {
        $recria = Stage::where('name', 'Recría')->first();
        $allSections = BarnSection::where('barn_id', $barnId)->orderBy('name')->get();
        $activeSectionIds = Animal::where('stage_id', $recria?->id)->where('status', 'Activo')->whereNotNull('seccion_id')->pluck('seccion_id')->unique();
        $procSectionId = Animal::where('stage_id', $recria?->id)->where('status', 'Activo')->where('management_lot', 'PROC')->whereNotNull('seccion_id')->value('seccion_id');

        $this->recriaAvailableSections = $allSections->map(function ($section) use ($activeSectionIds, $procSectionId) {
            $isProc = $section->id === $procSectionId;
            $isFull = !$isProc && $activeSectionIds->contains($section->id);
            return ['id' => $section->id, 'name' => $section->name, 'is_proc' => $isProc, 'is_full' => $isFull, 'available' => !$isFull];
        })->values()->toArray();

        if ($procSectionId) $this->i_seccion_id = $procSectionId; 
        else $this->i_seccion_id = collect($this->recriaAvailableSections)->first(fn($s) => $s['available'])['id'] ?? null;
    }

    public function updatedISelectedRoom($value)
    {
        if ($value && $this->ingresoType == 'RECRIA') {
            $isF1Batch = $this->i_genetic_id == 7;
            if ($isF1Batch) $this->i_quantity = 0;
            else {
                $this->i_birth_details = BirthDetail::whereHas('birth', function($q) use ($value) {
                    $q->where('room', $value)->where('genetic_id', $this->i_genetic_id)->where('estado', 2);
                })->where('status', 'MATERNIDAD')->with(['birth.genetic', 'birth.responsible'])->get()->toArray();
                if (empty($this->i_birth_details)) $this->dispatch('notify', ['icon' => 'info', 'title' => 'Sin animales', 'text' => 'No hay animales disponibles.']);
            }
            $this->i_selected_details = []; $this->i_all_selected = false; $this->syncDiscardRemaining();
        } else { $this->i_birth_details = []; $this->i_quantity = 0; $this->i_discard_remaining = false; }
    }

    public function updatedIAllSelected($value) { $this->i_selected_details = $value ? collect($this->i_birth_details)->pluck('id')->map(fn($id) => (string)$id)->toArray() : []; $this->i_quantity = count($this->i_selected_details); $this->syncDiscardRemaining(); }
    public function updatedISelectedDetails() { $this->i_quantity = count($this->i_selected_details); $this->i_all_selected = count($this->i_selected_details) === count($this->i_birth_details) && count($this->i_birth_details) > 0; $this->syncDiscardRemaining(); }
    public function updatedIQuantity() { if ($this->ingresoType == 'RECRIA' && in_array($this->i_genetic_id, [7, 8])) $this->syncDiscardRemaining(); }

    private function syncDiscardRemaining()
    {
        if ($this->ingresoType !== 'RECRIA' || !$this->i_selected_room) { $this->i_discard_remaining = false; return; }
        $this->i_discard_remaining = ($this->i_genetic_id != 7 && count($this->i_selected_details) > 0 && count($this->i_selected_details) < count($this->i_birth_details));
    }

    public function updatedINaveId() { 
        $this->i_seccion_id = null; $this->i_corral = null; 
        if ($this->i_nave_id) {
            $barn = Barn::find($this->i_nave_id);
            if ($barn && in_array($barn->name, ['LA', 'LB', 'LE'])) {
                $section = BarnSection::firstOrCreate(['barn_id' => $this->i_nave_id, 'name' => 'C'], ['description' => 'Sección C - ' . $barn->name]);
                $this->i_seccion_id = $section->id;
            }
        }
        $this->updateFeedType();
    }
    public function updatedISeccionId() { $this->i_corral = null; }
    public function updatedMNaveId() { $this->m_seccion_id = null; $this->m_corral = null; }
    public function updatedMSeccionId() { $this->m_corral = null; }

    public function processIngreso()
    {
        if ($this->ingresoType == 'RECRIA') {
            $isF1Batch = $this->i_genetic_id == 7;
            $rules = ['i_genetic_id' => 'required', 'i_selected_room' => 'required', 'i_seccion_id' => 'required', 'i_corral' => 'required', 'i_management_lot' => 'required', 'i_feed_type' => 'required'];
            if ($isF1Batch) { $rules['i_quantity'] = 'required|numeric|min:1'; $rules['i_weight'] = 'required'; }
            $this->validate($rules);
            if (!$isF1Batch && empty($this->i_selected_details)) { $this->dispatch('notify', ['icon' => 'error', 'title' => 'Falta Selección']); return; }
            $stage = Stage::where('name', 'Recría')->firstOrFail();

            DB::beginTransaction();
            try {
                $todayPicData = \App\Services\PicDateService::fromDate($this->operation_date);
                $currVuelta = $todayPicData['vuelta'];
                $currPic = $todayPicData['pic'];

                if ($isF1Batch) {
                    $lotNum = is_numeric($this->i_management_lot) ? (int)$this->i_management_lot : 0;
                    
                    // Inferencia de Vuelta para Lotes F1
                    $birthVuelta = ($lotNum > $currPic) ? ($currVuelta - 1) : $currVuelta;
                    $birthDate = \App\Services\PicDateService::toDate($birthVuelta, $lotNum);
                    $ageDays = $todayPicData['total_days'] - (($birthVuelta * 1000) + $lotNum);
                    
                    $sourcePrefix = 'S' . str_pad($this->i_selected_room, 2, '0', STR_PAD_LEFT);
                    $loteSap = $sourcePrefix . 'EXP' . $ageDays . '0';

                    $animal = Animal::create([
                        'quantity' => $this->i_quantity, 'entry_date' => $this->operation_date, 'birth_date' => $birthDate, 'source' => $sourcePrefix, 'management_lot' => $this->i_management_lot, 'genetic_id' => $this->i_genetic_id, 'sex' => 'Hembra', 'nave_id' => $this->i_nave_id, 'seccion_id' => $this->i_seccion_id, 'corral' => (int)$this->i_corral, 'stage_id' => $stage->id, 'status' => 'Activo', 'evento' => 'En Recría', 'lote_sap' => $loteSap, 'age_days' => $ageDays, 'weight' => $this->parseDecimal($this->i_weight), 'feed_type' => $this->i_feed_type,
                        'mother_tag' => '0', 'father_tag' => '0'
                    ]);
                    Movement::create([
                        'user_id' => auth()->id(), 'animal_id' => $animal->id, 'movement_date' => $this->operation_date, 'movement_type' => 'INGRESO_RECRIA', 'quantity' => $this->i_quantity, 'to_nave_id' => $this->i_nave_id, 'to_seccion_id' => $this->i_seccion_id, 'to_corral' => $animal->corral, 'to_stage_id' => $stage->id, 'note' => 'Ingreso Lote F1 - Sala ' . $this->i_selected_room,
                    ]);
                } else {
                    $details = BirthDetail::whereIn('id', $this->i_selected_details)->with('birth')->get();
                    foreach ($details as $detail) {
                        $lotNum = is_numeric($this->i_management_lot) ? (int)$this->i_management_lot : 0;
                        $birthDate = $detail->birth->calendar_date;
                        $birthPicData = \App\Services\PicDateService::fromDate($birthDate);
                        $ageDays = $todayPicData['total_days'] - $birthPicData['total_days'];
                        
                        $sourcePrefix = 'S' . str_pad($this->i_selected_room, 2, '0', STR_PAD_LEFT);
                        $loteSap = $sourcePrefix . 'EXP' . $ageDays . $detail->generated_id;

                        // Genealogy Lookup
                        $motherTag = $detail->birth->mother_tag ?: '0';
                        $fatherTag = $detail->birth->father_tag ?: '0';
                        $motherId = Animal::where('internal_id', $motherTag)->value('id');
                        $fatherId = Animal::where('internal_id', $fatherTag)->value('id');

                        $animal = Animal::create([
                            'quantity' => 1, 'entry_date' => $this->operation_date, 'birth_date' => $birthDate, 'source' => $sourcePrefix, 'management_lot' => $this->i_management_lot, 'internal_id' => $detail->generated_id, 'genetic_id' => $detail->birth->genetic_id, 'sex' => $detail->sex, 'nave_id' => $this->i_nave_id, 'seccion_id' => $this->i_seccion_id, 'corral' => (int)$this->i_corral, 'stage_id' => $stage->id, 'status' => 'Activo', 'evento' => 'En Recría', 'lote_sap' => $loteSap, 'age_days' => $ageDays, 'weight' => $this->parseDecimal($this->i_weight), 'feed_type' => $this->i_feed_type,
                            'mother_id' => $motherId, 'father_id' => $fatherId, 'mother_tag' => $motherTag, 'father_tag' => $fatherTag
                        ]);
                        Movement::create([
                            'user_id' => auth()->id(), 'animal_id' => $animal->id, 'movement_date' => $this->operation_date, 'movement_type' => 'INGRESO_RECRIA', 'quantity' => 1, 'to_nave_id' => $this->i_nave_id, 'to_seccion_id' => $this->i_seccion_id, 'to_corral' => $animal->corral, 'to_stage_id' => $stage->id, 'note' => 'Ingreso Individual - Arete: ' . $detail->generated_id,
                        ]);
                        $detail->update(['status' => 'RECRIA', 'animal_id' => $animal->id]);
                    }
                    foreach ($details->pluck('birth_id')->unique() as $bId) {
                        $rem = BirthDetail::where('birth_id', $bId)->where('status', 'MATERNIDAD')->count();
                        if ($rem === 0) Birth::where('id', $bId)->update(['estado' => 1]);
                        elseif ($this->i_discard_remaining) { 
                            BirthDetail::where('birth_id', $bId)->where('status', 'MATERNIDAD')->update(['status' => 'DESCARTADO']);
                            Birth::where('id', $bId)->update(['estado' => 3]);
                        }
                    }
                }
                DB::commit();
                if ($this->i_management_lot !== 'PROC') Animal::where('stage_id', $stage->id)->where('management_lot', 'PROC')->where('status', 'Activo')->update(['management_lot' => $this->i_management_lot]);
                $this->dispatch('notify', ['icon' => 'success', 'title' => 'Éxito']); $this->resetFields();
            } catch (\Exception $e) { DB::rollBack(); Log::error($e->getMessage()); $this->dispatch('notify', ['icon' => 'error', 'title' => 'Error', 'text' => $e->getMessage()]); }
        } elseif ($this->ingresoType == 'LEVANTE') {
            $isF1 = $this->l_genetic_id == 7;
            $rules = ['l_genetic_id' => 'required', 'l_selected_lot' => 'required', 'i_seccion_id' => 'required', 'i_corral' => 'required', 'i_weight' => 'required', 'i_feed_type' => 'required', 'i_quantity' => 'required|numeric|min:1'];
            if (!$isF1) $rules['l_selected_animals'] = 'required|array|min:1';
            $this->validate($rules);
            $stageRecriaId = Stage::where('name', 'Recría')->value('id');
            $stageLevante = Stage::where('name', 'Levante')->firstOrFail();

            DB::beginTransaction();
            try {
                if ($isF1) {
                    $items = Animal::where('management_lot', $this->l_selected_lot)->where('stage_id', $stageRecriaId)->where('status', 'Activo')->where('genetic_id', $this->l_genetic_id)->get();
                    $qtyToMove = $this->i_quantity;
                    foreach ($items as $item) {
                        if ($qtyToMove <= 0) break;
                        $moveQty = min($item->quantity, $qtyToMove);
                        $oldNave = $item->nave_id; $oldSec = $item->seccion_id; $oldCorral = $item->corral;
                        if ($moveQty < $item->quantity) {
                            $newItem = $item->replicate(); $newItem->quantity = $moveQty; $newItem->stage_id = $stageLevante->id; $newItem->evento = 'En Levante'; $newItem->nave_id = $this->i_nave_id; $newItem->seccion_id = $this->i_seccion_id; $newItem->corral = (int)$this->i_corral; $newItem->weight = $this->parseDecimal($this->i_weight); $newItem->save();
                            $item->decrement('quantity', $moveQty); $targetId = $newItem->id;
                        } else {
                            $item->update(['stage_id' => $stageLevante->id, 'evento' => 'En Levante', 'nave_id' => $this->i_nave_id, 'seccion_id' => $this->i_seccion_id, 'corral' => (int)$this->i_corral, 'weight' => $this->parseDecimal($this->i_weight)]); $targetId = $item->id;
                        }
                        Movement::create([
                            'user_id' => auth()->id(), 'animal_id' => $targetId, 'movement_date' => $this->operation_date, 'movement_type' => 'INGRESO_LEVANTE', 'quantity' => $moveQty, 'from_nave_id' => $oldNave, 'to_nave_id' => $this->i_nave_id, 'from_seccion_id' => $oldSec, 'to_seccion_id' => $this->i_seccion_id, 'from_corral' => $oldCorral, 'to_corral' => (int)$this->i_corral, 'from_stage_id' => $stageRecriaId, 'to_stage_id' => $stageLevante->id, 'note' => 'Lote ' . $this->l_selected_lot,
                        ]);
                        $qtyToMove -= $moveQty;
                    }
                } else {
                    foreach (Animal::whereIn('id', $this->l_selected_animals)->get() as $animal) {
                        $oldNave = $animal->nave_id; $oldSec = $animal->seccion_id; $oldCorral = $animal->corral;
                        $animal->update(['stage_id' => $stageLevante->id, 'evento' => 'En Levante', 'nave_id' => $this->i_nave_id, 'seccion_id' => $this->i_seccion_id, 'corral' => (int)$this->i_corral, 'weight' => $this->parseDecimal($this->i_weight)]);
                        Movement::create([
                            'user_id' => auth()->id(), 'animal_id' => $animal->id, 'movement_date' => $this->operation_date, 'movement_type' => 'INGRESO_LEVANTE', 'quantity' => $animal->quantity, 'from_nave_id' => $oldNave, 'to_nave_id' => $this->i_nave_id, 'from_seccion_id' => $oldSec, 'to_seccion_id' => $this->i_seccion_id, 'from_corral' => $oldCorral, 'to_corral' => (int)$this->i_corral, 'from_stage_id' => $stageRecriaId, 'to_stage_id' => $stageLevante->id,
                        ]);
                    }
                }
                DB::commit(); $this->dispatch('notify', ['icon' => 'success', 'title' => 'Éxito']); $this->resetFields();
            } catch (\Exception $e) { DB::rollBack(); Log::error($e->getMessage()); $this->dispatch('notify', ['icon' => 'error', 'title' => 'Error']); }
        } elseif ($this->ingresoType == 'PUBERTAD') {
            $this->validate(['i_source_id' => 'required', 'i_quantity' => 'required|numeric|min:1', 'i_seccion_id' => 'required', 'i_corral' => 'required']);
            $stageLevanteId = Stage::where('name', 'Levante')->value('id');
            $stagePubertad = Stage::where('name', 'Pubertad')->firstOrFail();
            $items = Animal::where('management_lot', $this->i_source_id)->where('stage_id', $stageLevanteId)->where('status', 'Activo')->get();

            DB::beginTransaction();
            try {
                $qtyToIndiv = $this->i_quantity; $corr = $this->i_start_correlative;
                foreach ($items as $item) {
                    if ($qtyToIndiv <= 0) break;
                    $takeQty = min($item->quantity, $qtyToIndiv);
                    $oldNave = $item->nave_id; $oldSec = $item->seccion_id; $oldCorral = $item->corral;
                    $item->decrement('quantity', $takeQty); if ($item->quantity == 0) $item->update(['status' => 'Inactivo']);
                    
                    for ($i = 0; $i < $takeQty; $i++) {
                        $newId = $this->i_prefix_id . str_pad($corr++, 3, '0', STR_PAD_LEFT);
                        $ind = Animal::create([
                            'internal_id' => $newId, 'management_lot' => $item->management_lot, 'quantity' => 1, 'status' => 'Activo', 'evento' => 'En Pubertad', 'nave_id' => $this->i_nave_id, 'seccion_id' => $this->i_seccion_id, 'corral' => (int)$this->i_corral, 'stage_id' => $stagePubertad->id, 'genetic_id' => $item->genetic_id, 'sex' => 'Hembra', 'entry_date' => now(), 'parent_animal_id' => $item->id,
                        ]);
                        Movement::create([
                            'user_id' => auth()->id(), 'animal_id' => $ind->id, 'movement_date' => now(), 'movement_type' => 'INGRESO_PUBERTAD', 'quantity' => 1, 'from_nave_id' => $oldNave, 'to_nave_id' => $this->i_nave_id, 'from_seccion_id' => $oldSec, 'to_seccion_id' => $this->i_seccion_id, 'from_corral' => $oldCorral, 'to_corral' => (int)$this->i_corral, 'from_stage_id' => $stageLevanteId, 'to_stage_id' => $stagePubertad->id,
                        ]);
                    }
                    $qtyToIndiv -= $takeQty;
                }
                DB::commit(); $this->dispatch('notify', ['icon' => 'success', 'title' => 'Éxito']); $this->resetFields();
            } catch (\Exception $e) { DB::rollBack(); Log::error($e->getMessage()); $this->dispatch('notify', ['icon' => 'error', 'title' => 'Error']); }
        }
    }

    public function processMovimiento()
    {
        $this->validate(['m_animal_id' => 'required', 'm_seccion_id' => 'required', 'm_corral' => 'required', 'm_quantity' => 'required|numeric|min:1']);
        $item = Animal::findOrFail($this->m_animal_id);
        if ($this->m_quantity > $item->quantity) { $this->addError('m_quantity', 'Excede inventario.'); return; }

        DB::beginTransaction();
        try {
            $oldNave = $item->nave_id; $oldSec = $item->seccion_id; $oldCorral = $item->corral;
            if ($this->m_quantity < $item->quantity) {
                $newItem = $item->replicate(); $newItem->quantity = $this->m_quantity; $newItem->nave_id = $this->m_nave_id; $newItem->seccion_id = $this->m_seccion_id; $newItem->corral = (int)$this->m_corral; $newItem->save();
                $item->decrement('quantity', $this->m_quantity); $targetId = $newItem->id;
            } else {
                $item->update(['nave_id' => $this->m_nave_id, 'seccion_id' => $this->m_seccion_id, 'corral' => (int)$this->m_corral]); $targetId = $item->id;
            }

            Movement::create([
                'user_id' => auth()->id(), 'animal_id' => $targetId, 'movement_date' => $this->operation_date, 'movement_type' => 'TRASLADO', 'quantity' => $this->m_quantity, 'from_nave_id' => $oldNave, 'to_nave_id' => $this->m_nave_id, 'from_seccion_id' => $oldSec, 'to_seccion_id' => $this->m_seccion_id, 'from_corral' => $oldCorral, 'to_corral' => (int)$this->m_corral,
            ]);

            DB::commit(); $this->dispatch('notify', ['icon' => 'success', 'title' => 'Éxito']); $this->resetFields();
        } catch (\Exception $e) { DB::rollBack(); Log::error($e->getMessage()); $this->dispatch('notify', ['icon' => 'error', 'title' => 'Error']); }
    }

    public function processCelo()
    {
        $this->validate(['c_animal_id' => 'required']);
        $item = Animal::findOrFail($this->c_animal_id);
        Movement::create(['user_id' => auth()->id(), 'animal_id' => $item->id, 'movement_date' => $this->operation_date, 'movement_type' => 'CELO', 'quantity' => 0, 'note' => $this->c_note]);
        $this->dispatch('notify', ['icon' => 'success', 'title' => 'Celo Registrado']); $this->reset(['c_animal_id', 'c_note']);
    }

    public function processActivacion()
    {
        $this->validate(['act_animal_id' => 'required', 'act_boar' => 'required']);
        $item = Animal::findOrFail($this->act_animal_id);
        $stage = Stage::where('name', 'Gestación')->firstOrFail();

        DB::beginTransaction();
        try {
            $item->update(['stage_id' => $stage->id, 'evento' => 'Inseminada']);
            Movement::create(['user_id' => auth()->id(), 'animal_id' => $item->id, 'movement_date' => $this->operation_date, 'movement_type' => 'ACTIVACION', 'quantity' => 0, 'to_stage_id' => $stage->id, 'note' => 'Activación - Verraco: ' . $this->act_boar]);
            DB::commit(); $this->dispatch('notify', ['icon' => 'success', 'title' => 'Éxito']); $this->resetFields();
        } catch (\Exception $e) { DB::rollBack(); $this->dispatch('notify', ['icon' => 'error', 'title' => 'Error']); }
    }

    public function processVenta()
    {
        $this->validate(['v_animal_id' => 'required', 'v_quantity' => 'required|numeric|min:1', 'v_weight' => 'required']);
        $item = Animal::findOrFail($this->v_animal_id);
        DB::beginTransaction();
        try {
            $item->decrement('quantity', $this->v_quantity);
            if ($item->quantity == 0) $item->update(['status' => 'Inactivo', 'evento' => 'Vendido/Descartado']);
            Movement::create(['user_id' => auth()->id(), 'animal_id' => $item->id, 'movement_date' => $this->operation_date, 'movement_type' => 'VENTA', 'quantity' => -$this->v_quantity, 'weight' => $this->parseDecimal($this->v_weight), 'note' => $this->v_invoice]);
            DB::commit(); $this->dispatch('notify', ['icon' => 'success', 'title' => 'Éxito']); $this->resetFields();
        } catch (\Exception $e) { DB::rollBack(); $this->dispatch('notify', ['icon' => 'error', 'title' => 'Error']); }
    }

    public function processMortalidad()
    {
        $this->validate(['d_animal_id' => 'required', 'd_quantity' => 'required|numeric|min:1', 'd_death_cause_id' => 'required']);
        $item = Animal::findOrFail($this->d_animal_id);
        DB::beginTransaction();
        try {
            $item->decrement('quantity', $this->d_quantity);
            if ($item->quantity == 0) $item->update(['status' => 'Inactivo', 'evento' => 'Muerte']);
            Movement::create(['user_id' => auth()->id(), 'animal_id' => $item->id, 'movement_date' => $this->operation_date, 'movement_type' => 'MUERTE', 'quantity' => -$this->d_quantity, 'death_cause_id' => $this->d_death_cause_id, 'note' => $this->d_note]);
            DB::commit(); $this->dispatch('notify', ['icon' => 'success', 'title' => 'Baja Registrada']); $this->resetFields();
        } catch (\Exception $e) { DB::rollBack(); Log::error($e->getMessage()); $this->dispatch('notify', ['icon' => 'error', 'title' => 'Error']); }
    }

    public function processPromocion()
    {
        $this->validate(['p_animal_id' => 'required', 'p_target_role' => 'required']);
        $item = Animal::findOrFail($this->p_animal_id);
        $barnName = match($this->p_target_role) { 'STUD' => 'STUD', 'CELADOR_DM1' => 'DM1', 'CELADOR_DM2' => 'DM2', default => null };
        $targetBarn = Barn::where('name', $barnName)->first();
        if (!$targetBarn) return;

        DB::beginTransaction();
        try {
            $reproStage = Stage::where('name', 'Reproducción')->orWhere('name', 'Monta')->first();
            $item->update(['nave_id' => $targetBarn->id, 'stage_id' => $reproStage ? $reproStage->id : $item->stage_id, 'evento' => 'Promocionado a ' . $this->p_target_role]);
            Movement::create(['user_id' => auth()->id(), 'animal_id' => $item->id, 'movement_date' => $this->operation_date, 'movement_type' => 'PROMOCION_MACHO', 'quantity' => 0, 'note' => 'Promoción a ' . $this->p_target_role]);
            DB::commit(); $this->dispatch('notify', ['icon' => 'success', 'title' => 'Éxito']); $this->resetFields();
        } catch (\Exception $e) { DB::rollBack(); $this->dispatch('notify', ['icon' => 'error', 'title' => 'Error']); }
    }

    private function resetFields()
    {
        $this->reset(['i_source_id', 'i_quantity', 'i_seccion_id', 'i_corral', 'i_weight', 'm_animal_id', 'm_quantity', 'm_seccion_id', 'm_corral', 'v_animal_id', 'v_quantity', 'v_weight', 'v_invoice', 'd_animal_id', 'd_quantity', 'd_death_cause_id', 'd_note', 'act_animal_id', 'act_boar', 'i_selected_room', 'i_birth_details', 'i_selected_details', 'i_all_selected', 'i_discard_remaining', 'i_feed_type', 'p_animal_id', 'p_target_role']);
        if ($this->ingresoType == 'RECRIA') $this->i_genetic_id = 7;
        $this->loadInventory(); $this->loadBirths();
        if ($this->ingresoType == 'RECRIA' && $this->i_nave_id) $this->loadRecriaSection($this->i_nave_id);
        $this->updateFeedType();
    }

    public function truncateAllData()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;'); Animal::truncate(); Movement::truncate(); BirthDetail::truncate(); Birth::truncate(); DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        $this->dispatch('notify', ['icon' => 'success', 'title' => 'Éxito']); $this->loadInventory(); $this->loadBirths();
    }

    public function render() { return view('livewire.inventory.movement-operations'); }
}
