<?php

declare(strict_types=1);

namespace App\Enums;

enum Destination: string
{
    case EmberMoon = 'ember-moon';
    case GlassNebula = 'glass-nebula';
    case TidalArchive = 'tidal-archive';

    public function label(): string
    {
        return match ($this) {
            self::EmberMoon => 'Ember Moon',
            self::GlassNebula => 'Glass Nebula',
            self::TidalArchive => 'Tidal Archive',
        };
    }
}
