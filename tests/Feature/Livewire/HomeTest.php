<?php

declare(strict_types=1);

use App\Data\ExpeditionPlanData;
use App\Enums\Destination;
use App\Enums\MissionPurpose;
use App\Enums\RiskClassification;
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
        ->assertSet('expeditionPlan', null);
});

test('the mission brief is validated before planning', function (string $property, mixed $value, string $rule): void {
    $component = Livewire::test(Home::class);

    $component
        ->set('callSign', 'Aurora Seven')
        ->set('destination', 'glass-nebula')
        ->set('crewSize', 4)
        ->set('durationInDays', 45)
        ->set('missionPurpose', 'research')
        ->set($property, $value)
        ->call('planExpedition')
        ->assertHasErrors([$property => $rule]);

    $component->assertSet('hasPlan', false);
})->with([
    'call sign is required' => ['callSign', '', 'required'],
    'call sign has a minimum length' => ['callSign', 'AB', 'min'],
    'destination must be recognized' => ['destination', 'unknown-system', Enum::class],
    'crew size has a lower bound' => ['crewSize', 0, 'min'],
    'duration has an upper bound' => ['durationInDays', 181, 'max'],
    'mission purpose must be recognized' => ['missionPurpose', 'tourism', Enum::class],
]);

test('a valid mission brief produces an expedition plan', function (): void {
    $component = Livewire::test(Home::class);

    $component
        ->set('callSign', 'Aurora Seven')
        ->set('destination', 'glass-nebula')
        ->set('crewSize', 4)
        ->set('durationInDays', 45)
        ->set('missionPurpose', 'research')
        ->call('planExpedition')
        ->assertHasNoErrors();

    $component
        ->assertSet('hasPlan', true)
        ->assertDispatched('expedition-planned');

    $component->assertSet('expeditionPlan', function (ExpeditionPlanData $plan): bool {
        expect($plan->callSign)->toBe('Aurora Seven');
        expect($plan->destination)->toBe(Destination::GlassNebula);
        expect($plan->missionPurpose)->toBe(MissionPurpose::Research);
        expect($plan->crewSize)->toBe(4);
        expect($plan->durationInDays)->toBe(45);
        expect($plan->riskClassification)->toBe(RiskClassification::Elevated);
        expect($plan->rationPacks)->toBe(180);
        expect($plan->waterLiters)->toBe(540);

        return true;
    });
});

test('the mission brief can be revised without losing its values', function (): void {
    $component = Livewire::test(Home::class);

    $component
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
        ->assertDispatched('mission-brief-revised');

    $component->assertSet('expeditionPlan', null);

    $component->call('planExpedition')
        ->assertHasNoErrors();

    $component
        ->assertSet('hasPlan', true);
});

test('the mission brief can be reset', function (): void {
    $component = Livewire::test(Home::class);

    $component
        ->set('callSign', 'Aurora Seven')
        ->set('destination', 'glass-nebula')
        ->set('crewSize', 4)
        ->set('durationInDays', 45)
        ->set('missionPurpose', 'research')
        ->call('planExpedition')
        ->set('crewSize', 0)
        ->assertHasErrors(['crewSize' => 'min']);

    $component
        ->call('resetMissionBrief')
        ->assertHasNoErrors();

    $component
        ->assertSet('expeditionPlan', null)
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

test('invalid edits discard the accepted plan before validation fails', function (string $property, mixed $value, string $rule): void {
    $component = Livewire::test(Home::class);

    $component
        ->set('callSign', 'Aurora Seven')
        ->set('destination', 'glass-nebula')
        ->set('crewSize', 4)
        ->set('durationInDays', 45)
        ->set('missionPurpose', 'research')
        ->call('planExpedition')
        ->set($property, $value)
        ->assertHasErrors([$property => $rule]);

    $component
        ->assertSet('hasPlan', false)
        ->assertSet('expeditionPlan', null)
        ->call('planExpedition')
        ->assertHasErrors([$property => $rule]);

    $component
        ->assertSet('hasPlan', false)
        ->assertSet('expeditionPlan', null);
})->with([
    'call sign' => ['callSign', '', 'required'],
    'destination' => ['destination', 'unknown-system', Enum::class],
    'crew size' => ['crewSize', 0, 'min'],
    'duration' => ['durationInDays', 181, 'max'],
    'mission purpose' => ['missionPurpose', 'tourism', Enum::class],
]);

test('valid edits require another submission before generating a plan', function (string $property, mixed $value): void {
    $component = Livewire::test(Home::class);

    $component
        ->set('callSign', 'Aurora Seven')
        ->set('destination', 'glass-nebula')
        ->set('crewSize', 4)
        ->set('durationInDays', 45)
        ->set('missionPurpose', 'research')
        ->call('planExpedition')
        ->set($property, $value)
        ->assertHasNoErrors();

    $component
        ->assertSet('hasPlan', false)
        ->assertSet('expeditionPlan', null)
        ->call('planExpedition')
        ->assertHasNoErrors();

    $component
        ->assertSet('hasPlan', true)
        ->assertSet('expeditionPlan', fn (ExpeditionPlanData $plan): bool => $plan->{$property} === $value
            || ($plan->{$property} instanceof BackedEnum && $plan->{$property}->value === $value));
})->with([
    'call sign' => ['callSign', 'Aurora Eight'],
    'destination' => ['destination', 'ember-moon'],
    'crew size' => ['crewSize', 5],
    'duration' => ['durationInDays', 60],
    'mission purpose' => ['missionPurpose', 'rescue'],
]);
