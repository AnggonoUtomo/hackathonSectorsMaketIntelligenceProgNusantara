# Tasks: Controlled Real Sectors Rollout

## Sebelum mulai

- [x] Scope dan non-scope jelas.
- [x] Dependency/keputusan terbuka diketahui.
- [x] Acceptance dan cara verifikasi ditetapkan.

## Increment 1: Dokumentasi dan konfigurasi

- [x] Buat work item controlled real rollout.
- [x] Tambahkan `MARKETDATA_PROVIDER_MODE=fake` ke `.env.example`.
- [x] Tambahkan config provider mode dengan default `fake`.

## Increment 2: Backend real-mode Discover

- [x] Map filter UI ke `StructuredScreenerCriteria`.
- [x] Route Temukan Saham memilih fake/real berdasarkan config.
- [x] Real-mode mengirim props Inertia dengan source `sectors_real`.
- [x] Test real-mode memakai `Http::fake()` dan membuktikan ledger/cache.

## Increment 3: UI/meta dan final gate

- [x] Pastikan UI tidak mengasumsikan backend fake untuk discover.
- [x] Focused test, full test, typecheck, lint, build, whitespace, dan
  sensitive-data scan lulus.

## Hasil

- [x] Scope selesai dan dokumentasi diperbarui.
- Perubahan: Temukan Saham dapat memakai fake atau real-mode Sectors
  berdasarkan config.
- Verifikasi: focused PHPUnit, full PHPUnit, typecheck, lint, build,
  whitespace, dan sensitive-data scan lulus.
- Risiko: smoke live real Sectors belum dijalankan sampai user memberi instruksi
  eksplisit.
