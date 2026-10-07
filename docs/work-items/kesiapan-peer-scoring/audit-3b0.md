# Audit 3B.0: Kelayakan Peer dan Scoring

## Status audit

- Tanggal: 2026-09-27.
- Jenis audit: dokumentasi resmi Sectors dan source lokal.
- Live API/credit: belum dilakukan.
- Scope: kesiapan data, peer, biaya, cache, dan gap sebelum implementasi
  Intelligence.

Audit ini tidak mengubah formula pada `docs/SCORING.md`, tidak membuka API key,
tidak menjalankan smoke berbayar, dan tidak menjadi persetujuan coding
kalkulator. Kesimpulan sementara: fondasi data real sudah ada, tetapi scoring v1
belum layak diimplementasikan sebelum contract, entitlement live, payload contoh,
dan strategi credit disetujui.

## Bukti dokumentasi resmi

| Area | Bukti resmi | Catatan untuk NusaLens |
| --- | --- | --- |
| Companies Screener | `https://docs.sectors.app/api-references/v2/indonesia/screener/companies` | Mendukung query terstruktur, limit maksimal 200, offset, `include_query_values`, biaya 1 credit untuk structured query dan 3 credit untuk natural query. Field mencakup klasifikasi, kapitalisasi pasar, rasio terbaru, dan banyak field tahunan. |
| Company Report | `https://docs.sectors.app/api-references/v2/indonesia/report/company-report` | Section bisa dipilih; biaya 1 credit per section. Section `valuation` berisi valuation dan riwayat metrik, tetapi tetap perlu dibuktikan keselarasan denominator, tanggal harga, dan periode untuk scoring v1. |
| Quarterly Financials | `https://docs.sectors.app/api-references/v2/indonesia/report/quarterly-financials` | Mengembalikan data per kuartal; biaya 1 credit per quarter. Lima kuartal per target dan peer dapat mahal jika dipakai untuk YoY. |
| Daily Transaction | `https://docs.sectors.app/api-references/v2/indonesia/transaction/daily` | Mendukung rentang tanggal sampai jendela terbaru 90 hari dan field OHLCV/market cap. Dokumentasi yang dibaca belum membuktikan harga sudah adjusted aksi korporasi. |
| Helper klasifikasi | `https://docs.sectors.app/api-references/v2/indonesia/helper-list/subsectors`, `https://docs.sectors.app/api-references/v2/indonesia/helper-list/industries`, `https://docs.sectors.app/api-references/v2/indonesia/helper-list/subindustries` | Masing-masing biaya 1 credit dan dapat membantu membangun hierarki Sector -> Subsector -> Industry -> Subindustry. Tetap perlu payload live untuk memastikan konsistensi dengan field company. |

## Bukti source lokal

| Source lokal | Yang sudah ada | Gap sebelum scoring |
| --- | --- | --- |
| `SectorsCompanyDirectory` | Search dan profile real melalui endpoint `companies` dan `company/report` section `overview`; memetakan sector, subSector, industry, market cap, harga, board, listing date, employees, indices. | Belum memetakan `sub_industry`; search bukan populasi peer; profile tidak menyimpan bukti peer dan eksklusi. |
| `StructuredCompanyScreener` | Adapter structured screener memakai endpoint `companies`, cache, ledger, dan `include_query_values`. | Allowlist criteria belum selaras penuh dengan field resmi seperti `pe_ttm`, `pb_mrq`, `roe_ttm`, `roa_ttm`, field bracket tahunan, dan growth terbaru. |
| `SectorsCompanyAnalytics` | Section on-demand `prices`, `financials`, dan `valuation`; biaya terpisah dan cache per section. | Financial mapping belum cukup untuk semua metrik v1; tidak memetakan NPL, risk weighted asset, current assets/liabilities, atau subindustry. |
| `CompanyResearchData` dan ringkasan 3A | Ringkasan deterministik sudah dapat menjelaskan fakta real satu perusahaan tanpa total skor. | Belum ada module Intelligence, kalkulator pure PHP, snapshot bukti immutable, atau contract hasil scoring. |
| Test MarketData existing | Fake HTTP membuktikan harga, empat kuartal financial, dan valuation section on-demand. | Belum ada test peer completeness, formula, cache stale/fallback, quota habis, atau replay snapshot. |

## Matriks metrik

| Komponen | Kandidat sumber | Status | Gap utama |
| --- | --- | --- | --- |
| Peer group | Helper klasifikasi dan Companies Screener | Sebagian siap | Perlu live payload untuk `sub_industry`, deduplikasi, pagination lengkap, dan kompatibilitas bank/nonbank. |
| Quality: ROE/ROA | Companies Screener `roe_ttm`, `roa_ttm`, atau field tahunan untuk hitung ulang | Belum lulus | Perlu unit, periode, denominator, dan aturan nilai negatif/denominator tidak valid. |
| Growth: YoY revenue/earnings | Companies Screener growth terbaru atau Quarterly Financials 5 kuartal | Belum lulus | Empat kuartal lokal tidak cukup untuk same-quarter YoY. Fan-out quarterly bisa melampaui quota harian. |
| Value: P/E TTM dan P/B MRQ | Companies Screener `pe_ttm`, `pb_mrq`; Company Report `valuation` sebagai bukti pembanding | Belum lulus | Perlu keselarasan harga, denominator, tanggal pasar, dan fallback jika nilai non-finite. |
| Market Strength | Daily Transaction 20 sesi atau lebih | Belum lulus | Perlu 21 close valid dengan tanggal pembanding sama antar-peer. Basis adjusted aksi korporasi belum terbukti. |
| Risk bank | Field tahunan bank pada Companies Screener, termasuk loan, NPL, capital, RWA | Belum lulus | Perlu bukti unit/definisi dan rumus CAR/NPL yang disetujui. Local mapping belum cukup. |
| Risk nonbank | Field debt/equity dan current assets/liabilities tahunan | Belum lulus | DER v1 perlu utang berbunga jika tersedia; current ratio butuh current assets/liabilities valid. |
| Keuangan nonbank | Identifikasi jenis bisnis dari klasifikasi | Perlu aturan unavailable | Risk 10% tetap unavailable untuk v1, bukan memakai rumus bank atau nonkeuangan. |

## Estimasi credit awal

Estimasi ini belum menggantikan smoke live. Angka dihitung dari biaya resmi per
endpoint/section yang dibaca, bukan dari payload akun lokal.

| Skenario | Perkiraan credit cold | Risiko |
| --- | --- | --- |
| Helper klasifikasi lengkap | 3 credit | Bisa di-cache lama, tetapi perlu bukti konsistensi dengan company result. |
| Satu halaman peer structured screener | 1 credit per page sampai 200 data | Kelompok besar butuh pagination; subset tidak boleh dianggap populasi lengkap. |
| Input screener dengan `query_values` | 1 credit per structured request | Kandidat paling hemat jika semua field scoring dapat ditarik sebagai projection/query value. |
| Daily target dan peer | 1 credit per symbol untuk rentang yang diminta | Target + 5 peer minimal 6 credit; kelompok valid lebih besar bisa mahal. |
| Quarterly 5 kuartal target dan peer | 5 credit per symbol | Target + 5 peer minimal 30 credit, melewati quota harian 20 jika semuanya cold. |
| Company Report valuation | 1 credit per symbol untuk satu section | Hindari default semua section karena biaya bisa naik ke seluruh section. |

Strategi yang paling masuk akal untuk 3B.1 adalah membuktikan apakah structured
screener dapat menyediakan populasi peer dan input rasio utama tanpa fan-out
perusahaan. Daily dan quarterly dipakai hanya untuk komponen yang memang tidak
bisa dipenuhi secara hemat dari screener.

## Gate berikutnya

- 3B.0 official-doc dan source-local audit: selesai untuk tahap awal.
- Entitlement live, payload akun lokal, unit, periode, dan contoh sanitasi:
  belum selesai.
- Sisa ledger/budget dan daftar request smoke: belum diperiksa.
- Proposal contract Intelligence, schema bukti, dan precision: belum diajukan.
- Implementasi 3B.1: belum diotorisasi.

Rekomendasi tahap berikutnya adalah membuat proposal smoke terarah yang sangat
kecil: satu target bank dan satu target nonbank, memakai structured screener
lebih dulu, lalu hanya mengambil endpoint tambahan jika field wajib tidak
tersedia. Proposal ini harus mencantumkan batas credit maksimum sebelum dijalankan.
