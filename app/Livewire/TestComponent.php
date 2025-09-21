<?php

namespace App\Livewire;

use Livewire\Component;

class TestComponent extends Component
{
    public $count = 0;
    public $message = '';

    public function increment()
    {
        $this->count++;
        $this->message = 'Livewire is working! Count: ' . $this->count;
    }

    public function render()
    {
        return view('livewire.test-component');
    }
}
