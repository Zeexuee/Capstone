<?php

namespace App\Models;

use App\Casts\Tanggal;
use App\Enums\ModelPrediksi;
use Database\Factories\MetrikModelFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Table(name: 'metrik_model')]
#[Fillable(['komoditas_id', 'model', 'mape', 'rmse', 'mae', 'r2', 'periode_uji_mulai', 'periode_uji_selesai', 'terpilih'])]
class MetrikModel extends Model
{
    /** @use HasFactory<MetrikModelFactory> */
    use HasFactory;

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
            'mape' => 'decimal:4',
            'rmse' => 'decimal:4',
            'mae' => 'decimal:4',
            'r2' => 'decimal:4',
            'periode_uji_mulai' => Tanggal::class,
            'periode_uji_selesai' => Tanggal::class,
            'terpilih' => 'boolean',
        ];
    }
}
