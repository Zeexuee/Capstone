<?php

namespace App\Enums;

/**
 * Tingkat rantai pasok tempat harga dicatat.
 */
enum TingkatHarga: string
{
    case Konsumen = 'konsumen';
    case Produsen = 'produsen';
}
