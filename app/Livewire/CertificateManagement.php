<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Certificate;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Title;

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

    #[Title('Listado de Certificados')]
    public function render()
    {
        $certificates = Certificate::where('animal_id', 'like', '%' . $this->search . '%')
            ->orWhere('lote', 'like', '%' . $this->search . '%')
            ->orWhere('raza', 'like', '%' . $this->search . '%')
            ->latest()
            ->paginate(15);

        return view('livewire.certificate-management', [
            'certificates' => $certificates
        ]);
    }    public function exportPdf($id)
    {
        $cert = Certificate::findOrFail($id);
        
        $templatePath = storage_path('app/templates/plantilla_certificado.xlsx');
        if (!file_exists($templatePath)) {
            $this->dispatch('notify', ['icon' => 'error', 'title' => 'Error', 'text' => 'Plantilla no encontrada en ' . $templatePath]);
            return;
        }

        // Preparar datos para el script de Python
        $data = $cert->toArray();
        $data['fecha_registro_formatted'] = \Carbon\Carbon::parse($cert->fecha_registro)->format('d/m/Y');
        $data['fecha_muerte_formatted'] = $cert->fecha_muerte ? \Carbon\Carbon::parse($cert->fecha_muerte)->format('d/m/Y') : 'N/A';
        $data['hora_registro'] = \Carbon\Carbon::parse($cert->fecha_registro)->format('H:i:s');
        
        // Rutas absolutas para las fotos
        if ($cert->arete_photo_path) $data['arete_photo_path'] = storage_path('app/public/' . $cert->arete_photo_path);
        if ($cert->tatuaje_photo_path) $data['tatuaje_photo_path'] = storage_path('app/public/' . $cert->tatuaje_photo_path);
        if ($cert->otra_photo_path) $data['otra_photo_path'] = storage_path('app/public/' . $cert->otra_photo_path);

        $jsonPath = storage_path("app/temp_cert_{$cert->id}.json");
        file_put_contents($jsonPath, json_encode($data));

        $fileName = "Certificado_Muerte_{$cert->animal_id}_{$cert->id}.pdf";
        $pdfPath = storage_path("app/public/certificates_pdf/" . $fileName);

        if (!file_exists(storage_path("app/public/certificates_pdf"))) {
            mkdir(storage_path("app/public/certificates_pdf"), 0755, true);
        }

        $pythonScript = base_path('python_scripts/generate_certificate_json.py');
        
        // Ejecutar script de Python
        $command = "python \"{$pythonScript}\" \"{$jsonPath}\" \"{$templatePath}\" \"{$pdfPath}\"";
        
        exec($command, $output, $returnCode);

        // Limpiar JSON temporal
        if (file_exists($jsonPath)) unlink($jsonPath);

        if ($returnCode !== 0) {
            $errorMsg = implode("\n", $output);
            $this->dispatch('notify', [
                'icon' => 'error', 
                'title' => 'Error en Python', 
                'text' => 'No se pudo generar el PDF. Asegúrese de que Excel esté instalado. Detalle: ' . substr($errorMsg, 0, 100)
            ]);
            return;
        }

        $pdfUrl = asset("storage/certificates_pdf/" . $fileName) . '?v=' . time();
        $this->dispatch('certificate-generated', ['url' => $pdfUrl]);
    }
}
