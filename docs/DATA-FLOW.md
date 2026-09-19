# Alur Data dan Anggaran Credit

## Pengambilan bertahap

| Tingkat | Data | Kapan digunakan |
| --- | --- | --- |
| 1: pencarian hemat | Companies Screener dan konteks subsektor. | Menyaring universe menjadi shortlist. |
| 2: kandidat terpilih | Section Company Report dan konteks peer yang diperlukan scoring. | Memperkaya kandidat yang benar-benar dibutuhkan. |
| 3: analisis mendalam | Keuangan kuartalan, harga harian, foreign flow; broker, berita/filing bila perlu. | Saat Detail Perusahaan atau Bandingkan dibuka. |

Jangan mengunduh semua data IDX sekaligus. Endpoint dan section prioritas
dicatat pada [API.md](API.md). Berita/filing adalah bukti tambahan dan tidak
masuk nilai utama MVP.

## Alur request

```text
UI -> Application use case -> kontrak data -> cek cache
  hit dan masih layak: gunakan data internal dari cache
  miss: adapter Sectors -> DTO vendor -> map + validate -> model internal
        -> simpan snapshot bila perlu -> isi cache
  -> hitung nilai / kirim response
```

Implementasi Redis, HTTP, dan persistence MySQL berada di Infrastructure.
Application mengatur proses; Domain menerima data internal untuk menghitung.
Snapshot hanya disimpan bila diperlukan, sesuai [DATA-MODEL.md](DATA-MODEL.md).

## TTL awal

| Data | TTL |
| --- | --- |
| Company overview | 24 jam |
| Financial report | 12 jam |
| Subsector context | 12 jam |
| Quarterly financial | 24 jam |
| Daily market | 30 menit-2 jam |
| Foreign flow | 1-6 jam |
| Broker summary | 1-6 jam |
| News/filings | 15 menit-1 jam |

Ini kebijakan awal internal, bukan janji freshness provider. TTL final dan
cache key mengikuti sifat data serta parameter query/section/rentang waktu
yang benar-benar dipakai. Tampilkan waktu pengambilan pada UI penting.
Data cache lama harus ditandai; jangan mencampurnya dengan data baru tanpa
keterangan atau menganggap kegagalan provider sebagai hasil kosong.

## Target credit internal

| Use case | Saat cache kosong |
| --- | --- |
| Discover | <= 5 credits |
| Company Lens | <= 10 credits |
| Compare 3 tickers | <= 20 credits |

Dengan cache yang masih layak, upayakan mendekati nol panggilan upstream.
Angka tersebut target NusaLens, bukan tarif resmi Sectors. Biaya endpoint,
section, rate limit, dan field aktual harus diperiksa pada dokumentasi resmi
saat implementasi. Catat estimasi penggunaan per use case agar bisa dievaluasi.

## Sebelum menambah API call

1. Tentukan endpoint dan data yang diperlukan fitur.
2. Perkirakan frekuensi dan credit pemanggilan.
3. Tentukan cache dan freshness yang cukup.
4. Periksa apakah endpoint yang lebih hemat memenuhi kebutuhan yang sama.
5. Tambahkan verifikasi fake HTTP, termasuk cache hit/miss dan error.

Hindari panggilan setiap React render, seluruh section report secara default,
riwayat harian seluruh IDX untuk shortlist, background refresh terlalu sering,
dan natural-language screener ketika structured query sudah cukup.
