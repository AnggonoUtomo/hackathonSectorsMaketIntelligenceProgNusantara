# Plan: Fake Comparison Flow

## Scope

Membuat Bandingkan dapat dicoba end-to-end dengan data fake internal, sebagai
lanjutan dari Discover dan Detail Perusahaan fake flow.

## Increment 1: Dokumentasi

- Perubahan: module docs dan work item.
- Prasyarat: tidak ada.
- Acceptance: scope, non-scope, acceptance, dan task increment tertulis.
- Verifikasi: `git diff --check`.

## Increment 2: Backend fake comparison

- Perubahan: class Application untuk payload fake, route `/bandingkan`, dan test.
- Prasyarat: halaman Bandingkan tetap auth + verified.
- Acceptance: empty, success, unknown, dan over-limit teruji.
- Verifikasi: focused PHPUnit.

## Increment 3: UI matrix

- Perubahan: props `comparison` di placeholder page, matrix UI, dan link dari
  detail perusahaan.
- Prasyarat: backend fake comparison hijau.
- Acceptance: typecheck, lint, build, dan feature test lulus.
- Verifikasi: full relevant checks.

## Batas berhenti dan pemulihan

Berhenti setelah fake flow berjalan dan commit. Jangan membuat persistence,
snapshot versioning, atau integrasi provider pada work item ini.
