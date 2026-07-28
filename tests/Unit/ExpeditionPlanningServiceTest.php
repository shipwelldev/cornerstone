<?php

declare(strict_types=1);

use App\Enums\Destination;
use App\Enums\MissionPurpose;
use App\Services\ExpeditionPlanningService;

test('it plans deterministic expeditions', function (
    Destination $destination,
    MissionPurpose $missionPurpose,
    int $crewSize,
    int $durationInDays,
    string $expectedRisk,
    int $expectedRationPacks,
    int $expectedWaterLiters,
): void {
    $expeditionPlan = new ExpeditionPlanningService()->plan(
        callSign: 'Aurora Seven',
        destination: $destination,
        crewSize: $crewSize,
        durationInDays: $durationInDays,
        missionPurpose: $missionPurpose,
    );

    expect($expeditionPlan->callSign)->toBe('Aurora Seven');
    expect($expeditionPlan->destination)->toBe($destination);
    expect($expeditionPlan->missionPurpose)->toBe($missionPurpose);
    expect($expeditionPlan->riskClassification)->toBe($expectedRisk);
    expect($expeditionPlan->rationPacks)->toBe($expectedRationPacks);
    expect($expeditionPlan->waterLiters)->toBe($expectedWaterLiters);
    expect($expeditionPlan->navigationRecommendation)->not->toBeEmpty();
    expect($expeditionPlan->survivalRecommendation)->not->toBeEmpty();
    expect($expeditionPlan->missionSpecialistRecommendation)->not->toBeEmpty();
    expect($expeditionPlan->advisory)->not->toBeEmpty();
})->with([
    'routine survey' => [Destination::EmberMoon, MissionPurpose::Survey, 2, 10, 'Routine', 20, 60],
    'elevated research mission' => [Destination::GlassNebula, MissionPurpose::Research, 4, 45, 'Elevated', 180, 540],
    'extreme rescue mission' => [Destination::TidalArchive, MissionPurpose::Rescue, 8, 120, 'Extreme', 960, 2880],
]);
