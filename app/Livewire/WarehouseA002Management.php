<?php

namespace App\Livewire;

use App\Models\WarehouseA002;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Spatie\SimpleExcel\SimpleExcelReader;
use Illuminate\Support\Facades\DB;

class WarehouseA002Management extends Component
{
    use WithPagination;
    use WithFileUploads;

    protected $paginationTheme = 'bootstrap';

    public $search = '';
    public $excelFile;

    public function mount()
    {
        \App\Models\ModuleUsage::track('a002', 'Inventario A002', '/warehouse/a002', 'ph-hard-drive', 'text-primary');
    }
    
    
    // Modal properties
    public $productId;
    public $IdCodigo;
    public $Codigo;
    public $Producto;
    public $UMB;
    public $Clasificacion;
    public $Stock = 0;
    public $StockMin = 0;
    public $SolicitudMin = 0;

    public $isEditMode = false;

    protected $rules = [
        'IdCodigo' => 'required|string|max:255',
        'Codigo' => 'required|string|max:255',
        'Producto' => 'required|string|max:255',
        'UMB' => 'required|string|max:50',
        'Clasificacion' => 'nullable|string|max:255',
        'Stock' => 'required|numeric|min:0',
        'StockMin' => 'required|numeric|min:0',
        'SolicitudMin' => 'required|numeric|min:0',
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function openModal()
    {
        $this->resetValidation();
        $this->reset(['productId', 'IdCodigo', 'Codigo', 'Producto', 'UMB', 'Clasificacion', 'Stock', 'StockMin', 'SolicitudMin']);
        $this->isEditMode = false;
        $this->dispatch('open-modal', ['id' => 'productModal']);
    }

    public function editProduct($id)
    {
        $this->resetValidation();
        $product = WarehouseA002::findOrFail($id);
        $this->productId = $product->id;
        $this->IdCodigo = $product->IdCodigo;
        $this->Codigo = $product->Codigo;
        $this->Producto = $product->Producto;
        $this->UMB = $product->UMB;
        $this->Clasificacion = $product->Clasificacion;
        $this->Stock = $product->Stock;
        $this->StockMin = $product->StockMin;
        $this->SolicitudMin = $product->SolicitudMin;
        
        $this->isEditMode = true;
        $this->dispatch('open-modal', ['id' => 'productModal']);
    }

    public function saveProduct()
    {
        $rules = $this->rules;
        $rules['IdCodigo'] = 'required|string|max:255|unique:warehouse_a002_s,IdCodigo,' . $this->productId;
        $rules['Codigo'] = 'required|string|max:255|unique:warehouse_a002_s,Codigo,' . $this->productId;
        
        $this->validate($rules);

        WarehouseA002::updateOrCreate(
            ['id' => $this->productId],
            [
                'IdCodigo' => $this->IdCodigo,
                'Codigo' => $this->Codigo,
                'Producto' => $this->Producto,
                'UMB' => $this->UMB,
                'Clasificacion' => $this->Clasificacion,
                'Stock' => $this->Stock,
                'StockMin' => $this->StockMin,
                'SolicitudMin' => $this->SolicitudMin,
            ]
        );

        $this->dispatch('close-modal', ['id' => 'productModal']);
        $this->dispatch('notify', [
            'icon' => 'success',
            'title' => 'Éxito',
            'text' => $this->isEditMode ? 'Producto actualizado correctamente' : 'Producto creado correctamente'
        ]);
    }

    public function confirmDelete($id)
    {
        $this->dispatch('confirm-delete', [
            'method' => 'deleteProduct',
            'id' => $id,
            'text' => '¿Estás seguro de que deseas eliminar este producto del almacén?'
        ]);
    }

    #[\Livewire\Attributes\On('deleteProduct')]
    public function deleteProduct($data)
    {
        WarehouseA002::findOrFail($data['id'])->delete();
        
        $this->dispatch('notify', [
            'icon' => 'success',
            'title' => 'Eliminado',
            'text' => 'Producto eliminado correctamente'
        ]);
    }

    public function importExcel()
    {
        $this->validate([
            'excelFile' => 'required|file|mimes:xlsx,xls,csv|max:10240', // Max 10MB
        ]);

        try {
            DB::beginTransaction();

            // Set all existing stock to 0 as requested by user logic
            // "solo actualizaremos las existencias de los productos que esten presente en el excel, los que no esten pasaran a 0"
            WarehouseA002::query()->update(['Stock' => 0]);

            $path = $this->excelFile->getRealPath();

            $rows = SimpleExcelReader::create($path)->getRows();

            foreach ($rows as $row) {
                // Ensure array keys exist or parse properly based on provided headers:
                // Material, Descripción del material, Lote, Unidad medida base, Libre utilización, En traslado (Centro), Centro, Almacén
                
                $codigo = $row['Material'] ?? null;
                $producto = $row['Descripción del material'] ?? 'Sin Descripción';
                $umb = $row['Unidad medida base'] ?? 'UN';
                $stockRaw = $row['Libre utilización'] ?? 0;
                
                if (!$codigo) continue;
                
                // Clean stock value (e.g. converting '1.000,50' to float if necessary, though simple excel usually handles generic numbers)
                // If it comes as a string, let's cast it safely
                $stock = is_numeric($stockRaw) ? (float) $stockRaw : 0;

                // Update or create preserving minimum stocks
                $product = WarehouseA002::where('Codigo', (string) $codigo)->first();

                if ($product) {
                    $product->Stock += $stock; // Accumulate if repeated
                    $product->Producto = $producto; // Update description if changed
                    $product->UMB = $umb;
                    $product->save();
                } else {
                    WarehouseA002::create([
                        'IdCodigo' => (string) $codigo, // User usually uses the same for IdCodigo and Codigo in this scenario
                        'Codigo' => (string) $codigo,
                        'Producto' => $producto,
                        'UMB' => $umb,
                        'Stock' => $stock
                    ]);
                }
            }

            DB::commit();

            $this->reset('excelFile');
            
            $this->dispatch('notify', [
                'icon' => 'success',
                'title' => 'Importación Exitosa',
                'text' => 'El inventario del Almacén A002 ha sido actualizado correctamente.'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            $this->dispatch('notify', [
                'icon' => 'error',
                'title' => 'Error de Importación',
                'text' => 'Hubo un error procesando el archivo: ' . $e->getMessage()
            ]);
        }
    }

    public function render()
    {
        $products = WarehouseA002::where('Codigo', 'like', '%' . $this->search . '%')
            ->orWhere('Producto', 'like', '%' . $this->search . '%')
            ->paginate(15);

        return view('livewire.warehouse-a002-management', [
            'products' => $products
        ])->title('Almacén A002');
    }
}
