<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\User;
use App\Models\Employee;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Illuminate\Support\Str;

class UserManagement extends Component
{
    use WithPagination;

    // Propiedades de búsqueda y lista
    public $search = '';
    public $perPage = 10;
    public $orderBy = 'id';
    public $isAsc = false;
    
    // Propiedades del Formulario
    public $userId;
    public $personal_id;
    public $name = '';
    public $short_name = '';
    public $username = '';
    public $email = '';
    public $password = '';
    public $password_confirmation = '';
    public $role = '';
    public $status = true; // mapped to status_id (1 = Active)

    // Búsqueda de Personal
    public string $employeeSearch = '';
    public $employeeResults = [];
    public $create;

    protected $queryString = [
        'search' => ['except' => ''],
        'page' => ['except' => 1],
        'create' => ['except' => null]
    ];

    public function mount()
    {
        if (request()->has('create')) {
            $this->create();
            $this->dispatch('open-modal', ['id' => 'userFormModal']);
        }
    }

    public function updatedCreate($value)
    {
        if ($value) {
            $this->create();
            $this->dispatch('open-modal', ['id' => 'userFormModal']);
        }
    }

    public function updatingSearch() { $this->resetPage(); }

    public function rules() {
        return [
            'name' => 'required|min:3|max:100',
            'short_name' => 'required|string|max:100',
            'username' => "required|string|max:50|unique:users,username,{$this->userId}",
            'email' => "required|email|unique:users,email,{$this->userId}",
            'password' => $this->userId ? 'nullable|min:6|confirmed' : 'required|min:6|confirmed',
            'role' => 'required|exists:roles,name',
            'status' => 'required|boolean',
            'personal_id' => 'nullable'
        ];
    }

    public function updatedEmployeeSearch($value)
    {
        if (strlen($value) < 2) {
            $this->employeeResults = [];
            return;
        }

        $this->employeeResults = Employee::where('first_names', 'like', "%{$value}%")
            ->orWhere('last_names', 'like', "%{$value}%")
            ->orWhere('national_id', 'like', "%{$value}%")
            ->take(5)
            ->get();
    }

    public function selectEmployee(Employee $employee)
    {
        $this->personal_id = $employee->national_id;
        $this->name = $employee->first_names . ' ' . $employee->last_names;
        
        // Nombre Corto: Primer nombre y primer apellido
        $firstName = explode(' ', trim($employee->first_names))[0];
        $lastName = explode(' ', trim($employee->last_names))[0];
        $this->short_name = $firstName . ' ' . $lastName;

        // Usuario: Primera letra inicial en mayúscula + primer apellido
        $initial = strtoupper(substr($firstName, 0, 1));
        $generatedUsername = $initial . $lastName;

        // Verificar si existe el usuario
        $exists = User::where('username', $generatedUsername)->exists();
        if ($exists) {
            $generatedUsername = $initial . '.' . $lastName;
        }

        $this->username = $generatedUsername;
        $this->employeeSearch = $employee->first_names . ' ' . $employee->last_names;
        $this->employeeResults = [];
    }

    public function create() {
        $this->reset(['userId', 'personal_id', 'name', 'short_name', 'username', 'email', 'password', 'password_confirmation', 'role', 'status', 'employeeSearch', 'employeeResults']);
        $this->status = true;
        // El modal se abre desde el botón en la vista (Bootstrap data attributes)
    }

    public function edit($id) {
        $user = User::findOrFail($id);
        $this->userId = $user->id;
        $this->personal_id = $user->personal_id;
        $this->name = $user->name;
        $this->short_name = $user->short_name;
        $this->username = $user->username;
        $this->email = $user->email;
        $this->role = $user->roles->first()?->name ?? '';
        $this->status = $user->status_id === 1;
        
        $this->employeeSearch = '';
        if ($user->employee) {
            $this->employeeSearch = $user->employee->first_names . ' ' . $user->employee->last_names;
        }
        // El modal se abre desde el botón en la vista (Bootstrap data attributes)
    }

    public function save() {
        $this->validate();

        $userData = [
            'name' => $this->name,
            'short_name' => $this->short_name,
            'username' => $this->username,
            'email' => $this->email,
            'personal_id' => $this->personal_id,
            'status_id' => $this->status ? 1 : 2,
        ];

        if (!empty($this->password)) {
            $userData['password'] = Hash::make($this->password);
        }

        $user = User::updateOrCreate(['id' => $this->userId], $userData);
        $user->syncRoles([$this->role]);

        $this->dispatch('close-modal', ['id' => 'userFormModal']);
        
        $this->dispatch('notify', [
            'icon' => 'success',
            'title' => $this->userId ? 'Usuario Actualizado' : 'Usuario Creado',
            'text' => "El usuario {$user->username} ha sido procesado correctamente."
        ]);
        
        $this->reset(['userId', 'personal_id', 'name', 'short_name', 'username', 'email', 'password', 'password_confirmation', 'role', 'status', 'employeeSearch', 'employeeResults']);
    }

    #[On('delete-user-confirmed')]
    public function delete($id) {
        $user = User::findOrFail($id);
        
        if ($user->id === auth()->id()) {
            return $this->dispatch('notify', [
                'icon' => 'error', 'title' => 'Acción no permitida', 'text' => 'No puedes eliminar tu propia cuenta.'
            ]);
        }
        
        $user->delete();
        $this->dispatch('notify', [
            'icon' => 'success', 
            'title' => 'Eliminado', 
            'text' => 'El usuario ha sido removido del sistema.'
        ]);
    }

    #[Title('Gestión de Usuarios')]
    public function render()
    {
        $users = User::with('roles')
            ->where(function($query) {
                $query->where('name', 'like', "%{$this->search}%")
                      ->orWhere('username', 'like', "%{$this->search}%")
                      ->orWhere('email', 'like', "%{$this->search}%");
            })
            ->orderBy($this->orderBy, $this->isAsc ? 'asc' : 'desc')
            ->paginate($this->perPage);

        return view('livewire.user-management', [
            'users' => $users,
            'roles' => Role::all(),
        ]);
    }
}
