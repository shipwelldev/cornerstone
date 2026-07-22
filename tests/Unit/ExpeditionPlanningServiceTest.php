<?php

declare(strict_types=1);

use App\Data\ExpeditionPlanData;
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

    expect($expeditionPlan)
        ->toBeInstanceOf(ExpeditionPlanData::class)
        ->callSign->toBe('Aurora Seven')
        ->destination->toBe($destination)
        ->missionPurpose->toBe($missionPurpose)
        ->riskClassification->toBe($expectedRisk)
        ->rationPacks->toBe($expectedRationPacks)
        ->waterLiters->toBe($expectedWaterLiters)
        ->navigationRecommendation->not->toBeEmpty()
        ->survivalRecommendation->not->toBeEmpty()
        ->missionSpecialistRecommendation->not->toBeEmpty()
        ->advisory->not->toBeEmpty();
})->with([
    'routine survey' => [Destination::EmberMoon, MissionPurpose::Survey, 2, 10, 'Routine', 20, 60],
    'elevated research mission' => [Destination::GlassNebula, MissionPurpose::Research, 4, 45, 'Elevated', 180, 540],
    'extreme rescue mission' => [Destination::TidalArchive, MissionPurpose::Rescue, 8, 120, 'Extreme', 960, 2880],
]);
