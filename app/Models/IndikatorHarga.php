<?php

namespace App\Models;

use App\Casts\Tanggal;
use App\Enums\TingkatHarga;
use Database\Factories\IndikatorHargaFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Table(name: 'indikator_harga')]
#[Fillable(['tanggal', 'provinsi_id', 'komoditas_id', 'tingkat_harga', 'harga', 'rata_rata_nasional', 'selisih', 'selisih_persen'])]
class IndikatorHarga extends Model
{
    /** @use HasFactory<IndikatorHargaFactory> */
    use HasFactory;

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
            'harga' => 'decimal:2',
            'rata_rata_nasional' => 'decimal:2',
            'selisih' => 'decimal:2',
            'selisih_persen' => 'decimal:2',
        ];
    }
}
