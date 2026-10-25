# Catatan Keputusan dan Asumsi Data

Dokumen ini mencatat keputusan tim tentang data, asumsi yang belum terkonfirmasi, dan kontrak format yang wajib diikuti pipeline ETL Python saat mengisi basis data. Perbarui dokumen ini setiap kali ada keputusan baru.

## 1. Musim panen (asumsi, belum terkonfirmasi)

Kolom `kalender.musim_panen` saat ini bernilai `true` untuk bulan **Maret dan April** setiap tahun.

Ini **asumsi kerja**, bukan fakta yang sudah diverifikasi. Data BPS yang ditemukan hanya menunjukkan bahwa potensi luas panen padi Februari sampai April 2026 diperkirakan 3,92 juta hektare, atau sekitar 1,3 juta hektare per bulan. Angka itu jauh lebih tinggi daripada Mei 2025 (0,98 juta hektare) dan Juni 2025 (0,79 juta hektare). Panen kedua juga terlihat pada Agustus 2025 (1,11 juta hektare) dan September 2025 (1,13 juta hektare).

Sebelum kolom ini dipakai sebagai fitur model, konfirmasi bulan panen raya dengan data luas panen padi bulanan BPS. Kalau hasilnya berbeda, ubah konstanta `BULAN_MUSIM_PANEN` di `database/seeders/KalenderSeeder.php`, lalu jalankan ulang `php artisan db:seed --class=KalenderSeeder`.

## 2. Satuan harga

| Kelompok | Varian | Satuan baku |
|---|---|---|
| Beras | Beras Medium, Beras Premium | kg |
| Minyak goreng | Minyak Goreng Kemasan, Minyak Goreng Curah | liter |

Aturan untuk minyak goreng:

- Semua harga minyak goreng disimpan dalam rupiah per liter.
- Harga minyak goreng yang tercatat per kilogram **tidak dikonversi dan tidak dimuat** ke `harga_harian`. Baris itu tetap disimpan di arsip CSV mentah, dan ETL wajib melaporkan jumlah baris yang dilewati.
- Alasannya: konversi kilogram ke liter membutuhkan faktor massa jenis yang bergantung pada suhu dan jenis minyak, dan belum ditemukan faktor konversi resmi yang dapat dirujuk. Memakai faktor perkiraan akan memasukkan galat sistematis ke perbandingan antarwilayah.
- Dampaknya kecil karena PIHPS, yang mencatat minyak goreng per kilogram, hanya dipakai sebagai pembanding validasi dan tidak ditampilkan di dasbor.
- Kalau ternyata Panel Harga Bapanas juga mencatat minyak goreng curah per kilogram, varian curah dikeluarkan dari cakupan dan hanya minyak goreng kemasan yang dipakai.

## 3. Kalender hari besar

Kalender mencakup 1 Januari 2024 sampai 31 Desember 2026. Hari besar yang dicatat:

| Hari besar | 2024 | 2025 | 2026 |
|---|---|---|---|
| Tahun Baru Imlek | 10 Feb | 29 Jan | 17 Feb |
| Awal Ramadan | 12 Mar | 1 Mar | 19 Feb |
| Idul Fitri (1 Syawal) | 10 Apr | 31 Mar | 21 Mar |
| Idul Adha | 17 Jun | 6 Jun | 27 Mei |
| Natal | 25 Des | 25 Des | 25 Des |

Sumber dan aturan pencatatan:

- Idul Fitri, Idul Adha, Imlek, dan Natal mengikuti hari libur nasional dalam SKB 3 Menteri, diperiksa silang dengan daftar OfficeHolidays 2024 dan 2025 serta daftar hari libur Indonesia 2026.
- Awal Ramadan mengikuti penetapan pemerintah melalui sidang isbat Kementerian Agama, bukan penetapan organisasi kemasyarakatan. Contohnya, pada 2026 pemerintah menetapkan 19 Februari, sedangkan Muhammadiyah 18 Februari.
- Untuk Idul Fitri hanya 1 Syawal yang dicatat, walaupun hari kedua juga libur nasional.

**Tahun 2027 belum dicatat.** Dua sumber yang ditemukan saling bertentangan:

| Hari besar 2027 | Sumber A (artikel yang mengutip SKB) | Sumber B (OfficeHolidays) |
|---|---|---|
| Tahun Baru Imlek | 6 Feb | 6 Feb |
| Idul Fitri | 10 Mar | 9 Mar |
| Idul Adha | 17 Mei | 16 Mei |
| Natal | 25 Des | 25 Des |

Kalender diperpanjang ke 2027 setelah SKB 3 Menteri tentang libur nasional 2027 dibaca langsung dari dokumen resminya. Ini perlu dikerjakan sebelum horizon prediksi melewati 31 Desember 2026.

## 4. Rata-rata nasional

Acuan rata-rata nasional adalah **angka nasional yang diterbitkan Panel Harga Pangan Bapanas**, bukan rata-rata dari 21 provinsi cakupan penelitian. Angka ini disimpan di tabel `harga_nasional`.

Indikator di tabel `indikator_harga` dihitung dari baris `harga_harian` bersumber Bapanas, dengan tanggal, komoditas, dan tingkat harga yang sama:

- `rata_rata_nasional` = harga di `harga_nasional`
- `selisih` = harga provinsi − rata-rata nasional
- `selisih_persen` = selisih ÷ rata-rata nasional × 100, dibulatkan dua desimal

Indikator hanya dibuat untuk tanggal yang memiliki harga provinsi dan harga nasional sekaligus.

## 5. Kontrak data untuk ETL

Skema basis data dimiliki migrasi Laravel. ETL hanya mengisi dan memperbarui baris, tidak membuat atau mengubah tabel.

- **Jangan memakai `pandas.to_sql(if_exists='replace')`.** Perintah itu menghapus dan membuat ulang tabel, sehingga unique key, index, dan foreign key hilang. Pakai `if_exists='append'` untuk tabel kosong, atau upsert dengan `INSERT ... ON DUPLICATE KEY UPDATE`.
- **Format tanggal selalu `YYYY-MM-DD`**, tanpa jam.
- **Tanggal tanpa data tidak diberi baris.** Jangan mengisi nol atau menyalin harga hari sebelumnya.
- **Harga dalam rupiah dengan dua desimal**, sudah dalam satuan baku komoditasnya.
- **Wilayah dicocokkan lewat `provinsi.kode`**, yaitu kode provinsi BPS dua digit, bukan lewat nama.
- **Komoditas dicocokkan lewat `komoditas.kode`**: `beras-medium`, `beras-premium`, `minyak-goreng-kemasan`, `minyak-goreng-curah`.
- **Prediksi selalu untuk harga konsumen.** Tabel `prediksi` tidak memiliki kolom tingkat harga, sehingga layanan Python hanya menulis prediksi harga tingkat konsumen dari sumber Bapanas.
- **Harga produsen hanya untuk beras.** Minyak goreng hanya dicatat di tingkat konsumen.

Nilai kolom kategori yang diterima:

| Kolom | Nilai |
|---|---|
| `tingkat_harga` | `konsumen`, `produsen` |
| `jenis_pasar` | `umum`, `tradisional`, `modern` |
| `sumber` | `bapanas`, `pihps` |
| `model` | `random_forest`, `xgboost`, `naive` |

Kunci upsert setiap tabel:

| Tabel | Kunci unik |
|---|---|
| `harga_harian` | tanggal, provinsi_id, komoditas_id, tingkat_harga, jenis_pasar, sumber |
| `harga_nasional` | komoditas_id, tingkat_harga, tanggal |
| `indikator_harga` | tanggal, provinsi_id, komoditas_id, tingkat_harga |
| `prediksi` | provinsi_id, komoditas_id, model, tanggal_sasaran |
| `metrik_model` | komoditas_id, model |

## 6. Data tiruan

`DataTiruanSeeder` mengisi tabel fakta dengan data buatan agar dasbor dapat dikembangkan sebelum ETL selesai.

- **Data tiruan tidak boleh dilaporkan sebagai hasil penelitian.** Angka harga, prediksi, dan metrik di dalamnya dibuat oleh program, bukan diukur.
- Jalankan dengan `php artisan db:seed --class=DataTiruanSeeder`. Seeder menolak berjalan di lingkungan production dan menolak berjalan kalau tabel fakta sudah berisi data.
- Sebelum memuat data asli, kosongkan basis data dengan `php artisan migrate:fresh --seed`. Perintah ini menghapus seluruh isi basis data lokal lalu mengisi ulang data acuan.
- Data tiruan bersifat deterministik, sehingga setiap anggota tim mendapat angka yang sama.
- Celah data sengaja dibuat agar tampilan celah di grafik dapat diuji. Rinciannya ada di PHPDoc `database/seeders/DataTiruanSeeder.php`.
