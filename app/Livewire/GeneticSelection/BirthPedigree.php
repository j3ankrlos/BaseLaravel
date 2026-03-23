<?php

namespace App\Livewire\GeneticSelection;

use App\Models\Birth;
use App\Models\BirthDetail;
use Livewire\Component;

class BirthPedigree extends Component
{
    public Birth $birth;
    public $details = [];

    public function mount($id)
    {
        $this->birth = Birth::with(['genetic', 'responsible', 'details'])->findOrFail($id);
        $this->details = $this->birth->details->toArray();
    }

    public function render()
    {
        return view('livewire.genetic-selection.birth-pedigree');
    }
}
