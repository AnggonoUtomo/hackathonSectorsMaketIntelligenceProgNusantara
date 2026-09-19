# Spesifikasi Mesin Penilaian

## Tujuan dan ownership

Module Intelligence menghasilkan Nilai Prioritas Riset yang deterministik,
dapat dijelaskan, dan memperhatikan kelompok perusahaan sejenis. Kalkulator
berupa pure PHP tanpa HTTP, Laravel, database, Redis, atau AI. Application
menyiapkan input internal; Domain melakukan perhitungan.

Konsep penting: `MetricValue`, `Percentile`, `Score`, `ScoreComponent`,
`ScoreWeight`, `ScoreBreakdown`, dan `ResearchPriority`.

## Lima komponen

| Komponen UI | Istilah teknis | Bobot awal | Kandidat metrik dari rancangan |
| --- | --- | --- | --- |
| Kesehatan Bisnis | Quality | 30% | ROE, ROA, net/gross profit margin, kualitas arus kas. |
| Pertumbuhan | Growth | 25% | Pertumbuhan pendapatan/laba kuartalan YoY, pendapatan tahunan, EPS, forecast jika tersedia. |
| Harga Saham | Value / Valuation | 20% | PE, PB, PS, perbandingan valuasi terhadap peer. |
| Kekuatan Pasar | Market Strength | 15% | Perubahan harga sekitar 30 hari, momentum relatif, aktivitas perdagangan, foreign flow; broker opsional. |
| Keamanan Keuangan | Risk Quality | 10% | DER, DAR, current ratio; drawdown/volatility jika dipakai. |

Konsep awal juga memberi contoh harga 90 hari, posisi terhadap harga tertinggi,
volume, kemampuan membayar bunga, dan arus kas terhadap utang. Seluruhnya masih
kandidat metrik, bukan kewajiban mengambil semua endpoint. Pilih metrik
berdasarkan data yang tersedia dan kebutuhan demo, lalu dokumentasikan sebelum
mengubah formula. Berita/filing tidak masuk nilai utama MVP.

Semua nilai menggunakan skala `0..100`. Bobot komponen berjumlah 100% dan
merupakan hipotesis produk awal yang perlu dapat dikonfigurasi.

```text
NilaiPrioritasRiset = jumlah(nilai_komponen x bobot_komponen)

Contoh ketika seluruh komponen tersedia:
90 x 0.30 + 80 x 0.25 + 60 x 0.20 + 75 x 0.15 + 85 x 0.10 = 78.75
```

Contoh tersebut ilustrasi perhitungan, bukan data saham aktual. Nilai tinggi
menunjukkan prioritas untuk riset lebih lanjut, bukan perintah membeli.

## Kelompok pembanding

Jangan membandingkan angka mentah lintas industri tanpa konteks. Bank dibandingkan
dengan perusahaan bank sejenis. Urutan kelompok dalam konsep NusaLens:

```text
Subsektor -> Industri -> Sektor
```

Subsektor adalah prioritas. Ketersediaan klasifikasi industri dan pemicu fallback
harus diperiksa saat integrasi. Sumber belum menetapkan jumlah minimum peer,
formula percentile, atau perlakuan nilai sama; jangan mengunci angka itu lewat
asumsi. UI menyebut percentile sebagai **Posisi Dibanding Perusahaan Sejenis**.

## Strategi industri

Bank tidak selalu cocok dinilai dengan rasio perusahaan umum. Kandidat metrik
bank mencakup NIM, LDR, CAR, CASA, pertumbuhan kredit/deposito, dan kualitas aset
jika tersedia. Rancangan menyediakan konsep berikut bila dibutuhkan:

```php
interface ScoreStrategy
{
    public function supports(CompanyContext $company): bool;
    public function calculate(ScoreInput $input): ScoreBreakdown;
}
```

`GeneralCorporateScoreStrategy` dan `BankScoreStrategy` adalah kandidat strategi,
bukan class yang sudah dibuat. Gunakan hanya strategi yang benar-benar
dibutuhkan demo dan didukung data; setiap pemilihan strategi harus dapat diuji.

## Data hilang

- Jangan mengubah data yang tidak tersedia menjadi angka nol.
- Keluarkan metrik yang hilang dari perhitungan dan hitung ulang bobot metrik
  yang tersedia.
- Tampilkan kelengkapan data agar pengguna memahami cakupan hasil.
- Kebijakan saat seluruh metrik/komponen kosong dan ambang kelayakan nilai
  masih perlu ditetapkan sebelum implementasi; jangan mengarang skor fallback.

## Bukti pembentuk nilai

Setiap metrik menyertakan nama, angka asli, percentile, bobot, kontribusi,
kelompok pembanding, dan waktu data diambil. Contoh bentuk data, bukan contract
JSON final:

```json
{
  "metric": "roe_ttm",
  "raw": 24.0,
  "percentile": 92,
  "weight": 0.35,
  "contribution": 32.2,
  "peer_group": "Banks",
  "fetched_at": "2026-09-19T00:00:00Z"
}
```

Bobot `0.35` pada contoh adalah bobot metrik dalam komponen, bukan bobot
Kesehatan Bisnis terhadap nilai akhir. UI menampilkan maknanya dengan bahasa
sederhana serta rumus, bobot, data, dan kontribusi yang dapat diperiksa.

## AI dan verifikasi

Research menerima `ScoreBreakdown` yang sudah selesai. AI opsional hanya
menjelaskan bukti tersebut, tidak menentukan nilai, mengganti formula,
memberikan BUY/HOLD/SELL, atau menambah klaim tanpa data. Penjelasan berbasis
aturan tetap harus tersedia pada alur inti.

Unit test mencakup data lengkap/hilang, reweighting, nilai ekstrem, pertumbuhan
negatif, peer kosong/terlalu sedikit, dan strategi industri yang digunakan.
Rincian QA ada di [QUALITY.md](QUALITY.md).

## Keputusan sebelum implementasi

- Daftar metrik final, arah baik/buruk setiap rasio, normalisasi, dan bobotnya.
- Definisi percentile, ties, outlier, peer minimum, dan fallback klasifikasi.
- Perlakuan data tidak valid, rasio yang tidak bermakna, komponen kosong,
  kelengkapan minimum, serta rumus persentase kelengkapan.
- Pembulatan nilai, ambang label UI, strategi industri MVP, dan penyimpanan
  identitas konfigurasi agar perhitungan dapat diulang.

Ini adalah batas spesifikasi sumber yang perlu diputuskan dalam work item
Intelligence, bukan perubahan formula yang sudah diterapkan.
