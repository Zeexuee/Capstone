<?php

namespace App\Enums;

/**
 * Jenis pasar tempat harga dicatat. Nilai Umum dipakai untuk sumber yang tidak
 * membedakan jenis pasar, sehingga kolom ini tidak pernah bernilai null dan
 * unique key pada tabel harga tetap berlaku.
 */
enum JenisPasar: string
{
    case Umum = 'umum';
    case Tradisional = 'tradisional';
    case Modern = 'modern';
}
