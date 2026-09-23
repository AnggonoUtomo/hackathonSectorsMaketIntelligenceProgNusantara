# Work Item: Discover UI mengikuti ContohUI

## Status dan owner

- Status: Done.
- Owner: Screening.
- Target: `resources/js/pages/nusalens/placeholder.tsx` dan style dashboard terkait.

## Kondisi awal

Halaman `/temukan-saham` masih memakai placeholder umum. User meminta UI dan
perilaku mengikuti folder `ContohUI`, khususnya pola halaman daftar yang padat,
memiliki ringkasan, shortcut, filter, tabel, empty state, dan pagination.

## Scope dan non-scope

Scope pekerjaan ini adalah tampilan dan perilaku frontend `/temukan-saham`.
Backend real/fake yang sudah ada tetap dipakai tanpa mengubah kontrak data.

Non-scope: scoring real, data detail perusahaan real, perubahan provider Sectors,
dan halaman NusaLens lain.

## Acceptance criteria

- [x] `/temukan-saham` memakai pola UI seperti `ContohUI`: summary cards, shortcut
      bar, filter/search area, tabel padat, status data, dan pagination.
- [x] Filter tetap mengirim query Inertia ke endpoint yang sama.
- [x] Empty state, loading state, reset filter, dan keyboard shortcut dasar
      tersedia.
- [x] Build dan typecheck frontend lulus.

## Dependency dan keputusan

Keputusan user tanggal 2026-09-23: contoh terbaru berada pada folder `ContohUI`.
Mode provider real/fake tetap dikendalikan oleh konfigurasi backend.

## Handoff

- Perubahan: halaman `/temukan-saham` memakai pola UI daftar dari `ContohUI`.
- Verifikasi: `npm run typecheck`, `npm run lint:check`, `npm run build`, dan
  focused feature test Discover lulus.
- Risiko terbuka: validasi browser manual tetap disarankan untuk rasa visual final.
