# Tasks: Comparison Fake Flow

## Sebelum mulai

- [x] Scope dan non-scope jelas.
- [x] Dependency/keputusan terbuka diketahui.
- [x] Acceptance dan cara verifikasi ditetapkan.

## Dokumentasi

- [x] Buat README module Comparison.
- [x] Buat specification module Comparison untuk fake flow.
- [x] Buat plan increment.
- [x] Buat work item `fake-comparison-flow`.

## Increment 2: Backend fake comparison payload

- [ ] Buat `FakeComparisonBuilder` atau nama setara di module Comparison.
- [ ] Validasi `symbols`: opsional, alfanumerik, maksimal 3, unknown ditolak.
- [ ] Route `/bandingkan` mengirim props `comparison`.
- [ ] Unit dan feature test backend fake comparison.

## Increment 3: UI matrix dan link detail

- [ ] Halaman Bandingkan menampilkan empty state saat belum ada symbol.
- [ ] Halaman Bandingkan menampilkan matrix side-by-side dari backend props.
- [ ] Detail perusahaan memiliki link ke `/bandingkan?symbols={symbol}`.
- [ ] Typecheck, lint, build, full test, whitespace, dan secret scan lulus.

## Hasil

- [ ] Scope selesai dan dokumentasi diperbarui.
- Perubahan: belum dikerjakan.
- Verifikasi: belum dijalankan.
- Risiko: persistence perbandingan privat dan versioning snapshot belum masuk
  fake flow.
