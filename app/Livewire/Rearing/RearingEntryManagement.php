<?php

namespace App\Livewire\Rearing;

use App\Models\Inventory;
use App\Models\Movement;
use App\Models\BarnSection;
use App\Models\Genetic;
use App\Models\Stage;
use App\Services\PicDateService;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Title;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

use App\Traits\HandlesDecimals;

#[Title('Ingresos a Recría')]
class RearingEntryManagement extends Component
{
    use WithPagination, HandlesDecimals;

    public $search = '';
    protected $paginationTheme = 'bootstrap';

    // Form fields
    public $entry_date, $pic_cycle, $pic_day, $source_room, $management_lot, $genetic_id, $sex, $destination, $weight, $quantity, $to_barn_section_id;
    public $is_individual = false;
    public $identifier; // Solo usado si es individual

    public $isModalOpen = false;
    public $barn_sections = [];
    public $genetics = [];

    protected function rules()
    {
        return [
            'entry_date' => 'required|date',
            'pic_cycle' => 'required|numeric',
            'pic_day' => 'required|numeric|min:0|max:999',
            'source_room' => 'required|string',
            'management_lot' => 'nullable|string', // Lote semanal (800)
            'genetic_id' => 'required|exists:genetics,id',
            'sex' => 'required|string|in:Macho,Hembra,Mixto',
            'quantity' => 'required|numeric|min:1' . ($this->is_individual ? '|max:1' : ''),
            'weight' => 'nullable',
            'to_barn_section_id' => 'required|exists:barn_sections,id',
            'identifier' => $this->is_individual ? 'required|string|unique:inventory,identifier' : 'nullable|string',
        ];
    }

    public function mount()
    {
        $this->entry_date = now()->format('Y-m-d');
        $this->updatePicData();
        $this->barn_sections = BarnSection::all();
        $this->genetics = Genetic::all();
    }

    public function updatePicData()
    {
        $data = PicDateService::fromDate($this->entry_date);
        $this->pic_cycle = $data['vuelta'];
        $this->pic_day = $data['pic'];
    }

    public function updatedEntryDate()
    {
        $this->updatePicData();
    }

    public function updatedIsIndividual($value)
    {
        if ($value) {
            $this->quantity = 1;
            $this->sex = ''; // Reset sex for individual
        } else {
            $this->identifier = null;
        }
    }

    public function save()
    {
        $this->validate();

        $stage = Stage::where('name', 'Recría')->first();

        DB::beginTransaction();

        try {
            // 1. MAESTRO (INVENTORY)
            $inventory = Inventory::create([
                'type' => $this->is_individual ? 'INDIVIDUO' : 'LOTE',
                'identifier' => $this->is_individual ? $this->identifier : $this->management_lot, // Para lotes, a veces su identificador es el mismo lote de manejo
                'management_lot' => $this->management_lot,
                'quantity' => $this->quantity,
                'status' => 'ACTIVO',
                'barn_section_id' => $this->to_barn_section_id,
                'stage_id' => $stage->id ?? null,
                'genetic_id' => $this->genetic_id,
                'sex' => $this->sex,
                'entry_date' => $this->entry_date,
                'entry_pic_cycle' => $this->pic_cycle,
                'entry_pic_day' => $this->pic_day,
                'current_weight' => $this->parseDecimal($this->weight),
            ]);

            // 2. BITACORA (MOVEMENTS)
            Movement::create([
                'inventory_id' => $inventory->id,
                'movement_date' => now(), // El momento en que se procesó el dato
                'pic_cycle' => $this->pic_cycle,
                'pic_day' => $this->pic_day,
                'movement_type' => 'INGRESO_RECRIA',
                'quantity' => $this->quantity,
                'weight' => $this->parseDecimal($this->weight),
                'to_barn_section_id' => $this->to_barn_section_id,
                'to_stage_id' => $stage->id ?? null,
                // Nota: source_room podría ser la sala de maternidad de donde vienen, pero al no tenerla como barn_section (física) en este mock, la dejamos nula y nos basamos en comentarios o una futura tabla de salas de maternidad
            ]);

            DB::commit();

            $this->dispatch('notify', ['icon' => 'success', 'title' => 'Ingreso Registrado', 'text' => 'Los animales han ingresado al inventario maestro y bitácora de recría.']);
            $this->resetForm();
            $this->isModalOpen = false;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error grabando ingreso recría: ' . $e->getMessage());
            $this->dispatch('notify', ['icon' => 'error', 'title' => 'Error', 'text' => 'Ocurrió un problema al guardar el registro.']);
        }
    }

    public function resetForm()
    {
        $this->entry_date = now()->format('Y-m-d');
        $this->updatePicData();
        $this->source_room = '';
        $this->management_lot = '';
        $this->genetic_id = '';
        $this->sex = '';
        $this->destination = '';
        $this->weight = '';
        $this->quantity = '';
        $this->to_barn_section_id = '';
        $this->is_individual = false;
        $this->identifier = '';
    }

    public function render()
    {
        // Consultamos el inventario actual en "Recría" o basado en movimientos
        $entries = Inventory::with(['barnSection', 'genetic'])
            ->where(function($q) {
                $q->whereHas('stage', function($q2) {
                    $q2->where('name', 'Recría');
                })->orWhereNull('stage_id');
            })
            ->when($this->search, function($q) {
                $q->where('identifier', 'like', '%' . $this->search . '%')
                  ->orWhere('management_lot', 'like', '%' . $this->search . '%');
            })
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('livewire.rearing.rearing-entry-management', [
            'entries' => $entries
        ]);
    }
}
