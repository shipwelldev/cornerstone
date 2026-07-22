<?php

declare(strict_types=1);

use App\Livewire\Home;
use Illuminate\Validation\Rules\Enum;
use Livewire\Features\SupportLockedProperties\CannotUpdateLockedPropertyException;
use Livewire\Livewire;

test('the mission brief starts empty', function (): void {
    Livewire::test(Home::class)
        ->assertSet('callSign', '')
        ->assertSet('destination', '')
        ->assertSet('crewSize', null)
        ->assertSet('durationInDays', null)
        ->assertSet('missionPurpose', '')
        ->assertSet('hasPlan', false)
        ->assertSee('Define the expedition')
        ->assertDontSee('Mission advisory');
});

test('the mission brief is validated before planning', function (string $property, mixed $value, string $rule): void {
    Livewire::test(Home::class)
        ->set('callSign', 'Aurora Seven')
        ->set('destination', 'glass-nebula')
        ->set('crewSize', 4)
        ->set('durationInDays', 45)
        ->set('missionPurpose', 'research')
        ->set($property, $value)
        ->call('planExpedition')
        ->assertHasErrors([$property => $rule])
        ->assertSet('hasPlan', false);
})->with([
    'call sign is required' => ['callSign', '', 'required'],
    'call sign has a minimum length' => ['callSign', 'AB', 'min'],
    'destination must be recognized' => ['destination', 'unknown-system', Enum::class],
    'crew size has a lower bound' => ['crewSize', 0, 'min'],
    'duration has an upper bound' => ['durationInDays', 181, 'max'],
    'mission purpose must be recognized' => ['missionPurpose', 'tourism', Enum::class],
]);

test('a valid mission brief produces an expedition plan', function (): void {
    Livewire::test(Home::class)
        ->set('callSign', 'Aurora Seven')
        ->set('destination', 'glass-nebula')
        ->set('crewSize', 4)
        ->set('durationInDays', 45)
        ->set('missionPurpose', 'research')
        ->call('planExpedition')
        ->assertHasNoErrors()
        ->assertSet('hasPlan', true)
        ->assertDispatched('expedition-planned')
        ->assertSee('Aurora Seven')
        ->assertSee('Elevated risk')
        ->assertSee('180')
        ->assertSee('540')
        ->assertSee('prism mapper');
});

test('the mission brief can be revised without losing its values', function (): void {
    Livewire::test(Home::class)
        ->set('callSign', 'Aurora Seven')
        ->set('destination', 'glass-nebula')
        ->set('crewSize', 4)
        ->set('durationInDays', 45)
        ->set('missionPurpose', 'research')
        ->call('planExpedition')
        ->call('reviseMissionBrief')
        ->assertSet('hasPlan', false)
        ->assertSet('callSign', 'Aurora Seven')
        ->assertSet('destination', 'glass-nebula')
        ->assertSet('crewSize', 4)
        ->assertSet('durationInDays', 45)
        ->assertSet('missionPurpose', 'research')
        ->assertDispatched('mission-brief-revised')
        ->assertSee('Define the expedition');
});

test('the mission brief can be reset', function (): void {
    Livewire::test(Home::class)
        ->set('callSign', 'Aurora Seven')
        ->set('destination', 'glass-nebula')
        ->set('crewSize', 4)
        ->set('durationInDays', 45)
        ->set('missionPurpose', 'research')
        ->call('resetMissionBrief')
        ->assertSet('callSign', '')
        ->assertSet('destination', '')
        ->assertSet('crewSize', null)
        ->assertSet('durationInDays', null)
        ->assertSet('missionPurpose', '')
        ->assertSet('hasPlan', false)
        ->assertDispatched('mission-brief-reset');
});

test('the planning step cannot be changed by the client', function (): void {
    Livewire::test(Home::class)
        ->set('hasPlan', true);
})->throws(CannotUpdateLockedPropertyException::class);
