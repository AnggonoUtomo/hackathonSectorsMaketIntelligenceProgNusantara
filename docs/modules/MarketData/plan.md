# Plan: MarketData Foundation

## Prinsip dan urutan

Plan ini belum merupakan izin coding source module. Fokusnya membangun pondasi
MarketData yang hemat credit dan testable tanpa live call Sectors. Urutan:
konfigurasi -> kontrak client -> fake HTTP -> ledger/reservasi -> cache -> satu
vertical slice screener structured.

## Increment 1: Konfigurasi dan client Sectors fake-first

- Prasyarat: persetujuan implementasi.
- File target: `config/services.php` atau config khusus, `.env.example`,
  `app/Modules/MarketData/...`, test Feature/Unit terkait.
- Buat client Infrastructure yang menerima base URL, API key, timeout, dan HTTP
  client Laravel.
- Jangan membaca API key dari frontend, log, exception message publik, atau
  dokumentasi output.
- Test memakai `Http::fake()` untuk success, error status, timeout, dan
  Authorization header tanpa mencetak nilai secret.
- Acceptance: tidak ada panggilan jaringan nyata pada test; error provider
  berbeda dari hasil kosong.

Checkpoint: jika konfigurasi env atau Sectors docs berbeda dari dokumen aktif,
berhenti dan update plan sebelum source melebar.

## Increment 2: Estimasi credit dan ledger/reservasi MySQL

- Rancang migration ledger/reservation minimal milik MarketData dengan ULID.
- Catat global budget 1.000, per-user daily 20 credit reset 00.00 WIB, endpoint,
  estimated_cost, reserved_cost, final status, attempt, dan correlation id.
- Terapkan reservasi atomik agar request bersamaan tidak overspend.
- Test concurrency/reservation dengan database test; jangan memakai Redis untuk
  sumber kebenaran budget.
- Acceptance: over-budget dan over-daily-quota ditolak sebelum upstream request;
  retry maksimal satu attempt tambahan tetap melewati reservasi.

Checkpoint: DDL dan locking atomik harus jelas sebelum migration dibuat.

## Increment 3: Cache/freshness wrapper

- Tambahkan cache key dari endpoint + parameter terstruktur + versi mapping.
- TTL awal: screener/harga/valuasi 1 jam; profil/klasifikasi 7 hari; fundamental
  24 jam, sesuai use case yang sudah dipakai.
- Cache hit tidak membuat ledger konsumsi baru. Cache miss/expired melewati
  reservasi.
- Test hit/miss/expired dengan waktu terkontrol dan Redis/cache fake yang
  proporsional; jangan flush Redis global.
- Acceptance: cache hit mengembalikan data valid dan tidak memanggil Sectors.

## Increment 4: Structured screener vertical slice

- Tambahkan contract minimal untuk structured companies screener.
- Validasi allowlist field/operator; jangan menerima expression mentah bebas
  dari user.
- Gunakan fake HTTP response provider untuk mapping simbol, nama, query_values,
  dan pagination.
- Belum membuat UI Discover besar. Endpoint internal/presentation hanya dibuat
  bila diperlukan untuk mencoba alur authenticated+verified.
- Acceptance: request verified user dapat mengambil shortlist via fake/provider
  internal, pagination dipertahankan, dan error state eksplisit.

## Gate dan penghentian

Tidak ada live Sectors call sampai user memberi izin eksplisit dan estimasi
credit per aksi ditampilkan. Jangan mengimplementasikan Company snapshot,
Intelligence scoring, Comparison, AI, atau UI penuh pada foundation ini.

## Referensi

- [Sectors API v2 overview](https://docs.sectors.app/get-started/v2/overview):
  v2 supported dan auth header `Authorization`.
- [Companies Screener](https://docs.sectors.app/api-references/v2/indonesia/screener/companies):
  structured query, natural query, pagination, fields, dan endpoint `/companies/`.
- [Company Report](https://docs.sectors.app/api-references/v2/indonesia/report/company-report):
  report per symbol/section.
- [Quarterly Financials](https://docs.sectors.app/api-references/v2/indonesia/report/quarterly-financials):
  data kuartalan dan parameter periode.
- [Daily Transaction Data](https://docs.sectors.app/api-references/v2/indonesia/transaction/daily):
  data harian untuk market series.
