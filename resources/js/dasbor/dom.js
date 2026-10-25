/**
 * Pembantu DOM. Seluruh teks dari API ditulis lewat textContent, tidak pernah
 * lewat innerHTML, agar data tidak dapat menyisipkan markup ke halaman.
 */

export function cari(id) {
    return document.getElementById(id);
}

export function tulis(id, teks) {
    cari(id).textContent = teks;
}

export function buat(tag, { kelas = '', teks = null, atribut = {} } = {}, anak = []) {
    const elemen = document.createElement(tag);

    if (kelas) {
        elemen.className = kelas;
    }

    if (teks !== null) {
        elemen.textContent = teks;
    }

    for (const [nama, nilai] of Object.entries(atribut)) {
        elemen.setAttribute(nama, nilai);
    }

    elemen.append(...anak);

    return elemen;
}

export function kosongkan(id) {
    cari(id).replaceChildren();
}

/**
 * Menampilkan pesan pada elemen role="status". Pesan kosong menyembunyikannya.
 */
export function status(id, pesan = '', galat = false) {
    const elemen = cari(id);
    elemen.textContent = pesan;
    elemen.classList.toggle('text-red-700', galat);
    elemen.classList.toggle('text-slate-600', !galat);
}

export function sel(teks, kelas = 'px-3 py-2') {
    return buat('td', { kelas, teks });
}
