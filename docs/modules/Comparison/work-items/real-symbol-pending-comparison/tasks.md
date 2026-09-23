# Tasks: Pending comparison untuk ticker real

## Sebelum mulai

- [x] Scope dan non-scope jelas.
- [x] Dependency/keputusan terbuka diketahui.
- [x] Acceptance dan cara verifikasi ditetapkan.

## Pekerjaan

- [x] Ubah builder agar unknown valid symbol menjadi pending.
- [x] Jadikan `/bandingkan` tanpa query menampilkan default MVP.
- [x] Sesuaikan test builder dan route.
- [x] Buat UI Comparison dashboard/table.
- [x] Jalankan verifikasi backend dan frontend.

## Hasil

- [x] Scope selesai dan dokumentasi diperbarui.
- Perubahan: `/bandingkan` menerima ticker real valid sebagai pending matrix.
- Verifikasi: focused compare tests, `npm run typecheck`, `npm run lint:check`,
  dan `npm run build` lulus.
- Risiko: nilai real belum dihitung.

Jangan menambah scope baru ke checklist tanpa instruksi user.
