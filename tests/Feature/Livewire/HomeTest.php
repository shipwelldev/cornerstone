<?php

declare(strict_types=1);

use App\Livewire\Home;
use Livewire\Features\SupportLockedProperties\CannotUpdateLockedPropertyException;
use Livewire\Livewire;

test('the counter can increment and reset', function (): void {
    Livewire::test(Home::class)
        ->assertSet('count', 0)
        ->assertSee('Server-backed counter')
        ->call('increment')
        ->assertSet('count', 1)
        ->call('resetCounter')
        ->assertSet('count', 0);
});

test('the counter cannot be changed by the client', function (): void {
    Livewire::test(Home::class)
        ->set('count', 10);
})->throws(CannotUpdateLockedPropertyException::class);
