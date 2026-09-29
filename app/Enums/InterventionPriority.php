<?php

declare(strict_types=1);

namespace App\Enums;

enum InterventionPriority: string
{
    case LOW = 'LOW';
    case MEDIUM = 'MEDIUM';
    case HIGH = 'HIGH';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
