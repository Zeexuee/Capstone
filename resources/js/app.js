import { mulaiDasbor } from './dasbor/index.js';

const konfigurasiDasbor = document.getElementById('konfigurasi-dasbor');

if (konfigurasiDasbor) {
    mulaiDasbor(JSON.parse(konfigurasiDasbor.textContent));
}
