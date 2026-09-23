# Plan: Discover UI mengikuti ContohUI

## Scope

Adaptasi halaman `/temukan-saham` agar memakai pola dashboard operasional dari
`ContohUI`, tanpa mengganti alur backend Screening/MarketData.

## Increment 1: Struktur UI

- Perubahan: pecah render Discover menjadi layout ringkasan, shortcut, filter,
  tabel, empty state, dan pagination.
- Prasyarat: payload `discover` yang sudah dikirim route.
- Acceptance: halaman tetap menerima hasil backend dan menampilkan daftar kandidat.
- Verifikasi: `npm run typecheck`.

## Increment 2: Perilaku

- Perubahan: live search debounce, reset filter, filter sector/limit, pagination
  lokal untuk hasil saat ini, shortcut `/`, `Esc`, dan `Enter`.
- Prasyarat: Inertia router.
- Acceptance: interaksi dasar tidak error dan tetap mengirim query ke
  `/temukan-saham`.
- Verifikasi: `npm run typecheck` dan focused feature test bila route berubah.

## Increment 3: Style pendukung

- Perubahan: tambahkan class dashboard yang dipakai contoh UI ke CSS utama.
- Prasyarat: Tailwind token project.
- Acceptance: class terpakai saat build dan tidak mendominasi satu warna.
- Verifikasi: `npm run build`.

## Batas berhenti dan pemulihan

Scope selesai ketika `/temukan-saham` mengikuti pola contoh dan verifikasi frontend
lulus. Jika build gagal, rollback perubahan UI/style terakhir dan laporkan error.
