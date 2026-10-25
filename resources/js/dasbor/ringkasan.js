import { ambil, dibatalkan } from './api.js';
import { cari, status, tulis } from './dom.js';
import * as format from './format.js';

const KARTU = ['nasional', 'perubahan', 'termurah', 'termahal', 'rentang'];

function kosongkanKartu() {
    for (const kartu of KARTU) {
        tulis(`kpi-${kartu}`, '–');
        tulis(`kpi-${kartu}-sub`, '\u00a0');
    }

    warnaiPerubahan(null);
}

function warnaiPerubahan(nilai) {
    const elemen = cari('kpi-perubahan');
    elemen.classList.toggle('text-mahal', nilai > 0);
    elemen.classList.toggle('text-murah', nilai < 0);
    elemen.classList.toggle('text-slate-900', !nilai);
}

function provinsi(id, data, satuan) {
    tulis(`kpi-${id}`, data.nama);
    tulis(`kpi-${id}-sub`, `${format.hargaPerSatuan(data.harga, satuan)} · ${format.persenBertanda(data.selisih_persen)} dari nasional`);
}

export async function muatRingkasan(konfigurasi, filter) {
    status('ringkasan-status', 'Memuat ringkasan…');

    let data;

    try {
        data = await ambil(konfigurasi.api.ringkasan, filter, 'ringkasan');
    } catch (galat) {
        if (!dibatalkan(galat)) {
            kosongkanKartu();
            status('ringkasan-status', galat.message, true);
        }

        return;
    }

    kosongkanKartu();

    if (data.tanggal === null) {
        status('ringkasan-status', 'Belum ada data harga untuk pilihan ini.');

        return;
    }

    const satuan = data.komoditas.satuan;

    tulis('kpi-nasional', format.hargaPerSatuan(data.harga_nasional, satuan));
    tulis('kpi-nasional-sub', `per ${format.tanggal(data.tanggal)}`);

    if (data.perubahan) {
        tulis('kpi-perubahan', format.persenBertanda(data.perubahan.persen));
        tulis('kpi-perubahan-sub', `dibanding ${format.tanggal(data.perubahan.tanggal_pembanding)}`);
        warnaiPerubahan(data.perubahan.persen);
    }

    if (data.termurah) {
        provinsi('termurah', data.termurah, satuan);
        provinsi('termahal', data.termahal, satuan);
        tulis('kpi-rentang', format.hargaPerSatuan(data.rentang.rupiah, satuan));
        tulis('kpi-rentang-sub', `termahal ${format.persen(data.rentang.persen)} di atas termurah`);
    }

    const { jumlah_berdata: berdata, jumlah_provinsi: semua } = data.cakupan;

    status('ringkasan-status', berdata < semua
        ? `${berdata} dari ${semua} provinsi memiliki data pada ${format.tanggal(data.tanggal)}. Provinsi tanpa data tidak ikut dibandingkan.`
        : '');
}
