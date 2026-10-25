<?php

namespace App\Models;

use App\Enums\KelompokKomoditas;
use App\Enums\SatuanHarga;
use Database\Factories\KomoditasFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Table(name: 'komoditas')]
#[Fillable(['kode', 'nama', 'kelompok', 'satuan'])]
class Komoditas extends Model
{
    /** @use HasFactory<KomoditasFactory> */
    use HasFactory;

    /**
     * Harga harian untuk komoditas ini.
     *
     * @return HasMany<HargaHarian, $this>
     */
    public function hargaHarian(): HasMany
    {
        return $this->hasMany(HargaHarian::class);
    }

    /**
     * Metrik evaluasi setiap model prediksi untuk komoditas ini.
     *
     * @return HasMany<MetrikModel, $this>
     */
    public function metrikModel(): HasMany
    {
        return $this->hasMany(MetrikModel::class);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'kelompok' => KelompokKomoditas::class,
            'satuan' => SatuanHarga::class,
        ];
    }
}
