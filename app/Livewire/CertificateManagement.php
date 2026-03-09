<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Certificate;
use Illuminate\Support\Facades\Auth;

class CertificateManagement extends Component
{
    use WithPagination;

    public $search = '';
    protected $paginationTheme = 'bootstrap';

    public function mount()
    {
        // Verificar roles (Spatie Permission check)
        if (!Auth::user()->hasAnyRole(['Super Admin', 'Admin', 'Veterinario'])) {
            abort(403, 'No tienes permiso para acceder a este módulo.');
        }

        \App\Models\ModuleUsage::track('certificates', 'Listado de Certificados', '/certificates', 'ph-list-bullets', 'text-info');
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function render()
    {
        $certificates = Certificate::where('animal_id', 'like', '%' . $this->search . '%')
            ->orWhere('lote', 'like', '%' . $this->search . '%')
            ->orWhere('raza', 'like', '%' . $this->search . '%')
            ->latest()
            ->paginate(15);

        return view('livewire.certificate-management', [
            'certificates' => $certificates
        ])->title('Listado de Certificados');
    }
}
