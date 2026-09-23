# Module: MarketData

## Tujuan dan boundary

- Source target: `app/Modules/MarketData/`.
- Tanggung jawab: integrasi provider Sectors, mapping response vendor ke model
  internal, cache/freshness, estimasi credit, ledger/reservasi credit, retry,
  timeout, dan observability tanpa secret.
- Di luar tanggung jawab: menyimpan fakta final perusahaan milik `Company`,
  menghitung nilai milik `Intelligence`, menyimpan perbandingan milik
  `Comparison`, dan membuat UI screener/detail.

MarketData adalah pintu tunggal akses Sectors API v2. Module lain tidak boleh
memanggil HTTP Sectors langsung atau memakai DTO vendor sebagai model bisnis.

## Public contract dan dependency

Implementasi increment riset terpandu menyediakan `Application/Contracts/CompanyDirectory`
untuk consumer `Screening/Application/SearchCompanies` dan
`Company/Application/GetCompanyProfile`. Adapter `SectorsCompanyDirectory`
dibinding di AppServiceProvider. Contract mengembalikan read model identitas,
pagination/profil dan fetchedAt; exception publik `MarketDataUnavailable`
menjaga caller tidak bergantung exception Infrastructure.

Search memakai Companies terstruktur dengan `like`; profil hanya section overview.
Payload campuran harga memakai TTL satu jam. Cache menyimpan waktu fetch asli,
lock menahan fetch identik bersamaan, dan correlation ULID baru dibuat tiap
cache miss yang memanggil provider. Tidak ada retry otomatis pada adapter ini.
Logo memakai asset publik Sectors, dengan fallback frontend bila tidak tersedia.
Rincian dan bukti: [redesain riset terpandu](../../work-items/redesain-riset-terpandu/README.md).

Kontrak awal direncanakan sebagai port Application yang dikonsumsi oleh use case
NusaLens, bukan class Infrastructure langsung. Bentuk final dibuat hanya saat
increment implementasi membutuhkan consumer nyata.

Candidate contract awal:

- structured companies screener;
- company report per section eksplisit;
- quarterly financials dengan batas kuartal/periode eksplisit;
- daily market data dengan rentang eksplisit;
- estimasi dan reservasi credit sebelum upstream request.

Dependency eksternal:

- Sectors Financial API v2 via HTTP backend.
- MySQL sebagai sumber kebenaran ledger/reservasi credit.
- Redis sebagai cache/queue, bukan sumber kebenaran budget.

## Operasi dan authorization

Semua fitur riset yang memakai MarketData wajib berada di belakang login dan
email verified. API key Sectors hanya ada di environment backend:

```env
SECTORS_API_BASE_URL=https://api.sectors.app/v2
SECTORS_API_KEY=
SECTORS_API_TIMEOUT=10
```

Jangan mencatat header `Authorization`, API key, signed URL auth, atau payload
yang mengandung secret. Automated test default wajib memakai fake HTTP dan tidak
mengonsumsi credit Sectors.

## Verifikasi

Dokumentasi awal dibuat sebelum source module. Verifikasi implementasi pertama
wajib mencakup:

- fake HTTP untuk success/error/timeout/retry;
- cache hit/miss dan TTL tanpa Redis flush global;
- reservasi ledger MySQL atomik dan hard budget;
- test bahwa cache hit tidak mengonsumsi credit;
- secret scan staged diff sebelum commit.

Source mengikuti `docs/ARCHITECTURE.md` dan `docs/FOLDER-STRUCTURE.md`.
