# Work Item: Ruang Riset Berbukti

## Status dan owner

- Status: implementasi 3A dan UX-1 selesai; dokumentasi kesiapan 3B tersedia.
- Tanggal: kajian 2026-09-25; handoff implementasi 2026-09-26.
- Owner: lintas module Research, Company, MarketData, Intelligence, Comparison,
  Screening, dan frontend.
- Target tahap ini: Ringkasan Riset satu perusahaan sesuai rincian [plan](plan.md).

Instruksi lanjut user pada 2026-09-25 mengaktifkan increment 3A setelah kajian
login Sectors dihentikan. Bagian kajian di bawah adalah catatan tahap sebelumnya;
increment 3B-5 belum diotorisasi. Tidak ada commit/push otomatis.

Instruksi 2026-09-26 mengaktifkan dokumentasi tahap berikutnya, bukan coding.
User menemukan dua pintu pencarian yang membingungkan. Inspeksi source mengonfirmasi
`/temukan-saham` dan `/perusahaan` memakai controller/halaman yang sama, tetapi
ditampilkan sebagai dua menu dan dua shortcut Dashboard. **UX-1: satu pintu
pencarian** pada [PRD](prd.md) dan [plan](plan.md) kemudian disetujui user dan
diimplementasikan pada tanggal yang sama. Sidebar/shortcut duplikat dihapus,
alias `/perusahaan` mengalihkan ke `/temukan-saham`, detail tetap dipertahankan.
Rincian kesiapan 3B ada di [work item peer/scoring](../kesiapan-peer-scoring/README.md).
Urutan berikutnya: evaluasi navigasi oleh user, lalu review gate audit kelayakan 3B.

## Kondisi awal

User meminta NusaLens lebih matang dan tidak sekadar menduplikasi tool Sectors.
Arah diferensiasi disetujui secara umum; detail alur UX baru belum disetujui.
Pekerjaan ini melanjutkan kajian yang terinterup, setelah increment 2
[redesain riset terpandu](../redesain-riset-terpandu/README.md) selesai.

### Bukti produk referensi

Observasi melalui browser pada halaman publik/guest. Screener diperiksa pada
2026-09-24; empat halaman lainnya diperiksa pada 2026-09-25. Ini bukan audit
fitur penuh akun berbayar. Klaim pembeda di bawah adalah hipotesis produk,
bukan bukti bahwa Sectors tidak memiliki kemampuan tersebut.

| Sumber | Yang terlihat atau dicoba | Batas verifikasi |
| --- | --- | --- |
| [Screener](https://sectors.app/screener) | Template gaya investasi, grup AND/OR, pembanding field/perhitungan, sort, limit, dan pilihan 218 variabel. Memilih Undervalued Banks mengubah form bank, P/E, dan dividend yield. | Tidak menekan Run Screener atau menjalankan save/export/workflow. |
| [Peers](https://sectors.app/peers) | Preview tiga bank, label maksimal lima perusahaan dan lima metrik, grafik antarperiode, kontrol Annual/Quarterly. Tab Net Income berhasil mengganti panel. | Batas adalah label preview, bukan jaminan semua paket. Data Quarterly dan ekspor belum diverifikasi. |
| [Company Ownership](https://sectors.app/indonesia/company-ownership) | Pencarian perusahaan/investor, komposisi kepemilikan, periode, tab Government Ownership dan Monthly Movement. Membuka Monthly Movement mengganti panel dan query URL; tersedia pembanding bulan, tampilan Company/Investor, dan filter jenis perubahan. | Limited Preview; sorting/filter penuh memerlukan akun. Tidak memvalidasi kelengkapan data atau semua kontrol. |
| [Chat](https://sectors.app/chat) | Contoh pertanyaan kesehatan perusahaan, perbandingan sektor, dan pasar. Modal About Data Visualization menjelaskan grafik otomatis dari hasil analisis. | Input guest nonaktif; tidak menguji jawaban, akurasi, sumber, atau grafik yang benar-benar dihasilkan. |
| [Analytics](https://sectors.app/indonesia/analytics) | Katalog heatmap/performa sektor, indeks, ranking perusahaan, kalender, saham paling aktif, dan pencarian. | Katalog dan preview terlihat; perhitungan masing-masing tool tidak diuji. |

Kesimpulan: filter, grafik, compare, kepemilikan, dan AI chat saja bukan pembeda.
Jangan menyebut Sectors hanya penyedia data: produk tersebut sudah menawarkan
analisis. NusaLens perlu membuktikan manfaat melalui alur riset yang lebih mudah
dipahami, konsisten, dan dapat ditelusuri.

### Bukti implementasi lokal

Pemeriksaan source pada 2026-09-25, bukan status historis blueprint:

- Autocomplete, direktori, profil, grafik harga/keuangan, dan valuasi real sudah
  tersedia; commit terakhir `4a1571c` mempertahankan fondasi ini.
- `SectorsCompanyAnalytics` mengambil empat kuartal, harga sekitar 90 hari,
  dan section valuasi sesuai kebutuhan. Empat kuartal saja tidak cukup untuk
  menghitung pertumbuhan YoY kuartal terbaru.
- Valuasi yang dipetakan saat ini adalah seri historis tahunan; belum menjadi
  bukti tersedianya P/E TTM dan P/B MRQ yang dipersyaratkan scoring v1.
- `app/Modules/Intelligence/` belum ada. Mesin nilai dan peer bukan sekadar
  fitur yang tinggal dihubungkan ke UI.
- `Comparison/Application/FakeComparisonBuilder.php` dan
  `Research/Application/RuleBasedResearchExplainer.php` masih memakai alur fake.
  Alur research legacy juga bergantung pada snapshot fake konkret Company.
- Migration penyimpanan perbandingan/versi belum tersedia. Persistence itu
  masih pekerjaan implementasi, bukan fitur siap pakai.

## Scope dan non-scope tahap implementasi

Scope selesai: Ringkasan Riset satu perusahaan, temuan deterministik laba,
margin nonkeuangan bila syarat terpenuhi, ekuitas, panel bukti, grafik/tabel,
keterbatasan dan pemeriksaan berikutnya. Data real memakai port/cache/ledger
existing. Tab ringkasan menjadi default di detail; laporan dimuat atas tindakan
eksplisit dengan estimasi hingga 5 credit cold, bukan otomatis saat membuka tab.

`/jelaskan-nilai` menjadi pencarian real; query symbol valid mengarah ke detail.
Nama route dan URL dipertahankan. Research runtime tidak memakai snapshot fake.
Class legacy belum dihapus; compare/kandidat legacy belum dimigrasikan.

Tidak ada perubahan formula scoring, schema, auth, dependency, smoke API
berbayar, commit, atau push. Tidak membuat notebook, watchlist, rekomendasi
transaksi, ownership explorer, chat umum, atau laporan tersimpan jenis baru.

## Acceptance criteria tahap kajian

- [x] Lima tool referensi dipetakan beserta batas verifikasinya.
- [x] Pembeda dinyatakan sebagai manfaat yang perlu diuji, bukan klaim eksklusif.
- [x] Fondasi real yang dapat dipakai ulang dan gap source dipisahkan.
- [x] Rancangan mencakup perilaku UX, bukti, biaya, dan data tidak tersedia.
- [x] Increment memiliki prasyarat, acceptance, verifikasi, dan batas berhenti.
- [x] Instruksi lanjut user mengaktifkan 3A; increment berikutnya tetap menunggu.

## Dependency dan keputusan

[DECISIONS](../../DECISIONS.md), [SCORING](../../SCORING.md),
[DATA-FLOW](../../DATA-FLOW.md), dan arsitektur aktif tetap berlaku.
Formula v1 dan batas MVP tidak berubah. Increment 3A mengikuti rincian plan
ini; 3B-5 tetap membutuhkan review gate masing-masing sebelum coding.

## Handoff

- Hasil: implementasi 3A, dokumentasi, dan test tersedia. Coba melalui
  `/jelaskan-nilai` atau `/perusahaan/{symbol}`, lalu Buka ringkasan riset.
- Verifikasi: 138 test / 598 assertion lulus, typecheck, lint file terkait,
  Pint, build, dan pemeriksaan diff. Bukti browser dicatat pada [tasks](tasks.md).
- Risiko: YoY, hubungan laba-kas, scoring/peer, compare real, dan persistence
  belum termasuk hasil tahap ini. Manfaat UX perlu dicoba user.
- Warning sidebar mobile existing: DialogContent belum memiliki description;
  tidak diubah karena di luar scope. Tidak ada perubahan database user.
- File user `Hackaton/` dan file LSP tidak diubah.
