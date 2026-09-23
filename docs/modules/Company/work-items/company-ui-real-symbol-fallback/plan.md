# Plan: Company UI dan fallback ticker real

## Scope

Merapikan halaman Company agar siap menerima ticker dari Discover real tanpa
404, sembari mengikuti pola UI tabel yang sudah dipakai di `/temukan-saham`.

## Increment 1: Payload backend

- Perubahan: `FakeCompanySnapshot` menyediakan daftar ringkas dan fallback detail
  untuk ticker valid yang belum ada snapshot.
- Prasyarat: route Company saat ini.
- Acceptance: `/perusahaan/{symbol}` tidak 404 untuk ticker uppercase alphanumeric.
- Verifikasi: focused feature test.

## Increment 2: UI frontend

- Perubahan: halaman `/perusahaan` memakai summary, shortcut, search/filter lokal,
  tabel, empty state, dan aksi detail/bandingkan.
- Prasyarat: payload list perusahaan.
- Acceptance: typecheck dan build lulus.
- Verifikasi: `npm run typecheck`, `npm run build`.

## Batas berhenti dan pemulihan

Berhenti setelah `/perusahaan` dan detail fallback aman. Jika integrasi real detail
dibutuhkan, buat work item terpisah karena memakai endpoint dan credit baru.
