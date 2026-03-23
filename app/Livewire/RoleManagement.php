<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Livewire\Attributes\On;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;

class RoleManagement extends Component
{
    use WithPagination;

    public $search = '';
    public $perPage = 10;
    public $orderBy = 'id';
    public $isAsc = false;

    // Form Properties
    public $roleId;
    public $name = '';
    public $guard_name = 'web';
    public $selectedPermissions = [];

    protected $queryString = [
        'search' => ['except' => ''],
        'page' => ['except' => 1]
    ];

    public function updatingSearch() { $this->resetPage(); }

    public function rules() {
        return [
            'name' => "required|string|max:100|unique:roles,name,{$this->roleId}",
            'guard_name' => 'required|string|max:50',
            'selectedPermissions' => 'nullable|array',
            'selectedPermissions.*' => 'exists:permissions,name',
        ];
    }

    #[Computed]
    public function allPermissions()
    {
        return Permission::orderBy('name')->get();
    }

    public function create()
    {
        $this->resetValidation();
        $this->reset(['roleId', 'name', 'guard_name', 'selectedPermissions']);
        $this->dispatch('open-modal', ['id' => 'roleFormModal']);
    }

    public function edit($id)
    {
        $this->resetValidation();
        $role = Role::with('permissions')->findOrFail($id);
        $this->roleId = $role->id;
        $this->name = $role->name;
        $this->guard_name = $role->guard_name;
        $this->selectedPermissions = $role->permissions->pluck('name')->toArray();
        $this->dispatch('open-modal', ['id' => 'roleFormModal']);
    }

    public function save()
    {
        $this->validate();

        if ($this->roleId) {
            $role = Role::findOrFail($this->roleId);
            $role->update([
                'name' => $this->name,
                'guard_name' => $this->guard_name,
            ]);
            $title = 'Rol Actualizado';
        } else {
            $role = Role::create([
                'name' => $this->name,
                'guard_name' => $this->guard_name,
            ]);
            $title = 'Rol Creado';
        }

        // Professional synchronization according to skill (syncPermissions replaces all)
        $role->syncPermissions($this->selectedPermissions);

        $this->dispatch('close-modal', ['id' => 'roleFormModal']);
        $this->dispatch('notify', [
            'icon' => 'success',
            'title' => 'Éxito',
            'text' => $title . ' correctamente.'
        ]);
        
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    }

    public function confirmDelete($id)
    {
        $this->dispatch('confirm-delete', [
            'id' => $id,
            'title' => '¿Eliminar Rol?',
            'text' => 'Esta acción no se puede deshacer y puede afectar los accesos de los usuarios.',
            'target' => 'deleteRole'
        ]);
    }

    #[On('deleteRole')]
    public function deleteRole($id)
    {
        $role = Role::findOrFail($id);
        
        // Prevent deletion of Super Admin role for safety
        if ($role->name === 'Super Admin') {
            $this->dispatch('notify', [
                'icon' => 'error',
                'title' => 'Acceso Denegado',
                'text' => 'El rol Super Admin es fundamental y no puede ser eliminado.'
            ]);
            return;
        }

        $role->delete();

        $this->dispatch('notify', [
            'icon' => 'success',
            'title' => 'Eliminado',
            'text' => 'El rol ha sido removido.'
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

    #[Title('Gestión de Roles')]
    public function render()
    {
        $roles = Role::with('permissions')
            ->where('name', 'like', "%{$this->search}%")
            ->orderBy($this->orderBy, $this->isAsc ? 'asc' : 'desc')
            ->paginate($this->perPage);

        return view('livewire.role-management', [
            'roles' => $roles
        ]);
    }
}
