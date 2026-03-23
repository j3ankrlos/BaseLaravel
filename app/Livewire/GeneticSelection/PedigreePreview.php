<?php

namespace App\Livewire\GeneticSelection;

use App\Models\Birth;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Title;

#[Title('Vista Previa Pedigree')]
class PedigreePreview extends Component
{
    public $room;
    public $pedigreeLot = '';
    public $originalLot = ''; // Para recordar cuál era el lote antes de posibles modificaciones
    public $births = [];

    public function mount($room)
    {
        $this->room = $room;

        // Buscar si ya existe algún lote asignado a los partos activos de esta sala
        $existingLot = Birth::where('room', $room)
                    ->where('estado', 2)
                    ->whereNotNull('maternity_lot')
                    ->where('maternity_lot', '!=', '')
                    ->value('maternity_lot');

        // Si ya tiene un lote, pre-llenar el campo para permitir su modificación.
        $this->pedigreeLot = $existingLot ?: '';
        $this->originalLot = $this->pedigreeLot;

        $this->loadBirths();
    }

    public function loadBirths()
    {
        // Solo partos activos (estado=2) y sin lote asignado aún
        $this->births = Birth::with(['details', 'genetic'])
            ->where('room', $this->room)
            ->where('estado', 2) // 2 = Activa
            ->where(function ($q) {
                $q->whereNull('maternity_lot')
                  ->orWhere('maternity_lot', '');
                
                // Si la sala ya tenía un lote original asignado, traer esos partos para ser vistos o re-actualizados
                if ($this->originalLot) {
                    $q->orWhere('maternity_lot', $this->originalLot);
                }
            })
            ->orderBy('calendar_date', 'asc')
            ->get();
    }

    public function generatePdf()
    {
        $this->validate([
            'pedigreeLot' => 'required|string|max:50'
        ], [
            'pedigreeLot.required' => 'Debe ingresar el Lote de Maternidad para generar el Pedigree.'
        ]);

        if (count($this->births) === 0) {
            $this->dispatch('notify', [
                'icon' => 'warning',
                'title' => 'Sin partos',
                'text' => 'No hay partos para exportar.'
            ]);
            return;
        }

        // Modificar el lote SÓLO de los partos activos de la sala que correspondan 
        // a los partos "sin lote" o "del lote original asignado antes de la nueva modificación".
        Birth::where('room', $this->room)
             ->where('estado', 2)
             ->where(function($q) {
                 $q->whereNull('maternity_lot')
                   ->orWhere('maternity_lot', '');
                 
                 if ($this->originalLot) {
                     $q->orWhere('maternity_lot', $this->originalLot);
                 }
             })
             ->update(['maternity_lot' => $this->pedigreeLot]);

        // Actualizado con éxito. Ahora el "original" de referencia es el nuevo
        $this->originalLot = $this->pedigreeLot;

        // Reload data
        $this->loadBirths();

        $user = Auth::user();
        $responsibleName = $user ? $user->name : 'Administrador';
        if ($user && $user->employee) {
            $responsibleName = trim($user->employee->first_names . ' ' . $user->employee->last_names);
        }
        Log::info("Generando Pedigree. Responsable: " . $responsibleName);

        // Prepare data for JSON
        $data = [
            'lote' => $this->pedigreeLot,
            'sala' => $this->room,
            'responsible' => mb_strtoupper($responsibleName),
            'births' => $this->births->map(function($birth) {
                return [
                    'room' => $birth->room,
                    'cage' => $birth->cage,
                    'mother_tag' => $birth->mother_tag,
                    'parity' => $birth->parity,
                    'father_tag' => $birth->father_tag,
                    'pic_cycle' => $birth->pic_cycle,
                    'pic_day' => $birth->pic_day,
                    'lnv' => $birth->lnv,
                    'quantity' => $birth->quantity,
                    'genetic_name' => optional($birth->genetic)->name,
                    'avg_weight' => $birth->details->count() > 0 ? (float) $birth->details->avg('weight') : null,
                    'details' => $birth->details->map(function($detail) {
                        return [
                            'ear_id' => $detail->ear_id,
                            'generated_id' => $detail->generated_id,
                            'weight' => (float) $detail->weight,
                            'teats_total' => $detail->teats_total,
                            'teats_left' => $detail->teats_left,
                            'teats_behind_shoulder_left' => $detail->teats_behind_shoulder_left,
                            'teats_behind_shoulder_right' => $detail->teats_behind_shoulder_right,
                            'sex' => $detail->sex,
                        ];
                    })->toArray()
                ];
            })->toArray()
        ];

        $jsonPath = storage_path("app/temp_pedigree_{$this->room}.json");
        file_put_contents($jsonPath, json_encode($data));

        $templatePath = storage_path('app/templates/plantilla_pedigree.xlsx');
        if (!file_exists($templatePath)) {
            $this->dispatch('notify', ['icon' => 'error', 'title' => 'Error', 'text' => 'Plantilla de Pedigree no encontrada.']);
            return;
        }

        $fileName = "Pedigree_Sala_{$this->room}_Lote_{$this->pedigreeLot}.pdf";
        $pdfPath = storage_path("app/public/pedigree_pdf/" . $fileName);

        if (!file_exists(storage_path("app/public/pedigree_pdf"))) {
            mkdir(storage_path("app/public/pedigree_pdf"), 0755, true);
        }

        $pythonScript = base_path('python_scripts/generate_pedigree_json.py');
        $command = "python \"{$pythonScript}\" \"{$jsonPath}\" \"{$templatePath}\" \"{$pdfPath}\"";
        
        exec($command, $output, $returnCode);

        // Limpiar JSON temporal
        if (file_exists($jsonPath)) unlink($jsonPath);

        if ($returnCode !== 0) {
            $errorMsg = implode("\n", $output);
            Log::error("Error en Pedigree Python: " . $errorMsg);
            $this->dispatch('notify', [
                'icon' => 'error', 
                'title' => 'Error en Python', 
                'text' => 'No se pudo generar el Pedigree. Por favor verifique que Excel esté cerrado y reintente.'
            ]);
            return;
        }

        return response()->download($pdfPath);
    }

    public function render()
    {
        return view('livewire.genetic-selection.pedigree-preview');
    }
}
