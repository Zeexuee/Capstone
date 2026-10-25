<?php

namespace App\Enums;

/**
 * Kelompok komoditas yang diteliti. Satu kelompok dapat memiliki beberapa varian,
 * misalnya beras medium dan beras premium.
 */
enum KelompokKomoditas: string
{
    case Beras = 'beras';
    case MinyakGoreng = 'minyak_goreng';

    /**
     * Nama kelompok untuk ditampilkan di antarmuka.
     */
    public function label(): string
    {
        return match ($this) {
            self::Beras => 'Beras',
            self::MinyakGoreng => 'Minyak goreng',
        };
    }
}
