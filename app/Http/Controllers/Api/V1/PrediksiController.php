<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\ModelPrediksi;
use App\Enums\SumberData;
use App\Enums\TingkatHarga;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\PrediksiRequest;
use App\Http\Resources\KomoditasResource;
use App\Http\Resources\ProvinsiResource;
use App\Models\HargaHarian;
use App\Models\Komoditas;
use App\Models\MetrikModel;
use App\Models\Prediksi;
use App\Models\Provinsi;
use Carbon\CarbonPeriod;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * Prediksi harga konsumen satu provinsi beserta metrik evaluasi model.
 *
 * Sesuai kriteria penerimaan Tabel 3.6, model terpilih hanya ditampilkan bila
 * MAPE di bawah ambang, R² tidak kurang dari ambang, dan galatnya lebih kecil
 * daripada garis dasar Naive. Bila tidak lolos, dasbor menampilkan garis dasar
 * Naive beserta keterangannya.
 */
class PrediksiController extends Controller
{
    public function __invoke(PrediksiRequest $request): JsonResponse
    {
        $komoditas = $request->komoditas();
        $provinsi = $request->provinsi();

        $metrik = MetrikModel::whereBelongsTo($komoditas)->get()->keyBy(fn (MetrikModel $baris): string => $baris->model->value);
        $evaluasi = $this->evaluasi($metrik);

        $terpilih = $metrik->firstWhere('terpilih', true);
        $modelTampil = $terpilih !== null && $evaluasi[$terpilih->model->value]['lolos']
            ? $terpilih->model
            : ModelPrediksi::Naive;

        $prediksi = Prediksi::whereBelongsTo($komoditas)
            ->whereBelongsTo($provinsi)
            ->whereIn('model', [$modelTampil, ModelPrediksi::Naive])
            ->orderBy('tanggal_sasaran')
            ->get()
            ->groupBy(fn (Prediksi $baris): string => $baris->model->value);

        return response()->json(['data' => [
            'komoditas' => KomoditasResource::make($komoditas),
            'provinsi' => ProvinsiResource::make($provinsi),
            'model' => ['kode' => $modelTampil->value, 'label' => $modelTampil->label()],
            'memakai_garis_dasar' => $modelTampil === ModelPrediksi::Naive,
            'dihasilkan_pada' => $prediksi->flatten()->max('dihasilkan_pada')?->toIso8601String(),
            'historis' => $this->historis($komoditas, $provinsi),
            'prediksi' => $this->titik($prediksi->get($modelTampil->value, collect())),
            'garis_dasar' => $modelTampil === ModelPrediksi::Naive
                ? []
                : $this->titik($prediksi->get(ModelPrediksi::Naive->value, collect())),
            'kriteria' => config('dasbor.kriteria'),
            'metrik' => $metrik->values()->map(fn (MetrikModel $baris): array => [
                'model' => $baris->model->value,
                'label' => $baris->model->label(),
                'mape' => (float) $baris->mape,
                'rmse' => (float) $baris->rmse,
                'mae' => (float) $baris->mae,
                'r2' => $baris->r2 === null ? null : (float) $baris->r2,
                'terpilih' => $baris->terpilih,
                'periode_uji_mulai' => $baris->periode_uji_mulai->toDateString(),
                'periode_uji_selesai' => $baris->periode_uji_selesai->toDateString(),
                ...$evaluasi[$baris->model->value],
            ]),
        ]]);
    }

    /**
     * Menilai setiap model terhadap kriteria penerimaan. Garis dasar Naive tidak
     * dibandingkan dengan dirinya sendiri, sehingga nilainya null.
     *
     * @param  Collection<string, MetrikModel>  $metrik
     * @return array<string, array{lolos_mape: bool, lolos_r2: bool, mengalahkan_naive: ?bool, lolos: bool}>
     */
    private function evaluasi(Collection $metrik): array
    {
        $kriteria = config('dasbor.kriteria');
        $naive = $metrik->get(ModelPrediksi::Naive->value);
        $hasil = [];

        foreach ($metrik as $kode => $baris) {
            $lolosMape = (float) $baris->mape < $kriteria['mape_maks'];
            $lolosR2 = $baris->r2 !== null && (float) $baris->r2 >= $kriteria['r2_min'];
            $mengalahkanNaive = $baris->model === ModelPrediksi::Naive
                ? null
                : $naive !== null
                    && (float) $baris->mape < (float) $naive->mape
                    && (float) $baris->rmse < (float) $naive->rmse
                    && (float) $baris->mae < (float) $naive->mae;

            $hasil[$kode] = [
                'lolos_mape' => $lolosMape,
                'lolos_r2' => $lolosR2,
                'mengalahkan_naive' => $mengalahkanNaive,
                'lolos' => $lolosMape && $lolosR2 && $mengalahkanNaive === true,
            ];
        }

        return $hasil;
    }

    /**
     * Harga konsumen Bapanas beberapa hari terakhir, dengan null pada hari tanpa data.
     *
     * @return array{tanggal: list<string>, harga: list<?float>}
     */
    private function historis(Komoditas $komoditas, Provinsi $provinsi): array
    {
        $kueri = HargaHarian::whereBelongsTo($komoditas)
            ->whereBelongsTo($provinsi)
            ->where('tingkat_harga', TingkatHarga::Konsumen)
            ->where('sumber', SumberData::Bapanas);

        $terakhir = $kueri->clone()->max('tanggal');

        if ($terakhir === null) {
            return ['tanggal' => [], 'harga' => []];
        }

        $selesai = Carbon::parse($terakhir);
        $mulai = $selesai->copy()->subDays(config('dasbor.prediksi.hari_historis') - 1);

        $harga = $kueri->whereBetween('tanggal', [$mulai->toDateString(), $selesai->toDateString()])
            ->get(['tanggal', 'harga'])
            ->mapWithKeys(fn (HargaHarian $baris): array => [$baris->tanggal->toDateString() => (float) $baris->harga]);

        $tanggal = [];
        $nilai = [];

        foreach (CarbonPeriod::create($mulai, $selesai) as $hari) {
            $kunci = $hari->toDateString();
            $tanggal[] = $kunci;
            $nilai[] = $harga->get($kunci);
        }

        return ['tanggal' => $tanggal, 'harga' => $nilai];
    }

    /**
     * @param  Collection<int, Prediksi>  $prediksi
     * @return list<array{tanggal: string, harga: float}>
     */
    private function titik(Collection $prediksi): array
    {
        return $prediksi->map(fn (Prediksi $baris): array => [
            'tanggal' => $baris->tanggal_sasaran->toDateString(),
            'harga' => (float) $baris->harga_prediksi,
        ])->values()->all();
    }
}
