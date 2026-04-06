<?php

namespace App\Livewire\Inventory;

use App\Models\Animal;
use App\Models\Movement;
use App\Models\BirthDetail;
use App\Models\BarnSection;
use App\Models\Barn;
use App\Models\Stage;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Title;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;

#[Title('Consulta de Trazabilidad')]
class TraceabilityViewer extends Component
{
    use WithPagination;

    public $search = '';
    public $selected_animal_id = null;
    public $activeTab = 'general'; // general, timeline

    protected $queryString = ['search', 'selected_animal_id'];

    public function updatingSearch()
    {
        $this->resetPage();
        $this->selected_animal_id = null;
    }

    public function selectAnimal($id)
    {
        $this->selected_animal_id = $id;
        $this->activeTab = 'timeline';
    }

    public function clearSelection()
    {
        $this->selected_animal_id = null;
        $this->activeTab = 'general';
    }

    public function getMovementColor($type)
    {
        return match($type) {
            'INGRESO_RECRIA', 'INGRESO_LEVANTE', 'INGRESO_PUBERTAD' => 'success',
            'TRASLADO', 'PROMOCION_MACHO' => 'info',
            'CELO', 'ACTIVACION' => 'warning',
            'VENTA' => 'primary',
            'MUERTE' => 'danger',
            default => 'secondary'
        };
    }

    public function render()
    {
        $combined = collect();

        if (!empty($this->search)) {
            $animalsQuery = Animal::with(['stage', 'nave', 'seccion', 'genetic', 'detail'])
                ->where('internal_id', 'like', '%' . $this->search . '%')
                ->orWhereHas('detail', function ($q) {
                    $q->where('management_lot', 'like', '%' . $this->search . '%')
                      ->orWhere('lote_sap', 'like', '%' . $this->search . '%');
                })
                ->orderBy('created_at', 'desc')
                ->get();
            
            $birthsQuery = BirthDetail::with(['birth.genetic'])
                ->whereNull('animal_id')
                ->where(function ($q) {
                    $q->where('generated_id', 'like', '%' . $this->search . '%')
                      ->orWhere('ear_id', 'like', '%' . $this->search . '%');
                })
                ->orderBy('created_at', 'desc')
                ->get();

            foreach ($animalsQuery as $a) {
                $combined->push($a);
            }

            foreach ($birthsQuery as $b) {
                $fake = new Animal();
                $fake->fake_id = 'b_' . $b->id;
                $fake->internal_id = $b->generated_id;
                $fake->management_lot = $b->birth->maternity_lot ?? 'No asig.';
                $fake->status = $b->status;
                $fake->created_at = $b->created_at;
                $fake->weight = $b->weight;
                $fake->sex = $b->sex;
                $fake->stage_id = 0; // Fake ID for frontend
                
                $fake->setRelation('genetic', $b->birth->genetic);
                $fake->setRelation('stage', new Stage(['name' => 'PREDESTETE']));
                
                $fakeNave = new Barn(['name' => 'Maternidad']);
                $fakeSeccion = new BarnSection(['name' => 'Sala ' . ($b->birth->room ?? '')]);
                $fake->setRelation('nave', $fakeNave);
                $fake->setRelation('seccion', $fakeSeccion);
                $fake->corral = $b->birth->cage;

                // Fake Detail for the split structure
                $fakeDetail = new \App\Models\AnimalDetail([
                    'management_lot' => $b->birth->maternity_lot ?? 'No asig.',
                    'source' => 'MATERNIDAD',
                    'weight' => $b->weight,
                    'lote_sap' => 'N/A'
                ]);
                $fake->setRelation('detail', $fakeDetail);

                $combined->push($fake);
            }

            $combined = $combined->sortByDesc('created_at')->values();
        }

        $currentPage = Paginator::resolveCurrentPage() ?: 1;
        $perPage = 10;
        $paginatedAnimals = new LengthAwarePaginator(
            $combined->forPage($currentPage, $perPage),
            $combined->count(),
            $perPage,
            $currentPage,
            ['path' => Paginator::resolveCurrentPath()]
        );

        $selectedAnimal = null;
        $movements = collect();
        $birthEvent = null;

        if ($this->selected_animal_id) {
            if (str_starts_with($this->selected_animal_id, 'b_')) {
                $realId = substr($this->selected_animal_id, 2);
                $b = BirthDetail::with(['birth.responsible', 'birth.genetic'])->find($realId);
                if ($b) {
                    $fake = new Animal();
                    $fake->fake_id = 'b_' . $b->id;
                    $fake->internal_id = $b->generated_id;
                    $fake->management_lot = $b->birth->maternity_lot ?? 'No asig.';
                    $fake->status = $b->status;
                    $fake->created_at = $b->created_at;
                    $fake->weight = $b->weight;
                    $fake->sex = $b->sex;
                    $fake->entry_date = \Carbon\Carbon::parse($b->birth->calendar_date);
                    $fake->source = 'MATERNIDAD';
                    
                    $fake->setRelation('genetic', $b->birth->genetic);
                    $fake->setRelation('stage', new Stage(['name' => 'PREDESTETE']));
                    
                    $fakeNave = new Barn(['name' => 'Maternidad']);
                    $fakeSeccion = new BarnSection(['name' => 'Sala ' . ($b->birth->room ?? '')]);
                    $fake->setRelation('nave', $fakeNave);
                    $fake->setRelation('seccion', $fakeSeccion);
                    $fake->corral = $b->birth->cage;

                    // Fake Detail for the individual selection
                    $fakeDetail = new \App\Models\AnimalDetail([
                        'management_lot' => $b->birth->maternity_lot ?? 'No asig.',
                        'source' => 'MATERNIDAD',
                        'weight' => $b->weight,
                        'lote_sap' => 'N/A'
                    ]);
                    $fake->setRelation('detail', $fakeDetail);

                    $selectedAnimal = $fake;
                    $birthEvent = $b;
                }
            } else {
                $selectedAnimal = Animal::with(['stage', 'nave', 'seccion', 'genetic', 'detail'])->find($this->selected_animal_id);
                if ($selectedAnimal) {
                    $movements = Movement::with(['user', 'fromNave', 'toNave', 'fromSeccion', 'toSeccion', 'fromStage', 'toStage', 'deathCause'])
                        ->where('animal_id', $this->selected_animal_id)
                        ->orderBy('movement_date', 'desc')
                        ->orderBy('created_at', 'desc')
                        ->get();
                        
                    $birthEvent = BirthDetail::with(['birth.responsible', 'birth.genetic'])
                        ->where('animal_id', $this->selected_animal_id)
                        ->first();
                }
            }
        }

        return view('livewire.inventory.traceability-viewer', [
            'animals' => $paginatedAnimals,
            'selectedAnimal' => $selectedAnimal,
            'movements' => $movements,
            'birthEvent' => $birthEvent
        ]);
    }
}
