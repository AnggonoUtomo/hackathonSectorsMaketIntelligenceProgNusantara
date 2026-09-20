# Tasks: MarketData Foundation

## Persiapan

- [x] Baca `docs/README.md`, `ARCHITECTURE.md`, `FOLDER-STRUCTURE.md`,
  `MODULES.md`, `DATA-FLOW.md`, `DATA-MODEL.md`, `SECURITY.md`, dan `API.md`.
- [x] Verifikasi ringkas dokumentasi resmi Sectors API v2 sebelum plan.
- [x] Buat dokumen module MarketData dan work item foundation.
- [ ] User menyetujui implementasi source module.

## Increment 1: Konfigurasi dan client Sectors fake-first

- [ ] Tambahkan konfigurasi backend untuk Sectors tanpa secret di source.
- [ ] Buat skeleton module hanya untuk class yang dipakai increment ini.
- [ ] Implement client HTTP Infrastructure dengan timeout dan error mapping.
- [ ] Test `Http::fake()` untuk success/error/timeout/auth header.
- [ ] Jalankan focused PHPUnit dan secret scan diff.

## Increment 2: Ledger/reservasi credit

- [ ] Rancang dan implement migration ledger/reservation minimal.
- [ ] Implement reservasi atomik global budget dan daily quota user.
- [ ] Test cache-free reservation, over-budget, over-quota, retry, dan concurrency.
- [ ] Dokumentasikan DDL dan batas rollback.

## Increment 3: Cache/freshness

- [ ] Implement cache key dan metadata untuk endpoint yang sudah dipakai.
- [ ] Test hit/miss/expired dan Redis/cache behavior tanpa flush global.
- [ ] Pastikan cache hit tidak membuat ledger konsumsi baru.

## Increment 4: Structured screener vertical slice

- [ ] Buat DTO internal screener minimal dan mapper provider.
- [ ] Validasi allowlist field/operator structured query.
- [ ] Test pagination, empty result, provider error, quota error, dan auth verified.
- [ ] Catat estimasi credit per aksi sebelum live request di masa depan.

## Handoff plan

Belum ada source MarketData, migration bisnis, package baru, atau panggilan
Sectors live. Plan menunggu persetujuan implementasi.
