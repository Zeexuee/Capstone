<?php

namespace App\Http\Requests\Api\V1;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class TrenRequest extends DasborRequest
{
    public const array AGREGASI = ['harian', 'mingguan', 'bulanan'];

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            ...parent::rules(),
            'provinsi' => ['nullable', 'array', 'max:'.config('dasbor.tren.maks_provinsi')],
            'provinsi.*' => ['string', 'distinct', Rule::exists('provinsi', 'kode')],
            'mulai' => ['nullable', 'date_format:Y-m-d'],
            'selesai' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:mulai'],
            'agregasi' => ['nullable', Rule::in(self::AGREGASI)],
        ];
    }

    /**
     * Membatasi rentang agar satu permintaan tidak memuat seluruh tabel harga.
     *
     * @return list<Closure(Validator): void>
     */
    public function after(): array
    {
        return [
            ...parent::after(),
            function (Validator $validator): void {
                if ($validator->errors()->hasAny(['mulai', 'selesai']) || ! $this->filled(['mulai', 'selesai'])) {
                    return;
                }

                $maksimum = config('dasbor.tren.rentang_maks_hari');

                if ($this->date('mulai', 'Y-m-d')->diffInDays($this->date('selesai', 'Y-m-d')) > $maksimum) {
                    $validator->errors()->add('selesai', "Rentang tanggal paling panjang {$maksimum} hari.");
                }
            },
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            ...parent::messages(),
            'provinsi.max' => 'Pilih paling banyak :max provinsi agar grafik tetap terbaca.',
        ];
    }

    /**
     * @return list<string>
     */
    public function kodeProvinsi(): array
    {
        return array_values($this->array('provinsi'));
    }

    public function mulai(): ?Carbon
    {
        return $this->date('mulai', 'Y-m-d');
    }

    public function selesai(): ?Carbon
    {
        return $this->date('selesai', 'Y-m-d');
    }

    public function agregasi(): string
    {
        return $this->filled('agregasi') ? $this->string('agregasi')->toString() : 'harian';
    }
}
