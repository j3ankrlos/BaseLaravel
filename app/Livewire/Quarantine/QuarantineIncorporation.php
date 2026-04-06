<?php

namespace App\Livewire\Quarantine;

use Livewire\Component;
use Livewire\Attributes\Title;
use App\Models\QuarantineBatch;
use App\Models\QuarantineItem;
use App\Models\Animal;
use App\Models\Barn;
use App\Models\BarnSection;
use App\Models\Stage;
use App\Models\Movement;
use App\Traits\HandlesDecimals;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

#[Title('Incorporación a Producción')]
class QuarantineIncorporation extends Component
{
    use HandlesDecimals;

    public $operation_date;
    public $q_batch_id;
    public $q_items = [];
    public $q_selected_items = [];
    
    // Destino
    public $i_nave_id;
    public $i_seccion_id;
    public $i_corral;
    public $i_weight;
    public $i_feed_type;

    public function mount()
    {
        $this->operation_date = now()->format('Y-m-d');
    }

    public function updatedQBatchId($value)
    {
        $this->q_selected_items = [];
        if ($value) {
            $this->q_items = QuarantineItem::where('quarantine_batch_id', $value)
                ->where('status', 'PENDIENTE')
                ->with('genetic')
                ->get()
                ->toArray();
            
            // Auto-seleccionar nave si ya está definida
            $batch = QuarantineBatch::find($value);
            if ($batch) {
                $targetName = ($batch->sex == 'MACHO') ? 'STUD' : 'PUB1';
                $targetBarn = Barn::where('name', $targetName)->first();
                if ($targetBarn) {
                    $this->i_nave_id = $targetBarn->id;
                    $this->i_seccion_id = null;
                    $this->i_corral = null;
                }
            }
        } else {
            $this->q_items = [];
        }
    }

    public function updatedINaveId()
    {
        $this->i_seccion_id = null;
        $this->i_corral = null;
    }

    public function processIncorporation()
    {
        $this->validate([
            'q_batch_id' => 'required',
            'q_selected_items' => 'required|array|min:1',
            'i_nave_id' => 'required',
            'i_seccion_id' => 'required',
            'i_corral' => 'required',
        ], [
            'q_batch_id.required' => 'Debe seleccionar un lote de importación.',
            'q_selected_items.required' => 'Debe seleccionar al menos un animal.',
            'i_nave_id.required' => 'La nave es obligatoria.',
            'i_seccion_id.required' => 'La sección es obligatoria.',
            'i_corral.required' => 'El corral es obligatorio.',
        ]);

        DB::beginTransaction();
        try {
            $items = QuarantineItem::whereIn('id', $this->q_selected_items)->get();
            $batch = QuarantineBatch::find($this->q_batch_id);

            foreach ($items as $item) {
                // 1. Calcular fechas referenciales para ancestros
                $baseBirth = $item->birth_date ? \Carbon\Carbon::parse($item->birth_date) : null;
                $birthGen1 = $baseBirth?->copy()->subYears(2)->format('Y-m-d');
                $birthGen2 = $baseBirth?->copy()->subYears(4)->format('Y-m-d');
                $birthGen3 = $baseBirth?->copy()->subYears(8)->format('Y-m-d');

                // Generación 3: Bisabuelos
                $fff = Animal::ensureAncestor($item->fff_tag, $item->fff_genetic, 'Macho', null, null, $birthGen3);
                $ffm = Animal::ensureAncestor($item->ffm_tag, $item->ffm_genetic, 'Hembra', null, null, $birthGen3);
                $fmf = Animal::ensureAncestor($item->fmf_tag, $item->fmf_genetic, 'Macho', null, null, $birthGen3);
                $fmm = Animal::ensureAncestor($item->fmm_tag, $item->fmm_genetic, 'Hembra', null, null, $birthGen3);
                $mff = Animal::ensureAncestor($item->mff_tag, $item->mff_genetic, 'Macho', null, null, $birthGen3);
                $mfm = Animal::ensureAncestor($item->mfm_tag, $item->mfm_genetic, 'Hembra', null, null, $birthGen3);
                $mmf = Animal::ensureAncestor($item->mmf_tag, $item->mmf_genetic, 'Macho', null, null, $birthGen3);
                $mmm = Animal::ensureAncestor($item->mmm_tag, $item->mmm_genetic, 'Hembra', null, null, $birthGen3);

                // Generación 2: Abuelos
                $ffId = Animal::ensureAncestor($item->ff_tag, $item->ff_genetic, 'Macho', $ffm, $fff, $birthGen2);
                $fmId = Animal::ensureAncestor($item->fm_tag, $item->fm_genetic, 'Hembra', $fmm, $fmf, $birthGen2);
                $mfId = Animal::ensureAncestor($item->mf_tag, $item->mf_genetic, 'Macho', $mfm, $mff, $birthGen2);
                $mmId = Animal::ensureAncestor($item->mm_tag, $item->mm_genetic, 'Hembra', $mmm, $mmf, $birthGen2);

                // Generación 1: Padres
                $fId = Animal::ensureAncestor($item->f_tag, $item->f_genetic, 'Macho', $fmId, $ffId, $birthGen1);
                $mId = Animal::ensureAncestor($item->m_tag, $item->m_genetic, 'Hembra', $mmId, $mfId, $birthGen1);

                // 2. Crear Animal Principal
                $stageName = ($item->sex == 'MACHO') ? 'Stud' : 'Pubertad';
                $stage = Stage::where('name', $stageName)->firstOrFail();

                $animal = Animal::create([
                    'internal_id' => $item->internal_id,
                    'official_id' => $item->official_id,
                    'quantity' => 1,
                    'status' => 'Activo',
                    'entry_date' => $this->operation_date,
                    'birth_date' => $item->birth_date,
                    'genetic_id' => $item->genetic_id,
                    'sex' => ($item->sex == 'MACHO') ? 'Macho' : 'Hembra',
                    'stage_id' => $stage->id,
                    'nave_id' => $this->i_nave_id,
                    'seccion_id' => $this->i_seccion_id,
                    'corral' => (int)$this->i_corral,
                    'mother_id' => $mId,
                    'father_id' => $fId,
                    'mother_tag' => $item->m_tag,
                    'father_tag' => $item->f_tag
                ]);

                $animal->detail()->create([
                    'management_lot' => $item->lote,
                    'weight' => $this->parseDecimal($this->i_weight),
                    'feed_type' => $this->i_feed_type,
                    'evento' => 'Incorporación desde Cuarentena'
                ]);

                // 3. Registrar Movimiento
                Movement::create([
                    'user_id' => auth()->id(),
                    'animal_id' => $animal->id,
                    'movement_date' => $this->operation_date,
                    'movement_type' => 'INGRESO_CUARENTENA',
                    'quantity' => 1,
                    'to_nave_id' => $this->i_nave_id,
                    'to_seccion_id' => $this->i_seccion_id,
                    'to_corral' => (int)$this->i_corral,
                    'to_stage_id' => $stage->id,
                    'note' => "Incorporación de importación: {$batch->document_number}. ID Oficial: {$item->official_id}",
                ]);

                // 4. Marcar como incorporado
                $item->update(['status' => 'INCORPORADO', 'animal_id' => $animal->id]);
            }

            DB::commit();
            $this->dispatch('notify', ['icon' => 'success', 'title' => 'Incorporación Exitosa', 'text' => count($items) . ' animales incorporados a producción.']);
            $this->reset(['q_selected_items', 'i_weight', 'i_feed_type']);
            $this->updatedQBatchId($this->q_batch_id);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error($e->getMessage());
            $this->dispatch('notify', ['icon' => 'error', 'title' => 'Error en Incorporación', 'text' => $e->getMessage()]);
        }
    }

    public function render()
    {
        $q_batches = QuarantineBatch::whereHas('items', function($q) {
            $q->where('status', 'PENDIENTE');
        })->orderBy('entry_date', 'desc')->get();

        $barns = Barn::all();
        $barnSections = [];
        if ($this->i_nave_id) {
            $barnSections = BarnSection::where('barn_id', $this->i_nave_id)->get();
        }

        return view('livewire.quarantine.quarantine-incorporation', [
            'q_batches' => $q_batches,
            'barns' => $barns,
            'barnSections' => $barnSections
        ]);
    }
}
