<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Permission\Models\Permission;
use Livewire\Attributes\On;

class PermissionManagement extends Component
{
    use WithPagination;

    public $search = '';
    public $perPage = 10;
    public $orderBy = 'id';
    public $isAsc = false;

    // Form Properties
    public $permissionId;
    public $name = '';
    public $guard_name = 'web';

    protected $queryString = [
        'search' => ['except' => ''],
        'page' => ['except' => 1]
    ];

    public function updatingSearch() { $this->resetPage(); }

    public function rules() {
        return [
            'name' => "required|string|max:100|unique:permissions,name,{$this->permissionId}",
            'guard_name' => 'required|string|max:50',
        ];
    }

    public function create()
    {
        $this->resetValidation();
        $this->reset(['permissionId', 'name', 'guard_name']);
        $this->dispatch('open-modal', ['id' => 'permissionFormModal']);
    }

    public function edit($id)
    {
        $this->resetValidation();
        $permission = Permission::findOrFail($id);
        $this->permissionId = $permission->id;
        $this->name = $permission->name;
        $this->guard_name = $permission->guard_name;
        $this->dispatch('open-modal', ['id' => 'permissionFormModal']);
    }

    public function save()
    {
        $this->validate();

        if ($this->permissionId) {
            $permission = Permission::findOrFail($this->permissionId);
            $permission->update([
                'name' => $this->name,
                'guard_name' => $this->guard_name,
            ]);
            $title = 'Permiso Actualizado';
        } else {
            Permission::create([
                'name' => $this->name,
                'guard_name' => $this->guard_name,
            ]);
            $title = 'Permiso Creado';
        }

        $this->dispatch('close-modal', ['id' => 'permissionFormModal']);
        $this->dispatch('notify', [
            'icon' => 'success',
            'title' => 'Éxito',
            'text' => $title . ' correctamente.'
        ]);
        
        // Reset permissions cache according to skill
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    }

    public function confirmDelete($id)
    {
        $this->dispatch('confirm-delete', [
            'id' => $id,
            'title' => '¿Eliminar Permiso?',
            'text' => 'Esta acción no se puede deshacer.',
            'target' => 'deletePermission'
        ]);
    }

    #[On('deletePermission')]
    public function deletePermission($id)
    {
        $permission = Permission::findOrFail($id);
        $permission->delete();

        $this->dispatch('notify', [
            'icon' => 'success',
            'title' => 'Eliminado',
            'text' => 'El permiso ha sido removido.'
        ]);
        
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
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

    public function render()
    {
        $permissions = Permission::where('name', 'like', "%{$this->search}%")
            ->orderBy($this->orderBy, $this->isAsc ? 'asc' : 'desc')
            ->paginate($this->perPage);

        return view('livewire.permission-management', [
            'permissions' => $permissions
        ])->title('Gestión de Permisos');
    }
}
