<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserManagement extends Component
{
    /**
     * TRAITS DE LIVEWIRE
     * WithPagination: Permite realizar paginación asíncrona (AJAX) sin recargar la página.
     */
    use WithPagination;

    /**
     * PROPIEDADES REACTIVAS DE BÚSQUEDA Y FILTRADO
     * Estas variables se sincronizan automáticamente con la vista (wire:model).
     */
    public string $search = '';
    public int $perPage = 10;
    public string $orderBy = 'id';
    public bool $isAsc = false;
    
    /**
     * PROPIEDADES DEL FORMULARIO (MODAL)
     * Centralizamos los datos del usuario para creación y edición.
     */
    public bool $showModal = false;
    public $userId; // Si está presente, el componente sabe que estamos EDITANDO.
    public string $name = '';
    public string $email = '';
    public string $password = '';
    public $role = '';

    /**
     * QUERY STRING
     * Mantiene el estado de la búsqueda y la página en la URL (ideal para compartir enlaces).
     */
    protected $queryString = [
        'search' => ['except' => ''],
        'page' => ['except' => 1]
    ];

    /**
     * HOOKS DE LIVEWIRE
     * updatingSearch: Se dispara justo antes de que 'search' cambie. 
     * Reseteamos la página a 1 para que los resultados nuevos no se pierdan en páginas altas.
     */
    public function updatingSearch() { $this->resetPage(); }

    /**
     * REGLAS DE VALIDACIÓN DINÁMICAS
     * El email debe ser único, pero ignoramos el ID del usuario actual si estamos editando.
     */
    public function rules() {
        return [
            'name' => 'required|min:3|max:100',
            'email' => "required|email|unique:users,email,{$this->userId}",
            'password' => $this->userId ? 'nullable|min:6' : 'required|min:6',
            'role' => 'required|exists:roles,name'
        ];
    }

    /**
     * MÉTODOS DE ACCIÓN (CRUD)
     */

    // Prepara el estado para un nuevo usuario
    public function create() {
        $this->reset(['userId', 'name', 'email', 'password', 'role']);
        $this->showModal = true;
    }

    // Carga los datos de un usuario existente para editar
    public function edit(User $user) {
        $this->userId = $user->id;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->role = $user->roles->first()?->name ?? '';
        $this->showModal = true;
    }

    // Guarda los cambios (Crear o Actualizar)
    public function save() {
        $data = $this->validate();

        $userData = [
            'name' => $data['name'],
            'email' => $data['email'],
        ];

        // Solo actualizamos el password si el usuario escribió uno nuevo
        if (!empty($data['password'])) {
            $userData['password'] = Hash::make($data['password']);
        }

        $user = User::updateOrCreate(['id' => $this->userId], $userData);
        
        // Sincronización de Roles con Spatie
        $user->syncRoles([$data['role']]);

        $this->showModal = false;
        
        // DISPARO DE EVENTO (NOTIFICACIÓN)
        // Este evento 'notify' es capturado por el JavaScript global en app.blade.php
        // para mostrar un SweetAlert2 elegante.
        $this->dispatch('notify', [
            'icon' => 'success',
            'title' => $this->userId ? 'Usuario Actualizado' : 'Usuario Creado',
            'text' => "El usuario {$user->name} ha sido procesado correctamente."
        ]);
        
        $this->reset(['userId', 'name', 'email', 'password', 'role']);
    }

    /**
     * ATRIBUTO #[On] (Escucha de eventos)
     * Este método se ejecuta cuando se confirma el borrado desde el frontend (SweetAlert).
     */
    #[On('delete-user-confirmed')]
    public function delete($id) {
        $user = User::findOrFail($id);
        
        if ($user->id === auth()->id()) {
            return $this->dispatch('notify', [
                'icon' => 'error', 'title' => 'Acción no permitida', 'text' => 'No puedes eliminar tu propia cuenta operativa.'
            ]);
        }
        
        $user->delete();
        $this->dispatch('notify', [
            'icon' => 'success', 
            'title' => 'Eliminado', 
            'text' => 'El registro ha sido removido del sistema.'
        ]);
    }

    /**
     * RENDERIZACIÓN
     * Aquí es donde sucede la magia del filtrado y búsqueda.
     */
    public function render()
    {
        // Eager Loading ('with'): Carga los roles en una sola consulta SQL para evitar el problema N+1.
        $users = User::with('roles')
            ->where(function($query) {
                $query->where('name', 'like', "%{$this->search}%")
                      ->orWhere('email', 'like', "%{$this->search}%");
            })
            ->orderBy($this->orderBy, $this->isAsc ? 'asc' : 'desc')
            ->paginate($this->perPage);

        return view('livewire.user-management', [
            'users' => $users,
            'roles' => Role::all(),
        ])->title('Gestión de Usuarios');
    }
}
