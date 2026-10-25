<?php

namespace App\Http\Resources;

use App\Models\Provinsi;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Provinsi
 */
class ProvinsiResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'kode' => $this->kode,
            'nama' => $this->nama,
            'pulau' => $this->pulau->value,
            'pulau_label' => $this->pulau->label(),
        ];
    }
}
