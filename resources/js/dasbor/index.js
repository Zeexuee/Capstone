import { cari } from './dom.js';
import { muatPerbandingan } from './perbandingan.js';
import { muatPrediksi } from './prediksi.js';
import { muatRingkasan } from './ringkasan.js';
import { kunciBatas, muatTren, pasangTabel, sesuaikanPemilih } from './tren.js';

function filterGlobal() {
    return {
        komoditas: cari('filter-komoditas').value,
        tingkat: cari('filter-tingkat').value,
        pulau: cari('filter-pulau').value,
    };
}

/**
 * Harga produsen hanya tersedia untuk beras, sehingga pilihan itu dinonaktifkan
 * untuk minyak goreng.
 */
function sesuaikanTingkat(konfigurasi) {
    const komoditas = konfigurasi.komoditas.find((satu) => satu.kode === cari('filter-komoditas').value);
    const pilihanProdusen = cari('filter-tingkat').querySelector('option[value="produsen"]');
    const hanyaKonsumen = komoditas?.kelompok !== 'beras';

    pilihanProdusen.disabled = hanyaKonsumen;

    if (hanyaKonsumen) {
        cari('filter-tingkat').value = 'konsumen';
    }
}

export function mulaiDasbor(konfigurasi) {
    const muatTerfilter = () => {
        const filter = filterGlobal();
        muatRingkasan(konfigurasi, filter);
        muatTren(konfigurasi, filter);
        muatPerbandingan(konfigurasi, filter);
    };

    cari('filter-komoditas').addEventListener('change', () => {
        sesuaikanTingkat(konfigurasi);
        muatTerfilter();
        muatPrediksi(konfigurasi, filterGlobal());
    });

    cari('filter-tingkat').addEventListener('change', muatTerfilter);

    cari('filter-pulau').addEventListener('change', () => {
        sesuaikanPemilih(konfigurasi, cari('filter-pulau').value);
        muatTerfilter();
    });

    for (const id of ['tren-rentang', 'tren-agregasi']) {
        cari(id).addEventListener('change', () => muatTren(konfigurasi, filterGlobal()));
    }

    cari('tren-pemilih').addEventListener('change', (peristiwa) => {
        if (peristiwa.target.name === 'tren-provinsi') {
            kunciBatas(konfigurasi);
            muatTren(konfigurasi, filterGlobal());
        }
    });

    cari('perbandingan-tanggal').addEventListener('change', () => muatPerbandingan(konfigurasi, filterGlobal()));
    cari('prediksi-provinsi').addEventListener('change', () => muatPrediksi(konfigurasi, filterGlobal()));

    pasangTabel();
    sesuaikanTingkat(konfigurasi);
    sesuaikanPemilih(konfigurasi, cari('filter-pulau').value);
    muatTerfilter();
    muatPrediksi(konfigurasi, filterGlobal());
}
