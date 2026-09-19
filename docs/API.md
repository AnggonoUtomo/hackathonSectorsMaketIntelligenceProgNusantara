# API dan Integrasi

Dokumen ini mencatat contract publik dan integrasi eksternal NusaLens. Source
aplikasi belum dibuat, sehingga endpoint internal belum tersedia.

## Sectors API v2

Sectors Financial API v2 adalah sumber data inti NusaLens.

Menghilangkan integrasi ini menghilangkan sumber untuk screener, detail,
perbandingan, dan penilaian. Semua panggilan melalui Infrastructure MarketData:
`SectorsApiClient` menangani HTTP; DTO dan mapper mengubah data vendor;
`SectorsMarketDataAdapter` memenuhi port internal; cache mengurangi upstream.

Environment backend:

```env
SECTORS_API_BASE_URL=https://api.sectors.app/v2
SECTORS_API_KEY=
SECTORS_API_TIMEOUT=10
```

API key hanya boleh berada di backend. Jangan pernah mengirim API key ke
frontend, log, source, test output, atau dokumentasi.

## Prioritas endpoint

| Tahap | Endpoint | Kegunaan |
| ----- | -------- | -------- |
| A | `GET /v2/companies/` | Pencarian awal dan shortlist. |
| B | `GET /v2/company/report/{symbol}/` | Detail perusahaan sesuai section yang dibutuhkan. |
| B | `GET /v2/subsector/report/{sub_sector}/` | Konteks peer/subsektor. |
| C | `GET /v2/financials/quarterly/{symbol}/` | Perkembangan keuangan kuartalan. |
| C | `GET /v2/daily/{symbol}/` | Kekuatan pasar sederhana. |
| C | `GET /v2/foreign-flow/{symbol}/` | Bukti aktivitas pasar tambahan. |
| C | `GET /v2/broker-summary/{symbol}/top/` | Opsional untuk detail lanjutan. |
| D | `GET /v2/news/`, `GET /v2/filings/` | Bukti tambahan, bukan nilai utama MVP. |

Tabel memakai path penuh `/v2/...`; base URL sudah mengandung `/v2`, sehingga
penyusunan URL client tidak boleh menggandakan prefix versi.

Section prioritas Company Report: `overview`, `financials`, `valuation`, dan
`peers`. Section prioritas Subsector Report: `statistics`, `valuation`, `growth`,
dan `companies`. Ambil section sesuai kebutuhan halaman, bukan semuanya secara
otomatis. Tahap C hanya untuk perusahaan yang dibuka atau dibandingkan.

Daftar ini berasal dari rancangan produk, bukan hasil uji API saat ini. Saat
implementasi, cocokkan auth header, query parameter, response, section, rate
limit, dan credit aktual dengan referensi resmi sebelum mengunci contract.

Utamakan structured query untuk screener. Jangan menggunakan natural-language
query jika structured query sudah cukup.

## Error handling

Minimal bedakan:

- `400`: query salah;
- `401/403`: API key atau izin bermasalah;
- `404`: saham/data tidak ditemukan;
- `429`: batas penggunaan API tercapai;
- `5xx`: server Sectors bermasalah;
- timeout atau gangguan jaringan.

Jangan menyamarkan error sebagai data kosong.

Bedakan hasil pencarian kosong, ticker tidak valid, cache kedaluwarsa, provider
tidak tersedia, dan rate limit pada response aplikasi/UI.

## Retry dan observability

- Jangan retry 4xx kecuali `429`.
- Retry `429`, `5xx`, atau timeout dengan jeda bertahap kecil dan jumlah terbatas.
- Tetapkan jumlah retry dan penanganan `Retry-After` pada work item integrasi;
  jangan membuat request berulang tanpa batas.
- Catat endpoint, status response, durasi, cache hit/miss, estimasi credit,
  dan request correlation id.
- Jangan mencatat `Authorization`, API key, atau payload sensitif.

## Keamanan contract

Input screener divalidasi dari allowlist field/operator yang didukung.
Expression mentah user tidak langsung diteruskan ke Sectors. Endpoint publik
aplikasi memakai rate limit dan tidak menjadi proxy bebas ke provider.
Detail aturan ada pada [SECURITY.md](SECURITY.md).

## Cache dan credit

Tahap pengambilan data, TTL awal, dan anggaran credit per use case dicatat pada
[DATA-FLOW.md](DATA-FLOW.md) sebagai acuan tunggal. Automated test menggunakan
fake HTTP, termasuk cache hit/miss, timeout, dan error mapping.

## Referensi implementasi

- [Sectors API v2 overview](https://docs.sectors.app/get-started/v2/overview).
- [API v2 references](https://docs.sectors.app/api-references/v2/).
- [API v2 changelog](https://docs.sectors.app/api-references/v2/changelog).

Referensi dipertahankan dari rancangan awal; endpoint/biaya belum diverifikasi
ulang terhadap layanan live pada pekerjaan dokumentasi ini.
