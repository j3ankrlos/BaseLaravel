<?php

namespace App\Livewire\Inventory;

use App\Models\Animal;
use App\Models\Barn;
use App\Models\Genetic;
use Livewire\Component;
use Livewire\WithPagination;

class InventoryListView extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $search = '';
    public $f_nave_id = '';
    public $f_genetic_id = '';
    public $f_feed_type = '';
    public $perPage = 15;

    public $sortBy = 'entry_date';
    public $sortDir = 'desc';

    protected $queryString = [
        'search' => ['except' => ''],
        'f_nave_id' => ['except' => ''],
        'f_genetic_id' => ['except' => ''],
        'f_feed_type' => ['except' => ''],
    ];

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedFNaveId()
    {
        $this->resetPage();
    }

    public function updatedFGeneticId()
    {
        $this->resetPage();
    }

    public function updatedFFeedType()
    {
        $this->resetPage();
    }

    public function clearFilters()
    {
        $this->reset(['search', 'f_nave_id', 'f_genetic_id', 'f_feed_type']);
        $this->resetPage();
    }

    public function sort($field)
    {
        if ($this->sortBy === $field) {
            $this->sortDir = ($this->sortDir === 'asc') ? 'desc' : 'asc';
        } else {
            $this->sortBy = $field;
            $this->sortDir = 'asc';
        }
    }

    public function render()
    {
        $currentPicData = \App\Services\PicDateService::fromDate(now());
        $currentPic = $currentPicData['pic'];

        $query = Animal::query()
            ->join('animal_details', 'animals.id', '=', 'animal_details.animal_id')
            ->selectRaw('
                MIN(animals.id) as id, 
                SUM(animals.quantity) as quantity,
                GROUP_CONCAT(DISTINCT animal_details.source ORDER BY animal_details.source SEPARATOR "-") as source,
                MIN(animals.entry_date) as entry_date,
                animal_details.management_lot,
                animals.internal_id,
                animals.genetic_id,
                animals.sex,
                animals.nave_id,
                animals.seccion_id,
                animals.corral,
                animals.stage_id,
                SUM(animal_details.weight * animals.quantity) / SUM(animals.quantity) as weight,
                MAX(animal_details.feed_type) as feed_type,
                MAX(animal_details.act_curso) as act_curso,
                MAX(animal_details.lote_sap) as lote_sap,
                MAX(animal_details.order_number) as order_number
            ')
            ->with(['genetic', 'nave', 'seccion', 'stage', 'semen'])
            ->where('animals.status', 'Activo')
            ->when($this->search, function ($q) {
                $q->where(function ($sq) {
                    $sq->where('animals.internal_id', 'like', '%' . $this->search . '%')
                       ->orWhere('animal_details.management_lot', 'like', '%' . $this->search . '%')
                       ->orWhere('animal_details.lote_sap', 'like', '%' . $this->search . '%')
                       ->orWhere('animal_details.source', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->f_nave_id, fn($q) => $q->where('animals.nave_id', $this->f_nave_id))
            ->when($this->f_genetic_id, fn($q) => $q->where('animals.genetic_id', $this->f_genetic_id))
            ->when($this->f_feed_type, fn($q) => $q->where('animal_details.feed_type', $this->f_feed_type))
            ->groupBy([
                'animal_details.management_lot',
                'animals.internal_id',
                'animals.genetic_id',
                'animals.sex',
                'animals.nave_id',
                'animals.seccion_id',
                'animals.corral',
                'animals.stage_id'
            ])
            ->orderBy($this->sortBy, $this->sortDir);

        return view('livewire.inventory.inventory-list-view', [
            'animals' => $query->paginate($this->perPage),
            'barns' => Barn::pluck('name', 'id'),
            'genetics' => Genetic::pluck('name', 'id'),
            'feedTypes' => \App\Models\AnimalDetail::whereNotNull('feed_type')->distinct()->pluck('feed_type'),
            'currentPic' => $currentPic,
        ]);
    }
}
