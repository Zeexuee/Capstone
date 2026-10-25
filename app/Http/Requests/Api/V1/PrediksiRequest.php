<?php

namespace App\Http\Requests\Api\V1;

use App\Models\Provinsi;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

/**
 * Prediksi selalu untuk harga konsumen, sehingga tingkat harga dan pulau tidak
 * dipakai di endpoint ini.
 */
class PrediksiRequest extends DasborRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'komoditas' => parent::rules()['komoditas'],
            'provinsi' => ['required', 'string', Rule::exists('provinsi', 'kode')],
        ];
    }

    /**
     * Pemeriksaan harga produsen dari kelas induk tidak berlaku di sini.
     *
     * @return list<\Closure(Validator): void>
     */
    public function after(): array
    {
        return [];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            ...parent::messages(),
            'provinsi.required' => 'Pilih satu provinsi untuk melihat prediksi.',
        ];
    }

    public function provinsi(): Provinsi
    {
        return Provinsi::where('kode', $this->string('provinsi')->toString())->sole();
    }
}
