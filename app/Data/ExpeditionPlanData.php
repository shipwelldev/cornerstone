<?php

declare(strict_types=1);

namespace App\Data;

use App\Enums\Destination;
use App\Enums\MissionPurpose;
use App\Enums\RiskClassification;

readonly class ExpeditionPlanData
{
    public function __construct(
        public string $callSign,
        public Destination $destination,
        public int $crewSize,
        public int $durationInDays,
        public MissionPurpose $missionPurpose,
        public RiskClassification $riskClassification,
        public int $rationPacks,
        public int $waterLiters,
        public string $navigationRecommendation,
        public string $survivalRecommendation,
        public string $missionSpecialistRecommendation,
        public string $advisory,
    ) {}
}
