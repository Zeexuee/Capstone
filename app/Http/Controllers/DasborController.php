<?php

namespace App\Http\Controllers;

use App\Enums\Pulau;
use App\Http\Resources\KomoditasResource;
use App\Http\Resources\ProvinsiResource;
use App\Models\HargaNasional;
use App\Models\Komoditas;
use App\Models\Provinsi;
use Illuminate\Contracts\View\View;

/**
 * Halaman dasbor. Daftar pilihan dirender di server, sedangkan angka dan grafik
 * dimuat oleh JavaScript dari endpoint api/v1.
 */
class DasborController extends Controller
{
    private const string KOMODITAS_AWAL = 'beras-medium';

    private const string PROVINSI_PREDIKSI_AWAL = '31';

    public function __invoke(): View
    {
        $komoditas = Komoditas::orderBy('kelompok')->orderBy('nama')->get();
        $provinsi = Provinsi::orderBy('pulau')->orderBy('nama')->get();
        $rentang = HargaNasional::query()->toBase()->selectRaw('min(tanggal) as awal, max(tanggal) as akhir')->first();

        $komoditasAwal = $komoditas->firstWhere('kode', self::KOMODITAS_AWAL) ?? $komoditas->first();
        $provinsiAwal = $provinsi->firstWhere('kode', self::PROVINSI_PREDIKSI_AWAL) ?? $provinsi->first();

        return view('dasbor', [
            'komoditasPerKelompok' => $komoditas->groupBy(fn (Komoditas $satu): string => $satu->kelompok->label()),
            'provinsiPerPulau' => $provinsi->groupBy(fn (Provinsi $satu): string => $satu->pulau->label()),
            'pulau' => Pulau::cases(),
            'komoditasAwal' => $komoditasAwal,
            'provinsiAwal' => $provinsiAwal,
            'tanggalAwal' => $rentang?->awal,
            'tanggalAkhir' => $rentang?->akhir,
            'dataTiruan' => (bool) config('dasbor.data_tiruan'),
            'kriteria' => config('dasbor.kriteria'),
            'maksProvinsi' => config('dasbor.tren.maks_provinsi'),
            'konfigurasi' => [
                'api' => [
                    'ringkasan' => route('api.v1.ringkasan'),
                    'tren' => route('api.v1.tren'),
                    'perbandingan' => route('api.v1.perbandingan'),
                    'prediksi' => route('api.v1.prediksi'),
                ],
                'komoditas' => KomoditasResource::collection($komoditas)->resolve(),
                'provinsi' => ProvinsiResource::collection($provinsi)->resolve(),
                'kriteria' => config('dasbor.kriteria'),
                'maksProvinsi' => config('dasbor.tren.maks_provinsi'),
                'tanggalAwal' => $rentang?->awal,
                'tanggalAkhir' => $rentang?->akhir,
            ],
        ]);
    }
}
