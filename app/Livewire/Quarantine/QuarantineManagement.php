<?php

namespace App\Livewire\Quarantine;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\QuarantineBatch;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;

class QuarantineManagement extends Component
{
    use WithPagination;

    public $search = '';
    public $perPage = 10;
    public $orderBy = 'entry_date';
    public $isAsc = false;
    public $expandedBatches = [];

    // Form fields
    public $batchId;
    public $entry_date;
    public $origin = '';
    public $provider = '';
    public $document_number = '';
    public $sex = 'HEMBRA';
    public $total_quantity = 0;
    public $batch_type = 'IMPORTACION';
    public $genetic_id;

    // Item Edit fields
    public $editingItemId;
    public $itemInternalId;
    public $itemOfficialId;
    public $itemBirthDate;
    public $itemSex;
    public $itemGeneticId;

    protected $queryString = [
        'search' => ['except' => ''],
        'page' => ['except' => 1]
    ];

    public function mount()
    {
        $this->entry_date = now()->format('Y-m-d');
    }

    public function updatingSearch() { $this->resetPage(); }

    public function rules() {
        return [
            'entry_date' => 'required|date',
            'origin' => 'required|string|max:255',
            'provider' => 'required|string|max:255',
            'document_number' => "required|string|max:100|unique:quarantine_batches,document_number,{$this->batchId}",
            'total_quantity' => 'required|integer|min:1',
            'batch_type' => 'required|in:IMPORTACION,CRECIMIENTO',
            'genetic_id' => 'required_if:batch_type,CRECIMIENTO|nullable|exists:genetics,id',
        ];
    }

    public function updatedBatchType($value)
    {
        if ($value === 'CRECIMIENTO') {
            $this->origin = 'SITIO 3';
            $this->provider = 'INTERNO';
        } else {
            $this->origin = '';
            $this->provider = '';
        }
    }

    public function validationAttributes()
    {
        return [
            'entry_date' => 'fecha de ingreso',
            'origin' => 'origen',
            'provider' => 'proveedor',
            'document_number' => 'número de documento',
            'sex' => 'sexo',
            'total_quantity' => 'cantidad de animales',
        ];
    }

    public function create()
    {
        $this->reset(['batchId', 'origin', 'provider', 'document_number', 'total_quantity', 'batch_type', 'genetic_id']);
        $this->entry_date = now()->format('Y-m-d');
        $this->batch_type = 'IMPORTACION';
        $this->sex = 'HEMBRA';
        $this->dispatch('open-modal', ['id' => 'batchFormModal']);
    }

    public function edit($id)
    {
        $batch = QuarantineBatch::findOrFail($id);
        $this->batchId = $batch->id;
        $this->entry_date = \Carbon\Carbon::parse($batch->entry_date)->format('Y-m-d');
        $this->origin = $batch->origin;
        $this->provider = $batch->provider;
        $this->document_number = $batch->document_number;
        $this->sex = $batch->sex;
        $this->total_quantity = $batch->total_quantity;
        $this->batch_type = $batch->batch_type;
        $this->genetic_id = $batch->genetic_id;
        $this->dispatch('open-modal', ['id' => 'batchFormModal']);
    }

    public function save()
    {
        $this->validate();

        $data = [
            'batch_type' => $this->batch_type,
            'genetic_id' => $this->genetic_id ?: null,
            'entry_date' => $this->entry_date,
            'origin' => mb_strtoupper($this->origin),
            'provider' => mb_strtoupper($this->provider),
            'document_number' => mb_strtoupper($this->document_number),
            'total_quantity' => $this->total_quantity,
        ];

        QuarantineBatch::updateOrCreate(['id' => $this->batchId], $data);

        $this->dispatch('close-modal', ['id' => 'batchFormModal']);
        $this->dispatch('notify', [
            'icon' => 'success',
            'title' => $this->batchId ? 'Lote Actualizado' : 'Lote Registrado',
            'text' => 'La información de importación se ha guardado correctamente.'
        ]);

        $this->reset(['batchId', 'origin', 'provider', 'document_number', 'total_quantity']);
    }

    public function editItem($id)
    {
        $item = \App\Models\QuarantineItem::findOrFail($id);
        $this->editingItemId = $item->id;
        $this->itemInternalId = $item->internal_id;
        $this->itemOfficialId = $item->official_id;
        $this->itemBirthDate = $item->birth_date ? \Carbon\Carbon::parse($item->birth_date)->format('Y-m-d') : null;
        $this->itemSex = $item->sex;
        $this->itemGeneticId = $item->genetic_id;
        
        $this->dispatch('open-modal', ['id' => 'itemEditModal']);
    }

    public function updatedItemOfficialId($value)
    {
        if (empty($value) || !$this->itemGeneticId) return;

        $genetic = \App\Models\Genetic::find($this->itemGeneticId);
        if (!$genetic) return;

        $prefix = strtoupper(substr($genetic->name, 0, 1));
        $suffix = substr(preg_replace('/[^0-9]/', '', $value), -5);
        
        if (strlen($suffix) > 0) {
            $this->itemInternalId = $prefix . $suffix;
        }
    }

    public function saveItem()
    {
        $this->validate([
            'itemInternalId' => [
                'required', 'string', 'max:100',
                function ($attribute, $value, $fail) {
                    $exists = \App\Models\Animal::where('internal_id', $value)
                        ->where('id', '!=', \App\Models\QuarantineItem::find($this->editingItemId)->animal_id)
                        ->exists();
                    if ($exists) {
                        $fail("El ID Interno '$value' ya existe en el sistema (Activo o Inactivo). Por favor verifique la trazabilidad.");
                    }
                }
            ],
            'itemOfficialId' => 'required|string|max:100',
            'itemBirthDate' => 'nullable|date',
            'itemSex' => 'required|in:MACHO,HEMBRA',
        ]);

        $item = \App\Models\QuarantineItem::findOrFail($this->editingItemId);
        $oldSex = $item->sex;

        $item->update([
            'internal_id' => $this->itemInternalId,
            'official_id' => $this->itemOfficialId,
            'birth_date' => $this->itemBirthDate,
            'sex' => $this->itemSex,
        ]);

        $this->dispatch('close-modal', ['id' => 'itemEditModal']);
        $this->dispatch('notify', [
            'icon' => 'success',
            'title' => 'Animal Actualizado',
            'text' => 'Los datos del animal se han corregido correctamente.'
        ]);
    }

    public function toggleBatch($id)
    {
        if (in_array($id, $this->expandedBatches)) {
            $this->expandedBatches = array_diff($this->expandedBatches, [$id]);
        } else {
            $this->expandedBatches[] = $id;
        }
    }

    public function sortBy($field)
    {
        if ($this->orderBy === $field) {
            $this->isAsc = !$this->isAsc;
        } else {
            $this->orderBy = $field;
            $this->isAsc = true;
        }
    }

    #[Title('Gestión de Cuarentena')]
    public function render()
    {
        $batches = QuarantineBatch::with(['items'])
            ->withCount(['items', 'items as incorporated_count' => function($query) {
                $query->where('status', 'INCORPORADO');
            }])
            ->where(function($query) {
                $query->where('origin', 'like', "%{$this->search}%")
                      ->orWhere('document_number', 'like', "%{$this->search}%")
                      ->orWhere('provider', 'like', "%{$this->search}%");
            })
            ->orderBy($this->orderBy, $this->isAsc ? 'asc' : 'desc')
            ->paginate($this->perPage);

        return view('livewire.quarantine.quarantine-management', [
            'batches' => $batches,
            'genetics' => \App\Models\Genetic::all()
        ]);
    }
}
