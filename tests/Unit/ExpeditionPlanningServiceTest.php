<?php

declare(strict_types=1);

use App\Enums\Destination;
use App\Enums\MissionPurpose;
use App\Enums\RiskClassification;
use App\Services\ExpeditionPlanningService;

test('it plans deterministic expeditions', function (
    Destination $destination,
    MissionPurpose $missionPurpose,
    int $crewSize,
    int $durationInDays,
    RiskClassification $expectedRisk,
    int $expectedRationPacks,
    int $expectedWaterLiters,
    string $navigationEquipment,
    string $survivalEquipment,
    string $specialistEquipment,
    string $advisoryAction,
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
    expect($expeditionPlan->navigationRecommendation)->toContain($navigationEquipment);
    expect($expeditionPlan->survivalRecommendation)->toContain($survivalEquipment);
    expect($expeditionPlan->missionSpecialistRecommendation)->toContain($specialistEquipment);
    expect($expeditionPlan->advisory)->toContain($advisoryAction);
})->with([
    'routine survey' => [Destination::EmberMoon, MissionPurpose::Survey, 2, 10, RiskClassification::Routine, 20, 60, 'thermal beacons', 'reserve oxygen pack', 'spectral scanner', 'routine checks'],
    'elevated research mission' => [Destination::GlassNebula, MissionPurpose::Research, 4, 45, RiskClassification::Elevated, 180, 540, 'prism mapper', 'shielded refuge module', 'sample vault', 'contingency day'],
    'extreme rescue mission' => [Destination::TidalArchive, MissionPurpose::Rescue, 8, 120, RiskClassification::Extreme, 960, 2880, 'gravimetric anchor', 'emergency stasis pod', 'medical extraction rig', 'abort window'],
]);


test('risk classification changes at crew and duration thresholds', function (
    Destination $destination,
    MissionPurpose $missionPurpose,
    int $crewSize,
    int $durationInDays,
    RiskClassification $expectedRisk,
): void {
    $plan = new ExpeditionPlanningService()->plan(
        callSign: 'Boundary Survey',
        destination: $destination,
        crewSize: $crewSize,
        durationInDays: $durationInDays,
        missionPurpose: $missionPurpose,
    );

    expect($plan->riskClassification)->toBe($expectedRisk);
})->with([
    'six crew stays routine' => [Destination::GlassNebula, MissionPurpose::Survey, 6, 30, RiskClassification::Routine],
    'seven crew becomes elevated' => [Destination::GlassNebula, MissionPurpose::Survey, 7, 30, RiskClassification::Elevated],
    'thirty days stays routine' => [Destination::GlassNebula, MissionPurpose::Survey, 2, 30, RiskClassification::Routine],
    'thirty-one days becomes elevated' => [Destination::GlassNebula, MissionPurpose::Survey, 2, 31, RiskClassification::Elevated],
    'ninety days stays elevated' => [Destination::TidalArchive, MissionPurpose::Survey, 2, 90, RiskClassification::Elevated],
    'ninety-one days becomes extreme' => [Destination::TidalArchive, MissionPurpose::Survey, 2, 91, RiskClassification::Extreme],
]);
