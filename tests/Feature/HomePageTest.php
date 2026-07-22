<?php

declare(strict_types=1);

test('the home page presents the canonical example', function (): void {
    $this->get(route('home'))
        ->assertSuccessful()
        ->assertSee('Canonical example')
        ->assertSee('Expedition planner')
        ->assertSee('Follow the vertical slice')
        ->assertSee('What belongs in CONTEXT.md?')
        ->assertSee('/remove-example');
});
