<?php

namespace App\Models;

use App\Casts\Tanggal;
use Database\Factories\KalenderFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Table(name: 'kalender', key: 'tanggal', keyType: 'string', incrementing: false, timestamps: false)]
#[Fillable(['tanggal', 'tahun', 'bulan', 'minggu_iso', 'hari_iso', 'akhir_pekan', 'hari_besar', 'musim_panen'])]
class Kalender extends Model
{
    /** @use HasFactory<KalenderFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'tanggal' => Tanggal::class,
            'tahun' => 'integer',
            'bulan' => 'integer',
            'minggu_iso' => 'integer',
            'hari_iso' => 'integer',
            'akhir_pekan' => 'boolean',
            'musim_panen' => 'boolean',
        ];
    }
}
