<?php

namespace App\Livewire\GeneticSelection;

use App\Models\Birth;
use App\Models\BirthDetail;
use App\Models\Genetic;
use App\Models\Employee;
use App\Services\PicDateService;
use Livewire\Component;

use App\Traits\HandlesDecimals;

class BirthEdit extends Component
{
    use HandlesDecimals;

    public Birth $birth;
    public $calendar_date, $pic_cycle, $pic_day, $sala, $jaula, $madre, $paridad, $padre, $lnv, $responsable_id, $lote_maternidad, $pic_destete;
    public $piglets = [];

    protected $rules = [
        'calendar_date' => 'required|date',
        'pic_cycle'     => 'required|numeric',
        'pic_day'       => 'required|numeric|min:0|max:999',
        'sala'          => 'required',
        'jaula'         => 'required',
        'madre'         => 'required',
        'paridad'       => 'required|numeric',
        'padre'         => 'required',
        'lnv'           => 'required|numeric',
        'responsable_id'=> 'required|exists:employees,id',
        'lote_maternidad' => 'nullable|string|max:50',
        'pic_destete'   => 'nullable|numeric|min:0|max:9999',
        'piglets.*.sex' => 'required',
    ];

    public function mount($id)
    {
        $this->birth = Birth::with(['details'])->findOrFail($id);

        $this->calendar_date = $this->birth->calendar_date->format('Y-m-d');
        $this->pic_cycle     = $this->birth->pic_cycle;
        $this->pic_day       = $this->birth->pic_day;
        $this->sala          = $this->birth->room;
        $this->jaula         = $this->birth->cage;
        $this->madre         = $this->birth->mother_tag;
        $this->paridad       = $this->birth->parity;
        $this->padre         = $this->birth->father_tag;
        $this->lnv           = $this->birth->lnv;
        $this->responsable_id = $this->birth->responsible_id;
        $this->lote_maternidad = $this->birth->maternity_lot;
        $this->pic_destete = $this->birth->pic_destete;

        $this->piglets = $this->birth->details->map(fn($d) => [
            'id'                          => $d->id,
            'generated_id'                => $d->generated_id,
            'ear_id'                      => $d->ear_id,
            'weight'                      => $d->weight,
            'teats_total'                 => $d->teats_total,
            'teats_left'                  => $d->teats_left,
            'teats_behind_shoulder_left'  => $d->teats_behind_shoulder_left,
            'teats_behind_shoulder_right' => $d->teats_behind_shoulder_right,
            'sex'                         => $d->sex,
        ])->toArray();
    }

    public function updatedCalendarDate($value)
    {
        $data = PicDateService::fromDate($value);
        $this->pic_cycle = $data['vuelta'];
        $this->pic_day   = $data['pic'];
    }

    public function updatedPicCycle($value)
    {
        if (is_numeric($value) && is_numeric($this->pic_day)) {
            $this->calendar_date = PicDateService::toDate($value, $this->pic_day)->format('Y-m-d');
        }
    }

    public function updatedPicDay($value)
    {
        if (is_numeric($this->pic_cycle) && is_numeric($value)) {
            $this->calendar_date = PicDateService::toDate($this->pic_cycle, $value)->format('Y-m-d');
        }
    }

    public function save()
    {
        $this->validate();

        $this->birth->update([
            'calendar_date'  => $this->calendar_date,
            'pic_cycle'      => $this->pic_cycle,
            'pic_day'        => $this->pic_day,
            'room'           => $this->sala,
            'cage'           => $this->jaula,
            'mother_tag'     => $this->madre,
            'parity'         => $this->paridad,
            'father_tag'     => $this->padre,
            'lnv'            => $this->lnv,
            'responsible_id' => $this->responsable_id,
            'maternity_lot'  => $this->lote_maternidad,
            'pic_destete'    => $this->pic_destete,
        ]);

        foreach ($this->piglets as $piglet) {
            $isMale = $piglet['sex'] === 'Macho';
            BirthDetail::find($piglet['id'])?->update([
                'weight'                      => $this->parseDecimal($piglet['weight']),
                'teats_total'                 => $isMale || empty($piglet['teats_total']) ? null : $piglet['teats_total'],
                'teats_left'                  => $isMale || empty($piglet['teats_left']) ? null : $piglet['teats_left'],
                'teats_behind_shoulder_left'  => $isMale || empty($piglet['teats_behind_shoulder_left']) ? null : $piglet['teats_behind_shoulder_left'],
                'teats_behind_shoulder_right' => $isMale || empty($piglet['teats_behind_shoulder_right']) ? null : $piglet['teats_behind_shoulder_right'],
                'sex'                         => $piglet['sex'],
            ]);
        }
        
        // Registrar en bitácora
        \App\Models\ModuleUsage::track('birth_edit', 'Edición de Parto (ID: ' . $this->birth->id . ')', '/genetic-selection/list', 'ph-pencil-simple', 'text-warning');

        session()->flash('message', 'Parto actualizado correctamente.');
        return $this->redirect('/genetic-selection/list', true);
    }

    public function render()
    {
        return view('livewire.genetic-selection.birth-edit', [
            'genetics'  => Genetic::all(),
            'employees' => Employee::all(),
        ]);
    }
}
