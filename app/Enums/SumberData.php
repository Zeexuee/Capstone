<?php

namespace App\Enums;

/**
 * Sumber data harga. Panel Harga Pangan Bapanas adalah sumber utama,
 * sedangkan PIHPS Bank Indonesia dipakai sebagai pembanding validasi silang.
 */
enum SumberData: string
{
    case Bapanas = 'bapanas';
    case Pihps = 'pihps';
}
