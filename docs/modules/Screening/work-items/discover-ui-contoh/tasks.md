# Tasks: Discover UI mengikuti ContohUI

## Sebelum mulai

- [x] Scope dan non-scope jelas.
- [x] Dependency/keputusan terbuka diketahui.
- [x] Acceptance dan cara verifikasi ditetapkan.

## Pekerjaan

- [x] Baca pola halaman dan komponen dari `ContohUI`.
- [x] Adaptasi `/temukan-saham` ke layout dashboard padat.
- [x] Tambahkan perilaku search/filter/reset/shortcut/pagination.
- [x] Tambahkan style dashboard yang diperlukan.

## Hasil

- [x] Scope selesai dan dokumentasi diperbarui.
- Perubahan: `/temukan-saham` memakai summary cards, shortcut bar, filter bar,
  tabel, empty state, dan pagination ala `ContohUI`.
- Verifikasi: `npm run typecheck`, `npm run lint:check`, `npm run build`, dan
  `php artisan test tests/Feature/NusaLensNavigationTest.php --filter=discover`
  lulus.
- Risiko: belum ada browser screenshot di sesi ini.

Jangan menambah scope baru ke checklist tanpa instruksi user.
