import ApexCharts from 'apexcharts';
import localeId from 'apexcharts/dist/locales/id.json';

export const WARNA = {
    nasional: '#64748b',
    merek: '#1a4f8b',
    murah: '#0f766e',
    mahal: '#b45309',
    panen: '#16a34a',
    penanda: '#94a3b8',
    provinsi: ['#1a4f8b', '#0f766e', '#b45309', '#7c3aed', '#be123c'],
};

const DASAR = {
    chart: {
        locales: [localeId],
        defaultLocale: 'id',
        fontFamily: 'inherit',
        toolbar: { show: false },
        zoom: { enabled: false },
        animations: { enabled: false },
    },
    dataLabels: { enabled: false },
    grid: { borderColor: '#e2e8f0', strokeDashArray: 3 },
    legend: { position: 'top', horizontalAlign: 'left', fontSize: '13px' },
    noData: { text: 'Tidak ada data untuk pilihan ini.' },
};

/**
 * Grafik lama dihancurkan lalu dibuat ulang, sehingga anotasi dan sumbu dari
 * pilihan sebelumnya tidak tertinggal.
 */
export function gambar(wadah, grafikLama, opsi) {
    grafikLama?.destroy();

    const grafik = new ApexCharts(wadah, {
        ...DASAR,
        ...opsi,
        chart: { ...DASAR.chart, ...opsi.chart },
    });

    grafik.render();

    return grafik;
}
