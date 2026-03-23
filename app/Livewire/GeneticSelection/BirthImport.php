<?php

namespace App\Livewire\GeneticSelection;

use App\Models\Birth;
use App\Models\BirthDetail;
use App\Models\Genetic;
use App\Models\Employee;
use App\Services\PicDateService;
use Livewire\Component;
use Livewire\WithFileUploads;
use Spatie\SimpleExcel\SimpleExcelReader;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Livewire\Attributes\Title;

#[Title('Importar Partos desde Excel')]
class BirthImport extends Component
{
    use WithFileUploads;

    public $excelFile;
    public $importing = false;
    public $results = null;

    protected $rules = [
        'excelFile' => 'required|mimes:xlsx,xls,csv|max:10240', // 10MB max
    ];

    public function import()
    {
        $this->validate();
        $this->importing = true;
        
        try {
            $path = $this->excelFile->getRealPath();
            
            $geneticsMap = Genetic::all()->pluck('id', 'name')->toArray();
            $employees = Employee::all();

            $rows = SimpleExcelReader::create($path)->getRows();
            $birthsGrouped = [];
            
            foreach ($rows as $row) {
                // Helper can convert any Excel value to a clean string
                // Handles Excel's "helpful" date conversions for non-date fields
                $asString = function($val, $isDateColumn = false) {
                    if ($val instanceof \DateTimeInterface) {
                        // If it's a date column (like "Fecha"), format as Y-m-d
                        if ($isDateColumn) return $val->format('Y-m-d');
                        
                        // If it's NOT a date column but Excel thinks it is (like Sala 25),
                        // convert it back to the numeric value.
                        $baseDate = new \DateTime('1899-12-30');
                        $diff = $baseDate->diff($val);
                        return (string)$diff->days;
                    }
                    return trim((string)$val);
                };

                $dateRaw = $row['Fecha'] ?? null;
                $mother = $row['IDMADRE'] ?? null;
                $room = $row['SALA'] ?? ($row['Sala'] ?? null);
                $cage = $row['JAULA'] ?? ($row['Jaula'] ?? null);

                if (!$mother || !$dateRaw) continue;

                $dateStr = $asString($dateRaw, true);
                $motherStr = $asString($mother);
                $roomStr = trim($asString($room));
                $cageStr = trim($asString($cage));

                $key = "{$motherStr}_{$dateStr}_{$roomStr}_{$cageStr}";

                if (!isset($birthsGrouped[$key])) {
                    // Header Data
                    $dateObj = null;
                    if ($dateRaw instanceof \DateTimeInterface) {
                        $dateObj = $dateRaw;
                    } else {
                        try {
                            $dateObj = Carbon::createFromFormat('d/m/Y', $dateRaw);
                        } catch (\Exception $e) {
                            try { $dateObj = Carbon::parse($dateRaw); } catch (\Exception $e2) { $dateObj = now(); }
                        }
                    }
                    
                    $formattedDate = $dateObj->format('Y-m-d');
                    $picData = PicDateService::fromDate($formattedDate);

                    // PIC/Vuelta from Excel or calculated
                    $picEx = $asString($row['PIC'] ?? '');
                    $vltEx = $asString($row['Vuelta'] ?? '');
                    
                    $finalPicCycle = !empty($vltEx) ? (int)$vltEx : $picData['vuelta'];
                    $finalPicDay = !empty($picEx) ? (int)$picEx : $picData['pic'];

                    // Genetic Mapping
                    $geneticName = strtoupper(trim($asString($row['GENETICA'] ?? '')));
                    $geneticId = $geneticsMap[$geneticName] ?? null;
                    if (!$geneticId) {
                        foreach ($geneticsMap as $name => $id) {
                            if (str_contains(strtoupper($name), $geneticName)) {
                                $geneticId = $id;
                                break;
                            }
                        }
                    }

                    // Employee Mapping (Robust Match + Auto-Create)
                    $targetResp = trim($asString($row['Responsable'] ?? ''));
                    $respId = null;
                    
                    // Normalize helper (removes accents, uppercases)
                    $normalize = function($text) {
                        $utf8 = [
                            '/[áàâãªä]/u' => 'A', '/[ÁÀÂÃÄ]/u' => 'A', '/[ÍÌÎÏ]/u' => 'I', '/[íìîï]/u' => 'I',
                            '/[éèêë]/u'   => 'E', '/[ÉÈÊË]/u'   => 'E', '/[óòôõºö]/u' => 'O', '/[ÓÒÔÕÖ]/u' => 'O',
                            '/[úùûü]/u'   => 'U', '/[ÚÙÛÜ]/u'   => 'U', '/[ç]/u'       => 'C', '/[Ç]/u'       => 'C',
                            '/[ñ]/u'       => 'N', '/[Ñ]/u'       => 'N'
                        ];
                        return preg_replace(array_keys($utf8), array_values($utf8), strtoupper($text));
                    };

                    $targetNorm = $normalize($targetResp);
                    $targetWords = array_filter(explode(' ', $targetNorm), fn($w) => strlen($w) > 2);

                    foreach ($employees as $emp) {
                        $fullEmpName = $normalize($emp->first_names . ' ' . $emp->last_names);
                        $allMatch = true;
                        foreach ($targetWords as $word) {
                            if (!str_contains($fullEmpName, $word)) {
                                $allMatch = false;
                                break;
                            }
                        }
                        if ($allMatch && !empty($targetWords)) {
                            $respId = $emp->id;
                            break;
                        }
                    }

                    // If not found, auto-create as a historical employee
                    if (!$respId && !empty($targetResp)) {
                        $nameParts = explode(' ', trim($targetResp), 2);
                        $firstName = strtoupper($nameParts[0] ?? $targetResp);
                        $lastName  = strtoupper($nameParts[1] ?? 'N/A');

                        $newEmp = Employee::firstOrCreate(
                            ['first_names' => $firstName, 'last_names' => $lastName],
                            [
                                'national_id' => 'IMP-' . strtoupper(substr(preg_replace('/\s+/', '', $targetResp), 0, 10)) . '-' . now()->format('ymd'),
                                'status'      => 'imported'
                            ]
                        );
                        $respId = $newEmp->id;
                        // Add to employees collection so subsequent rows can find this one
                        $employees->push($newEmp);
                    }

                    $birthsGrouped[$key] = [
                        'header' => [
                            'calendar_date'  => $formattedDate,
                            'pic_cycle'      => $finalPicCycle,
                            'pic_day'        => $finalPicDay,
                            'room'           => $roomStr,
                            'cage'           => $cageStr,
                            'mother_tag'     => $motherStr,
                            'parity'         => (int)$asString($row['PARIDAD'] ?? 0),
                            'father_tag'     => $asString($row['IDPADRE'] ?? ''),
                            'lnv'            => (int)$asString($row['LNV'] ?? 0),
                            'quantity'       => (int)$asString($row['N° SELECION'] ?? 0),
                            'maternity_lot'  => $lotVal = $asString($row['Lote Maternidad'] ?? ''),
                            'estado'         => !empty($lotVal) ? 1 : 2,
                            'genetic_id'     => $geneticId,
                            'responsible_id' => $respId,
                        ],
                        'details' => []
                    ];
                }

                $pesoStr = $asString($row['PESO'] ?? '0');
                
                $birthsGrouped[$key]['details'][] = [
                    'generated_id'                => $asString($row['ID ARETE'] ?? ''),
                    'ear_id'                      => $asString($row['ID OREJA'] ?? ''),
                    'weight'                      => str_replace(',', '.', $pesoStr),
                    'teats_total'                 => (int)$asString($row['N PEZONES'] ?? 0),
                    'teats_left'                  => (int)$asString($row['IZQ'] ?? 0),
                    'teats_behind_shoulder_left'  => (int)$asString($row['DTRZ OMB IZQ.'] ?? 0),
                    'teats_behind_shoulder_right' => (int)$asString($row['DTRZ OMB DER.'] ?? 0),
                    'sex'                         => ucfirst(strtolower($asString($row['SEXO'] ?? 'Hembra'))),
                ];
            }

            $countBirths = 0;
            $countDetails = 0;

            DB::transaction(function () use ($birthsGrouped, &$countBirths, &$countDetails) {
                foreach ($birthsGrouped as $data) {
                    // Sobreescribimos la cantidad del encabezado con el conteo real de los detalles recolectados
                    $data['header']['quantity'] = count($data['details']);
                    
                    $birth = Birth::create($data['header']);
                    $countBirths++;
                    
                    foreach ($data['details'] as $detail) {
                        $detail['birth_id'] = $birth->id;
                        BirthDetail::create($detail);
                        $countDetails++;
                    }
                    if ($data['header']['genetic_id']) {
                        Genetic::find($data['header']['genetic_id'])->increment('last_id_counter', count($data['details']));
                    }
                }
            });

            $this->results = ['success' => true, 'births' => $countBirths, 'details' => $countDetails];
            session()->flash('message', "Importación completada: {$countBirths} partos y {$countDetails} lechones registrados.");

        } catch (\Exception $e) {
            Log::error("Error importación: " . $e->getMessage());
            $this->results = ['success' => false, 'error' => $e->getMessage()];
            session()->flash('error', $e->getMessage());
        }

        $this->importing = false;
    }

    public function render()
    {
        return view('livewire.genetic-selection.birth-import');
    }
}
