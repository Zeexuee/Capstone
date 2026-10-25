<?php

namespace App\Models;

use App\Casts\Tanggal;
use App\Enums\ModelPrediksi;
use Database\Factories\PrediksiFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Table(name: 'prediksi')]
#[Fillable(['provinsi_id', 'komoditas_id', 'model', 'tanggal_sasaran', 'harga_prediksi', 'dihasilkan_pada'])]
class Prediksi extends Model
{
    /** @use HasFactory<PrediksiFactory> */
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
            'model' => ModelPrediksi::class,
            'tanggal_sasaran' => Tanggal::class,
            'harga_prediksi' => 'decimal:2',
            'dihasilkan_pada' => 'datetime',
        ];
    }
}
