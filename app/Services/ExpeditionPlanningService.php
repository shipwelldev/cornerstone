<?php

declare(strict_types=1);

namespace App\Services;

use App\Data\ExpeditionPlanData;
use App\Enums\Destination;
use App\Enums\MissionPurpose;

class ExpeditionPlanningService
{
    public function plan(
        string $callSign,
        Destination $destination,
        int $crewSize,
        int $durationInDays,
        MissionPurpose $missionPurpose,
    ): ExpeditionPlanData {
        $riskClassification = $this->calculateRiskClassification(
            destination: $destination,
            missionPurpose: $missionPurpose,
            crewSize: $crewSize,
            durationInDays: $durationInDays,
        );

        return new ExpeditionPlanData(
            callSign: $callSign,
            destination: $destination,
            crewSize: $crewSize,
            durationInDays: $durationInDays,
            missionPurpose: $missionPurpose,
            riskClassification: $riskClassification,
            rationPacks: $this->calculateRationPacks($crewSize, $durationInDays),
            waterLiters: $this->calculateWaterLiters($crewSize, $durationInDays),
            navigationRecommendation: $this->navigationRecommendation($destination),
            survivalRecommendation: $this->survivalRecommendation($riskClassification),
            missionSpecialistRecommendation: $this->missionSpecialistRecommendation($missionPurpose),
            advisory: $this->advisory($riskClassification),
        );
    }

    private function calculateRiskClassification(
        Destination $destination,
        MissionPurpose $missionPurpose,
        int $crewSize,
        int $durationInDays,
    ): string {
        $riskScore = $this->destinationRiskScore($destination);
        $riskScore += $this->missionPurposeRiskScore($missionPurpose);

        if ($crewSize > 6) {
            $riskScore++;
        }

        if ($durationInDays > 30) {
            $riskScore++;
        }

        if ($durationInDays > 90) {
            $riskScore++;
        }

        if ($riskScore >= 5) {
            return 'Extreme';
        }

        if ($riskScore >= 3) {
            return 'Elevated';
        }

        return 'Routine';
    }

    private function destinationRiskScore(Destination $destination): int
    {
        return match ($destination) {
            Destination::EmberMoon => 1,
            Destination::GlassNebula => 2,
            Destination::TidalArchive => 3,
        };
    }

    private function missionPurposeRiskScore(MissionPurpose $missionPurpose): int
    {
        return match ($missionPurpose) {
            MissionPurpose::Survey => 0,
            MissionPurpose::Research => 1,
            MissionPurpose::Rescue => 2,
        };
    }

    private function calculateRationPacks(int $crewSize, int $durationInDays): int
    {
        return $crewSize * $durationInDays;
    }

    private function calculateWaterLiters(int $crewSize, int $durationInDays): int
    {
        $dailyWaterLitersPerCrewMember = 3;

        return $crewSize * $durationInDays * $dailyWaterLitersPerCrewMember;
    }

    private function navigationRecommendation(Destination $destination): string
    {
        return match ($destination) {
            Destination::EmberMoon => 'Deploy thermal beacons to keep the approach corridor visible through ash storms.',
            Destination::GlassNebula => 'Carry a prism mapper to correct the nebula\'s shifting optical distortions.',
            Destination::TidalArchive => 'Pack a gravimetric anchor for navigation near the archive\'s tidal wells.',
        };
    }

    private function survivalRecommendation(string $riskClassification): string
    {
        return match ($riskClassification) {
            'Extreme' => 'Assign every crew member an emergency stasis pod and independent distress transmitter.',
            'Elevated' => 'Add a shielded refuge module with seventy-two hours of reserve atmosphere.',
            default => 'Carry one reserve oxygen pack per crew member for routine contingencies.',
        };
    }

    private function missionSpecialistRecommendation(MissionPurpose $missionPurpose): string
    {
        return match ($missionPurpose) {
            MissionPurpose::Survey => 'Calibrate a long-range spectral scanner before departure.',
            MissionPurpose::Research => 'Prepare an isolated sample vault for unknown material.',
            MissionPurpose::Rescue => 'Reserve a medical extraction rig and two remote recovery drones.',
        };
    }

    private function advisory(string $riskClassification): string
    {
        return match ($riskClassification) {
            'Extreme' => 'Multiple risk factors overlap. Establish an abort window before crossing the final relay.',
            'Elevated' => 'Build one full contingency day into the flight plan and review the primary hazard at briefing.',
            default => 'Standard expedition protocols are sufficient, with routine checks at every relay.',
        };
    }
}
