<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\TingkatHarga;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\RingkasanRequest;
use App\Http\Resources\KomoditasResource;
use App\Models\HargaNasional;
use App\Models\IndikatorHarga;
use App\Models\Komoditas;
use App\Models\Provinsi;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;

/**
 * Kartu ringkasan: harga nasional terkini, perubahannya, provinsi termurah dan
 * termahal, serta rentang selisih di antara keduanya.
 */
class RingkasanController extends Controller
{
    private const int HARI_PEMBANDING = 30;

    public function __invoke(RingkasanRequest $request): JsonResponse
    {
        $komoditas = $request->komoditas();
        $tingkat = $request->tingkat();
        $pulau = $request->pulau();

        $nasional = fn (): Builder => HargaNasional::whereBelongsTo($komoditas)->where('tingkat_harga', $tingkat);
        $terkini = $nasional()->orderByDesc('tanggal')->first();

        $jumlahProvinsi = Provinsi::when($pulau, fn (Builder $kueri): Builder => $kueri->where('pulau', $pulau))->count();

        if ($terkini === null) {
            return response()->json(['data' => $this->kosong($komoditas, $tingkat, $jumlahProvinsi)]);
        }

        $pembanding = $nasional()
            ->where('tanggal', '<=', $terkini->tanggal->copy()->subDays(self::HARI_PEMBANDING)->toDateString())
            ->orderByDesc('tanggal')
            ->first();

        $indikator = IndikatorHarga::with('provinsi')
            ->whereBelongsTo($komoditas)
            ->where('tingkat_harga', $tingkat)
            ->where('tanggal', $terkini->tanggal->toDateString())
            ->when($pulau, fn (Builder $kueri): Builder => $kueri->whereRelation('provinsi', 'pulau', $pulau))
            ->orderBy('harga')
            ->orderBy('provinsi_id')
            ->get();

        $termurah = $indikator->first();
        $termahal = $indikator->last();

        return response()->json(['data' => [
            'komoditas' => KomoditasResource::make($komoditas),
            'tingkat_harga' => $tingkat->value,
            'tanggal' => $terkini->tanggal->toDateString(),
            'harga_nasional' => (float) $terkini->harga,
            'perubahan' => $pembanding === null ? null : [
                'tanggal_pembanding' => $pembanding->tanggal->toDateString(),
                'harga_pembanding' => (float) $pembanding->harga,
                'persen' => round(((float) $terkini->harga - (float) $pembanding->harga) / (float) $pembanding->harga * 100, 2),
            ],
            'termurah' => $termurah === null ? null : $this->provinsi($termurah),
            'termahal' => $termahal === null ? null : $this->provinsi($termahal),
            'rentang' => $termurah === null ? null : [
                'rupiah' => (float) $termahal->harga - (float) $termurah->harga,
                'persen' => round(((float) $termahal->harga - (float) $termurah->harga) / (float) $termurah->harga * 100, 2),
            ],
            'cakupan' => [
                'jumlah_provinsi' => $jumlahProvinsi,
                'jumlah_berdata' => $indikator->count(),
            ],
        ]]);
    }

    /**
     * @return array{kode: string, nama: string, harga: float, selisih: float, selisih_persen: float}
     */
    private function provinsi(IndikatorHarga $indikator): array
    {
        return [
            'kode' => $indikator->provinsi->kode,
            'nama' => $indikator->provinsi->nama,
            'harga' => (float) $indikator->harga,
            'selisih' => (float) $indikator->selisih,
            'selisih_persen' => (float) $indikator->selisih_persen,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function kosong(Komoditas $komoditas, TingkatHarga $tingkat, int $jumlahProvinsi): array
    {
        return [
            'komoditas' => KomoditasResource::make($komoditas),
            'tingkat_harga' => $tingkat->value,
            'tanggal' => null,
            'harga_nasional' => null,
            'perubahan' => null,
            'termurah' => null,
            'termahal' => null,
            'rentang' => null,
            'cakupan' => ['jumlah_provinsi' => $jumlahProvinsi, 'jumlah_berdata' => 0],
        ];
    }
}
