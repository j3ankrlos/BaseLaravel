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
    public $f_barn_id = '';
    public $f_genetic_id = '';
    public $f_feed_type = '';
    public $perPage = 15;

    public $sortBy = 'entry_date';
    public $sortDir = 'desc';

    protected $queryString = [
        'search' => ['except' => ''],
        'f_barn_id' => ['except' => ''],
        'f_genetic_id' => ['except' => ''],
        'f_feed_type' => ['except' => ''],
    ];

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedFBarnId()
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
        $this->reset(['search', 'f_barn_id', 'f_genetic_id', 'f_feed_type']);
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
            ->selectRaw('
                MIN(id) as id, 
                SUM(quantity) as quantity,
                GROUP_CONCAT(DISTINCT source ORDER BY source SEPARATOR "-") as source,
                MIN(entry_date) as entry_date,
                management_lot,
                internal_id,
                genetic_id,
                sex,
                barn_id,
                barn_section_id,
                pen_id,
                stage_id,
                SUM(weight * quantity) / SUM(quantity) as weight,
                MAX(age_days) as age_days,
                MAX(feed_type) as feed_type
            ')
            ->with(['genetic', 'barn', 'barnSection', 'pen', 'stage'])
            ->where('status', 'Activo')
            ->when($this->search, function ($q) {
                $q->where(function ($sq) {
                    $sq->where('internal_id', 'like', '%' . $this->search . '%')
                       ->orWhere('management_lot', 'like', '%' . $this->search . '%')
                       ->orWhere('lote_sap', 'like', '%' . $this->search . '%')
                       ->orWhere('source', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->f_barn_id, fn($q) => $q->where('barn_id', $this->f_barn_id))
            ->when($this->f_genetic_id, fn($q) => $q->where('genetic_id', $this->f_genetic_id))
            ->when($this->f_feed_type, fn($q) => $q->where('feed_type', $this->f_feed_type))
            ->groupBy([
                'management_lot',
                'internal_id',
                'genetic_id',
                'sex',
                'barn_id',
                'barn_section_id',
                'pen_id',
                'stage_id'
            ])
            ->orderBy($this->sortBy, $this->sortDir);

        return view('livewire.inventory.inventory-list-view', [
            'animals' => $query->paginate($this->perPage),
            'barns' => Barn::pluck('name', 'id'),
            'genetics' => Genetic::pluck('name', 'id'),
            'feedTypes' => Animal::whereNotNull('feed_type')->distinct()->pluck('feed_type'),
            'currentPic' => $currentPic,
        ]);
    }
}
