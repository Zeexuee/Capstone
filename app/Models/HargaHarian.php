<?php

namespace App\Models;

use App\Casts\Tanggal;
use App\Enums\JenisPasar;
use App\Enums\SumberData;
use App\Enums\TingkatHarga;
use Database\Factories\HargaHarianFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Table(name: 'harga_harian')]
#[Fillable(['tanggal', 'provinsi_id', 'komoditas_id', 'tingkat_harga', 'jenis_pasar', 'sumber', 'harga'])]
class HargaHarian extends Model
{
    /** @use HasFactory<HargaHarianFactory> */
    use HasFactory;

    /**
     * Kolom yang bersama-sama mengidentifikasi satu baris harga. Dipakai sebagai
     * kunci upsert agar ETL dapat dijalankan ulang tanpa membuat baris ganda.
     *
     * @var list<string>
     */
    public const array KUNCI_UNIK = ['tanggal', 'provinsi_id', 'komoditas_id', 'tingkat_harga', 'jenis_pasar', 'sumber'];

    /**
     * Mirror of the database default so unsaved models expose the same value.
     *
     * @var array<string, string>
     */
    protected $attributes = [
        'jenis_pasar' => 'umum',
    ];

    /**
     * @return BelongsTo<Provinsi, $this>
     */
    public function provinsi(): BelongsTo
    {
        return $this->belongsTo(Provinsi::class);
    }

    /**
     * @return BelongsTo<Komoditas, $this>
     */
    public function komoditas(): BelongsTo
    {
        return $this->belongsTo(Komoditas::class);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'tanggal' => Tanggal::class,
            'tingkat_harga' => TingkatHarga::class,
            'jenis_pasar' => JenisPasar::class,
            'sumber' => SumberData::class,
            'harga' => 'decimal:2',
        ];
    }
}
