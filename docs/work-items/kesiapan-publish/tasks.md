# Tasks Kesiapan Publish

- [x] Scope dan acceptance dicatat sebelum coding.
- [x] Audit dependency dan patch kompatibel tanpa dependency baru.
- [x] Rate limit publik/riset serta security headers dengan test.
- [x] Preflight production yang aman dan test failure/success.
- [x] Environment example produksi, deployment/backup/rollback runbook.
- [x] CI reproducible dan pemeriksaan yang tidak mengubah source; perubahan sudah di-push, hasil Actions dilacak terpisah.
- [x] Suite penuh, typecheck, lint, build, audit dan smoke HTTP/browser lokal.
- [x] Sinkronkan README/checklist dengan bukti dan batas verifikasi.
- [x] Koreksi case-sensitive path Inertia untuk Linux dan jalankan regresi RED/GREEN.

Bukti dan angka tes: [README](README.md), 6-7 Oktober 2026. Persiapan lokal selesai;
item server berikut sengaja belum dicentang, bukan pekerjaan yang terlupakan.

## Memerlukan hosting (bukan penghalang persiapan lokal)

- [ ] Domain, HTTPS, document root public dan akses file sensitif diuji.
- [ ] Preflight check-services lulus pada environment produksi nyata.
- [ ] Email verifikasi/reset masuk ke inbox serta tautan HTTPS berfungsi.
- [ ] Backup/restore, queue, scheduler, monitoring dan rollback diuji di server.
- [ ] Smoke browser target dan satu smoke provider terarah dalam budget.
