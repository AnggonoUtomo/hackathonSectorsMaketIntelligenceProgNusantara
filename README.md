# NusaLens

**Market intelligence untuk memahami saham Indonesia sebelum menelitinya lebih jauh.**

NusaLens membantu pengguna menyaring perusahaan, membandingkan saham sejenis,
dan memahami alasan di balik angka keuangan serta pergerakan pasar. Data dari
Sectors Financial API v2 diolah menjadi **Nilai Prioritas Riset**, disertai
metrik, kelompok pembanding, kelengkapan, dan tanggal sumbernya.

Bukan daftar saham yang harus dibeli. Tujuannya membantu menjawab:
**"Perusahaan mana yang layak saya teliti lebih lanjut, dan mengapa?"**

> Status 7 Oktober 2026: alur pencarian, cockpit, analisis peer, kandidat sejenis,
> perbandingan, dan riset tersimpan sudah terhubung ke backend/data real.
> Grafik memakai Recharts. Total nilai hanya muncul jika syarat data terpenuhi;
> data yang belum terverifikasi tidak diganti angka contoh.
> Persiapan rilis tersedia; aplikasi belum dipasang pada hosting publik.

## Yang sudah bisa dicoba

Panduan langkah demi langkah tersedia di [User Guide](docs/USER-GUIDE.md).

1. Login dengan email terverifikasi, lalu buka **Temukan Saham**.
2. Ketik nama atau kode perusahaan, lalu pilih hasil pencarian.
3. Pada detail, buka **Harga**, **Keuangan**, atau **Valuasi** sesuai kebutuhan.
4. Pilih rentang harga 30/90 hari atau kelompok angka keuangan. Tombol tabel
   menampilkan angka lengkap; tooltip grafik menampilkan nilai per periode.
5. Tekan **Analisis perusahaan sejenis**, lalu buka komponen untuk melihat
   nilai mentah, periode, peer, percentile, dan bobotnya.
   Tautan bukti membuka halaman berisi grafik, input dan rumus, bukan JSON mentah.
6. **Lihat kandidat sejenis** untuk menjelajahi bukti fundamental yang sama,
   atau **Bandingkan** untuk memilih maksimal tiga perusahaan.
7. Muat bagian yang diperlukan dan pilih **Simpan Snapshot**. Buka kembali
   lewat **Riset Tersimpan**; nama dapat diubah dan versi dapat dihapus.

Harga memakai rupiah; grafik keuangan memakai miliar rupiah dan tabelnya rupiah
lengkap. Valuasi memakai rasio kali (x), bukan label murah/mahal. Tanggal sumber
dan waktu pengambilan ditampilkan terpisah. Nilai tidak tersedia tidak diisi nol.

## Untuk siapa?

Investor ritel, peneliti saham pemula, dan analis yang ingin menyusun kandidat
riset berdasarkan bukti, tanpa harus menggabungkan banyak angka secara manual.
Cakupan MVP meliputi perusahaan bank dan nonbank di Indonesia.

## Fungsi utama

| Fitur MVP              | Manfaat                                                                             |
| ---------------------- | ----------------------------------------------------------------------------------- |
| Temukan Saham          | Menyaring dan mengurutkan kandidat melalui kriteria terstruktur.                    |
| Detail Perusahaan      | Melihat profil, metrik keuangan/pasar, lima komponen nilai, dan sumbernya.          |
| Bandingkan             | Menyandingkan maksimal 3 saham beserta angka asli dan posisi terhadap peer.         |
| Jelaskan Nilai         | Menelusuri rumus, bobot, kontribusi metrik, dan data yang belum tersedia.           |
| Temukan Kandidat       | Menjelajahi kandidat sejenis menurut kesehatan bisnis, pertumbuhan, dan keamanan keuangan dari bukti tersimpan. |
| Perbandingan Tersimpan | Menyimpan manual, memberi nama, membuka kembali, dan menghapus perbandingan privat. |

## Alur Riset

1. Daftar secara publik, verifikasi email, lalu login untuk mengakses fitur riset.
2. Buka **Temukan Saham**, pilih kelompok perusahaan dan kriteria pencarian.
3. Buka **Detail Perusahaan** untuk memeriksa angka, periode laporan, dan freshness.
4. Baca bukti pada **Analisis perusahaan sejenis**; jangan melihat total tanpa kelengkapannya.
5. Bandingkan maksimal **3 saham** untuk melihat perbedaan relatif.
6. Simpan perbandingan yang ingin ditinjau kembali. Hasil lama mempertahankan
   snapshot saat disimpan; **Buat versi terbaru**, lalu simpan, menghasilkan versi baru sesuai
   cache dan kuota, bukan menimpa sejarah.

Perbandingan tersimpan hanya dapat diakses pemilik. MVP tidak merekam seluruh
aktivitas secara otomatis dan belum menyediakan tautan berbagi publik.

## Bagaimana nilai dihitung?

Sistem menghitung lima komponen pada skala **0-100**. AI tidak menentukan nilainya.

| Komponen          | Bobot | Metrik MVP                                                                        |
| ----------------- | ----- | --------------------------------------------------------------------------------- |
| Kesehatan Bisnis  | 30%   | ROE dan ROA tahunan.                                                              |
| Pertumbuhan       | 25%   | Pertumbuhan pendapatan dan laba kuartalan dibanding kuartal yang sama tahun lalu. |
| Harga Saham       | 20%   | PE TTM dan PB berdasarkan kuartal terbaru.                                        |
| Kekuatan Pasar    | 15%   | Perubahan harga selama 20 sesi bursa, dengan konsistensi harga terverifikasi.     |
| Keamanan Keuangan | 10%   | Bank: CAR dan NPL. Perusahaan nonkeuangan: DER dan current ratio.                 |

Perusahaan keuangan nonbank, seperti asuransi, tidak dipaksakan memakai rasio
risiko perusahaan nonkeuangan. Komponen risiko belum tersedia untuk kelompok
ini dan mengurangi kelengkapan, bukan diisi nol.

### 1. Bandingkan dengan perusahaan sejenis

Setiap metrik dibandingkan dengan **minimal 5 perusahaan lain** yang datanya
valid, memakai semua peer valid dalam kelompok terpilih. Kelompok diperluas
dari subindustri, industri, subsektor, lalu sektor hanya bila masih relevan.
Bank tidak dicampur nonbank. Filter pencarian pengguna tidak mengubah peer.

Periode dan basis perhitungan harus sebanding. Sistem memprioritaskan periode
terbaru, dengan fallback maksimal satu periode sebelumnya bila diperlukan,
dan menampilkan periode yang dipakai.

### 2. Ubah posisi relatif menjadi percentile

Dengan target ikut populasi, `N` adalah jumlah seluruh perusahaan valid dan
`rank` posisi berurutan dari nilai mentah terkecil:

```text
percentile = 100 * (rank - 1) / (N - 1)
```

Nilai sama memakai rata-rata rank. Untuk metrik yang dalam model v1 dinilai
lebih baik jika lebih rendah, yaitu PE, PB, DER, dan NPL, hasil dibalik menjadi
`100 - percentile`. Semua nilai sama menghasilkan percentile 50.

Ini konvensi NusaLens untuk posisi relatif, bukan peluang untung atau probabilitas
investasi aman. Dua perusahaan dari kelompok peer berbeda tidak otomatis
sebanding hanya karena total skornya sama.

### 3. Gabungkan komponen

Metrik dalam satu komponen memiliki bobot setara. Jika seluruh komponen tersedia:

```text
Nilai Prioritas Riset =
  (Kesehatan Bisnis x 30%) + (Pertumbuhan x 25%)
  + (Harga Saham x 20%) + (Kekuatan Pasar x 15%)
  + (Keamanan Keuangan x 10%)
```

**Contoh ilustrasi, bukan data saham aktual:**

```text
(80 x 30%) + (70 x 25%) + (60 x 20%) + (50 x 15%) + (90 x 10%)
= 70,00
```

### 4. Periksa kelengkapan sebelum menampilkan total

Data hilang atau rasio tidak bermakna dikeluarkan, bukan diganti nol. Bobot
metrik tersedia disesuaikan di dalam komponen; bila seluruh komponen kosong,
bobot antar-komponen yang tersisa dinormalisasi.

Kelengkapan dihitung dari **bobot awal sebelum penyesuaian**, bukan jumlah field.
Total hanya ditampilkan bila kelengkapan **minimal 60%**. Contohnya, komponen
risiko 10% kosong dan komponen lainnya lengkap berarti kelengkapan 90%.
Di bawah ambang, tampilkan **Data belum cukup** dan data yang tersedia.

Angka ditampilkan dengan **2 desimal**, sementara perhitungan dan pengurutan
menggunakan nilai sebelum pembulatan tampilan. Tidak ada label tinggi/sedang/rendah.
Rasio PE negatif akibat rugi tidak diperlakukan sebagai saham paling murah.
Spesifikasi lengkap: [Perhitungan v1](docs/SCORING.md).

## Data, keterbatasan, dan penjelasan

- Tanggal data pasar, periode laporan, kelengkapan, dan status data lama terlihat.
- Cache mengurangi panggilan API; pembaruan hanya saat data dibutuhkan.
- Scoring memakai fundamental dengan cache 24 jam. Valuasi peer bersifat
  opsional, memakai harga dengan cache 1 jam dan batas usia observasi 24 jam.
  Pengecualian bursa tutup belum dipakai karena kalender/basis waktunya belum terbukti.
- Data tidak tersedia atau budget habis tidak disamarkan sebagai hasil kosong.
- Budget MVP: 1.000 credit Sectors sekali pakai dan 20 credit per akun per hari,
  reset 00.00 WIB. Membaca cache tidak memakai credit; cadangan 600 credit tidak
  otomatis dipakai. Aturan ini belum merupakan paket komersial.
- Penjelasan berbasis aturan selalu menjadi bagian inti. AI hanya pelengkap
  opsional atas permintaan, menjelaskan bukti yang sudah dihitung.

**Keterbatasan yang terlihat dalam aplikasi:** momentum 20 sesi belum diaktifkan
pada sumber real karena konsistensi aksi korporasi belum terbukti. Valuasi peer
memerlukan data pasar seluruh kelompok; jika kuota tidak cukup, fundamental
tetap tersedia tanpa memotong sampel. Pada smoke lama AADI, kelengkapan fundamental
65,00% membuat total ditahan oleh formula v1.0.0. Sejak 6 Oktober 2026, formula
v1.1.0 memakai ambang 60% sehingga analisis baru dengan kelengkapan 65,00%
memenuhi syarat total. Snapshot lama tetap utuh; jalankan analisis baru untuk
memakai aturan terbaru. Penurunan ambang tidak membuat data yang hilang menjadi lengkap.
Tab Valuasi historis juga tidak otomatis menjadi input PE TTM/PB MRQ.

## Pengembangan berikutnya

| Tahap           | Rencana                                                                                   | Status                                                         |
| --------------- | ----------------------------------------------------------------------------------------- | -------------------------------------------------------------- |
| Fondasi MVP     | Verifikasi email, ULID, MySQL, cache dan ledger credit. | Terimplementasi dan diuji. |
| Alur riset inti | Pencarian, detail, skor berbukti, kandidat fundamental, compare dan snapshot privat. | Terimplementasi; ketersediaan metrik mengikuti syarat sumber dan kuota. |
| Kelengkapan sumber | Basis harga adjusted, kalender bursa dan cakupan live valuasi peer. | Masih perlu bukti provider; tidak mengubah formula agar total muncul. |
| Pelengkap demo  | AI explainer setelah alur inti stabil.                                                    | Opsional; provider, model, dan budget belum dipilih.           |
| Pasca-MVP       | Paket berbayar dan/atau API key milik pengguna sendiri (BYOK).                            | Arah pengembangan; belum ada desain paket, harga, atau jadwal. |

Watchlist, riwayat screener otomatis, laporan tersimpan terpisah, dan sharing
publik **di luar MVP dan belum menjadi komitmen pengembangan**. Rencana rinci,
prasyarat, dan batasnya ada pada [Roadmap](docs/ROADMAP.md).

## Menjalankan dan berkontribusi

Fondasi menggunakan Laravel 12, Inertia React, dan MySQL. Panduan instalasi,
konfigurasi lokal, dan command ada di [Setup Environment](docs/ENVIRONMENT.md);
pemeriksaan ada di [Quality](docs/QUALITY.md). Detail teknis sengaja dipisahkan
dari pengantar produk ini.

Mulai dari [indeks dokumentasi](docs/README.md) dan [keputusan aktif](docs/DECISIONS.md)
sebelum mengembangkan fitur. Arsitektur dan pola kerja tetap mengikuti dokumen
NusaLens, tanpa menganggap seluruh target sudah tersedia di source.

Hasil pekerjaan, batas data, dan verifikasi tersedia di
[Penuntasan MVP](docs/work-items/penuntasan-mvp/README.md).
Untuk publikasi, gunakan [Panduan Deployment](docs/DEPLOYMENT.md) dan
[Kesiapan Publish](docs/work-items/kesiapan-publish/README.md). Domain, HTTPS,
SMTP nyata dan pemeriksaan server masih harus diselesaikan setelah hosting ada.

## Disclaimer

NusaLens menyediakan informasi dan analisis untuk tujuan riset, bukan nasihat
investasi atau rekomendasi membeli, menjual, maupun menahan instrumen keuangan.
Nilai merupakan hipotesis model yang dapat memiliki keterbatasan data.
Keputusan investasi tetap menjadi tanggung jawab pengguna.
