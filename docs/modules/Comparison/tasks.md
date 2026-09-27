# Tasks: Comparison Flow

## Sebelum mulai

- [x] Scope dan non-scope jelas.
- [x] Dependency/keputusan terbuka diketahui.
- [x] Acceptance dan cara verifikasi ditetapkan.

## Dokumentasi

- [x] Buat README module Comparison.
- [x] Buat specification module Comparison untuk flow awal.
- [x] Buat plan increment.
- [x] Buat work item `fake-comparison-flow`.

## Increment 2: Backend fake comparison payload

- [x] Buat `FakeComparisonBuilder` atau nama setara di module Comparison.
- [x] Validasi `symbols`: opsional, alfanumerik, maksimal 3, unknown ditolak.
- [x] Route `/bandingkan` mengirim props `comparison`.
- [x] Unit dan feature test backend fake comparison.

## Increment 3: UI matrix dan link detail

- [x] Halaman Bandingkan menampilkan empty state saat belum ada symbol.
- [x] Halaman Bandingkan menampilkan matrix side-by-side dari backend props.
- [x] Detail perusahaan memiliki link ke `/bandingkan?symbols={symbol}`.
- [x] Typecheck, lint, build, full test, whitespace, dan sensitive-data scan lulus.

## Increment 4: Selection-first cleanup

- [x] Ganti fake comparison payload menjadi `ComparisonSelectionBuilder`.
- [x] Halaman Bandingkan mulai kosong tanpa default `BBCA, TLKM, ICBP`.
- [x] Gunakan autocomplete perusahaan dan chip pilihan maksimal 3 saham.
- [x] Hapus matrix/metrik fake dari payload compare.
- [x] Unit dan feature test mengunci state `empty|selected`.

## Hasil

- [x] Scope awal selesai dan dokumentasi diperbarui.
- Perubahan awal: backend fake comparison, validasi query, UI matrix, dan link
  dari Detail Perusahaan ke Bandingkan selesai.
- Perubahan terbaru: compare dibersihkan menjadi selection-first tanpa data fake,
  dengan autocomplete dan payload kosong untuk increment real berikutnya.
- Verifikasi: focused PHPUnit, full PHPUnit, typecheck, lint, build,
  whitespace, dan sensitive-data scan lulus.
- Risiko: matriks real, persistence perbandingan privat, dan versioning snapshot
  belum masuk flow ini.
