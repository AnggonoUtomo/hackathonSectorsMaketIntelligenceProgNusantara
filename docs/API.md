# API dan Integrasi

Dokumen ini mencatat target contract publik dan integrasi eksternal NusaLens.
Endpoint pencarian/profil/analytics real sudah tersedia. Bagian target kontrak
di bawah tetap rancangan kecuali disebut terimplementasi.

## Endpoint analytics terimplementasi

`GET /nusalens/companies/{symbol}/analysis?section=prices|financials|valuation`
memerlukan login dan verifikasi email, throttle 60/menit. Simbol empat karakter
alfanumerik, section wajib dari whitelist; parameter provider tidak diteruskan
dari input bebas. Respons 200 berisi symbol, section, rows dan fetchedAt.
Harga juga menyertakan range start/end (tanggal WIB); cache lama tanpa range
masih didukung frontend menggunakan tanggal fetchedAt dalam WIB.
Setiap row memiliki date (tanggal laporan/harga atau tahun valuasi), metrik
numerik nullable. Seri diurutkan naik; periode duplikat ditolak.

Error: 401 tanpa sesi, 403 belum verified, 422 input invalid, 404 emiten tidak
tersedia, 429 kuota/rate limit, 502 provider/payload bermasalah, 503 lock timeout.
Pesan provider mentah dan API key tidak dikirim. Empty list sah dibedakan dari
JSON rusak. Detail endpoint/cache/biaya: [MarketData](modules/MarketData/README.md).

## Endpoint ringkasan riset terimplementasi

`GET /nusalens/companies/{symbol}/research` wajib login/verified, throttle
60/menit, simbol empat karakter alfanumerik. Respons 200 memiliki symbol,
ruleVersion, companyKind, classification, period, state (`ready`, `empty`,
`unavailable`), sources, findings, checks, chartRows, dan score null.

Finding mencantumkan nilai, unit, aturan, input evidence (field/unit/basis/
periode/sourceId), keterbatasan, serta pemeriksaan berikutnya. Sources memuat
endpoint/section dan fetchedAt asli. Ready berarti ada fakta, bukan riset lengkap.
State empty hanya untuk respons laporan kosong yang sah. Error provider tidak
menjadi 200 empty; error menggunakan status/pesan aman seperti analytics.

Input overview dan empat laporan quarterly berbagi cache existing. Tidak ada
query provider bebas, AI, nilai v1, atau penyimpanan laporan baru. Lihat
[Research](modules/Research/README.md) untuk batas interpretasi dan biaya.

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
Header Sectors memakai `Authorization: <api-key>` tanpa prefix Bearer, sesuai
[overview v2](https://docs.sectors.app/get-started/v2/overview).
Variabel contoh ini belum berarti wiring konfigurasi Sectors sudah tersedia.

## Prioritas endpoint

| Tahap | Endpoint | Kegunaan |
| ----- | -------- | -------- |
| A | `GET /v2/companies/` | Pencarian awal dan shortlist. |
| B | `GET /v2/company/report/{symbol}/` | Detail perusahaan sesuai section yang dibutuhkan. |
| B | `GET /v2/subsector/report/{sub_sector}/` | Konteks peer/subsektor. |
| C | `GET /v2/financials/quarterly/{symbol}/` | Perkembangan keuangan kuartalan. |
| C | `GET /v2/daily/{symbol}/` | Kekuatan pasar sederhana. |

Tabel memakai path penuh `/v2/...`; base URL sudah mengandung `/v2`, sehingga
penyusunan URL client tidak boleh menggandakan prefix versi.

Section prioritas Company Report: `overview`, `financials`, `valuation`, dan
`peers`. Section prioritas Subsector Report: `statistics`, `valuation`, `growth`,
dan `companies`. Ambil section sesuai kebutuhan halaman, bukan semuanya secara
otomatis. Data scoring juga mencakup seluruh peer valid yang dibutuhkan, bukan
hanya ticker yang dibuka atau dibandingkan. Peer provider tidak otomatis sama
dengan populasi NusaLens. Foreign flow, broker, berita, filing, dan forecast
bukan metrik skor v1 atau request wajib MVP.

Daftar ini berasal dari rancangan produk, bukan hasil uji API saat ini. Saat
implementasi, cocokkan auth header, query parameter, response, section, rate
limit, dan credit aktual dengan referensi resmi sebelum mengunci contract.

Utamakan structured query untuk screener. Jangan menggunakan natural-language
query jika structured query sudah cukup.

## Kontrak sumber dan biaya

Berikut hasil pembacaan dokumentasi resmi saat wawancara, bukan pengujian API
live atau jaminan kelengkapan setiap emiten. Verifikasi lagi sebelum integrasi:

| Sumber | Biaya/kendala penting |
| --- | --- |
| [Companies Screener](https://docs.sectors.app/api-references/v2/indonesia/screener/companies) | Structured query 1 credit; natural-language 3. Respons paginated; field tahun/kuartal eksplisit diperlukan untuk periode sebanding. |
| [Company Report](https://docs.sectors.app/api-references/v2/indonesia/report/company-report) | 1 credit per section; default 8 section. Selalu pilih section eksplisit. |
| [Quarterly Financials](https://docs.sectors.app/api-references/v2/indonesia/report/quarterly-financials) | 1 credit per kuartal dikembalikan; batasi n_quarters/report_date. Approx default true perlu dikendalikan dan periode hasil diperiksa. |
| [Daily](https://docs.sectors.app/api-references/v2/indonesia/transaction/daily) | 1 credit, rentang maksimal 90 hari; close harian, tidak menjanjikan adjusted close. |

Hierarchy helper: subindustry berada di industry, industry di subsector, lalu
sector. Mapper menormalkan field/slug provider tanpa menganggap seluruh anggota
sektor relevan. Periode implisit terbaru, field yang bisa difilter, dan
`include_query_values` bukan bukti bahwa semua input/metadata dapat diambil
dalam satu query. Validasi projection, pagination, unit dan tanggal sumber.

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
- Maksimal 1 retry (2 attempt total) untuk `429`, `5xx`, atau timeout yang
  bersifat sementara; tiap attempt wajib lolos budget dan kuota.
- Hormati `Retry-After` untuk 429; jika tidak layak ditunggu dalam alur request,
  gunakan fallback/error eksplisit. Jangan retry tanpa batas atau mengasumsikan
  timeout tidak ditagih provider.
- Catat endpoint, status response, durasi, cache hit/miss, estimasi credit,
  dan request correlation id.
- Jangan mencatat `Authorization`, API key, atau payload sensitif.

## Keamanan contract

Input screener divalidasi dari allowlist field/operator yang didukung.
Expression mentah user tidak langsung diteruskan ke Sectors. Endpoint publik
aplikasi memakai rate limit; seluruh endpoint riset memerlukan login dan email
verified. Aplikasi tidak menjadi proxy bebas ke provider.
Detail aturan ada pada [SECURITY.md](SECURITY.md).

## Cache dan credit

Tahap pengambilan data, TTL normal/fallback, hard budget dan kuota dicatat pada
[DATA-FLOW.md](DATA-FLOW.md) sebagai acuan tunggal. Automated test menggunakan
fake HTTP, termasuk cache hit/miss, timeout, dan error mapping.

## Referensi implementasi

- [Sectors API v2 overview](https://docs.sectors.app/get-started/v2/overview).
- [API v2 references](https://docs.sectors.app/api-references/v2/).
- [API v2 changelog](https://docs.sectors.app/api-references/v2/changelog).

Endpoint/biaya tidak diuji terhadap layanan live pada pekerjaan dokumentasi ini.
Gunakan tautan endpoint spesifik di atas atau [indeks resmi](https://docs.sectors.app/llms.txt)
jika halaman indeks umum berubah. Jangan menghabiskan credit untuk test otomatis.
