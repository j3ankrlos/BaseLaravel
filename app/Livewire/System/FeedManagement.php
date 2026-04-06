<?php

namespace App\Livewire\System;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Feed;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Illuminate\Validation\Rule;

class FeedManagement extends Component
{
    use WithPagination;

    public $search = '';
    public $perPage = 10;
    public $orderBy = 'name';
    public $isAsc = true;

    // Form properties
    public $feedId;
    public $name = '';
    public $provider = 'TUNAL';
    public $code = '';
    public $cost_center = '';

    protected $queryString = [
        'search' => ['except' => ''],
        'page' => ['except' => 1]
    ];

    public function updatingSearch() { $this->resetPage(); }

    public function rules() {
        return [
            'name' => 'required|string|max:255',
            'provider' => 'required|string|max:255',
            'code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('feeds', 'code')->ignore($this->feedId),
            ],
            'cost_center' => 'nullable|string|max:100',
        ];
    }

    public function validationAttributes()
    {
        return [
            'name' => 'tipo de alimento',
            'provider' => 'proveedor',
            'code' => 'código de alimento',
            'cost_center' => 'centro de costo',
        ];
    }

    #[On('open-feed-modal')]
    public function create()
    {
        $this->resetValidation();
        $this->reset(['feedId', 'name', 'provider', 'code', 'cost_center']);
        $this->provider = 'TUNAL';
        $this->dispatch('open-modal', ['id' => 'feedFormModal']);
    }

    public function edit($id)
    {
        $this->resetValidation();
        $feed = Feed::findOrFail($id);
        $this->feedId = $feed->id;
        $this->name = $feed->name;
        $this->provider = $feed->provider;
        $this->code = $feed->code;
        $this->cost_center = $feed->cost_center;
        $this->dispatch('open-modal', ['id' => 'feedFormModal']);
    }

    public function save()
    {
        $this->validate();

        $data = [
            'name' => mb_strtoupper($this->name),
            'provider' => mb_strtoupper($this->provider),
            'code' => $this->code,
            'cost_center' => $this->cost_center ? mb_strtoupper($this->cost_center) : null,
        ];

        if ($this->feedId) {
            $feed = Feed::findOrFail($this->feedId);
            $feed->update($data);
            $title = 'Alimento Actualizado';
        } else {
            Feed::create($data);
            $title = 'Alimento Creado';
        }

        $this->dispatch('close-modal', ['id' => 'feedFormModal']);
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
            'title' => '¿Eliminar Alimento?',
            'text' => 'Esta acción no se puede deshacer.',
            'target' => 'deleteFeed'
        ]);
    }

    #[On('deleteFeed')]
    public function deleteFeed($id)
    {
        $feed = Feed::findOrFail($id);
        $feed->delete();

        $this->dispatch('notify', [
            'icon' => 'success',
            'title' => 'Eliminado',
            'text' => 'El alimento ha sido eliminado.'
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

    #[Title('Gestión de Alimentos')]
    public function render()
    {
        $feeds = Feed::query()
            ->where(function($q) {
                $q->where('name', 'like', "%{$this->search}%")
                  ->orWhere('code', 'like', "%{$this->search}%")
                  ->orWhere('provider', 'like', "%{$this->search}%");
            })
            ->orderBy($this->orderBy, $this->isAsc ? 'asc' : 'desc')
            ->paginate($this->perPage);

        return view('livewire.system.feed-management', [
            'feeds' => $feeds
        ]);
    }
}
