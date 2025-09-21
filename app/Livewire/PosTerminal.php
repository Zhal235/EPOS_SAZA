<?php

namespace App\Livewire;

use Livewire\Component;

class PosTerminal extends Component
{
    public function render()
    {
        return view('livewire.pos-terminal')
            ->layout('layouts.epos', [
                'header' => 'POS Terminal'
            ]);
    }
}
