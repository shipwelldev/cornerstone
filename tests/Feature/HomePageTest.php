<?php

declare(strict_types=1);

test('the home page presents the application foundation', function (): void {
    $this->get(route('home'))
        ->assertSuccessful()
        ->assertSeeInOrder(['Build from a solid', 'cornerstone.'])
        ->assertSee('Livewire + Tailwind ready')
        ->assertSee('Server-backed counter');
});
