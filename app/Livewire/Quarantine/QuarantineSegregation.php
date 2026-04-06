<?php

namespace App\Livewire\Quarantine;

use Livewire\Component;
use App\Models\QuarantineBatch;
use App\Models\QuarantineItem;
use App\Models\Animal;
use App\Models\Genetic;
use App\Models\Stage;
use App\Services\PicDateService;
use Livewire\Attributes\Title;

class QuarantineSegregation extends Component
{
    public $batchId;
    public $batch;

    // Animal Básico
    public $internal_id = '';
    public $official_id = '';
    public $genetic_id = '';
    public $birth_date;
    public $sex = '';
    public $quantity = 1;

    // Ubicación
    public $barn_id;
    public $barn_section_id;
    public $pen_id;

    public $sections = [];
    public $pens = [];

    // Ancestros - Generación 1 (Padres)
    public $f_tag, $f_genetic;
    public $m_tag, $m_genetic;

    // Ancestros - Generación 2 (Abuelos)
    // Paternos
    public $ff_tag, $ff_genetic;
    public $fm_tag, $fm_genetic;
    // Maternos
    public $mf_tag, $mf_genetic;
    public $mm_tag, $mm_genetic;

    // Ancestros - Generación 3 (Bisabuelos)
    // Paterno-Paterno
    public $fff_tag, $fff_genetic;
    public $ffm_tag, $ffm_genetic;
    // Paterno-Materno
    public $fmf_tag, $fmf_genetic;
    public $fmm_tag, $fmm_genetic;
    // Materno-Paterno
    public $mff_tag, $mff_genetic;
    public $mfm_tag, $mfm_genetic;
    // Materno-Materno
    public $mmf_tag, $mmf_genetic;
    public $mmm_tag, $mmm_genetic;

    public function mount($batch)
    {
        $this->batch = QuarantineBatch::findOrFail($batch);
        $this->batchId = $this->batch->id;
        $this->sex = $this->batch->sex;
        $this->birth_date = now()->subMonths(3)->format('Y-m-d');

        // Nave por defecto: CUARENTENA
        $quarantineBarn = \App\Models\Barn::where('name', 'CUARENTENA')->first();
        if ($quarantineBarn) {
            $this->barn_id = $quarantineBarn->id;
            $this->updatedBarnId($this->barn_id);
        }
    }

    public function updatedBarnId($value)
    {
        $this->sections = \App\Models\BarnSection::where('barn_id', $value)->get();
        $this->barn_section_id = null;
        $this->pen_id = null;
        $this->pens = [];
    }

    public function updatedBarnSectionId($value)
    {
        $this->pens = \App\Models\Pen::where('barn_section_id', $value)->get();
        $this->pen_id = null;
    }

    public function rules()
    {
        return [
            'official_id' => 'required|string',
            'internal_id' => [
                'required', 'string',
                function ($attribute, $value, $fail) {
                    $exists = Animal::where('internal_id', $value)->exists();
                    if ($exists) {
                        $fail("El ID Interno '$value' ya existe en el sistema (Activo o Inactivo). Verifique la trazabilidad.");
                    }
                }
            ],
            'genetic_id'  => 'required|exists:genetics,id',
            'birth_date'  => 'nullable|date',
            'sex'         => 'required|in:MACHO,HEMBRA',
            'quantity'    => [
                'required', 'integer', 'min:1',
                function ($attribute, $value, $fail) {
                    $remaining = $this->batch->total_quantity - $this->batch->current_quantity;
                    if ($value > $remaining) {
                        $fail("La cantidad supera el saldo disponible del lote ($remaining).");
                    }
                }
            ],
            'barn_id'     => 'required|exists:barns,id',
            // ...
            'barn_section_id' => 'nullable|exists:barn_sections,id',
            'pen_id'      => 'nullable|exists:pens,id',
            // Pedigrí completo 100% opcional
            'f_tag' => 'nullable', 'm_tag' => 'nullable',
        ];
    }

    public function updatedOfficialId($value)
    {
        if (empty($value) || !$this->genetic_id) return;

        $genetic = Genetic::find($this->genetic_id);
        if (!$genetic) return;

        $prefix = strtoupper(substr($genetic->name, 0, 1));
        $suffix = substr(preg_replace('/[^0-9]/', '', $value), -5);
        
        if (strlen($suffix) > 0) {
            $this->internal_id = $prefix . $suffix;
        }
    }

    public function updatedGeneticId($value)
    {
        if ($this->official_id) {
            $this->updatedOfficialId($this->official_id);
        }
    }

    public function save()
    {
        $this->validate();

        if ($this->batch->current_quantity >= $this->batch->total_quantity) {
             return $this->dispatch('notify', ['icon' => 'error', 'title' => 'Lote Completado', 'text' => 'Ya se ha segregado la cantidad total de animales.']);
        }

        // Calcular LOTE (PIC) basado en la fecha de nacimiento
        $lotePic = null;
        if ($this->birth_date) {
            $picData = PicDateService::fromDate($this->birth_date);
            $lotePic = $picData['pic'];
        }

        // Guardar en la tabla de Staging (QuarantineItem)
        $item = QuarantineItem::create([
            'quarantine_batch_id' => $this->batchId,
            'internal_id'         => $this->quantity == 1 ? mb_strtoupper($this->internal_id) : null,
            'official_id'         => $this->quantity == 1 ? ($this->official_id ?: null) : null,
            'genetic_id'          => $this->genetic_id ?: null,
            'birth_date'          => $this->birth_date ?: null,
            'sex'                 => $this->sex,
            'lote'                => $lotePic,
            'quantity'            => $this->quantity,
            'barn_id'             => $this->barn_id,
            'barn_section_id'     => $this->barn_section_id,
            'pen_id'              => $this->pen_id,
            
            // Pedigrí Tags y Genéticas (Solo si cantidad es 1)
            'f_tag' => $this->quantity == 1 ? $this->f_tag : null, 
            'f_genetic_id' => $this->quantity == 1 ? $this->f_genetic : null,
            'm_tag' => $this->quantity == 1 ? $this->m_tag : null, 
            'm_genetic_id' => $this->quantity == 1 ? $this->m_genetic : null,
            
            'ff_tag' => $this->quantity == 1 ? $this->ff_tag : null, 
            'ff_genetic_id' => $this->quantity == 1 ? $this->ff_genetic : null,
            'fm_tag' => $this->quantity == 1 ? $this->fm_tag : null, 
            'fm_genetic_id' => $this->quantity == 1 ? $this->fm_genetic : null,
            'mf_tag' => $this->quantity == 1 ? $this->mf_tag : null, 
            'mf_genetic_id' => $this->quantity == 1 ? $this->mf_genetic : null,
            'mm_tag' => $this->quantity == 1 ? $this->mm_tag : null, 
            'mm_genetic_id' => $this->quantity == 1 ? $this->mm_genetic : null,
            
            'fff_tag' => $this->quantity == 1 ? $this->fff_tag : null, 
            'ffm_tag' => $this->quantity == 1 ? $this->ffm_tag : null, 
            'fmf_tag' => $this->quantity == 1 ? $this->fmf_tag : null, 
            'fmm_tag' => $this->quantity == 1 ? $this->fmm_tag : null, 
            'mff_tag' => $this->quantity == 1 ? $this->mff_tag : null, 
            'mfm_tag' => $this->quantity == 1 ? $this->mfm_tag : null, 
            'mmf_tag' => $this->quantity == 1 ? $this->mmf_tag : null, 
            'mmm_tag' => $this->quantity == 1 ? $this->mmm_tag : null, 
            
            'status' => 'PENDIENTE'
        ]);

        $this->batch->increment('current_quantity', $this->quantity);

        $this->reset(['internal_id', 'official_id', 'genetic_id', 'birth_date', 'sex', 'quantity',
            'f_tag', 'f_genetic', 'm_tag', 'm_genetic',
            'ff_tag', 'ff_genetic', 'fm_tag', 'fm_genetic',
            'mf_tag', 'mf_genetic', 'mm_tag', 'mm_genetic',
            'fff_tag', 'fff_genetic', 'ffm_tag', 'ffm_genetic',
            'fmf_tag', 'fmf_genetic', 'fmm_tag', 'fmm_genetic',
            'mff_tag', 'mff_genetic', 'mfm_tag', 'mfm_genetic',
            'mmf_tag', 'mmf_genetic', 'mmm_tag', 'mmm_genetic',
        ]);
        $this->sex        = $this->batch->sex;
        $this->birth_date = now()->subMonths(3)->format('Y-m-d');

        $this->dispatch('notify', [
            'icon'  => 'success',
            'title' => 'Animal Segregado',
            'text'  => "El animal {$item->internal_id} ha sido registrado en la lista de espera de la importación.",
        ]);
    }

    public function recrotalar($itemId, $newId)
    {
        $item = QuarantineItem::findOrFail($itemId);
        $oldId = $item->internal_id;

        $item->update([
            'internal_id' => mb_strtoupper($newId)
        ]);

        $this->dispatch('notify', [
            'icon' => 'success',
            'title' => 'ID Actualizado',
            'text' => "Se ha cambiado el ID de {$oldId} a " . mb_strtoupper($newId)
        ]);
    }

    /**
     * Crea o actualiza un ancestro en la tabla animals.
     *
     * @param string|null $tag       ID interno del ancestro
     * @param int|null    $geneticId Genética del ancestro
     * @param string      $sex       MACHO | HEMBRA
     * @param int|null    $motherId  ID de la madre del ancestro
     * @param int|null    $fatherId  ID del padre del ancestro
     * @param string|null $birthDate Fecha estimada (-2, -4 ó -8 años respecto al importado)
     */
    private function ensureAncestor($tag, $geneticId, $sex, $motherId = null, $fatherId = null, $birthDate = null)
    {
        if (empty($tag)) return null;

        return Animal::updateOrCreate(
            ['internal_id' => mb_strtoupper($tag)],
            [
                'genetic_id' => $geneticId ?: null,
                'sex'        => $sex,
                'status'     => 'REFERENCIA',
                'mother_id'  => $motherId,
                'father_id'  => $fatherId,
                'birth_date' => $birthDate,   // Fecha estimada por generación
                'quantity'   => 0,            // No cuenta como inventario operativo
            ]
        );
    }

    #[Title('Segregación de Lote')]
    public function render()
    {
        return view('livewire.quarantine.quarantine-segregation', [
            'genetics' => Genetic::all(),
            'barns' => \App\Models\Barn::all(),
            'segregatedAnimals' => QuarantineItem::where('quarantine_batch_id', $this->batchId)->latest()->get()
        ]);
    }
}
