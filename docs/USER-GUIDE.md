# Panduan Penggunaan NusaLens

Status: 3 Oktober 2026. Panduan ini mengikuti alur yang tersedia, bukan mockup.
NusaLens membantu riset, bukan memberi keputusan beli/jual/tahan.

## 1. Masuk dan cari perusahaan

1. Buka aplikasi, daftar bila belum memiliki akun, lalu verifikasi email.
2. Login. Dashboard menyediakan pencarian nama/kode dan akses riset tersimpan.
3. Ketik nama, misalnya `Adaro`, tanpa perlu mengetahui ticker. Pilih hasil
   autocomplete dengan nama/logo yang sesuai, atau buka **Temukan Saham**.
4. Pada daftar, terapkan/reset pencarian dan gunakan pagination. Pilih perusahaan
   untuk masuk ke `/perusahaan/AADI`, misalnya PT Adaro Andalan Indonesia Tbk.

`/perusahaan` hanya mengalihkan ke Temukan Saham. Bukan daftar kedua.
Nama mirip belum tentu perusahaan yang sama: cocokkan nama lengkap dan profil.

## 2. Pahami satu perusahaan

Tab di cockpit memisahkan pertanyaan riset:

| Tab | Yang diperiksa |
| --- | --- |
| Ringkasan Riset | Laba/rugi, margin, ekuitas dan temuan yang memiliki bukti. |
| Profil | Identitas dan klasifikasi bisnis. |
| Harga | Grafik harga historis, rentang 30/90 hari, serta tabel sumber. |
| Keuangan | Angka kuartalan dan tren pendapatan/laba/aset/ekuitas. |
| Valuasi | Rasio historis tahunan yang disediakan provider. |

1. Tekan **Buka ringkasan riset** untuk mengambil fakta yang dibutuhkan.
2. Buka **Bukti dan perhitungan** pada temuan. Periksa input, unit, periode,
   rumus, sumber dan waktu pengambilannya.
3. Buka tab lain sesuai pertanyaan, tidak perlu memuat seluruh bagian sekaligus.
4. Grafik keuangan memakai satuan ringkas; tabel menyajikan angka lengkap.
   Tooltip membantu membaca periode. Kosong berarti tidak tersedia, bukan nol.

Ringkasan memakai `research-facts-v1`, misalnya margin `laba / pendapatan x 100`.
Ini berbeda dari formula peringkat peer `nusalens-v1.0.0` pada langkah berikutnya.
Hubungan laba dan kas tetap perlu diperiksa karena kesamaan basis arus kas belum
terverifikasi. Daftar keterbatasan bukan angka tambahan dari API.

## 3. Analisis perusahaan sejenis

1. Di bagian **Posisi AADI dibanding perusahaan sejenis**, tekan
   **Analisis perusahaan sejenis**.
2. Sistem mengambil input fundamental dan seluruh populasi sektor melalui backend.
   Sistem memilih peer kompatibel; tidak hanya memakai saham pilihan pengguna.
3. Baca kelengkapan berbobot, jumlah populasi sumber, grafik dan lima komponen.
4. Buka satu komponen. Periksa nilai mentah, periode, kelompok, jumlah peer,
   rank/percentile, bobot efektif dan alasan perusahaan dikeluarkan.
5. **Buka seluruh bukti perhitungan** membuka JSON input/hasil immutable untuk
   penelusuran rinci. Bukti ini tidak memuat API key atau data pribadi pengguna.

| Komponen | Bobot | Inti perhitungan |
| --- | --- | --- |
| Kesehatan Bisnis | 30% | ROE/ROA tahunan. |
| Pertumbuhan | 25% | `(kuartal sekarang / kuartal sama tahun lalu - 1) x 100`. |
| Harga Saham | 20% | Kapitalisasi pasar / laba empat kuartal; kapitalisasi / ekuitas kuartalan. |
| Kekuatan Pasar | 15% | Perubahan penutupan 20 sesi dengan basis aksi korporasi terverifikasi. |
| Keamanan Keuangan | 10% | Bank CAR/NPL; nonkeuangan DER/current ratio. |

Nilai relatif memerlukan sedikitnya **lima peer lain** valid per metrik. Semua
peer valid dipakai. Bank tidak dicampur nonbank. Fallback periode maksimal satu
tahun/kuartal, ditampilkan jelas. Angka tampilan dua desimal; rumus tidak dibulatkan
di tengah. Rincian ada di [SCORING](SCORING.md).

### Mengapa total belum muncul?

Total memerlukan **kelengkapan berbobot minimal 70% sebelum penyesuaian bobot**.
Quality + Growth + Risk lengkap baru 65%. Contoh smoke AADI menghasilkan 65,00%:
tiga komponen dapat dibaca tetapi total memang harus ditahan. Ini bukan skor nol.
Keuangan nonbank tidak dipaksa memakai rasio risiko industri.

**Lengkapi valuasi peer** mencoba mengambil harga seluruh kelompok yang memenuhi
syarat laporan. Estimasi jumlah panggilan harus muat dalam kuota. Jika tidak cukup,
tidak dimulai pengambilan parsial kelompok tersebut. Jika provider gagal di tengah,
data parsial tidak dipakai sebagai sampel lengkap; credit yang telanjur terpakai
tetap tercatat. Laba TTM/ekuitas/tanggal pasar harus cocok agar valuasi masuk nilai.

Momentum real tetap tidak tersedia sampai basis aksi korporasi terbukti. Tab Harga
boleh menampilkan seri mentah, tetapi itu bukan izin memakainya sebagai skor momentum.
Tab Valuasi historis juga tidak menggantikan PE TTM/PB MRQ pada mesin nilai.

### Data lama

Lihat waktu sumber. Saat masa berlaku habis, panel diberi status historis.
**Perbarui analisis** membaca ulang sumber/cache sesuai kebijakan; tidak memperbarui
usia data hanya karena perhitungan dijalankan lagi. Perbarui analisis awal memakai
fundamental; valuasi opsional perlu diminta kembali. Hasil lama tidak ditimpa.

## 4. Temukan pembanding

1. Setelah analisis muncul, pilih **Lihat kandidat sejenis**.
2. Cari nama/kode dan urutkan menurut Kesehatan Bisnis, Pertumbuhan atau Keamanan
   Keuangan. Tekan **Terapkan**; **Reset** kembali ke seluruh kandidat terkait.
3. Daftar memakai bukti tersimpan, tanpa panggilan Sectors tambahan. Pagination
   tidak mengubah populasi peer. Nilai kosong tetap diurutkan setelah nilai valid.
4. Buka detail dengan ikon mata atau gunakan ikon Bandingkan untuk menyandingkan
   target awal dengan kandidat itu.

Kelompok dan periode tiap metrik dapat berbeda. Skor identik bukan berarti
risiko atau prospek identik. Daftar ini bukan screener seluruh IDX atau rekomendasi.

## 5. Bandingkan maksimal tiga saham

1. Gunakan **Bandingkan** dari cockpit/kandidat atau sidebar.
2. Cari perusahaan lewat autocomplete, lalu tambahkan pilihan. Maksimal tiga.
   URL menyimpan pilihan `symbols`; saat kosong tidak ada saham contoh otomatis.
3. Periksa profil. Muat harga, keuangan, atau valuasi bila diperlukan.
4. Jalankan analisis perusahaan sejenis untuk setiap saham yang ingin dinilai.
   Mesin memakai peer masing-masing, bukan tiga pilihan sebagai kelompok scoring.
5. Periksa matriks, grafik, kelengkapan, dan bukti. Jangan menyamakan peringkat
   perusahaan dari jenis bisnis berbeda tanpa membaca kelompok pembandingnya.

Error pada satu saham tidak menghapus data saham lain. Snapshot memerlukan profil
pilihan yang berhasil dimuat; tidak memaksa pengambilan semua section atau skor.

## 6. Simpan dan lanjutkan riset

1. Tekan **Simpan Snapshot**. Data yang telah dimuat disimpan, tanpa refresh API.
2. Buka **Riset Tersimpan** di sidebar untuk mencari nama dan membuka versi lama.
3. Ikon pensil mengubah nama. Ikon hapus meminta konfirmasi untuk menghapus versi.
4. Pada detail pilih **Buat versi terbaru** untuk membuka pilihan saham yang sama.
5. Muat kembali bagian/nilai yang dibutuhkan, kemudian simpan. Versi sebelumnya
   tetap utuh; versi baru berisi bagian yang dimuat pada sesi pembaruan tersebut.

Snapshot privat hanya milik akun yang menyimpan. Angka berasal dari receipt backend
dan bukti Intelligence, bukan angka kiriman browser yang dipercaya begitu saja.
Seluruh input/hasil skor disalin ke snapshot agar tetap dapat dibaca setelah bukti
bersama berusia lebih dari 30 hari dibersihkan. Menghapus versi tidak menghapus
versi lain. Tidak ada sharing publik atau riwayat pencarian otomatis.

## 7. Saat data atau credit tidak tersedia

- Kuota aplikasi 20 credit per akun/hari, reset 00.00 WIB. Budget project 1.000
  sekali pakai; bukan refill harian. Cadangan 600 tidak digunakan otomatis.
- Cache hit, bukti tersimpan, dan membuka snapshot tidak memerlukan credit Sectors.
- Cache fundamental 24 jam, pasar peer 1 jam dengan batas observasi 24 jam;
  pengecualian libur bursa belum digunakan tanpa kalender yang terbukti.
- Jangan menekan ulang berulang kali saat limit tercapai. Buka riset tersimpan.
- Provider error, peer kurang, dan data kosong ditampilkan berbeda. Error tidak
  disulap menjadi skor, nol, atau rekomendasi transaksi.

## Batas MVP dan rencana lanjut

Alur terpandu memakai langkah/aksi pada cockpit, kandidat, compare dan snapshot;
tidak ada wizard modal terpisah. AI belum diintegrasikan. Berikutnya: validasi
basis harga/kalender serta cakupan valuasi live, kemudian AI opsional dengan budget
terpisah. Paket berbayar/BYOK adalah arah pasca-MVP, belum implementasi.
Lihat [Roadmap](ROADMAP.md) dan [hasil verifikasi](work-items/penuntasan-mvp/README.md).
