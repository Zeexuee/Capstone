<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Carbon;

class PerbandinganRequest extends DasborRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            ...parent::rules(),
            'tanggal' => ['nullable', 'date_format:Y-m-d'],
        ];
    }

    public function tanggal(): ?Carbon
    {
        return $this->date('tanggal', 'Y-m-d');
    }
}
