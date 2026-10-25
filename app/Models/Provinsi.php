<?php

namespace App\Models;

use App\Enums\Pulau;
use Database\Factories\ProvinsiFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Table(name: 'provinsi')]
#[Fillable(['kode', 'nama', 'pulau'])]
class Provinsi extends Model
{
    /** @use HasFactory<ProvinsiFactory> */
    use HasFactory;

    /**
     * Harga harian yang tercatat di provinsi ini.
     *
     * @return HasMany<HargaHarian, $this>
     */
    public function hargaHarian(): HasMany
    {
        return $this->hasMany(HargaHarian::class);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'pulau' => Pulau::class,
        ];
    }
}
