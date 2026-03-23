<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\DeathCause;
use App\Models\DeathSystem;
use Livewire\Attributes\On;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Illuminate\Validation\Rule;

class DeathCauseManagement extends Component
{
    use WithPagination;

    public $search = '';
    public $perPage = 10;
    public $orderBy = 'name';
    public $isAsc = true;

    // Form properties
    public $causeId;
    public $name = '';
    public $death_system_id = '';

    protected $queryString = [
        'search' => ['except' => ''],
        'page' => ['except' => 1]
    ];

    public function updatingSearch() { $this->resetPage(); }

    public function rules() {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('death_causes', 'name')->ignore($this->causeId),
            ],
            'death_system_id' => 'required|exists:death_systems,id',
        ];
    }

    public function validationAttributes()
    {
        return [
            'name' => 'nombre de la causa',
            'death_system_id' => 'sistema involucrado',
        ];
    }

    #[On('open-cause-modal')]
    public function create()
    {
        $this->resetValidation();
        $this->reset(['causeId', 'name', 'death_system_id']);
        $this->dispatch('open-modal', ['id' => 'causeFormModal']);
    }

    public function edit($id)
    {
        $this->resetValidation();
        $cause = DeathCause::findOrFail($id);
        $this->causeId = $cause->id;
        $this->name = $cause->name;
        $this->death_system_id = $cause->death_system_id;
        $this->dispatch('open-modal', ['id' => 'causeFormModal']);
    }

    public function save()
    {
        $this->validate();

        if ($this->causeId) {
            $cause = DeathCause::findOrFail($this->causeId);
            $cause->update([
                'name' => mb_strtoupper($this->name),
                'death_system_id' => $this->death_system_id,
            ]);
            $title = 'Causa Actualizada';
        } else {
            DeathCause::create([
                'name' => mb_strtoupper($this->name),
                'death_system_id' => $this->death_system_id,
            ]);
            $title = 'Causa Creada';
        }

        $this->dispatch('close-modal', ['id' => 'causeFormModal']);
        $this->dispatch('notify', [
            'icon' => 'success',
            'title' => 'Éxito',
            'text' => $title . ' correctamente.'
        ]);
    }

    public function confirmDelete($id)
    {
        $this->dispatch('confirm-delete', [
            'id' => $id,
            'title' => '¿Eliminar Causa de Muerte?',
            'text' => 'Esta acción no se puede deshacer y puede afectar los certificados existentes.',
            'target' => 'deleteCause'
        ]);
    }

    #[On('deleteCause')]
    public function deleteCause($id)
    {
        $cause = DeathCause::findOrFail($id);
        $cause->delete();

        $this->dispatch('notify', [
            'icon' => 'success',
            'title' => 'Eliminado',
            'text' => 'La causa de muerte ha sido eliminada.'
        ]);
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

    #[Computed]
    public function deathSystems()
    {
        return DeathSystem::orderBy('name')->get();
    }

    #[Title('Gestión de Causas de Muerte')]
    public function render()
    {
        $causes = DeathCause::with('system')
            ->where('name', 'like', "%{$this->search}%")
            ->orderBy($this->orderBy, $this->isAsc ? 'asc' : 'desc')
            ->paginate($this->perPage);

        return view('livewire.death-cause-management', [
            'causes' => $causes
        ]);
    }
}
