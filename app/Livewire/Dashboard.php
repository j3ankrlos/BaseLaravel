<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\WarehouseA002;
use App\Models\WarehouseA006;
use App\Models\TransferRequest;
use App\Models\Employee;
use App\Models\User;
use App\Models\ModuleUsage;

class Dashboard extends Component
{
    public function render()
    {
        $statsA002       = WarehouseA002::count();
        $statsA006       = WarehouseA006::count();
        $statsEmployees  = Employee::count();

        $solicitudesPendientes = TransferRequest::where('estado', 'pendiente')->count();

        // Alertas: productos en A006 con stock crítico (stock <= StockMin)
        $alertasA006 = WarehouseA006::whereColumn('Stock', '<=', 'StockMin')->count();

        // Actividad reciente: últimas solicitudes
        $recentRequests = TransferRequest::with(['solicitante', 'aprobador', 'details'])
            ->latest()
            ->take(8)
            ->get();

        // Top 4 módulos más usados
        $topModules = ModuleUsage::orderBy('hits', 'desc')
            ->take(4)
            ->get();

        // Si no hay datos (primera vez), mostrar algunos por defecto
        if ($topModules->isEmpty()) {
            $topModules = collect([
                (object)[ 'display_name' => 'Nueva Solicitud', 'url' => '/warehouse/a006', 'icon' => 'ph-paper-plane-right', 'color_class' => 'text-warning' ],
                (object)[ 'display_name' => 'Gestionar Personal', 'url' => '/employees', 'icon' => 'ph-briefcase', 'color_class' => 'text-secondary' ],
                (object)[ 'display_name' => 'Registrar Asistencia', 'url' => '/attendance', 'icon' => 'ph-calendar-check', 'color_class' => 'text-danger' ],
                (object)[ 'display_name' => 'Inventario A002', 'url' => '/warehouse/a002', 'icon' => 'ph-hard-drive', 'color_class' => 'text-primary' ],
            ]);
        }

        return view('livewire.dashboard', [
            'statsA002'             => $statsA002,
            'statsA006'             => $statsA006,
            'statsEmployees'        => $statsEmployees,
            'solicitudesPendientes' => $solicitudesPendientes,
            'alertasA006'           => $alertasA006,
            'recentRequests'        => $recentRequests,
            'topModules'            => $topModules,
        ])->title('Resumen del Sistema');
    }
}
