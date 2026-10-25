<?php

namespace App\Enums;

/**
 * Satuan baku harga sebuah komoditas. Pipeline ETL wajib menyeragamkan harga
 * ke satuan ini sebelum disimpan, sehingga harga dengan satuan berbeda tidak
 * pernah tercampur dalam satu perbandingan.
 */
enum SatuanHarga: string
{
    case Kilogram = 'kg';
    case Liter = 'liter';

    /**
     * Label satuan untuk ditempelkan pada angka harga, misalnya "/kg".
     */
    public function label(): string
    {
        return match ($this) {
            self::Kilogram => 'kg',
            self::Liter => 'liter',
        };
    }
}
