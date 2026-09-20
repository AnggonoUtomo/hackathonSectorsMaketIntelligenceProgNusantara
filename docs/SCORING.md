# Spesifikasi Mesin Penilaian v1

## Status dan ownership

Keputusan MVP disetujui user pada 2026-09-20; kalkulator belum diimplementasikan.
Intelligence memiliki formula, hasil, bukti input/peer, dan versi konfigurasi.
Domain berupa pure PHP tanpa HTTP, Laravel, database, Redis, atau AI.
Application menyiapkan input internal melalui kontrak publik.

Nilai Prioritas Riset adalah hipotesis produk untuk riset, bukan prediktor profit,
penilaian keamanan investasi absolut, atau rekomendasi BUY/HOLD/SELL.

## Paket metrik final MVP

| Komponen | Bobot | Bank | Nonbank | Arah percentile |
| --- | --- | --- | --- | --- |
| Kesehatan Bisnis / Quality | 30% | ROE, ROA tahunan | Sama | Lebih tinggi |
| Pertumbuhan / Growth | 25% | Pertumbuhan pendapatan dan laba kuartalan YoY | Sama | Lebih tinggi |
| Harga Saham / Value | 20% | PE TTM, PB kuartal terbaru | Sama | Lebih rendah |
| Kekuatan Pasar / Market Strength | 15% | Perubahan harga 20 sesi bursa | Sama | Lebih tinggi |
| Keamanan Keuangan / Risk Quality | 10% | CAR tahunan, rasio NPL tahunan | DER dan current ratio tahunan, hanya perusahaan nonkeuangan | CAR/current ratio lebih tinggi; NPL/DER lebih rendah |

Bobot metrik setara di dalam setiap komponen: masing-masing 1/2 untuk komponen
dua metrik dan 1 untuk momentum. Arah tersebut adalah preferensi model v1,
bukan kesimpulan bahwa rasio selalu lebih baik tanpa batas secara ekonomi.
Bobot/formula dikonfigurasi dan diberi versi di kode, bukan editor bobot di UI.

Asuransi dan perusahaan keuangan nonbank tetap tercakup, tetapi tidak dipaksa
memakai DER/current ratio. Komponen risiko tidak tersedia dan mengurangi
kelengkapan 10 poin persentase; jangan menghapus bobot itu dari denominator
kelengkapan. Skor masih mungkin tersedia bila total kelengkapan >=70%.

Foreign flow, broker, forecast, PS, NIM, CASA, berita, dan filing tidak masuk
skor v1. Jangan menambahkan metrik atau endpoint hanya karena provider mendukungnya.

## Kontrak data dan validitas

Dokumentasi [Sectors Screener](https://docs.sectors.app/api-references/v2/indonesia/screener/companies)
mencantumkan field rasio tahunan, PE TTM/PB MRQ, data kuartalan, CAR, dan nominal
NPL/kredit bruto. Tersedianya field dalam dokumentasi bukan jaminan semua emiten
memiliki nilai valid. Adapter harus membuktikan mapping, unit, periode, dan basis.

- Pertumbuhan YoY: `(nilai_kuartal / nilai_kuartal_tahun_lalu - 1) * 100`.
  Pembanding harus positif; nol/negatif tidak dipaksakan menjadi persen normal.
  Nilai sekarang negatif dapat menghasilkan pertumbuhan negatif yang valid.
  Rugi/turnaround tetap ditampilkan sebagai konteks, bukan metrik pengganti.
- Rasio NPL: `non_performing_loan / gross_loan * 100`, dengan periode/basis sama
  dan kredit bruto positif. Jangan menyamakan allowance dengan NPL.
- ROE memerlukan ekuitas positif dan ROA aset positif. Kerugian dengan denominator
  positif bukan alasan otomatis menghapus rasio negatif.
- PE dengan laba <=0 dan PB dengan ekuitas <=0 tidak bermakna untuk ranking
  valuasi ini. Harga dan denominator harus valid. PE negatif bukan paling murah.
- DER memakai utang berbunga, bukan diam-diam total liabilitas; ekuitas harus
  positif. Current ratio memerlukan liabilitas lancar positif. Rasio bank
  memerlukan denominator positif, unit dan definisi yang terverifikasi.
- Null, non-finite, denominator tidak valid, periode tidak kompatibel, atau peer
  tidak cukup membuat metrik tidak tersedia, disertai alasan. Tidak semua angka
  negatif tidak valid. Outlier valid tetap digunakan, tanpa winsorization/clipping.

Momentum: `(close_t / close_t_minus_20_sessions - 1) * 100`, memerlukan 21
penutupan pada rentang 20 sesi dengan tanggal pembanding yang sama antar-peer,
harga positif, dan konsistensi aksi korporasi yang dapat diverifikasi. Data
tersedia pada [Daily Transaction Data](https://docs.sectors.app/api-references/v2/indonesia/transaction/daily),
tetapi dokumentasi tidak memastikan adjusted close. Jangan memakai seri mentah
yang konsistensinya belum terbukti; tandai metrik tidak tersedia. Bukan total
return termasuk dividen, bukan 20 hari kalender, dan bukan tick terakhir intraday.

## Peer dan periode

Urutan fallback kelompok paling sempit ke paling luas:

```text
Subindustry -> Industry -> Subsector -> Sector
```

Hierarki mengikuti helper resmi [Subindustries](https://docs.sectors.app/api-references/v2/indonesia/helper-list/subindustries),
[Industries](https://docs.sectors.app/api-references/v2/indonesia/helper-list/industries),
dan [Subsectors](https://docs.sectors.app/api-references/v2/indonesia/helper-list/subsectors).
Fallback hanya ke perusahaan yang secara bisnis dan basis metrik kompatibel;
jangan mencampur bank/nonbank, atau memaksa seluruh sektor menjadi peer relevan.

- Minimal 5 perusahaan LAIN yang valid per metrik, tidak termasuk target.
- Pakai semua peer valid di kelompok terpilih, bukan 5 pertama atau top 5.
- Filter screener pengguna hanya memilih kandidat tampilan, tidak mengubah peer.
- Kelompok dapat berbeda per metrik; simpan anggota, alasan fallback, periode,
  dan jumlah valid. Anggota yang gagal diambil karena budget bukan sampel sah
  untuk diam-diam dianggap seluruh kelompok.
- ROE/ROA dan risiko memakai tahun laporan yang sama; pertumbuhan memakai kuartal
  dan basis yang sama. PE TTM/PB MRQ memerlukan periode laporan serta tanggal
  harga yang selaras antar-peer. Jangan mengganti TTM dengan annual tanpa versi.
- Coba periode terbaru pada seluruh kelompok relevan terlebih dahulu. Hanya jika
  masih kurang, boleh mundur MAKSIMAL satu periode: satu kuartal untuk kuartalan,
  satu tahun untuk tahunan, kemudian ulangi fallback kelompok.
- Periode lama ditampilkan eksplisit. Kebijakan mundur periode bukan izin memakai
  cache kedaluwarsa atau mengubah tanggal harga. Jika tetap tidak cukup, unavailable.

## Percentile, kelengkapan, dan skor

Urutkan nilai mentah naik. Gunakan rank 1-based, target ikut populasi, sehingga
`N >= 6`. Ties menggunakan rata-rata posisi rank yang ditempati.

```text
p = 100 * (average_rank - 1) / (N - 1)
percentile = p untuk higher-is-better; 100 - p untuk lower-is-better
```

Ini konvensi normalisasi NusaLens, bukan satu-satunya definisi percentile.
Tanpa ties, ujung populasi bernilai 0 dan 100; semua sama bernilai 50.
UI menyebutnya "Posisi Dibanding Perusahaan Sejenis".

Definisikan `Wc` bobot komponen (jumlah 1), `wm` bobot awal metrik dalam komponen
(jumlah 1), dan `available_m` bernilai 1 hanya untuk metrik yang memiliki input
dan percentile valid. Kelengkapan dihitung SEBELUM reweighting:

```text
kelengkapan = 100 * sum_c(Wc * sum_m(wm * available_m))
nilai_komponen = sum_m(percentile_m * wm * available_m) / sum_m(wm * available_m)
nilai_total = sum_komponen_tersedia(Wc * nilai_komponen) / sum_komponen_tersedia(Wc)
```

Metrik hilang dikeluarkan, bobot tersedia dinormalisasi di dalam komponen.
Komponen tanpa metrik ditampilkan tidak tersedia, bukan nol. Bobot antar-komponen
dinormalisasi hanya bila suatu komponen kosong seluruhnya.

- Kelengkapan >=70%: total boleh ditampilkan walau ada komponen kosong.
- Kelengkapan <70%: tahan total, tampilkan data yang tersedia dan "Data belum cukup".
- Contoh: hanya komponen risiko 10% kosong, komponen lain lengkap -> kelengkapan
  90%, total dapat dihitung dari empat komponen dengan bobot dinormalisasi.
- Satu dari dua metrik Quality hilang mengurangi kelengkapan 15 poin persentase,
  bukan dianggap tetap 100% setelah reweighting.
- UI menampilkan 2 angka desimal, tanpa label tinggi/sedang/rendah. Kalkulasi,
  pengurutan, ambang 70%, dan identitas input memakai nilai sebelum pembulatan
  tampilan; tidak ada pembulatan antara tahap.

## Bukti, reuse, dan AI

Simpan hasil dan snapshot input immutable di MySQL: angka mentah/unit, periode,
tanggal pasar, fetched_at, validitas/alasan eksklusi, anggota peer dan nilainya,
rank/percentile, bobot awal/efektif, kontribusi, kelengkapan, dan versi formula.
Input berubah menghasilkan snapshot baru, bukan menimpa hasil historis.
Snapshot Company menyimpan fakta; bukti Intelligence menentukan fakta mana yang
dipakai dalam satu kalkulasi. Kontrak persistence ada di [DATA-MODEL.md](DATA-MODEL.md).

Hasil identik boleh dibagi lintas pengguna bila input/peer/config identik, tanpa
membagikan perbandingan pribadi. Reuse untuk hasil terkini hanya jika usia sumber
masih layak menurut [DATA-FLOW.md](DATA-FLOW.md). Retensi historis bukan freshness.

Research menerima hasil final. Penjelasan aturan selalu tersedia; AI hanya
merangkum bukti atas permintaan setelah inti stabil. AI tidak menentukan angka,
mengganti rumus, mengarang bukti, atau memberi BUY/HOLD/SELL.

## Gate implementasi

Paket metrik sudah disetujui, bukan hasil validasi live data. Sebelum kalkulator
dan adapter dikunci, buktikan periode/basis/unit, projection dan pagination
screener, data peer lengkap, konsistensi aksi korporasi, precision numerik, dan
biaya pengambilan. Ketidakcocokan nyata dilaporkan, bukan mengubah formula diam-diam.
Unit test menggunakan fixture/fake, mencakup kasus di [QUALITY.md](QUALITY.md).
