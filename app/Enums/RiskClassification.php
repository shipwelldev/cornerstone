<?php

declare(strict_types=1);

namespace App\Enums;

enum RiskClassification
{
    case Routine;
    case Elevated;
    case Extreme;

    public function label(): string
    {
        return match ($this) {
            self::Routine => 'Routine',
            self::Elevated => 'Elevated',
            self::Extreme => 'Extreme',
        };
    }
}
