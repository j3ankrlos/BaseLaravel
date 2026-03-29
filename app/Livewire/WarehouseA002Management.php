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
    public $Codigo;
    public $Producto;
    public $UMB;
    public $Clasificacion;
    public $Stock = 0;
    public $StockMin = 0;
    public $SolicitudMin = 0;

    public $isEditMode = false;

    protected $rules = [
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
        $this->reset(['productId', 'Codigo', 'Producto', 'UMB', 'Clasificacion', 'Stock', 'StockMin', 'SolicitudMin']);
        $this->isEditMode = false;
        $this->dispatch('open-modal', ['id' => 'productModal']);
    }

    public function editProduct($id)
    {
        $this->resetValidation();
        $product = WarehouseA002::findOrFail($id);
        $this->productId = $product->id;
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
        $rules['Codigo'] = 'required|string|max:255|unique:warehouse_a002_s,Codigo,' . $this->productId;
        
        $this->validate($rules);

        WarehouseA002::updateOrCreate(
            ['id' => $this->productId],
            [
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

            // Seteamos stock a 0 como política habitual
            WarehouseA002::query()->update(['Stock' => 0]);

            $path = $this->excelFile->getRealPath();
            $rows = SimpleExcelReader::create($path)->getRows();

            $processedCount = 0;
            $updatedCount = 0;
            $createdCount = 0;

            foreach ($rows as $row) {
                // Limpieza de datos y mapeo inteligente (Ignoramos mayúsculas/minúsculas en el futuro si fuera necesario, por ahora exactos)
                $codigo = $row['Material'] ?? $row['Codigo'] ?? $row['CÓDIGO'] ?? $row['codigo'] ?? null;
                
                if (!$codigo) continue;

                $processedCount++;

                $producto = $row['Descripción del material'] ?? $row['Producto'] ?? $row['PRODUCTO'] ?? $row['Descripcion'] ?? 'Sin Descripción';
                $umb = $row['Unidad medida base'] ?? $row['UMB'] ?? $row['Unidad de Medida'] ?? 'UN';
                $clasificacion = $row['Clasificacion'] ?? $row['Clasificación'] ?? $row['Categoría'] ?? null;
                
                // Procesamiento de Stock libre utilización
                $stockRaw = $row['Libre utilización'] ?? $row['Stock'] ?? $row['STOCK'] ?? 0;
                // Si viene como string con formato europeo (1.234,50), limpiamos
                if (is_string($stockRaw)) {
                    $stockRaw = str_replace(['.', ','], ['', '.'], $stockRaw);
                }
                $stock = is_numeric($stockRaw) ? (float) $stockRaw : 0;

                // Campos para carga completa (si existen en el excel)
                // Usamos aliases incluyendo la variante con 's' (Solisitud) enviada por el usuario
                $stockMin = isset($row['StockMin']) ? $row['StockMin'] : ($row['Stock Mínimo'] ?? $row['STOCK MÍNIMO'] ?? null);
                
                $solicitudMin = $row['SolicitudMin'] ?? $row['Solicitud Mínima'] ?? $row['SolisitudMin'] ?? $row['SOLICITUD MÍNIMA'] ?? null;

                // Buscamos por código único (Material)
                $product = WarehouseA002::where('Codigo', (string) $codigo)->first();

                $data = [
                    'Producto' => (string) $producto,
                    'UMB' => (string) $umb,
                    'Stock' => ($product ? $product->Stock : 0) + $stock, // Acumulamos stock si se repite Material
                ];

                if ($clasificacion !== null) $data['Clasificacion'] = (string) $clasificacion;
                if ($stockMin !== null && is_numeric($stockMin)) $data['StockMin'] = (float) $stockMin;
                if ($solicitudMin !== null && is_numeric($solicitudMin)) $data['SolicitudMin'] = (float) $solicitudMin;

                if ($product) {
                    $product->update($data);
                    $updatedCount++;
                } else {
                    $data['Codigo'] = (string) $codigo;
                    $data['StockMin'] = $data['StockMin'] ?? 0;
                    $data['SolicitudMin'] = $data['SolicitudMin'] ?? 0;
                    WarehouseA002::create($data);
                    $createdCount++;
                }
            }

            DB::commit();
            $this->reset('excelFile');
            
            $this->dispatch('notify', [
                'icon' => 'success',
                'title' => 'Importación Exitosa',
                'text' => "Se procesaron {$processedCount} registros: {$updatedCount} actualizados y {$createdCount} nuevos."
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            $this->dispatch('notify', [
                'icon' => 'error',
                'title' => 'Error de Importación',
                'text' => 'Error: ' . $e->getMessage()
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
