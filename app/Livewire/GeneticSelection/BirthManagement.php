<?php

namespace App\Livewire\GeneticSelection;

use App\Models\Birth;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class BirthManagement extends Component
{
    use WithPagination;

    public $search = '';
    protected $paginationTheme = 'bootstrap';

    public $pedigreeRoom = null;
    public $pedigreeLot = '';

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function openPedigreeModal($room)
    {
        $this->pedigreeRoom = $room;
        
        // Cargar el lote actual si ya existe uno en esta sala
        $existingBirth = Birth::where('room', $room)
            ->whereNotNull('maternity_lot')
            ->where('maternity_lot', '!=', '')
            ->first();
            
        $this->pedigreeLot = $existingBirth ? $existingBirth->maternity_lot : '';
        
        $this->dispatch('show-pedigree-modal');
    }

    public function exportPedigree()
    {
        $this->validate([
            'pedigreeLot' => 'required|string|max:50'
        ], [
            'pedigreeLot.required' => 'Debe ingresar el Lote de Maternidad.'
        ]);

        $templatePath = storage_path('app/templates/plantilla_pedigree.xlsx');
        if (!file_exists($templatePath)) {
            $this->dispatch('notify', [
                'icon' => 'error',
                'title' => 'Plantilla no encontrada',
                'text' => 'Por favor aségurese de que el archivo plantilla_pedigree.xlsx esté en storage/app/templates/',
            ]);
            $this->dispatch('hide-pedigree-modal');
            return;
        }

        // Fetch births for this room to export
        $births = Birth::where('room', $this->pedigreeRoom)->get();

        if ($births->isEmpty()) {
            $this->dispatch('notify', [
                'icon' => 'warning',
                'title' => 'Sin partos',
                'text' => 'No hay partos registrados para esta sala.'
            ]);
            return;
        }

        // Get old lot to delete old PDF if it exists
        $oldBirth = Birth::where('room', $this->pedigreeRoom)->first();
        $oldLot = $oldBirth ? $oldBirth->maternity_lot : null;

        if ($oldLot && $oldLot !== $this->pedigreeLot) {
            $oldPdfPath = "pedigrees/Pedigree_Sala_{$this->pedigreeRoom}_Lote_{$oldLot}.pdf";
            if (\Illuminate\Support\Facades\Storage::disk('public')->exists($oldPdfPath)) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($oldPdfPath);
            }
        }

        // Update maternity_lot for all births in this room
        Birth::where('room', $this->pedigreeRoom)
             ->update([
                 'maternity_lot' => $this->pedigreeLot,
             ]);

        // Re-fetch with details
        $birthsToExport = Birth::with(['details', 'genetic'])
                            ->where('room', $this->pedigreeRoom)
                            ->orderBy('calendar_date', 'asc')
                            ->get();

        $this->dispatch('hide-pedigree-modal');
        
        // Load Excel Template
        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($templatePath);
        $sheet = $spreadsheet->getActiveSheet();

        // Data starts at row 9 based on the original image
        $index = 1;
        $itemCount = 0;

        foreach ($birthsToExport as $birth) {
            $avgWeight = $birth->details->count() > 0 ? number_format($birth->details->avg('weight'), 2) : '';
            
            foreach ($birth->details as $detail) {
                // Calculate actual row in the Excel template
                $page = (int) floor($itemCount / 40);
                $rowInPage = $itemCount % 40;
                $row = 9 + ($page * 47) + $rowInPage;

                $sheet->setCellValue('A' . $row, $index++);
                $sheet->setCellValue('B' . $row, $birth->room);
                $sheet->setCellValue('C' . $row, $birth->cage);
                $sheet->setCellValue('D' . $row, $birth->mother_tag);
                $sheet->setCellValue('E' . $row, $birth->parity);
                $sheet->setCellValue('F' . $row, $birth->father_tag);
                $sheet->setCellValue('G' . $row, str_pad($birth->pic_day, 3, '0', STR_PAD_LEFT));
                $sheet->setCellValue('I' . $row, $birth->lnv);
                $sheet->setCellValue('J' . $row, $birth->quantity);
                $sheet->setCellValue('K' . $row, $detail->ear_id);
                $sheet->setCellValue('L' . $row, $detail->generated_id);
                $sheet->setCellValue('M' . $row, optional($birth->genetic)->name);
                $sheet->setCellValue('N' . $row, $detail->weight ?? '');
                $sheet->setCellValue('O' . $row, $avgWeight);
                $sheet->setCellValue('P' . $row, $detail->teats_total);
                $sheet->setCellValue('T' . $row, $detail->teats_left);
                $sheet->setCellValue('U' . $row, $detail->teats_behind_shoulder_left);
                $sheet->setCellValue('V' . $row, $detail->teats_behind_shoulder_right);
                $sheet->setCellValue('W' . $row, '');
                $sheet->setCellValue('X' . $row, 'LOTE: ' . $this->pedigreeLot);

                $itemCount++;
            }
        }

        // Setup the print area to include the footer (which takes 6 rows after the 40 records)
        $maxPage = (int) floor(max(0, $itemCount - 1) / 40);
        $maxRow = 54 + ($maxPage * 47); // Data 9-48 + Footer 49-54
        $sheet->getPageSetup()->setPrintArea('A1:X' . $maxRow);

        // Inject Supervisor Name in all printed pages securely without breaking merged cells
        $userName = auth()->user()->name ?? '';
        for ($p = 0; $p <= $maxPage; $p++) {
            $baseRow = $p * 47;
            
            // To maintain Excel's native merge and centering, we just inject the value into the first cell (A)
            // Ensure we do NOT overwrite D53 or others which breaks the merged cell A53:I53
            $sheet->setCellValue('A' . (53 + $baseRow), "Nombre y Apellido:               " . $userName);
        }

        // Configure Margins
        $sheet->getPageMargins()->setTop(0.5);
        $sheet->getPageMargins()->setRight(0.2);
        $sheet->getPageMargins()->setLeft(0.2);
        $sheet->getPageMargins()->setBottom(0.5);

        // Set explicit A4 Portrait to override any wide-format properties hidden inside the .xlsx template
        $sheet->getPageSetup()->setPaperSize(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_A4);
        $sheet->getPageSetup()->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_PORTRAIT);
        $sheet->getPageSetup()->setFitToPage(true);
        $sheet->getPageSetup()->setFitToWidth(1);
        $sheet->getPageSetup()->setFitToHeight(0);

        // Configure PhpSpreadsheet to use Mpdf for flawless scaling of merged cells and tables natively
        $class = \PhpOffice\PhpSpreadsheet\Writer\Pdf\Mpdf::class;
        \PhpOffice\PhpSpreadsheet\IOFactory::registerWriter('Pdf', $class);

        // Ensure directory exists
        if (!Storage::disk('public')->exists('pedigrees')) {
            Storage::disk('public')->makeDirectory('pedigrees');
        }

        $fileName = "Pedigree_Sala_{$this->pedigreeRoom}_Lote_{$this->pedigreeLot}.pdf";
        $fullPath = storage_path("app/public/pedigrees/" . $fileName);

        $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Pdf');
        $writer->save($fullPath);

        $pdfUrl = asset("storage/pedigrees/" . $fileName) . '?v=' . time();
        $this->dispatch('pedigree-generated', ['url' => $pdfUrl]);
        
        $this->dispatch('notify', [
            'icon' => 'success',
            'title' => 'Generado',
            'text' => 'Pedigree PDF generado correctamente.'
        ]);
    }

    public function delete($id)
    {
        $birth = Birth::find($id);
        if ($birth) {
            $birth->delete();
            $this->dispatch('notify', [
                'icon' => 'success',
                'title' => 'Eliminado',
                'text' => 'Registro de parto eliminado correctamente.',
            ]);
        }
    }

    public function render()
    {
        // Group by room + maternity_lot to handle cyclic room reuse
        $roomsQuery = Birth::select(
                'room',
                'maternity_lot',
                DB::raw('MIN(pic_cycle) as pic_cycle'),
                DB::raw('MIN(calendar_date) as first_date'),
                DB::raw('MAX(calendar_date) as last_date'),
                DB::raw('COUNT(*) as total_partos'),
                DB::raw('SUM(quantity) as total_lechones')
            );
            
        if ($this->search) {
            $roomsQuery->where(function ($q) {
                $q->where('room', 'like', '%' . $this->search . '%')
                  ->orWhere('mother_tag', 'like', '%' . $this->search . '%')
                  ->orWhere('maternity_lot', 'like', '%' . $this->search . '%')
                  ->orWhere(DB::raw("CONCAT(pic_cycle, '-', LPAD(pic_day, 3, '0'))"), 'like', '%' . $this->search . '%');
            });
        }

        // Fetch ALL groups — normalize empty lot to null in PHP to avoid duplicates
        $allGroups = $roomsQuery
            ->groupBy('room', 'maternity_lot')
            ->orderByDesc('first_date')
            ->get()
            ->map(function ($r) {
                // Normalize empty string to null so '' and NULL collapse together
                $r->maternity_lot = ($r->maternity_lot === '' || $r->maternity_lot === null) ? null : $r->maternity_lot;
                return $r;
            })
            // Re-group after normalization to merge '' and NULL groups for the same room
            ->groupBy(function ($r) {
                return $r->room . '__' . ($r->maternity_lot ?? 'sin_lote');
            })
            ->map(function ($group) {
                // Merge duplicates: sum totals, take min/max dates
                $first = $group->first();
                $first->total_partos   = $group->sum('total_partos');
                $first->total_lechones = $group->sum('total_lechones');
                $first->first_date     = $group->min('first_date');
                $first->last_date      = $group->max('last_date');
                return $first;
            })
            ->values()
            ->sortByDesc('last_date')
            ->values();

        // Manual pagination of the collection
        $perPage = 40;
        $page = request('page', 1);
        $rooms = new \Illuminate\Pagination\LengthAwarePaginator(
            $allGroups->forPage($page, $perPage),
            $allGroups->count(),
            $perPage,
            $page,
            ['path' => request()->url()]
        );

        // Build a unique key per room+lot group for fetching births
        $birthsByGroup = collect();
        foreach ($rooms as $room) {
            $lot = $room->maternity_lot;
            $query = Birth::with(['genetic', 'responsible', 'details'])
                ->where('room', $room->room);
            
            if ($lot) {
                $query->where('maternity_lot', $lot);
            } else {
                $query->where(function ($q) {
                    $q->whereNull('maternity_lot')->orWhere('maternity_lot', '');
                });
            }

            $births = $query->orderByDesc('calendar_date')->get();
            $lotKey = $lot ?? 'sin_lote';
            $birthsByGroup[$room->room . '__' . $lotKey] = $births;
        }

        // Farm lookup
        $roomsOnPage = $rooms->pluck('room')->unique()->toArray();
        $barnFarms = [];
        foreach ($roomsOnPage as $roomName) {
            $barn = \App\Models\Barn::where('name', $roomName)->first();
            if ($barn) {
                $barnFarms[$roomName] = $barn->farm;
                continue;
            }
            $section = \App\Models\BarnSection::where('name', $roomName)->with('barn')->first();
            if ($section && $section->barn) {
                $barnFarms[$roomName] = $section->barn->farm;
            }
        }

        return view('livewire.genetic-selection.birth-management', [
            'rooms'         => $rooms,
            'birthsByGroup' => $birthsByGroup,
            'barnFarms'     => $barnFarms,
        ]);
    }
}
