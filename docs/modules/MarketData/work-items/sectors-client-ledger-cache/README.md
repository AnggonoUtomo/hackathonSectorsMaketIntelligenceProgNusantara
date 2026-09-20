# Work Item: Sectors Client, Ledger, dan Cache

## Status dan owner

- Status: Planned; menunggu persetujuan implementasi.
- Owner: MarketData.
- Target: `app/Modules/MarketData`, konfigurasi Sectors backend, ledger MySQL,
  cache Redis, dan test fake HTTP.

## Kondisi awal

Fondasi auth/ULID/verified, Redis local, Mailpit local, dan QA auth sudah
selesai. `app/Modules/MarketData` belum ada. Database aplikasi sudah
diinisialisasi dari migration starter/fondasi, tetapi belum memiliki tabel
bisnis MarketData.

Dokumentasi resmi Sectors API v2 diperiksa ulang pada 2026-09-20. v2 adalah
versi supported; auth memakai header `Authorization`; endpoint prioritas
foundation tetap screener `/companies/`, company report, quarterly financials,
dan daily data.

## Scope dan non-scope

Scope:

- konfigurasi backend Sectors tanpa secret di source;
- Sectors HTTP client fake-first;
- error mapping eksplisit;
- ledger/reservasi credit MySQL;
- cache/freshness wrapper Redis;
- structured screener minimal sebagai vertical slice.

Non-scope:

- live Sectors call atau konsumsi credit real;
- UI Discover penuh;
- Company snapshot final, scoring, comparison, AI, billing, BYOK;
- natural-language screener;
- mengambil semua endpoint/section IDX sekaligus.

## Acceptance criteria

- [ ] Automated test tidak melakukan network call nyata ke Sectors.
- [ ] API key tidak muncul di source, frontend, log, dokumentasi output, atau
  test output.
- [ ] Cache hit tidak memakai credit dan tidak memanggil upstream.
- [ ] Reservasi MySQL mencegah overspend global/daily.
- [ ] Error provider tidak disamarkan sebagai data kosong.
- [ ] Structured screener hanya memakai allowlist field/operator.

## Dependency dan keputusan

- ADR-003: akses riset verified, ULID, persistence ownership.
- `docs/DATA-FLOW.md`: TTL, fallback, budget 1.000, cadangan 600 perlu approval,
  daily quota 20 credit/user/day reset WIB, retry maksimal satu.
- `docs/API.md`: endpoint prioritas dan larangan menggandakan `/v2`.
- `docs/SECURITY.md`: secret backend-only dan validasi query.

## Handoff

- Perubahan saat ini: dokumentasi module dan plan foundation.
- Verifikasi: dokumen aktif dan dokumentasi resmi Sectors dibaca ulang; belum
  ada source code atau migration bisnis.
- Risiko terbuka: DDL ledger dan locking atomik harus diputuskan saat increment
  implementasi sebelum migration dibuat.
