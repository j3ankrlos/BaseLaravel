<?php

namespace App\Livewire\GeneticSelection;

use App\Models\Birth;
use App\Models\BirthDetail;
use App\Models\Genetic;
use App\Models\Employee;
use App\Services\PicDateService;
use Livewire\Component;
use Illuminate\Support\Facades\DB;
use App\Traits\HandlesDecimals;

class BirthRegistration extends Component
{
    use HandlesDecimals;

    // Current form header fields
    public $calendar_date, $pic_cycle, $pic_day;
    public $sala, $jaula, $madre, $paridad, $padre, $lnv, $cantidad = 1, $genetica_id, $responsable_id;

    // Accumulated pending births (not yet saved to DB)
    public $pending_births = [];

    // "Ultimo ID" preview per genetic (computed live from DB + pending)
    public $ultimo_id = 0;

    protected $rules = [
        'calendar_date'  => 'required|date',
        'pic_cycle'      => 'required|numeric',
        'pic_day'        => 'required|numeric|min:0|max:999',
        'sala'           => 'required',
        'jaula'          => 'required',
        'madre'          => 'required',
        'paridad'        => 'required|numeric',
        'padre'          => 'required',
        'lnv'            => 'required|numeric',
        'cantidad'       => 'required|numeric|min:1|max:50',
        'genetica_id'    => 'required|exists:genetics,id',
        'responsable_id' => 'required|exists:employees,id',
        'pending_births.*.piglets.*.sex' => 'required',
    ];

    public function mount()
    {
        $this->calendar_date = date('Y-m-d');
        $this->calculatePicFields();
    }

    public function updatedCalendarDate($value)
    {
        $this->calculatePicFields();
    }

    public function updatedPicCycle($value)
    {
        $this->calculateCalendarDate();
    }

    public function updatedPicDay($value)
    {
        $this->calculateCalendarDate();
    }

    public function updatedGeneticaId($value)
    {
        $this->refreshUltimoId();
    }

    protected function calculatePicFields()
    {
        if ($this->calendar_date) {
            $data = PicDateService::fromDate($this->calendar_date);
            $this->pic_cycle = $data['vuelta'];
            $this->pic_day   = $data['pic'];
        }
    }

    protected function calculateCalendarDate()
    {
        if (is_numeric($this->pic_cycle) && is_numeric($this->pic_day)) {
            $this->calendar_date = PicDateService::toDate($this->pic_cycle, $this->pic_day)->format('Y-m-d');
        }
    }

    protected function refreshUltimoId()
    {
        if ($this->genetica_id) {
            $genetic = Genetic::find($this->genetica_id);
            if ($genetic) {
                // Base counter from DB + how many we already added in this session for this genetic
                $pendingCount = collect($this->pending_births)
                    ->where('birth_header.genetic_id', $this->genetica_id)
                    ->sum('birth_header.quantity');

                $this->ultimo_id = $genetic->last_id_counter + $pendingCount;
            }
        } else {
            $this->ultimo_id = 0;
        }
    }

    /**
     * Add the current form as a pending birth to the table (without saving to DB yet)
     */
    public function addToPending()
    {
        $this->validateOnly('calendar_date');
        $this->validateOnly('pic_cycle');
        $this->validateOnly('pic_day');
        $this->validateOnly('sala');
        $this->validateOnly('jaula');
        $this->validateOnly('madre');
        $this->validateOnly('paridad');
        $this->validateOnly('padre');
        $this->validateOnly('lnv');
        $this->validateOnly('cantidad');
        $this->validateOnly('genetica_id');
        $this->validateOnly('responsable_id');

        $genetica = Genetic::find($this->genetica_id);

        // Current counter = DB counter + already added in session
        $pendingCountSameGenetic = collect($this->pending_births)
            ->where('birth_header.genetic_id', $this->genetica_id)
            ->sum('birth_header.quantity');
        $startCounter = $genetica->last_id_counter + $pendingCountSameGenetic;

        // F1-T camada logic:
        // camada = births registered in DB for this date + births added in pending for this date
        $camadaDelDia = 1;
        if ($genetica->name === 'F1-T') {
            $dbCount = Birth::where('calendar_date', $this->calendar_date)
                ->where('genetic_id', $this->genetica_id)
                ->count();
            $pendingCount = collect($this->pending_births)
                ->filter(fn($b) => $b['birth_header']['genetic_id'] == $this->genetica_id
                    && $b['birth_header']['calendar_date'] == $this->calendar_date)
                ->count();
            $camadaDelDia = $dbCount + $pendingCount + 1;
        }

        // Build piglets list
        $piglets = [];
        for ($i = 1; $i <= $this->cantidad; $i++) {
            $nextCounter = $startCounter + $i;
            $generatedId = $genetica->code . str_pad($nextCounter, 5, '0', STR_PAD_LEFT);

            // ID Oreja
            $earId = $generatedId;
            if ($genetica->name === 'F1-T') {
                $earId = str_pad($this->pic_day, 3, '0', STR_PAD_LEFT) . '-F' . $camadaDelDia . $i;
            }

            $piglets[] = [
                'generated_id'                => $generatedId,
                'ear_id'                      => $earId,
                'weight'                      => null,
                'teats_total'                 => 14,
                'teats_left'                  => 7,
                'teats_behind_shoulder_left'  => 0,
                'teats_behind_shoulder_right' => 0,
                'sex'                         => 'Hembra',
            ];
        }

        $this->pending_births[] = [
            'birth_header' => [
                'calendar_date'   => $this->calendar_date,
                'pic_cycle'       => $this->pic_cycle,
                'pic_day'         => $this->pic_day,
                'room'            => $this->sala,
                'cage'            => $this->jaula,
                'mother_tag'      => $this->madre,
                'parity'          => $this->paridad,
                'father_tag'      => $this->padre,
                'lnv'             => $this->lnv,
                'quantity'        => $this->cantidad,
                'genetic_id'      => $this->genetica_id,
                'genetic_name'    => $genetica->name,
                'responsible_id'  => $this->responsable_id,
            ],
            'piglets' => $piglets,
        ];

        // Reset only the mother-specific fields, keep date/location/responsible
        $this->reset(['madre', 'paridad', 'padre', 'lnv', 'cantidad', 'genetica_id']);
        $this->cantidad = 1;
        $this->ultimo_id = 0;
    }

    /**
     * Remove a pending birth by index
     */
    public function removePending($index)
    {
        array_splice($this->pending_births, $index, 1);
        $this->pending_births = array_values($this->pending_births);
        $this->refreshUltimoId();
    }

    /**
     * Save all pending births to the database
     */
    public function saveAll()
    {
        if (empty($this->pending_births)) {
            $this->dispatch('notify', [
                'icon'  => 'warning',
                'title' => 'Sin partos pendientes',
                'text'  => 'Agrega al menos un parto antes de guardar.',
            ]);
            return;
        }

        DB::transaction(function () {
            foreach ($this->pending_births as $pending) {
                $header = $pending['birth_header'];

                $birth = Birth::create([
                    'calendar_date'  => $header['calendar_date'],
                    'pic_cycle'      => $header['pic_cycle'],
                    'pic_day'        => $header['pic_day'],
                    'room'           => $header['room'],
                    'cage'           => $header['cage'],
                    'mother_tag'     => $header['mother_tag'],
                    'parity'         => $header['parity'],
                    'father_tag'     => $header['father_tag'],
                    'lnv'            => $header['lnv'],
                    'quantity'       => $header['quantity'],
                    'genetic_id'     => $header['genetic_id'],
                    'responsible_id' => $header['responsible_id'],
                    'estado'         => 2, // 2: Activa
                ]);

                foreach ($pending['piglets'] as $piglet) {
                    $piglet['weight'] = $this->parseDecimal($piglet['weight']);
                    
                    $isMale = $piglet['sex'] === 'Macho';
                    $piglet['teats_total'] = $isMale || empty($piglet['teats_total']) ? null : $piglet['teats_total'];
                    $piglet['teats_left'] = $isMale || empty($piglet['teats_left']) ? null : $piglet['teats_left'];
                    $piglet['teats_behind_shoulder_left'] = $isMale || empty($piglet['teats_behind_shoulder_left']) ? null : $piglet['teats_behind_shoulder_left'];
                    $piglet['teats_behind_shoulder_right'] = $isMale || empty($piglet['teats_behind_shoulder_right']) ? null : $piglet['teats_behind_shoulder_right'];

                    BirthDetail::create(array_merge($piglet, ['birth_id' => $birth->id]));
                }

                Genetic::find($header['genetic_id'])->increment('last_id_counter', $header['quantity']);
            }
        });

        $count = count($this->pending_births);
        
        // Registrar en bitácora
        \App\Models\ModuleUsage::track('birth_registration', "Registro de $count Partos", '/genetic-selection/birth-registration', 'ph-baby', 'text-pink');

        $this->reset(['sala', 'jaula', 'madre', 'paridad', 'padre', 'lnv', 'cantidad', 'genetica_id', 'responsable_id', 'pending_births', 'ultimo_id']);
        $this->calendar_date = date('Y-m-d');
        $this->calculatePicFields();

        session()->flash('message', "Se guardaron {$count} parto(s) correctamente.");

        return $this->redirect('/genetic-selection/list', true);
    }

    public function render()
    {
        return view('livewire.genetic-selection.birth-registration', [
            'genetics'  => Genetic::all(),
            'employees' => Employee::all(),
        ]);
    }
}
