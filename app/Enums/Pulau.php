<?php

namespace App\Enums;

/**
 * Pulau yang menjadi cakupan wilayah penelitian.
 */
enum Pulau: string
{
    case Jawa = 'jawa';
    case Kalimantan = 'kalimantan';
    case Sumatra = 'sumatra';

    /**
     * Nama pulau untuk ditampilkan di antarmuka.
     */
    public function label(): string
    {
        return match ($this) {
            self::Jawa => 'Jawa',
            self::Kalimantan => 'Kalimantan',
            self::Sumatra => 'Sumatra',
        };
    }
}
