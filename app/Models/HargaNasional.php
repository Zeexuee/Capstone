<?php

namespace App\Models;

use App\Casts\Tanggal;
use App\Enums\TingkatHarga;
use Database\Factories\HargaNasionalFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Table(name: 'harga_nasional')]
#[Fillable(['tanggal', 'komoditas_id', 'tingkat_harga', 'harga'])]
class HargaNasional extends Model
{
    /** @use HasFactory<HargaNasionalFactory> */
    use HasFactory;

    /**
     * Kolom yang bersama-sama mengidentifikasi satu baris harga nasional.
     *
     * @var list<string>
     */
    public const array KUNCI_UNIK = ['komoditas_id', 'tingkat_harga', 'tanggal'];

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
            'harga' => 'decimal:2',
        ];
    }
}
