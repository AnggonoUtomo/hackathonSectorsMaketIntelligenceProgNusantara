# Plan: Controlled Real Sectors Rollout

## Scope

Mengaktifkan jalur real Sectors secara terkendali untuk Temukan Saham saja,
tanpa membuat automated test mengonsumsi credit live.

## Increment 1: Dokumentasi dan konfigurasi mode

- Perubahan: work item dan config `MARKETDATA_PROVIDER_MODE`.
- Prasyarat: user menyetujui hybrid fake/test dan real/local.
- Acceptance: default tetap `fake`; `.env.example` punya mode provider.
- Verifikasi: config test ringan dan `git diff --check`.

## Increment 2: Backend real-mode Discover

- Perubahan: route Temukan Saham memilih fake atau real berdasarkan config;
  real-mode memanggil `StructuredCompanyScreener`.
- Prasyarat: tidak ada live request pada test.
- Acceptance: test real-mode memakai `Http::fake()`, ledger dibuat saat miss,
  props Inertia source `sectors_real`.
- Verifikasi: focused PHPUnit.

## Increment 3: UI/meta dan final gate

- Perubahan: UI menampilkan source/cache/credit dari backend tanpa asumsi fake.
- Prasyarat: backend real-mode hijau.
- Acceptance: typecheck, lint, build, full test, whitespace, dan sensitive-data
  scan lulus.
- Verifikasi: final gate sebelum commit.

## Batas berhenti dan pemulihan

Berhenti setelah Temukan Saham dapat berjalan mode fake dan real dengan test
fake HTTP. Jangan menjalankan live smoke test tanpa instruksi eksplisit dan
estimasi credit.
