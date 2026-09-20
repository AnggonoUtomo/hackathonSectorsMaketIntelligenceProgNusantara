# Tasks: Sectors Client, Ledger, dan Cache

## Plan

- [x] Baca dokumen arsitektur, struktur, module, data-flow, security, API, dan
  Sectors docs resmi.
- [x] Tulis scope, non-scope, acceptance, increment, dan stop condition.
- [x] User menyetujui implementasi.

## Client fake-first

- [x] Tambahkan config env Sectors di backend.
- [x] Buat client HTTP dan error mapping.
- [x] Test success/error/timeout/header dengan fake HTTP.

## Ledger/reservasi

- [x] Finalisasi DDL minimal.
- [x] Implement migration dan reservasi atomik.
- [x] Test budget/quota/retry/failure rollback.

## Cache/freshness

- [x] Implement cache key/TTL/metadata.
- [x] Test hit/miss/expired dan no-credit-on-hit.

## Structured screener

- [x] Implement DTO/mapper minimal.
- [x] Implement allowlist query.
- [x] Test pagination, cache hit, provider error eksplisit, dan no-credit-on-hit.
- [ ] Test empty, quota, dan auth verified saat inbound endpoint dibuat.
