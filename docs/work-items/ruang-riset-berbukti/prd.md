# PRD: Ruang Riset Berbukti

## Status

Increment 3A dilanjutkan atas instruksi user, 2026-09-25. Rincian implementasi
dan batas data ada di [plan](plan.md); increment 3B-5 tetap memerlukan review.
Tambahan 2026-09-26: dokumentasi kesiapan 3B dan navigasi UX-1 atas masukan user.
UX-1 disetujui dan selesai diimplementasikan; gate 3B tetap menunggu review.

## Masalah, tujuan, dan pengguna

Pengguna awam dapat melihat angka tetapi belum tentu memahami apa yang perlu
diteliti. Menambah menu, indikator, dan grafik dapat memperbesar kebingungan.
Wizard yang hanya membungkus screener/compare juga belum cukup berbeda.

Tujuan: setelah memilih perusahaan, pengguna memahami satu temuan penting,
bukti yang mendukung atau membatasinya, dan langkah pemeriksaan berikutnya.
Pengguna tidak perlu hafal ticker atau menulis prompt AI untuk memperoleh hasil.

Positioning yang diusulkan: **NusaLens membantu menguji alasan riset saham
dengan bukti yang dapat ditelusuri.** Bukan mesin penentu saham yang harus dibeli.

## Pilihan arah

| Arah | Keputusan usulan | Alasan |
| --- | --- | --- |
| Screener lebih banyak filter | Bukan fokus pembeda | Sectors sudah menyediakan screener ekspresif. |
| Chat AI umum | Tunda | Sudah ada pada referensi; kualitas bukti dan biaya belum terkendali. |
| Explorer kepemilikan | Tunda | Overlap langsung, entitlement dan cakupan API belum dibuktikan. |
| Uji temuan dengan bukti dan keterbatasan | Prioritas pertama | Hasil riset terstruktur yang dapat diperiksa dan dipakai pengguna awam. |
| Bandingkan alasan, bukan hanya angka | Tahap berikutnya | Memperlihatkan trade-off tanpa memaksakan pemenang. |
| Tinjau perubahan versi riset | Setelah persistence manual | Memisahkan perubahan bisnis dari perubahan data, peer, atau formula. |

Ketiga arah terpilih adalah satu alur kerja, bukan tiga dashboard terpisah.
Kesamaan fungsi dasar tetap wajar; keberhasilan dinilai dari manfaat alurnya,
bukan sekadar nama fitur baru. Observasi guest tidak membuktikan eksklusivitas.

## Scope dan non-scope

- Pertahankan pencarian nama/ticker, logo, profil, dan Recharts berbasis data real.
- Tambahkan Ringkasan Riset terstruktur pada konteks perusahaan.
- Gunakan penjelasan deterministik dengan bukti, bukan opini AI sebagai dasar.
- Compare maksimal tiga perusahaan; penilaian peer tetap hanya yang sebanding.
- Simpan hanya perbandingan secara manual, privat, berbasis snapshot berversi
  sesuai MVP. Ringkasan satu perusahaan bukan entitas laporan tersimpan baru.
- Tidak ada BUY/HOLD/SELL, target harga, prediksi pasti, confidence investasi,
  watchlist, auto-alert, trading, atau pengambilan seluruh dataset.

## Requirement

### Alur pengguna

1. Cari nama perusahaan atau ticker dengan autocomplete dan logo. Hasil tetap
   bebas dieksplorasi melalui pagination; bukan hanya beberapa contoh perusahaan.
2. Pilih perusahaan. Identitas, periode data, dan Ringkasan Riset menjadi fokus;
   detail profil dan grafik yang sudah ada tetap dapat dibuka.
3. Buka suatu temuan untuk melihat angka sumber, grafik yang relevan, perhitungan,
   bukti pembanding, dan keterbatasannya.
4. Lihat hal yang masih perlu diperiksa. Jika membutuhkan data tambahan,
   tampilkan kebutuhan/estimasi credit sebelum tindakan, bukan fetch diam-diam.
5. Bila perlu, tambah perusahaan ke perbandingan, maksimal tiga. Bandingkan
   alasan dan keterbatasan, bukan hanya tabel metrik atau urutan nilai.
6. Setelah persistence tersedia, simpan perbandingan manual. Pembaruan membuat
   versi baru; versi lama tetap mempertahankan bukti dan konteks perhitungannya.

Ringkasan ditampilkan lebih dahulu; tahapan terpandu boleh dibuka sesuai
kebutuhan tanpa memaksa pengguna melewati banyak layar. Ini usulan perubahan
wizard lama yang perlu persetujuan. URL dan akses fitur existing dipertahankan
sampai ada audit consumer serta persetujuan perubahan navigasi.

### UX-1: satu pintu pencarian

Masukan user 2026-09-26: `/temukan-saham` dan `/perusahaan` terasa sama dan
membingungkan. Inspeksi [routes](../../../routes/web.php) mengonfirmasi keduanya
memanggil `CompanySearchController::index`, merender `nusalens/discover`, dan
menggunakan pencarian/dataset yang sama. Ini bukan dua fungsi yang berbeda.
Temuan awal bersumber dari kode. Setelah persetujuan, implementasi diverifikasi
melalui feature test dan browser terisolasi; hasil ada pada [tasks](tasks.md).

| Konteks | Perilaku terimplementasi |
| --- | --- |
| Sidebar | Satu menu **Temukan Saham** menuju `/temukan-saham`; menu **Perusahaan** dihapus dari sidebar. |
| Dashboard | Pertahankan satu shortcut pencarian. Shortcut **Detail Perusahaan** yang kini menuju daftar `/perusahaan` tidak diduplikasi. |
| URL `/perusahaan` | Tetap tersedia sebagai redirect 302 ke `/temukan-saham`; pertahankan keyword/page/limit yang valid. |
| Detail `/perusahaan/{symbol}` | Tetap halaman detail/ringkasan perusahaan yang dipilih. URL dan nama route tidak berubah. |
| Kembali ke hasil | Pertahankan filter dan halaman asal; fallback menuju `/temukan-saham`, bukan direktori duplikat. |
| Status menu aktif | Temukan Saham tetap aktif pada hasil dengan query URL dan detail perusahaan, termasuk saat sidebar diciutkan/drawer mobile. |

Alur: **Temukan Saham -> pilih perusahaan -> Ringkasan Riset ->
buka bukti/detail bila dibutuhkan**. Pengguna tidak harus memilih antara dua
menu dengan isi yang sama. Nama module Company tetap; menu tidak harus mencerminkan
setiap module backend.

Nama route `discover`, `companies`, dan `companies.show` tetap dipertahankan.
Redirect tidak mengambil data provider atau membuat reservasi credit; halaman
tujuan menjalankan query normal satu kali. Login, verifikasi, validasi query,
dan throttle tetap berlaku. Prefetch navigasi tidak boleh memicu pengambilan
provider berbiaya sebelum tindakan pengguna.

Scope ini tidak menghapus `/jelaskan-nilai`, merombak seluruh sidebar, atau
mengubah compare/kandidat. Temuan kebingungan lain dicatat untuk review terpisah.
Acceptance: satu pintu pencarian terlihat, bookmark lama tetap berfungsi,
detail tetap bisa dibuka, dan kembali ke daftar tidak kehilangan konteks.

### Isi Ringkasan Riset

| Bagian | Isi | Batas |
| --- | --- | --- |
| Temuan | Pernyataan singkat yang dapat dibuktikan, angka, satuan, periode | Tidak menyimpulkan murah/aman hanya dari satu rasio. |
| Bukti pembanding | Fakta lain yang menguatkan atau membatasi pembacaan awal | Jika tidak ada, sebut belum tersedia; jangan mengarang bantahan. |
| Keterbatasan | Data hilang, usia sumber, periode/basis tidak sebanding | Tidak mengganti nilai hilang menjadi nol. |
| Pemeriksaan berikutnya | Pertanyaan spesifik yang belum terjawab dan data yang diperlukan | Bukan instruksi membeli/menjual atau rekomendasi emiten. |
| Nilai Prioritas Riset | Hasil scoring v1 dan kelengkapan jika syarat terpenuhi | Boleh tidak tersedia; tidak wajib untuk increment pertama. |

Contoh semantik, bukan angka atau kesimpulan tentang emiten tertentu:
"Laba positif, kas operasi negatif pada periode yang sama" dapat menjadi
temuan nonbank nonkeuangan jika basis sumber cocok. Keterbatasannya: perbedaan
itu sendiri tidak membuktikan manipulasi laba atau penyebab tertentu.
Pemeriksaan berikutnya dapat berupa komponen modal kerja, tanpa mengklaim
komponen tersebut telah tersedia atau menjadi penyebab.

Untuk bank, jangan menerapkan interpretasi arus kas/utang perusahaan industri.
Mulai dari fakta pendapatan bunga, laba, kredit, atau simpanan yang benar-benar
tersedia. Penilaian risiko bank memerlukan CAR/NPL sesuai scoring v1. Perusahaan
keuangan nonbank tidak otomatis menerima paket risiko bank maupun industri.

### Kontrak bukti konseptual

Setiap temuan harus dapat ditelusuri ke:

- Identitas perusahaan, ID/versi aturan, input mentah dan hasil perhitungan.
- Field, endpoint/section sumber tanpa credential, mata uang, dan satuan.
- Periode laporan, basis quarterly/YTD/annual/TTM atau tanggal observasi pasar.
- Waktu pengambilan, freshness, dan alasan unavailable bila diperlukan.
- Rumus, syarat penerapan bank/nonbank, serta keterbatasan interpretasi.
- Definisi, jumlah, dan bukti peer bila temuan memakai perbandingan peer.

Ini kebutuhan informasi, bukan keputusan membuat tabel atau abstraksi baru.
Versi aturan penjelasan terpisah dari versi formula scoring. Format dua desimal
hanya pada tampilan; perhitungan memakai presisi sumber.

### Aturan analisis

- Verifikasi basis quarterly vs YTD, mata uang, satuan, dan periode sebelum
  membandingkan revenue, earnings, atau cash flow. Nama field saja tidak cukup.
- Empat kuartal tidak cukup untuk YoY kuartal terbaru. Diperlukan kuartal yang
  sama tahun sebelumnya, bukan mengganti YoY dengan QoQ tanpa penjelasan.
- Persentase perubahan tidak dihitung dengan pembagi nol; perubahan dari
  nilai negatif perlu konteks nilai absolut, bukan label pertumbuhan menyesatkan.
- P/E historis tahunan bukan otomatis P/E TTM. Harga harian belum boleh menjadi
  input momentum sebelum konsistensi basis corporate action diverifikasi.
- Scoring v1 tetap memakai bobot 30/25/20/15/10, minimal lima peer lain per
  metrik, seluruh peer valid dalam kelompok, dan kelengkapan berbobot 70%.
  Jangan mengecilkan peer secara diam-diam agar lolos quota.
- Bank dan nonbank tidak dicampur dalam peer. Compare lintas jenis dapat
  menampilkan fakta masing-masing tanpa ranking atau skor bersama yang tidak sah.
- Tidak ada label tinggi/sedang/rendah untuk skor; kelengkapan bukan probabilitas
  kebenaran atau keberhasilan investasi.
- Versi lama vs baru hanya dibandingkan langsung bila periodisasi dan basis
  cocok. Pisahkan perubahan input, peer, formula, dan aturan penjelasan dari
  perubahan kinerja bisnis; tidak semua perubahan nilai berarti bisnis berubah.
- Jika kepemilikan dikaji kelak, hilang dari dataset berambang 1% tidak berarti
  menjual seluruh saham. Dataset parsial juga tidak membuktikan seluruh beneficial
  ownership. Fitur tersebut belum termasuk increment sekarang.

### UX dan visualisasi

- Satu aksi utama per konteks, bukti rinci dapat dibuka bertahap. Tidak membuat
  menu baru untuk setiap indikator atau menambah hero/landing page.
- Grafik menjawab pertanyaan: tren metrik dengan periode jelas, pasangan
  laba/kas yang sebanding, distribusi peer, atau perubahan versi. Tidak semua
  grafik ditampilkan sekaligus dan tidak mencampur unit/skala tanpa penjelasan.
- Recharts memakai tooltip angka/satuan/periode, legend, dukungan layar kecil,
  dan alternatif tabel yang dapat diakses. Sumber, missing, loading, dan error
  dibedakan; garis tidak mengisi data yang hilang dengan nol.
- Tabel mengikuti pola ContohUI yang berlaku. Gunakan ikon/tooltip familiar,
  label status netral, fokus keyboard, dan informasi tidak hanya berbasis warna.
- Bantuan menjelaskan arti data, bukan teks promosi fitur atau petunjuk teknis
  tentang framework. Detail teknis sumber tersedia pada bukti bila dibutuhkan.

### Data, credit, dan keamanan

Seluruh data produksi melewati adapter MarketData dan ledger existing. Cache
dibaca ulang tanpa memperbarui usia sumber. Menghitung ringkasan dari input yang
sama tidak membutuhkan credit tambahan. API key tetap hanya di backend.

Estimasi berdasarkan reservasi adapter saat ini, bukan jaminan tarif provider:
overview cold sekitar 1 credit + empat kuartal 4 credit = sekitar 5 credit per
perusahaan. Cache valid dapat menurunkan biaya; harga/valuasi masing-masing
sekitar 1 tambahan hanya bila dibutuhkan. Pencarian, cache miss lain, dan peer
belum tercakup. Query tambahan wajib dinilai ulang sebelum smoke berbayar.

Budget 1.000 sekali pakai, reserve 600 perlu persetujuan, quota 20/account/hari
WIB, TTL/fallback, dan ketentuan retry tetap mengikuti baseline. Peer lengkap
dapat melebihi quota: tampilkan keterbatasan dan hentikan fetch, jangan bypass
quota atau mengganti kekurangan dengan fake. Automated test default fake HTTP;
smoke real terarah melalui adapter memerlukan persetujuan scope/estimasi.

## Acceptance criteria

- [x] Pengguna dapat mencari dengan nama tanpa mengetahui ticker.
- [x] Setiap temuan dapat dibuka sampai nilai, periode, sumber, dan rumusnya.
- [x] Input dan versi aturan yang sama memberikan hasil yang sama tanpa AI.
- [x] Missing, stale, basis tidak cocok, dan error provider memiliki status berbeda.
- [x] Interpretasi nonkeuangan tidak diterapkan pada bank/keuangan nonbank.
- [x] Ringkasan menyajikan fakta walau nilai prioritas belum dapat dihitung.
- [x] Tidak ada angka fake pada jalur produksi yang telah dimigrasikan.
- [ ] Pengguna uji dapat menyebut satu temuan, satu keterbatasan, dan pemeriksaan
  berikutnya tanpa bantuan pengembang. Target awal lima menit adalah hipotesis
  usability, belum hasil pengujian.

## Pertanyaan terbuka

Prioritas 3A telah dilanjutkan atas instruksi user. Hal terbuka adalah hasil uji
pemahaman pengguna, kontrak/biaya peer untuk scoring, serta schema versi manual.
Tahap berikutnya ditinjau pada gate masing-masing, bukan otomatis dikerjakan.
