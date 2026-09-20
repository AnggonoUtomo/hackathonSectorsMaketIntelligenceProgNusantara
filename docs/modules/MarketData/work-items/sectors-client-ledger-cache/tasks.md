# Tasks: Sectors Client, Ledger, dan Cache

## Plan

- [x] Baca dokumen arsitektur, struktur, module, data-flow, security, API, dan
  Sectors docs resmi.
- [x] Tulis scope, non-scope, acceptance, increment, dan stop condition.
- [ ] User menyetujui implementasi.

## Client fake-first

- [ ] Tambahkan config env Sectors di backend.
- [ ] Buat client HTTP dan error mapping.
- [ ] Test success/error/timeout/header dengan fake HTTP.

## Ledger/reservasi

- [ ] Finalisasi DDL minimal.
- [ ] Implement migration dan reservasi atomik.
- [ ] Test budget/quota/retry/concurrency.

## Cache/freshness

- [ ] Implement cache key/TTL/metadata.
- [ ] Test hit/miss/expired dan no-credit-on-hit.

## Structured screener

- [ ] Implement DTO/mapper minimal.
- [ ] Implement allowlist query.
- [ ] Test pagination, empty, error, quota, dan auth verified.
