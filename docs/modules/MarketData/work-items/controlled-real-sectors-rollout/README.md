# Work Item: Controlled Real Sectors Rollout

## Status dan owner

- Status: Done.
- Owner: MarketData + Screening.
- Target: real-mode terbatas untuk halaman Temukan Saham.

## Kondisi awal

MarketData sudah memiliki `SectorsApiClient`, ledger/reservasi credit,
`MarketDataCache`, dan `StructuredCompanyScreener` yang diuji dengan fake HTTP.
UI Temukan Saham masih memakai backend fake `FakeDiscoverScreener`.

User sudah memasukkan API key Sectors ke `.env` lokal dan menyetujui pendekatan
hybrid: automated test tetap fake, aplikasi lokal dapat memakai real Sectors
secara terkendali.

## Scope dan non-scope

Scope:

- konfigurasi mode provider `fake|real` dengan default aman `fake`;
- sambungkan Temukan Saham ke `StructuredCompanyScreener` saat mode `real`;
- tetap gunakan cache 1 jam dan reservasi 1 credit pada cache miss;
- test real-mode memakai `Http::fake()`, bukan live request;
- UI menampilkan source/meta bahwa data berasal dari real-mode Sectors saat
  aktif.

Non-scope:

- live smoke test otomatis;
- Detail Perusahaan real;
- Bandingkan real;
- Company snapshot persistence;
- Intelligence scoring final;
- natural-language screener;
- mengambil semua endpoint/section sekaligus.

## Acceptance criteria

- [ ] Default mode tetap fake dan test existing tidak menghubungi Sectors.
- [ ] `MARKETDATA_PROVIDER_MODE=real` mengubah Temukan Saham memakai
  `StructuredCompanyScreener`.
- [ ] Real-mode memakai ledger/cache; cache hit tidak menambah credit.
- [ ] Real-mode test memakai `Http::fake()` dan membuktikan props Inertia.
- [ ] API key tidak tampil di source, frontend, log, docs output, atau test
  output.

## Dependency dan keputusan

- `docs/DATA-FLOW.md`: structured screener 1 credit, TTL screener 1 jam, cache
  hit tidak memakai credit, ledger MySQL sebagai sumber kebenaran.
- `docs/SECURITY.md`: API key backend-only dan semua fitur riset wajib login +
  verified.
- `docs/API.md`: endpoint `/companies/` pada base `/v2`, structured query,
  response paginated, dan Authorization header.

## Handoff

- Perubahan: config `MARKETDATA_PROVIDER_MODE`, real-mode terbatas untuk
  Temukan Saham, mapping filter UI ke structured screener, props Inertia source
  `sectors_real`, dan test fake HTTP untuk ledger/cache.
- Verifikasi: focused PHPUnit real-mode Discover dan MarketData lulus; final
  gate lulus.
- Risiko terbuka: kontrak query/field real Sectors perlu smoke manual dengan
  credit sadar setelah wiring fake-HTTP lulus.
