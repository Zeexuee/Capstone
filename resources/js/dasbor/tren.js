import { ambil, dibatalkan } from './api.js';
import { buat, cari, kosongkan, sel, status, tulis } from './dom.js';
import * as format from './format.js';
import { gambar, WARNA } from './grafik.js';

const SATUAN_PERIODE = { harian: 'hari', mingguan: 'minggu', bulanan: 'bulan' };

let grafik = null;
let dataTerakhir = null;

function kotakProvinsi() {
    return [...document.querySelectorAll('input[name="tren-provinsi"]')];
}

/**
 * Provinsi di luar pulau terpilih disembunyikan dan dilepas, lalu pilihan
 * dikunci ketika batas maksimum tercapai.
 */
export function sesuaikanPemilih(konfigurasi, pulau) {
    for (const kelompok of document.querySelectorAll('[data-kelompok-pulau]')) {
        kelompok.hidden = pulau !== '' && kelompok.dataset.kelompokPulau !== pulau;
    }

    for (const kotak of kotakProvinsi()) {
        if (pulau !== '' && kotak.dataset.pulau !== pulau) {
            kotak.checked = false;
        }
    }

    kunciBatas(konfigurasi);
}

export function kunciBatas(konfigurasi) {
    const terpilih = kotakProvinsi().filter((kotak) => kotak.checked).length;

    for (const kotak of kotakProvinsi()) {
        kotak.disabled = !kotak.checked && terpilih >= konfigurasi.maksProvinsi;
    }

    tulis('tren-jumlah-provinsi', String(terpilih));
}

function rentangTanggal(konfigurasi) {
    const pilihan = cari('tren-rentang').value;
    const akhir = konfigurasi.tanggalAkhir;

    if (!akhir) {
        return {};
    }

    if (pilihan === 'semua') {
        return { mulai: konfigurasi.tanggalAwal, selesai: akhir };
    }

    return { mulai: format.tambahHari(akhir, 1 - Number(pilihan)), selesai: akhir };
}

function anotasi(data) {
    const penanda = data.penanda.map((satu) => ({
        x: format.keWaktu(satu.tanggal),
        borderColor: WARNA.penanda,
        strokeDashArray: 3,
        label: {
            text: satu.nama,
            orientation: 'vertical',
            borderColor: 'transparent',
            style: { background: 'transparent', color: '#475569', fontSize: '11px' },
        },
    }));

    const panen = data.musim_panen.map((satu) => ({
        x: format.keWaktu(satu.mulai),
        x2: format.keWaktu(satu.selesai),
        fillColor: WARNA.panen,
        opacity: 0.08,
        borderColor: 'transparent',
        label: {
            text: 'Musim panen',
            position: 'top',
            orientation: 'horizontal',
            textAnchor: 'start',
            offsetX: 6,
            offsetY: 16,
            borderColor: 'transparent',
            style: { background: 'transparent', color: '#15803d', fontSize: '11px' },
        },
    }));

    return [...panen, ...penanda];
}

function gambarGrafik(data) {
    const satuan = data.komoditas.satuan;

    grafik = gambar(cari('tren-grafik'), grafik, {
        chart: { type: 'line', height: 360 },
        series: data.seri.map((seri) => ({
            name: seri.nama,
            data: data.tanggal.map((tanggal, i) => [format.keWaktu(tanggal), seri.harga[i]]),
        })),
        colors: [WARNA.nasional, ...WARNA.provinsi],
        stroke: {
            curve: 'straight',
            width: data.seri.map((_, i) => (i === 0 ? 2 : 2.5)),
            dashArray: data.seri.map((_, i) => (i === 0 ? 6 : 0)),
        },
        markers: { size: data.agregasi === 'harian' ? 0 : 3 },
        xaxis: { type: 'datetime', labels: { datetimeUTC: true } },
        yaxis: { labels: { formatter: (nilai) => format.rupiah(nilai) } },
        tooltip: {
            shared: true,
            x: { formatter: (waktu) => format.periode(new Date(waktu).toISOString().slice(0, 10), data.agregasi) },
            y: { formatter: (nilai) => (nilai === null || nilai === undefined ? 'tanpa data' : format.hargaPerSatuan(nilai, satuan)) },
        },
        annotations: { xaxis: anotasi(data) },
    });
}

function tampilkanCelah(data) {
    kosongkan('tren-celah');

    const satuanPeriode = SATUAN_PERIODE[data.agregasi];

    for (const seri of data.seri.slice(1)) {
        const kosong = seri.harga.filter((nilai) => nilai === null).length;

        if (kosong > 0) {
            cari('tren-celah').append(buat('li', {
                teks: `${seri.nama}: ${kosong} dari ${seri.harga.length} ${satuanPeriode} tanpa data pada periode ini.`,
            }));
        }
    }
}

function isiTabel() {
    const data = dataTerakhir;

    if (!data || !cari('tren-rincian').open) {
        return;
    }

    kosongkan('tren-tabel-kepala');
    kosongkan('tren-tabel-isi');

    cari('tren-tabel-kepala').append(buat('tr', {}, [
        buat('th', { kelas: 'px-3 py-2', teks: 'Periode', atribut: { scope: 'col' } }),
        ...data.seri.map((seri) => buat('th', { kelas: 'px-3 py-2 text-right', teks: seri.nama, atribut: { scope: 'col' } })),
    ]));

    const baris = data.tanggal.map((tanggal, i) => buat('tr', {}, [
        buat('th', { kelas: 'px-3 py-2 font-normal', teks: format.periode(tanggal, data.agregasi), atribut: { scope: 'row' } }),
        ...data.seri.map((seri) => sel(seri.harga[i] === null ? 'tanpa data' : format.rupiah(seri.harga[i]), 'px-3 py-2 text-right tabular-nums')),
    ]));

    cari('tren-tabel-isi').append(...baris.reverse());
}

export function pasangTabel() {
    cari('tren-rincian').addEventListener('toggle', isiTabel);
}

export async function muatTren(konfigurasi, filter) {
    status('tren-status', 'Memuat tren harga…');

    const provinsi = kotakProvinsi().filter((kotak) => kotak.checked).map((kotak) => kotak.value);

    let data;

    try {
        data = await ambil(konfigurasi.api.tren, {
            komoditas: filter.komoditas,
            tingkat: filter.tingkat,
            provinsi,
            agregasi: cari('tren-agregasi').value,
            ...rentangTanggal(konfigurasi),
        }, 'tren');
    } catch (galat) {
        if (!dibatalkan(galat)) {
            status('tren-status', galat.message, true);
        }

        return;
    }

    dataTerakhir = data;
    gambarGrafik(data);
    tampilkanCelah(data);
    isiTabel();

    status('tren-status', `${format.tanggal(data.mulai)} sampai ${format.tanggal(data.selesai)} · harga per ${data.komoditas.satuan}`);
}
