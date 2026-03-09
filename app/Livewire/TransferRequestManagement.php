<?php

namespace App\Livewire;

use App\Models\TransferRequest;
use App\Models\WarehouseA002;
use App\Models\WarehouseA006;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;

class TransferRequestManagement extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $search = '';

    public function mount()
    {
        \App\Models\ModuleUsage::track('requests', 'Gestionar Solicitudes', '/warehouse/requests', 'ph-check-square', 'text-success');
    }
    
    
    // Properties for modal management
    public $requestId;
    public $requestFolio;
    public $requestComentarios;
    public $requestDetails = []; // To hold details lines
    public $approvalData = []; // To track changes in approvals

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function manageRequest($id)
    {
        $this->resetValidation();
        $request = TransferRequest::with('details')->findOrFail($id);
        
        $this->requestId = $request->id;
        $this->requestFolio = $request->folio;
        $this->requestComentarios = $request->comentarios;
        
        $this->requestDetails = $request->details->toArray();
        $this->approvalData = [];

        foreach ($this->requestDetails as $detail) {
            $targetProductA002 = WarehouseA002::where('Codigo', $detail['Codigo'])->first();
            $stockDisponible = $targetProductA002 ? $targetProductA002->Stock : 0;
            
            $this->approvalData[$detail['id']] = [
                'cantidad_aprobada' => $detail['cantidad_solicitada'], // pre-fill
                'stock_a002' => $stockDisponible,
            ];
        }

        $this->dispatch('open-modal', ['id' => 'manageModal']);
    }

    public function approveRequest()
    {
        // Custom validation to ensure no approved qty exceeds available stock
        foreach ($this->requestDetails as $detail) {
            $dId = $detail['id'];
            $reqQ = $this->approvalData[$dId]['cantidad_aprobada'];
            $stock = $this->approvalData[$dId]['stock_a002'];

            if ($reqQ < 0) {
                $this->addError("approvalData.{$dId}.cantidad_aprobada", "No puede ser menor a 0.");
                return;
            }
            if ($reqQ > $stock) {
                $this->addError("approvalData.{$dId}.cantidad_aprobada", "Supera el stock disponible ($stock).");
                return;
            }
        }

        try {
            DB::beginTransaction();

            $request = TransferRequest::findOrFail($this->requestId);
            
            foreach ($this->requestDetails as $detailData) {
                $dId = $detailData['id'];
                $approvedQty = $this->approvalData[$dId]['cantidad_aprobada'];

                if ($approvedQty > 0) {
                    $productA002 = WarehouseA002::where('Codigo', $detailData['Codigo'])->first();

                    if ($productA002) {
                        $productA002->Stock -= $approvedQty;
                        $productA002->save();
                    }

                    $productA006 = WarehouseA006::where('Codigo', $detailData['Codigo'])->first();
                    if ($productA006) {
                        $productA006->Stock += $approvedQty;
                        $productA006->save();
                    } else {
                        WarehouseA006::create([
                            'IdCodigo' => $detailData['IdCodigo'],
                            'Codigo' => $detailData['Codigo'],
                            'Producto' => $detailData['Producto'],
                            'UMB' => $detailData['UMB'],
                            'Stock' => $approvedQty
                        ]);
                    }
                }

                $detailQuery = \App\Models\TransferRequestDetail::find($dId);
                if($detailQuery){
                    $detailQuery->update([
                        'cantidad_aprobada' => $approvedQty
                    ]);
                }
            }

            $request->update([
                'estado' => 'aprobada',
                'user_id_aprobador' => auth()->id()
            ]);

            DB::commit();

            $this->dispatch('close-modal', ['id' => 'manageModal']);
            $this->dispatch('notify', [
                'icon' => 'success',
                'title' => 'Solicitud Aprobada',
                'text' => 'La mercancía se ha procesado correctamente y enviada al Almacén A006.'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            $this->dispatch('notify', [
                'icon' => 'error',
                'title' => 'Error',
                'text' => 'Ocurrió un error al procesar la aprobación: ' . $e->getMessage()
            ]);
        }
    }

    public function rejectRequest($id)
    {
        $request = TransferRequest::findOrFail($id);
        if ($request->estado !== 'pendiente') return;

        $request->update([
            'estado' => 'rechazada',
            'user_id_aprobador' => auth()->id()
        ]);

        $this->dispatch('close-modal', ['id' => 'manageModal']);
        $this->dispatch('notify', [
            'icon' => 'info',
            'title' => 'Rechazada',
            'text' => 'La solicitud de transferencia ha sido denegada.'
        ]);
    }

    public function render()
    {
        $requests = TransferRequest::with(['solicitante', 'aprobador', 'details'])
            ->where(function($q) {
                if ($this->search) {
                    $q->where('folio', 'like', '%' . $this->search . '%')
                      ->orWhereHas('details', function($q2) {
                          $q2->where('Codigo', 'like', '%' . $this->search . '%')
                             ->orWhere('Producto', 'like', '%' . $this->search . '%');
                      });
                }
            })
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('livewire.transfer-request-management', [
            'requests' => $requests
        ])->title('Solicitudes');
    }
}
