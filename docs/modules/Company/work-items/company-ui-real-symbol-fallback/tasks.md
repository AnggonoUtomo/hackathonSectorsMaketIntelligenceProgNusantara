# Tasks: Company UI dan fallback ticker real

## Sebelum mulai

- [x] Scope dan non-scope jelas.
- [x] Dependency/keputusan terbuka diketahui.
- [x] Acceptance dan cara verifikasi ditetapkan.

## Pekerjaan

- [x] Tambahkan daftar ringkas dan fallback detail di Company snapshot.
- [x] Kirim payload `companies` ke halaman `/perusahaan`.
- [x] Buat UI table Company mengikuti pola `ContohUI`.
- [x] Sesuaikan test untuk fallback ticker real.

## Hasil

- [x] Scope selesai dan dokumentasi diperbarui.
- Perubahan: `/perusahaan` memakai tabel dashboard, dan detail ticker real yang
  belum punya snapshot menampilkan status pending.
- Verifikasi: focused route tests, `npm run typecheck`, `npm run lint:check`, dan
  `npm run build` lulus.
- Risiko: detail real masih di luar scope.

Jangan menambah scope baru ke checklist tanpa instruksi user.
