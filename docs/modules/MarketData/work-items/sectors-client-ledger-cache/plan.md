# Plan: Sectors Client, Ledger, dan Cache

## Increment 1: Client fake-first

Implementasi paling kecil adalah client Infrastructure yang bisa diuji dengan
fake HTTP. Client membaca config backend, menyusun URL tanpa menggandakan `/v2`,
menambahkan Authorization header, timeout, dan memetakan status provider ke
exception internal.

Verifikasi:

- `Http::fake()` success dan error;
- timeout fake;
- assertion header tanpa menampilkan nilai API key;
- test memastikan tidak ada network nyata.

## Increment 2: Ledger/reservasi

Buat migration dan service Application/Infrastructure minimal untuk reservasi
credit. MySQL menjadi sumber kebenaran budget dan kuota. Redis tidak dipakai
untuk sisa budget.

Verifikasi:

- global 1.000 credit hard stop;
- per-user 20 credit/day reset Asia/Jakarta;
- request bersamaan tidak overspend;
- retry maksimal satu attempt tambahan;
- timeout/unknown charge tidak refund optimistis.

## Increment 3: Cache/freshness

Tambahkan cache wrapper dan metadata untuk endpoint yang dipakai. Cache hit
mengembalikan data mapped dan tidak menyentuh ledger konsumsi baru.

Verifikasi:

- cache hit/miss/expired;
- key memakai endpoint, parameter, dan mapping version;
- tidak ada flush Redis global.

## Increment 4: Structured screener

Buat satu vertical slice structured screener minimal sebagai consumer nyata
client, ledger, dan cache.

Verifikasi:

- allowlist field/operator;
- pagination response;
- empty result vs provider error;
- authenticated verified boundary bila endpoint aplikasi dibuat.

## Stop condition

Berhenti setelah foundation siap dengan fake HTTP. Live Sectors call hanya
dilakukan setelah user menyetujui estimasi credit untuk aksi tertentu.
