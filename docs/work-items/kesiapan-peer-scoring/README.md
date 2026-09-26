# Work Item: Kesiapan Peer dan Scoring

## Status dan tujuan

- Status: dokumentasi dan inventaris source lokal; implementasi belum disetujui.
- Tanggal: 2026-09-26.
- Owner: Intelligence, Company, MarketData, Research, dan frontend.
- Induk: [Ruang Riset Berbukti](../ruang-riset-berbukti/README.md), increment 3B.

Menentukan apakah data, populasi peer, biaya, dan bukti cukup untuk menjalankan
Nilai Prioritas Riset v1. Persetujuan membuat dokumentasi bukan persetujuan
membuat module, migration, atau menjalankan panggilan Sectors berbayar.

Ringkasan Riset 3A sudah tersedia tanpa skor. Penyederhanaan dua pintu pencarian
ditangani terpisah melalui [increment UX-1](../ruang-riset-berbukti/plan.md).
Perbaikan navigasi tidak perlu menunggu kesiapan scoring.

## Scope dan non-scope

- Inventaris input yang sudah dipetakan, gap validasi, serta gate data/credit.
- Rencana ownership, bukti immutable, increment end-to-end, dan pengujian.
- Tidak mengubah formula, bobot, batas peer, quota, atau aturan cache.
- Tidak membuat screener/peer explorer baru, AI chat, atau rekomendasi transaksi.
- Tidak memigrasikan compare, menyimpan perbandingan, atau mengubah navigasi
  pada pekerjaan dokumentasi ini.

## Inventaris source lokal

Hasil pembacaan kode pada 2026-09-26, bukan audit response live atau entitlement.

| Kebutuhan | Fondasi existing | Gap sebelum scoring |
| --- | --- | --- |
| Identitas dan kelompok peer | Direktori/overview real; klasifikasi sektor, subsektor, industry | Subindustry belum dipetakan; hierarki dan kompatibilitas bisnis perlu dibuktikan. Daftar hasil pencarian bukan populasi peer. |
| Quality 30% | Quarterly memetakan aset, ekuitas, dan laba | ROE/ROA tahunan, denominator positif, periode dan definisi rasio belum menjadi input scoring terverifikasi. |
| Growth 25% | Empat kuartal revenue/earnings | YoY kuartal terbaru memerlukan kuartal yang sama tahun lalu; empat kuartal tidak cukup. Basis/unit harus sama. |
| Value 20% | Section valuation dipetakan sebagai seri historis tahunan | Bukan bukti P/E TTM dan P/B MRQ dengan tanggal harga/denominator yang selaras. |
| Market Strength 15% | Daily sekitar 90 hari tersedia | Verifikasi 21 penutupan positif, tanggal pembanding yang sama, dan konsistensi aksi korporasi; bukan 20 hari kalender. |
| Risk bank 10% | Sebagian field keuangan bank tersedia | CAR dan nominal NPL/kredit bruto tahunan beserta definisi/unit belum dipetakan untuk scoring. Allowance bukan NPL. |
| Risk nonkeuangan 10% | Debt/equity tersedia pada quarterly | DER tahunan harus utang berbunga, bukan total liabilitas; current ratio tahunan memerlukan aset/liabilitas lancar valid. |
| Keuangan nonbank | Tetap dapat dibuka pada detail/ringkasan | Risk v1 tidak tersedia; tetap mengurangi kelengkapan 10 poin, bukan memakai aturan bank/industri. |
| Kalkulasi dan bukti | Contract CompanyResearchData dan ringkasan deterministik 3A | Module Intelligence belum ada. Perlu kalkulator, seleksi peer, contract hasil dan persistence bukti, bukan sekadar menghubungkan UI. |

Source utama:
[adapter analytics](../../../app/Modules/MarketData/Infrastructure/Sectors/SectorsCompanyAnalytics.php),
[contract data riset](../../../app/Modules/Company/Application/Contracts/CompanyResearchData.php).
Nama field yang mirip tidak membuktikan kesetaraan periode, unit, atau definisi.

## Aturan yang tidak dinegosiasikan di increment ini

[SCORING](../../SCORING.md) tetap sumber rumus tunggal:

- Minimal lima peer lain valid per metrik; target ikut populasi percentile.
  Gunakan seluruh peer valid dalam kelompok terpilih, bukan lima pertama.
- Hierarki Subindustry -> Industry -> Subsector -> Sector, dengan kompatibilitas
  bisnis/basis. Jangan campur bank/nonbank atau memakai pilihan compare sebagai peer.
- Coba periode terbaru pada seluruh kelompok relevan sebelum mundur maksimal
  satu periode laporan. Fallback periode bukan izin memakai cache kedaluwarsa.
- Ties memakai average rank; metrik hilang bukan nol. Kelengkapan dihitung
  sebelum normalisasi bobot, total hanya tersedia pada kelengkapan >=70%.
- Ambang dan kalkulasi memakai presisi sebelum pembulatan; tampilan dua desimal.
  Tidak ada label kategori skor atau confidence investasi.
- Populasi yang baru diambil sebagian karena budget tidak boleh dianggap lengkap.
  Ringkasan fakta 3A tetap bisa dipakai saat skor belum tersedia.

## Gate dan acceptance dokumentasi

- [x] Inventaris membedakan data existing, gap, dan asumsi yang belum diuji.
- [x] Scope audit, estimasi credit, ownership, dan increment ada pada [plan](plan.md).
- [x] Tugas implementasi dipisahkan dari tugas dokumentasi pada [tasks](tasks.md).
- [ ] Bukti field/periode/unit, entitlement, pagination dan kelengkapan peer lengkap.
- [ ] Estimasi cold/warm dan sisa budget terverifikasi; smoke terarah disetujui.
- [ ] Proposal contract/struktur Intelligence dan schema bukti mendapat persetujuan.

Tiga gate terakhir masih terbuka. Tidak menjanjikan seluruh scoring dapat
dipenuhi provider atau cukup dalam quota 20 credit per akun per hari.

## Referensi dan handoff

[Arsitektur](../../ARCHITECTURE.md), [struktur](../../FOLDER-STRUCTURE.md),
[model data](../../DATA-MODEL.md), [alur data](../../DATA-FLOW.md),
[keputusan](../../DECISIONS.md), dan [keamanan](../../SECURITY.md) tetap berlaku.
Hasil tahap ini hanya dokumen. Tidak ada API berbayar, perubahan data user,
dependency, kode, commit, atau push pada scope ini.
