<?php

namespace App\Http\Resources;

use App\Models\Komoditas;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Komoditas
 */
class KomoditasResource extends JsonResource
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
            'kelompok' => $this->kelompok->value,
            'kelompok_label' => $this->kelompok->label(),
            'satuan' => $this->satuan->value,
        ];
    }
}
