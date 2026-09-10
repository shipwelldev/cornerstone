<?php

declare(strict_types=1);

test('the home route serves the expedition planner', function (): void {
    $this->get(route('home'))
        ->assertSuccessful();
});
