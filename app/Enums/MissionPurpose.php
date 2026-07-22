<?php

declare(strict_types=1);

namespace App\Enums;

enum MissionPurpose: string
{
    case Survey = 'survey';
    case Research = 'research';
    case Rescue = 'rescue';

    public function label(): string
    {
        return match ($this) {
            self::Survey => 'Survey',
            self::Research => 'Research',
            self::Rescue => 'Rescue',
        };
    }
}
