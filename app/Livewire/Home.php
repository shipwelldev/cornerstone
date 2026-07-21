<?php

declare(strict_types=1);

namespace App\Livewire;

use Illuminate\Contracts\View\View;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Cornerstone')]
class Home extends Component
{
    #[Locked]
    public int $count = 0;

    public function increment(): void
    {
        $this->count++;
    }

    public function resetCounter(): void
    {
        $this->count = 0;
    }

    public function render(): View
    {
        return view('livewire.home');
    }
}
