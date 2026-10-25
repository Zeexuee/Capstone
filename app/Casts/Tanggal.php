<?php

namespace App\Casts;

use DateTimeInterface;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * Cast untuk kolom tanggal tanpa jam.
 *
 * Cast bawaan `date` menyimpan nilai dengan format `Y-m-d H:i:s`. Kolom DATE di
 * MySQL memotongnya diam-diam, tetapi SQLite menyimpannya apa adanya, sehingga
 * upsert dari ETL Python yang mengirim `Y-m-d` tidak cocok dengan unique key.
 * Cast ini selalu menyimpan `Y-m-d` agar perilakunya sama di kedua basis data.
 *
 * @implements CastsAttributes<Carbon, DateTimeInterface|string>
 */
class Tanggal implements CastsAttributes
{
    /**
     * Cast the given value.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): ?Carbon
    {
        if ($value === null) {
            return null;
        }

        return Carbon::parse($value)->startOfDay();
    }

    /**
     * Prepare the given value for storage.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): ?string
    {
        if ($value === null) {
            return null;
        }

        if ($value instanceof DateTimeInterface) {
            return $value->format('Y-m-d');
        }

        return Carbon::parse($value)->toDateString();
    }
}
