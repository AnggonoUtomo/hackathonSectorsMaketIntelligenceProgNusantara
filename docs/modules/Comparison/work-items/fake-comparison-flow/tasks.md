# Tasks: Fake Comparison Flow

## Sebelum mulai

- [x] Scope dan non-scope jelas.
- [x] Dependency/keputusan terbuka diketahui.
- [x] Acceptance dan cara verifikasi ditetapkan.

## Pekerjaan

- [x] Tulis dokumentasi module/work item sebelum coding.
- [x] Buat backend fake comparison payload.
- [x] Tambahkan validasi query `symbols`.
- [x] Tambahkan test empty/success/over-limit/unknown.
- [x] Update UI Bandingkan menjadi matrix.
- [x] Tambahkan link dari Detail Perusahaan ke Bandingkan.

## Hasil

- [ ] Scope selesai dan dokumentasi diperbarui.
- Perubahan: backend fake comparison payload, validasi query, route props, UI
  matrix, dan link dari detail selesai.
- Verifikasi: focused PHPUnit, full PHPUnit, typecheck, lint, build,
  whitespace, dan sensitive-data scan lulus.
- Risiko: persistence privat dan versioning snapshot di luar scope.
