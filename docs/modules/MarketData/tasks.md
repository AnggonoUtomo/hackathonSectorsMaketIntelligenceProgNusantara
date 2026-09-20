# Tasks: MarketData Foundation

## Persiapan

- [x] Baca `docs/README.md`, `ARCHITECTURE.md`, `FOLDER-STRUCTURE.md`,
  `MODULES.md`, `DATA-FLOW.md`, `DATA-MODEL.md`, `SECURITY.md`, dan `API.md`.
- [x] Verifikasi ringkas dokumentasi resmi Sectors API v2 sebelum plan.
- [x] Buat dokumen module MarketData dan work item foundation.
- [x] User menyetujui implementasi source module.

## Increment 1: Konfigurasi dan client Sectors fake-first

- [x] Tambahkan konfigurasi backend untuk Sectors tanpa secret di source.
- [x] Buat skeleton module hanya untuk class yang dipakai increment ini.
- [x] Implement client HTTP Infrastructure dengan timeout dan error mapping.
- [x] Test `Http::fake()` untuk success/error/timeout/auth header.
- [x] Jalankan focused PHPUnit dan secret scan diff.

## Increment 2: Ledger/reservasi credit

- [x] Rancang dan implement migration ledger/reservation minimal.
- [x] Implement reservasi atomik global budget dan daily quota user.
- [x] Test cache-free reservation, over-budget, over-quota, retry, dan failure rollback.
- [x] Dokumentasikan DDL dan batas rollback.

## Increment 3: Cache/freshness

- [ ] Implement cache key dan metadata untuk endpoint yang sudah dipakai.
- [ ] Test hit/miss/expired dan Redis/cache behavior tanpa flush global.
- [ ] Pastikan cache hit tidak membuat ledger konsumsi baru.

## Increment 4: Structured screener vertical slice

- [ ] Buat DTO internal screener minimal dan mapper provider.
- [ ] Validasi allowlist field/operator structured query.
- [ ] Test pagination, empty result, provider error, quota error, dan auth verified.
- [ ] Catat estimasi credit per aksi sebelum live request di masa depan.

## Hasil increment 1

Source MarketData minimal sudah dibuat untuk client fake-first. Konfigurasi
Sectors ada di `.env.example` dan `config/services.php` tanpa secret. Test
`SectorsApiClientTest` membuktikan URL base `/v2` tidak digandakan, header
Authorization dikirim, missing key menolak request sebelum network, error
400/401/403/404/429/5xx dipetakan eksplisit, timeout dipetakan, dan exception
tidak membawa API key. Tidak ada migration bisnis, ledger/cache, UI, atau live
Sectors call pada increment ini.

## Hasil increment 2

Ledger/reservasi credit minimal sudah dibuat pada tabel
`market_data_credit_reservations` dengan ULID primary key, `user_id` ULID tanpa
FK constraint, `usage_date`, endpoint, estimated credits, attempt, status, dan
correlation id unik per attempt. Tidak memakai Redis sebagai sumber kebenaran.

`CreditReservationService` melakukan reservasi dalam transaksi database,
menghitung pemakaian status `reserved` dan `committed`, menolak over budget
global, over daily quota user, dan attempt melebihi batas retry. Tanggal kuota
harian memakai timezone konfigurasi, default `Asia/Jakarta`.

Test `CreditReservationTest` membuktikan reservasi sukses, hard budget global,
kuota harian user, tanggal reset WIB, retry maksimal satu, dan kegagalan
reservasi tidak menulis row ledger. Belum ada integrasi cache, upstream Sectors,
atau rekonsiliasi final charge pada increment ini.
