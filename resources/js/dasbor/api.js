/**
 * Pemanggil endpoint api/v1. Setiap bagian dasbor memakai kunci sendiri,
 * sehingga permintaan lama dibatalkan ketika pengguna mengganti pilihan
 * sebelum respons sebelumnya tiba.
 */

const pengendali = new Map();

export class GalatApi extends Error {
    constructor(pesan, status) {
        super(pesan);
        this.name = 'GalatApi';
        this.status = status;
    }
}

export function dibatalkan(galat) {
    return galat?.name === 'AbortError';
}

export async function ambil(alamat, parameter, kunci) {
    pengendali.get(kunci)?.abort();

    const pengendaliBaru = new AbortController();
    pengendali.set(kunci, pengendaliBaru);

    const url = new URL(alamat, window.location.origin);

    for (const [nama, nilai] of Object.entries(parameter)) {
        if (Array.isArray(nilai)) {
            nilai.forEach((satu) => url.searchParams.append(`${nama}[]`, satu));
        } else if (nilai !== null && nilai !== undefined && nilai !== '') {
            url.searchParams.set(nama, nilai);
        }
    }

    let respons;

    try {
        respons = await fetch(url, {
            headers: { Accept: 'application/json' },
            signal: pengendaliBaru.signal,
        });
    } catch (galat) {
        if (dibatalkan(galat)) {
            throw galat;
        }

        throw new GalatApi('Tidak dapat terhubung ke server. Periksa koneksi lalu coba lagi.', 0);
    }

    const isi = await respons.json().catch(() => null);

    if (!respons.ok) {
        throw new GalatApi(pesanGalat(respons.status, isi), respons.status);
    }

    return isi.data;
}

function pesanGalat(status, isi) {
    if (status === 422 && isi?.errors) {
        return Object.values(isi.errors).flat()[0];
    }

    if (status === 429) {
        return 'Terlalu banyak permintaan dalam waktu singkat. Tunggu sebentar lalu coba lagi.';
    }

    return 'Data gagal dimuat. Coba muat ulang halaman.';
}
