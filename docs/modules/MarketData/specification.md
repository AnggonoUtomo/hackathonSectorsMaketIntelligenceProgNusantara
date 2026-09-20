# Specification: MarketData

## Status

Draft untuk implementasi awal; menunggu persetujuan sebelum source
`app/Modules/MarketData` dibuat.

## Tujuan, scope, dan non-scope

MarketData menyediakan akses internal yang hemat credit ke Sectors API v2.
Module ini menangani request provider, response validation, mapping,
cache/freshness, retry, timeout, dan ledger/reservasi credit.

Scope implementasi awal:

- konfigurasi Sectors tanpa secret di source;
- client HTTP internal dengan base URL, timeout, auth header, dan error mapping;
- fake HTTP test sebagai default;
- struktur ledger/reservasi credit MySQL;
- cache metadata dan TTL awal untuk endpoint prioritas;
- kontrak minimal untuk screener structured query sebagai vertical slice pertama.

Non-scope implementasi awal:

- live request Sectors dan konsumsi credit real;
- UI Discover, scoring, Company snapshot final, comparison, AI, billing/BYOK;
- mengambil semua endpoint/section sekaligus;
- natural-language screener.

## Arsitektur

- Module: `app/Modules/MarketData/`.
- Inbound adapter: belum ada HTTP publik langsung pada increment foundation.
- Use case: Application mengorkestrasi estimasi credit, reservasi, cache lookup,
  request provider, mapping, dan pencatatan hasil.
- Aturan Domain: budget hard stop, kuota harian per akun, retry maksimal satu,
  cache hit tidak memakai credit, dan Redis bukan sumber kebenaran budget.
- Outbound port: kontrak provider internal milik Application.
- Outbound adapter: Infrastructure Sectors client/adapter, cache, dan
  persistence ledger.
- Composition root: provider Laravel dibuat bila binding port-adapter sudah
  diperlukan oleh consumer nyata.

## Contract dan data

Endpoint prioritas berdasarkan dokumen aktif dan verifikasi resmi 2026-09-20:

| Use case | Endpoint | Catatan credit/cache |
| --- | --- | --- |
| Screener structured | `GET /companies/` pada base `/v2` | Structured query 1 credit; TTL normal 1 jam. |
| Company report | `GET /company/report/{symbol}/` | 1 credit per section; section eksplisit. |
| Quarterly financials | `GET /financials/quarterly/{symbol}/` | Credit mengikuti kuartal dikembalikan; batasi periode. |
| Daily market | `GET /daily/{symbol}/` | 1 credit; rentang maksimal mengikuti provider, cache 1 jam. |

Base URL default sudah mengandung `/v2`; client tidak boleh menggandakan prefix.
Header auth provider memakai `Authorization: <api-key>`.

Ledger konseptual:

- global budget: 1.000 credit Sectors sekali pakai;
- cadangan 600 tidak dipakai tanpa approval baru;
- kuota user: 20 credit aktual per akun per hari reset 00.00 WIB;
- reservation dibuat sebelum attempt upstream;
- retry maksimal satu attempt tambahan dan tetap perlu reservasi;
- tidak ada refund optimistis untuk timeout/unknown charge.

Nama tabel, kolom, index, dan mekanisme locking masih gate implementasi.

## Authorization, audit, dan UI

MarketData tidak mengekspos endpoint publik bebas. Consumer riset wajib login
dan verified. Log boleh berisi endpoint, status, durasi, cache hit/miss,
estimasi credit, dan correlation id. Log tidak boleh berisi API key,
Authorization header, atau data rahasia.

UI belum dibuat pada foundation. Error contract internal harus membedakan cache
miss, provider unavailable, budget habis, quota habis, unauthorized provider,
not found, validation error, timeout, dan rate limit.

## Dependency

Tidak ada import privat lintas module pada foundation. Company/Screening menjadi
consumer pada increment berikutnya setelah contract minimal stabil.

## Acceptance dan verifikasi

- [ ] Sectors client memakai base URL, timeout, dan Authorization header dari
  config backend; test membuktikan header ada tanpa menampilkan nilai secret.
- [ ] Fake HTTP test membuktikan success, 400, 401/403, 404, 429, 5xx, dan
  timeout dipetakan ke error internal yang eksplisit.
- [ ] Ledger/reservasi MySQL mencegah budget/kuota terlampaui pada request
  bersamaan yang diuji.
- [ ] Cache hit memakai Redis/cache abstraction dan tidak membuat upstream
  request atau konsumsi credit.
- [ ] Tidak ada live Sectors call pada automated test.

## Risiko dan keputusan terbuka

- Detail DDL ledger dan locking atomik perlu dipilih sebelum coding.
- Apakah konfigurasi API key disimpan hanya `.env` atau ada admin settings
  terenkripsi adalah keputusan masa depan, bukan MVP foundation.
- Browser/UI Discover belum masuk foundation.
- Kontrak response internal final menunggu vertical slice Screening/Company.
