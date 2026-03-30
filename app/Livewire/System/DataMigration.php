<?php

namespace App\Livewire\System;

use Livewire\Component;
use Livewire\WithFileUploads;
use Spatie\SimpleExcel\SimpleExcelReader;
use App\Models\Employee;
use App\Models\Area;
use App\Models\Unit;
use App\Models\State;
use App\Models\Municipality;
use App\Models\Parish;
use App\Models\Shift;
use App\Models\Position;
use App\Models\AssignedPost;
use App\Models\PayrollType;
use App\Models\Birth;
use App\Models\BirthDetail;
use App\Models\Genetic;
use App\Models\Animal;
use App\Models\Barn;
use App\Models\BarnSection;
use App\Models\Stage;
use App\Services\PicDateService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Livewire\Attributes\Title;

#[Title('Migración de Datos')]
class DataMigration extends Component
{
    use WithFileUploads;

    public $employeeFile;
    public $birthFile;
    public $animalFile;
    public $isImportingEmployees = false;
    public $isImportingBirths = false;
    public $isImportingAnimals = false;

    public function mount()
    {
        // Solo Super Admin puede acceder
        if (!auth()->user()->hasRole('Super Admin')) {
            abort(403, 'No tiene permisos para acceder a esta sección.');
        }
    }

    /**
     * IMPORTACIÓN DE EMPLEADOS
     */
    public function importEmployees()
    {
        try {
            $this->validate([
                'employeeFile' => 'required|max:10240|mimes:xlsx,xls,csv,txt'
            ]);

            $this->isImportingEmployees = true;
            $path = $this->employeeFile->getRealPath();
            $rows = SimpleExcelReader::create($path)->getRows();

            $count = 0;
            $errors = 0;

            DB::beginTransaction();
            foreach ($rows as $row) {
                $get = function($names) use ($row) {
                    foreach ((array)$names as $name) {
                        $cleanName = strtolower(trim($name));
                        $cleanName = str_replace(['á', 'é', 'í', 'ó', 'ú', 'ñ'], ['a', 'e', 'i', 'o', 'u', 'n'], $cleanName);
                        foreach ($row as $key => $value) {
                            $cleanKey = strtolower(trim($key));
                            $cleanKey = str_replace(['á', 'é', 'í', 'ó', 'ú', 'ñ'], ['a', 'e', 'i', 'o', 'u', 'n'], $cleanKey);
                            if ($cleanKey === $cleanName && !empty($value)) {
                                return is_string($value) ? trim($value) : $value;
                            }
                        }
                    }
                    return null;
                };

                $nationalId = (string) $get(['cedula', 'identidad', 'dni', 'id', 'DNI', 'IDENTIDAD', 'CEDULA']);
                if (empty($nationalId)) { $errors++; continue; }

                $positionName = $get(['cargo', 'posicion', 'posicion_actual']);
                $areaName     = $get(['area centro de costo', 'area', 'centro de costo', 'area cc', 'departamento']);
                $postName     = $get(['area asignada', 'puesto', 'puesto asignado', 'asignacion', 'seccion']);
                $shiftCode    = $get(['turno', 'horario', 'rotacion']);
                $unitName     = $get(['unidadproduccion', 'unidad', 'sitio', 'finca', 'unidad_produccion']);
                $payrollName  = $get(['nomina', 'tipo nomina', 'tipo de nomina', 'tipo_nomina']);

                $position     = $positionName ? Position::whereRaw('UPPER(name) = ?', [strtoupper($positionName)])->first() : null;
                $area         = $areaName ? Area::whereRaw('UPPER(name) = ?', [strtoupper($areaName)])->first() : null;
                $assignedPost = $postName ? AssignedPost::whereRaw('UPPER(name) = ?', [strtoupper($postName)])->first() : null;
                $shift        = $shiftCode ? Shift::whereRaw('UPPER(code) = ?', [strtoupper($shiftCode)])->orWhereRaw('UPPER(name) = ?', [strtoupper($shiftCode)])->first() : null;
                $unit         = $unitName ? Unit::whereRaw('UPPER(name) = ?', [strtoupper($unitName)])->first() : null;
                $payrollTypeId = $get(['fk_idtiponomina', 'nomina_id', 'payroll_type_id']);
                $payrollType   = is_numeric($payrollTypeId) ? PayrollType::find($payrollTypeId) : ($payrollName ? PayrollType::whereRaw('UPPER(name) = ?', [strtoupper($payrollName)])->first() : null);

                $entryDate = null;
                $dateRaw = $get(['fechaingreso', 'fecha ingreso', 'ingreso', 'fecha_ingreso', 'fecha']);
                if (!empty($dateRaw)) { try { $entryDate = Carbon::parse($dateRaw)->format('Y-m-d'); } catch (\Exception $e) { $entryDate = null; } }

                Employee::updateOrCreate(
                    ['national_id' => $nationalId],
                    [
                        'first_names'      => $get(['nombres', 'primer nombre', 'nombre']) ?? 'S/N',
                        'last_names'       => $get(['apellidos', 'segundo nombre', 'apellido']) ?? 'S/N',
                        'phone_mobile'     => $get(['telefono', 'celular', 'telefono movil', 'movil']),
                        'phone_fixed'      => $get(['telefono fijo', 'fijo', 'casa']),
                        'state_id'         => is_numeric($stId = $get(['fk_idestador', 'estado_id'])) && State::find($stId) ? $stId : null,
                        'municipality_id'  => is_numeric($mId = $get(['fk_idmunicipior', 'municipio_id'])) && Municipality::find($mId) ? $mId : null,
                        'parish_id'        => is_numeric($pId = $get(['fk_idparroquiar', 'parroquia_id'])) && Parish::find($pId) ? $pId : null,
                        'city'             => $get(['ciudad', 'localidad']),
                        'address'          => $get(['direccion', 'domicilio', 'direccion_exacta']),
                        'entry_date'       => $entryDate,
                        'file_number'      => $get(['numeroficha', 'ficha', 'num ficha', 'expediente', 'id_ficha']),
                        'payroll_type_id'  => $payrollType?->id,
                        'position_id'      => $position?->id,
                        'area_id'          => $area?->id,
                        'cost_center_code' => $get(['centrocosto', 'codigo centro costo', 'centro_costo']) ?? $area?->cost_center,
                        'assigned_post_id' => $assignedPost?->id,
                        'shift_id'         => $shift?->id,
                        'unit_id'          => $unit?->id,
                        'estatus'          => $get(['estatus', 'tipo contrato', 'condicion']) ?? 'Fijo',
                        'estadonomina'     => $get(['estadonomina', 'estado nomina', 'estado', 'nomina']) ?? 'Activo',
                        'status'           => $get(['estadonomina', 'estado nomina', 'estado', 'nomina']) ?? 'Activo',
                    ]
                );
                $count++;
            }
            DB::commit();
            $this->reset('employeeFile');
            $this->dispatch('notify', [
                'icon' => $errors > 0 ? 'warning' : 'success', 
                'title' => "Personal: $count procesados",
                'text' => $errors > 0 ? "Omitidos $errors sin ID." : "Importación exitosa."
            ]);
        } catch (\Exception $e) {
            if (DB::transactionLevel() > 0) DB::rollBack();
            Log::error("Error importando empleados: " . $e->getMessage());
            $this->dispatch('notify', ['icon' => 'error', 'title' => 'Error', 'text' => $e->getMessage()]);
        }
        $this->isImportingEmployees = false;
    }

    /**
     * IMPORTACIÓN DE PARTOS
     */
    public function importBirths()
    {
        try {
            $this->validate([
                'birthFile' => 'required|max:10240|mimes:xlsx,xls,csv'
            ]);

            $this->isImportingBirths = true;
            $path = $this->birthFile->getRealPath();
            
            $geneticsMap = Genetic::all()->pluck('id', 'name')->toArray();
            $employees = Employee::all();
            $rows = SimpleExcelReader::create($path)->getRows();
            $birthsGrouped = [];
            
            foreach ($rows as $row) {
                $asString = function($val, $isDateColumn = false) {
                    if ($val instanceof \DateTimeInterface) {
                        if ($isDateColumn) return $val->format('Y-m-d');
                        $baseDate = new \DateTime('1899-12-30'); $diff = $baseDate->diff($val); return (string)$diff->days;
                    }
                    return trim((string)$val);
                };

                $mother = $row['IDMADRE'] ?? null;
                $dateRaw = $row['Fecha'] ?? null;
                if (!$mother || !$dateRaw) continue;

                $motherStr = $asString($mother);
                $dateStr = $asString($dateRaw, true);
                $roomStr = trim($asString($row['SALA'] ?? ($row['Sala'] ?? '')));
                $cageStr = trim($asString($row['JAULA'] ?? ($row['Jaula'] ?? '')));
                $key = "{$motherStr}_{$dateStr}_{$roomStr}_{$cageStr}";

                if (!isset($birthsGrouped[$key])) {
                    $dateObj = ($dateRaw instanceof \DateTimeInterface) ? $dateRaw : (Carbon::createFromFormat('d/m/Y', $dateRaw) ?: Carbon::parse($dateRaw));
                    $formattedDate = $dateObj->format('Y-m-d');
                    $picData = PicDateService::fromDate($formattedDate);
                    
                    $geneticName = strtoupper(trim($asString($row['GENETICA'] ?? '')));
                    $geneticId = $geneticsMap[$geneticName] ?? null;
                    if (!$geneticId) { foreach ($geneticsMap as $name => $id) { if (str_contains(strtoupper($name), $geneticName)) { $geneticId = $id; break; } } }

                    $targetResp = trim($asString($row['Responsable'] ?? ''));
                    $respId = null;
                    if (!empty($targetResp)) {
                        $normalize = function($text) {
                            $utf8 = ['/[áàâãªä]/u' => 'A', '/[ÁÀÂÃÄ]/u' => 'A', '/[ÍÌÎÏ]/u' => 'I', '/[íìîï]/u' => 'I', '/[éèêë]/u' => 'E', '/[ÉÈÊË]/u' => 'E', '/[óòôõºö]/u' => 'O', '/[ÓÒÔÕÖ]/u' => 'O', '/[úùûü]/u' => 'U', '/[ÚÙÛÜ]/u' => 'U', '/[ç]/u' => 'C', '/[Ç]/u' => 'C', '/[ñ]/u' => 'N', '/[Ñ]/u' => 'N'];
                            return preg_replace(array_keys($utf8), array_values($utf8), strtoupper($text));
                        };
                        $targetNorm = $normalize($targetResp);
                        $targetWords = array_filter(explode(' ', $targetNorm), fn($w) => strlen($w) > 2);
                        foreach ($employees as $emp) {
                            $fullEmpName = $normalize($emp->first_names . ' ' . $emp->last_names);
                            $allMatch = true; 
                            foreach ($targetWords as $word) { if (!str_contains($fullEmpName, $word)) { $allMatch = false; break; } }
                            if ($allMatch && !empty($targetWords)) { $respId = $emp->id; break; }
                        }
                        if (!$respId) {
                            $parts = explode(' ', $targetResp, 2);
                            $newEmp = Employee::firstOrCreate(['first_names' => strtoupper($parts[0]), 'last_names' => strtoupper($parts[1] ?? 'N/A')], ['national_id' => 'IMP-' . strtoupper(substr(preg_replace('/\s+/', '', $targetResp), 0, 10)) . '-' . now()->format('ymd'), 'status' => 'imported']);
                            $respId = $newEmp->id; $employees->push($newEmp);
                        }
                    }

                    $birthsGrouped[$key] = [
                        'header' => [
                            'calendar_date'  => $formattedDate,
                            'pic_cycle'      => !empty($v=$asString($row['Vuelta'] ?? '')) ? (int)$v : $picData['vuelta'],
                            'pic_day'        => !empty($p=$asString($row['PIC'] ?? '')) ? (int)$p : $picData['pic'],
                            'room'           => $roomStr, 'cage' => $cageStr, 'mother_tag' => $motherStr, 'parity' => (int)$asString($row['PARIDAD'] ?? 0),
                            'father_tag'     => $asString($row['IDPADRE'] ?? ''), 'lnv' => (int)$asString($row['LNV'] ?? 0),
                            'maternity_lot'  => $lotVal = $asString($row['Lote Maternidad'] ?? ''), 'estado' => !empty($lotVal) ? 1 : 2,
                            'genetic_id'     => $geneticId, 'responsible_id' => $respId,
                        ],
                        'details' => []
                    ];
                }
                $birthsGrouped[$key]['details'][] = [
                    'generated_id'  => $asString($row['ID ARETE'] ?? ''), 'ear_id' => $asString($row['ID OREJA'] ?? ''),
                    'weight'        => str_replace(',', '.', $asString($row['PESO'] ?? '0')),
                    'teats_total'   => (int)$asString($row['N PEZONES'] ?? 0), 'sex' => ucfirst(strtolower($asString($row['SEXO'] ?? 'Hembra'))),
                ];
            }

            $countBirths = 0; $countDetails = 0;
            DB::transaction(function () use ($birthsGrouped, &$countBirths, &$countDetails) {
                foreach ($birthsGrouped as $data) {
                    $data['header']['quantity'] = count($data['details']);
                    $birth = Birth::create($data['header']); $countBirths++;
                    foreach ($data['details'] as $detail) { $detail['birth_id'] = $birth->id; BirthDetail::create($detail); $countDetails++; }
                    if ($data['header']['genetic_id']) { Genetic::find($data['header']['genetic_id'])->increment('last_id_counter', count($data['details'])); }
                }
            });

            $this->reset('birthFile');
            $this->dispatch('notify', ['icon' => 'success', 'title' => 'Partos: Carga completa', 'text' => "Registrados $countBirths partos y $countDetails lechones."]);
        } catch (\Exception $e) {
            if (DB::transactionLevel() > 0) DB::rollBack();
            Log::error("Error importando partos: " . $e->getMessage());
            $this->dispatch('notify', ['icon' => 'error', 'title' => 'Error', 'text' => $e->getMessage()]);
        }
        $this->isImportingBirths = false;
    }

    /**
     * IMPORTACIÓN DE ANIMALES (INVENTARIO)
     */
    public function importAnimals()
    {
        try {
            $this->validate([
                'animalFile' => 'required|max:20480|mimes:xlsx,xls,csv'
            ]);

            $this->isImportingAnimals = true;
            $path = $this->animalFile->getRealPath();
            $rows = SimpleExcelReader::create($path)->getRows();

            $genetics = Genetic::all();
            $barns = Barn::all();
            $sections = BarnSection::all();

            $count = 0;
            $pedigreeData = []; // Para vinculación post-carga

            DB::beginTransaction();

            $todayPicData = PicDateService::fromDate(now());
            $currVuelta = $todayPicData['vuelta'];
            $currPic = $todayPicData['pic'];

            foreach ($rows as $row) {
                $get = function($names) use ($row) {
                    foreach ((array)$names as $name) {
                        $cleanName = strtolower(trim($name));
                        $cleanName = str_replace(['á', 'é', 'í', 'ó', 'ú', 'ñ', '.', ' ', '-', '_'], '', $cleanName);
                        foreach ($row as $key => $value) {
                            $cleanKey = strtolower(trim($key));
                            $cleanKey = str_replace(['á', 'é', 'í', 'ó', 'ú', 'ñ', '.', ' ', '-', '_'], '', $cleanKey);
                            if ($cleanKey === $cleanName && !empty($value)) {
                                return is_string($value) ? trim($value) : $value;
                            }
                        }
                    }
                    return null;
                };

                $fmtDate = function($val) {
                    if (empty($val)) return null;
                    try {
                        if ($val instanceof \DateTimeInterface) return $val->format('Y-m-d');
                        return Carbon::parse($val)->format('Y-m-d');
                    } catch (\Exception $e) { return null; }
                };

                $genName = strtoupper($get(['raza', 'genetica']) ?? '');
                $genetic = $genName ? $genetics->first(fn($g) => strtoupper($g->name) === $genName || str_contains(strtoupper($g->name), $genName)) : null;

                $stageName = strtoupper($get(['etapa', 'stage']) ?? '');
                $stage = $stageName ? Stage::where('name', 'like', "%$stageName%")->first() : null;

                $barnName = strtoupper($get(['nave', 'pabellon', 'area']) ?? '');
                $barn = $barnName ? $barns->first(fn($b) => strtoupper($b->name) === $barnName) : null;

                $secName = strtoupper($get(['seccion', 'sala']) ?? '');
                $section = $secName ? $sections->first(fn($s) => strtoupper($s->name) === $secName) : null;

                $internalId = $get(['idanimal', 'id', 'internalid', 'identificacion', 'i-d']);
                $lotNum = $get(['lote', 'lotemanejo']);
                $birthDateExcel = $fmtDate($get(['fechanacimiento', 'fnacimiento', 'nacimiento', 'f. nacimiento']));
                
                // Lógica de cálculo de fecha de nacimiento por PIC
                $birthDateFinal = $birthDateExcel;
                $ageDays = (int)$get(['edad', 'dias']);

                if (!$birthDateFinal && $lotNum && is_numeric($lotNum)) {
                    $lotPic = (int)$lotNum;
                    $birthVuelta = ($lotPic > $currPic) ? ($currVuelta - 1) : $currVuelta;
                    $birthDateFinal = PicDateService::toDate($birthVuelta, $lotPic);
                }

                if ($birthDateFinal && !$ageDays) {
                    $birthPicData = PicDateService::fromDate($birthDateFinal);
                    $ageDays = $todayPicData['total_days'] - $birthPicData['total_days'];
                }

                $animal = Animal::updateOrCreate(
                    ['internal_id' => $internalId],
                    [
                        'quantity'          => (int)$get(['inv', 'cantidad']) ?: 1,
                        'entry_date'        => $fmtDate($get(['finicio', 'fechaingreso', 'entrada', 'f. inicio'])),
                        'birth_date'        => $birthDateFinal,
                        'source'            => $get(['origen', 'procedencia']),
                        'age_days'          => $ageDays,
                        'management_lot'    => $lotNum, // PIC
                        'genetic_id'        => $genetic?->id,
                        'stage_id'          => $stage?->id,
                        'sex'               => $get(['sexo', 'genero']),
                        'lote_sap'          => $get(['lotesap', 'sap', 'lote sap']),
                        'act_curso'         => $get(['actcurso', 'actividad', 'act. curso']),
                        'order_number'      => (int)$get(['orden']),
                        'evento'            => $get(['evento', 'estado']) ?? 'Activa',
                        'weight'            => (float)str_replace(',', '.', $get(['peso']) ?: 0),
                        'nave_id'           => $barn?->id,
                        'seccion_id'        => $section?->id,
                        'corral'            => (int)$get(['corral', 'jaula']),
                        'feed_type'         => $get(['tipoalimento', 'alimento', 'tipo alimento']),
                        'status'            => $get(['estatus', 'status']) ?? 'Activo',
                        'mother_tag'        => $get(['idmadre', 'madre']) ?? '0',
                        'father_tag'        => $get(['idpadre', 'padre']) ?? '0',
                    ]
                );

                // Guardamos los tags de los padres para la segunda fase
                if ($animal->father_tag !== '0' || $animal->mother_tag !== '0') {
                    $pedigreeData[$animal->id] = [
                        'father_tag' => $animal->father_tag,
                        'mother_tag' => $animal->mother_tag
                    ];
                }

                $count++;
            }

            // SEGUNDA FASE: Vinculación de Pedigree en memoria (Mapa de IDs)
            $animalMap = Animal::whereNotNull('internal_id')->pluck('id', 'internal_id');

            foreach ($pedigreeData as $animalId => $tags) {
                $update = [];
                if ($tags['father_tag'] && isset($animalMap[$tags['father_tag']])) {
                    $update['father_id'] = $animalMap[$tags['father_tag']];
                }
                if ($tags['mother_tag'] && isset($animalMap[$tags['mother_tag']])) {
                    $update['mother_id'] = $animalMap[$tags['mother_tag']];
                }
                if (!empty($update)) {
                    Animal::where('id', $animalId)->update($update);
                }
            }

            DB::commit();

            $this->reset('animalFile');
            $this->dispatch('notify', [
                'icon' => 'success',
                'title' => "Inventario: $count registros",
                'text' => "Importación exitosa. Ubicaciones y pedigree procesados."
            ]);
        } catch (\Exception $e) {
            if (DB::transactionLevel() > 0) DB::rollBack();
            Log::error("Error importando animales: " . $e->getMessage());
            $this->dispatch('notify', ['icon' => 'error', 'title' => 'Error', 'text' => $e->getMessage()]);
        }
        $this->isImportingAnimals = false;
    }

    public function render()
    {
        return view('livewire.system.data-migration');
    }
}
