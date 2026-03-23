<?php

namespace App\Livewire;

use App\Models\WarehouseA006;
use App\Models\TransferRequest;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Title;
use Livewire\Attributes\On;
use App\Models\WarehouseA002;

class WarehouseA006Management extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public function mount()
    {
        \App\Models\ModuleUsage::track('a006', 'Inventario A006', '/warehouse/a006', 'ph-stack', 'text-info');
    }

    public $search = '';

    // Modal Request Properties
    public $productSearch = '';
    public $searchResults = [];
    
    // Array for multiple items up to 24
    public $requestItems = [];
    public $reqComentarios = '';

    protected $rules = [
        'requestItems.*.codigo'   => 'required|string|max:255',
        'requestItems.*.producto' => 'required|string|max:255',
        'requestItems.*.umb'      => 'required|string|max:50',
        'requestItems.*.cantidad' => 'required',
        'reqComentarios'          => 'nullable|string',
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function openRequestModal()
    {
        $this->resetValidation();
        $this->reqComentarios = '';
        $this->requestItems = [];
        $this->productSearch = '';
        $this->searchResults = [];
        $this->dispatch('open-modal', ['id' => 'requestModal']);
    }

    public function updatedProductSearch($value)
    {
        if (strlen($value ?? '') >= 2) {
            $this->searchResults = WarehouseA002::where('Codigo', 'like', '%' . $value . '%')
                ->orWhere('Producto', 'like', '%' . $value . '%')
                ->take(10)
                ->get();
        } else {
            $this->searchResults = [];
        }
    }

    public function selectProduct($id)
    {
        if (count($this->requestItems) >= 24) {
            $this->dispatch('notify', [
                'icon' => 'warning',
                'title' => 'Límite alcanzado',
                'text' => 'No puedes agregar más de 24 ítems en una sola solicitud.'
            ]);
            return;
        }

        $product = WarehouseA002::find($id);
        
        if ($product) {
            // Check if already added
            $exists = collect($this->requestItems)->contains('codigo', $product->Codigo);
            
            if (!$exists) {
                // Determine Minimum Request (fallback to 1 if it's 0 or null)
                $qty = $product->SolicitudMin > 0 ? $product->SolicitudMin : 1;

                $this->requestItems[] = [
                    'codigo' => $product->Codigo,
                    'producto' => $product->Producto,
                    'umb' => $product->UMB,
                    'cantidad' => $qty,
                    'stock_a002' => $product->Stock ?? 0,
                ];
            } else {
                $this->dispatch('notify', [
                    'icon' => 'info',
                    'title' => 'Ítem Duplicado',
                    'text' => 'Ese producto ya está en la lista de solicitudes.'
                ]);
            }
        }

        $this->productSearch = '';
        $this->searchResults = [];
    }

    public function removeItem($index)
    {
        unset($this->requestItems[$index]);
        $this->requestItems = array_values($this->requestItems);
    }

    public function submitRequest()
    {
        // Normalize decimals: convert comma to dot in all cantidad fields
        foreach ($this->requestItems as $i => $item) {
            $raw = str_replace(',', '.', trim($item['cantidad'] ?? ''));
            // Clamp to max 3 decimal places
            if (is_numeric($raw)) {
                $this->requestItems[$i]['cantidad'] = round((float) $raw, 3);
            } else {
                $this->requestItems[$i]['cantidad'] = $raw; // let validation catch it
            }
        }

        $this->validate([
            'requestItems.*.codigo'   => 'required|string|max:255',
            'requestItems.*.producto' => 'required|string|max:255',
            'requestItems.*.umb'      => 'required|string|max:50',
            'requestItems.*.cantidad' => 'required|numeric|min:0.001',
            'reqComentarios'          => 'nullable|string',
        ]);

        // Generar un folio simple (ej: REQ-2026-0001)
        $latest = TransferRequest::latest('id')->first();
        $nextId = $latest ? $latest->id + 1 : 1;
        $folio = 'REQ-' . date('Y') . '-' . str_pad($nextId, 4, '0', STR_PAD_LEFT);

        $request = TransferRequest::create([
            'folio' => $folio,
            'estado' => 'pendiente',
            'comentarios' => $this->reqComentarios,
            'user_id_solicitante' => auth()->id()
        ]);

        foreach ($this->requestItems as $item) {
            $request->details()->create([
                'IdCodigo' => $item['codigo'],
                'Codigo' => $item['codigo'],
                'Producto' => $item['producto'],
                'UMB' => $item['umb'],
                'cantidad_solicitada' => $item['cantidad'],
                'cantidad_aprobada' => 0,
            ]);
        }

        $this->dispatch('close-modal', ['id' => 'requestModal']);
        $this->dispatch('notify', [
            'icon' => 'success',
            'title' => 'Solicitud Enviada',
            'text' => count($this->requestItems) . ' ítem(s) solicitados al Almacén A002 correctamente.'
        ]);
    }

    #[Title('Almacén A006')]
    public function render()
    {
        $products = WarehouseA006::where('Codigo', 'like', '%' . $this->search . '%')
            ->orWhere('Producto', 'like', '%' . $this->search . '%')
            ->paginate(15);

        return view('livewire.warehouse-a006-management', [
            'products' => $products
        ]);
    }
}
