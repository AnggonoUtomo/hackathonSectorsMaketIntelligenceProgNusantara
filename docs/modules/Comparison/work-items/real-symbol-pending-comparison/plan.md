# Plan: Pending comparison untuk ticker real

## Scope

Membuat `/bandingkan` mulus untuk ticker valid dari Discover/Company walaupun
data metrik real belum tersedia.

## Increment 1: Backend payload

- Perubahan: builder membuat row pending untuk ticker valid yang tidak ada di
  dataset fake.
- Prasyarat: validasi symbol lama.
- Acceptance: `ADES,AADI` menghasilkan state `ready`.
- Verifikasi: unit test builder dan feature test route.

## Increment 2: UI matrix

- Perubahan: matrix Comparison memakai summary/shortcut/table pattern dan tetap
  menerima pending values.
- Prasyarat: payload comparison lama.
- Acceptance: typecheck dan build lulus.
- Verifikasi: `npm run typecheck`, `npm run build`.

## Batas berhenti dan pemulihan

Berhenti setelah flow compare ticker real tidak error. Integrasi scoring real
dibuat sebagai work item terpisah.
