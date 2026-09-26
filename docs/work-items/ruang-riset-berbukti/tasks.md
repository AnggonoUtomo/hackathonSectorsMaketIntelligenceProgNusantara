# Tasks: Ruang Riset Berbukti

## Sebelum mulai

- [x] Scope kajian dan non-scope implementasi jelas.
- [x] Baca dokumen aktif, template, serta source yang relevan.
- [x] Pisahkan keputusan accepted, kondisi source, dan usulan baru.
- [x] Acceptance dan cara verifikasi ditetapkan.

## Kajian dan dokumentasi

- [x] Catat screener dan empat tool Sectors dengan sumber dan batas observasi.
- [x] Uji tab metrik Peers, Monthly Movement, dan informasi visualisasi Chat.
- [x] Inventaris fondasi real serta gap Intelligence, fake legacy, dan persistence.
- [x] Rumuskan manfaat pembeda tanpa klaim eksklusif yang tidak terbukti.
- [x] Buat PRD dengan alur pengguna, bukti, visualisasi, dan non-scope.
- [x] Buat increment dengan ownership, endpoint, credit/cache, dan pengujian.
- [x] Tautkan indeks dan tandai review lanjutan pada work item sebelumnya.
- [x] Periksa tautan lokal, whitespace, dan scope diff dokumentasi.
- [x] User menginstruksikan lanjut proses berikutnya; scope aktif 3A, 2026-09-25.

## Implementasi setelah persetujuan

- [x] 3A: verifikasi basis/field sumber dan kebutuhan contract.
- [x] 3A: implementasikan aturan, ringkasan real, bukti, dan state kegagalan.
- [x] 3A: unit/feature, typecheck, lint, Pint, dan build.
- [x] 3A: tutup pemeriksaan browser desktop/mobile.
- [ ] 3A: uji pemahaman bersama pengguna; tidak digantikan oleh automated test.
- [ ] 3B: review struktur Intelligence, entitlement, data lengkap, dan biaya peer.
- [ ] 3B: implementasi kalkulator v1, bukti peer, dan unavailable yang benar.
- [ ] 4: migrasi compare ke real dan bandingkan alasan maksimal tiga perusahaan.
- [ ] 5: review schema, implementasi simpan manual privat, versi, dan delta.

Setiap increment berhenti pada handoff; checkbox ini bukan otorisasi menjalankan
seluruh roadmap sekaligus. Rincian task implementasi diperbarui saat gate disetujui.

## Hasil

- Perubahan: CompanyResearchData public contract, Research domain/use case,
  endpoint protected, UI ringkasan dengan bukti/grafik, dan migrasi pintu research
  legacy ke pencarian/detail real. Tidak ada DB migration/dependency baru.
- Verifikasi 2026-09-26: `php artisan test --compact` 138 passed (598 assertions),
  `npm run typecheck`, ESLint empat file frontend terkait, Pint, `npm run build`,
  serta `git diff --check` lulus. Test default memakai fake HTTP terisolasi;
  `Http::preventStrayRequests()` mencegah credit nyata terpakai saat pengujian.
- Browser QA: aplikasi dibootstrap pada server loopback terpisah, SQLite
  in-memory dan fake HTTP; tidak login/mengubah akun atau database produksi.
  Screenshot desktop/mobile diperiksa, grafik terisi dan logo tampil.
- Browser PASS: desktop, mobile 390px/320px tanpa page overflow, bukti terbuka,
  grafik/tabel, loading, tanpa auto-fetch/refetch ketika pindah tab, error/retry,
  empty, aturan bank, dan drawer mobile. Tidak ada JS exception/Recharts warning.
  Chrome DevTools MCP terputus setelah interup; pengujian lanjutan memakai Chrome
  headless terisolasi melalui CDP tanpa dependency baru. Fixture bukan data live.
- Validasi dokumentasi: 52 tautan lokal pada 10 dokumen lulus. File fixture dan
  server QA sementara dibersihkan/dihentikan; screenshot lokal tetap di storage
  testing yang di-ignore. Session/profile Chrome pribadi tidak disentuh.
- Perbaikan saat QA: grafik hanya dirender pada tab aktif agar tidak mendapat
  ukuran nol. State ringkasan tetap dipertahankan saat pindah tab.
- Sisa warning existing: drawer sidebar tidak memiliki DialogContent description.
- URL lokal `/login` memberi HTTP 200. Tidak ada smoke Sectors berbayar,
  commit, push, atau perubahan file user `Hackaton/`/LSP.
- Risiko: manfaat UX belum diuji user; basis cash flow, kebutuhan peer/scoring,
  compare real dan persistence tetap gate tahap berikutnya. Fallback stale dari
  adapter existing belum ditambahkan; provider error ditampilkan eksplisit.
