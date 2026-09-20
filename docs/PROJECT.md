# Project NusaLens

## Status

Starter Laravel 12/Inertia React sudah tersedia, termasuk auth/settings dan
toolchain. Module bisnis NusaLens belum dibuat. Keputusan MVP disetujui user
pada 2026-09-20; ini target implementasi, bukan klaim fitur sudah berjalan.

## Masalah

Pengguna harus membaca banyak angka keuangan dan data pasar sebelum dapat
memilih perusahaan mana yang ingin dianalisis lebih dalam.

## Tujuan

NusaLens menyederhanakan riset awal saham Indonesia dengan menyaring data,
membandingkan perusahaan sejenis, menghitung lima aspek penilaian, dan
menghasilkan **Nilai Prioritas Riset**.

```text
Data Sectors -> disaring -> dibandingkan dengan perusahaan sejenis
  -> dinilai pada lima aspek -> Nilai Prioritas Riset -> penjelasan berbukti
```

Nilai tambah track Market Intelligence adalah pengolahan dan penjelasan data,
bukan hanya menampilkan response API. NusaLens menguji kemampuan riset awal
yang terfokus, bukan menggantikan platform trading intelligence yang lebih luas.

## Pengguna

- Investor ritel yang ingin belajar dan melakukan riset sendiri.
- Peneliti saham pemula.
- Analis yang membutuhkan daftar kandidat dengan cepat.
- Pengguna yang ingin membandingkan saham dalam kelompok bisnis yang sama.

## Scope aktif MVP

- Registrasi terbuka untuk umum. Semua fitur riset memerlukan login dan email
  terverifikasi; halaman auth/verifikasi tetap dapat digunakan sesuai alurnya.
- Bank dan nonbank tercakup sesuai kelayakan metrik pada [SCORING.md](SCORING.md).
- Temukan Saham: screener/filter berdasarkan sektor, ukuran, kesehatan bisnis,
  pertumbuhan, harga saham, utang, dan Nilai Prioritas Riset.
- Detail Perusahaan: profil, lima komponen nilai, Nilai Prioritas Riset, dan
  penjelasan mengapa nilai terbentuk.
- Bandingkan: maksimal 3 saham berdampingan.
- Simpan perbandingan secara manual, beri nama, buka kembali, dan hapus; hanya
  pemilik yang dapat mengakses. Hasil tersimpan memakai snapshot saat disimpan,
  bukan diam-diam diganti data terbaru. "Perbarui data" menghasilkan versi baru,
  tetap mengikuti cache, kuota, dan budget. Tidak otomatis merekam aktivitas.
- Jelaskan Nilai: rumus, bobot, data, posisi dibanding perusahaan sejenis, dan
  kontribusi komponen.
- Temukan Kandidat: kelompok kandidat seperti bisnis sehat, pertumbuhan menarik,
  harga relatif menarik, dan aktivitas pasar kuat.
- Penjelasan berbasis aturan wajib. Ringkasan AI opsional setelah inti stabil,
  atas permintaan pengguna, dengan fallback ke penjelasan aturan. Demo tidak
  bergantung AI; provider/model/budget ditentukan sebelum integrasi.

## Alur pengguna dan istilah

1. Login dengan email terverifikasi, lalu buka Temukan Saham dan pilih kriteria.
2. Telusuri shortlist dengan pagination; filter tersimpan di URL.
3. Urutkan berdasarkan Nilai Prioritas Riset setelah scoring tersedia.
4. Buka Detail Perusahaan untuk profil, nilai, data pendukung, dan peer context.
5. Buka "Mengapa Nilainya Seperti Ini?" untuk rumus, bobot, dan kontribusi metrik.
6. Bandingkan perusahaan sejenis untuk melihat kelebihan/kekurangan relatif.

| Istilah teknis | Bahasa UI | Pertanyaan yang dijawab |
| --- | --- | --- |
| Quality / Financial Quality | Kesehatan Bisnis | Seberapa baik laba, modal, aset, dan arus kas dikelola? |
| Growth | Pertumbuhan | Apakah bisnis sedang berkembang? |
| Value / Valuation | Harga Saham | Bagaimana harga relatif terhadap perusahaan sejenis? |
| Market Strength | Kekuatan Pasar | Bagaimana pergerakan harga dan aktivitas pasar? |
| Risk Quality | Keamanan Keuangan | Seberapa sehat utang dan kemampuan memenuhi kewajiban? |
| Research Priority Score | Nilai Prioritas Riset | Perusahaan mana yang layak dipelajari lebih lanjut? |
| Peer Comparison | Perbandingan dengan Perusahaan Sejenis | Bagaimana posisi relatif perusahaan? |
| Percentile | Posisi Dibanding Perusahaan Sejenis | Di mana posisi metrik dalam kelompok pembanding? |
| Explainability | Penjelasan Nilai | Data apa yang membentuk hasil penilaian? |

UI memakai istilah Kandidat Riset; hindari Strong Buy, Buy Now, Guaranteed
Return, Target Profit, atau Recommended Investment. Contoh angka/label pada
konsep awal hanya ilustrasi, bukan ambang kategori atau formula final.

## Di luar scope MVP

- Watchlist, riwayat screener otomatis, laporan riset tersimpan terpisah, dan
  tautan berbagi publik. Modul Research tetap menjelaskan skor tanpa harus
  membuat persistence laporan sendiri.
- Paket berbayar dan API key milik pengguna (BYOK) adalah arah masa depan,
  bukan scope saat ini; perlu kajian entitlement, secret, dan sharing data kelak.
- Integrasi broker, eksekusi order, trading otomatis, dan rekomendasi
  BUY/HOLD/SELL.
- Akuntansi portofolio lengkap, billing/subscription, dan enterprise
  multi-tenant.
- Crypto, forex, backtesting kompleks, prediksi harga machine learning,
  scraping sentimen media sosial, dan multi-agent AI.
- Aplikasi mobile, Elasticsearch, Kafka, Kubernetes, dan MinIO.

## Stack dan constraint

- Laravel 12, PHP 8.4+, Inertia.js, React + TypeScript, MySQL, Redis,
  Tailwind CSS, dan Recharts.
- ULID untuk entitas termasuk users serta foreign key terkait; migrasi dari
  starter perlu rencana aman, bukan izin menghapus data.
- Sectors Financial API v2 adalah sumber data inti.
- Sectors API key hanya boleh berada di backend.
- Automated test menggunakan fake HTTP, bukan real Sectors API.
- Credit API harus dihemat; data diambil bertahap sesuai kebutuhan pengguna.
- Module langsung di `app/Modules/{Module}` dengan DDD-lite Modular Monolith
  dan Hexagonal Architecture, mengikuti rancangan NusaLens.
- PHP 8.4+ adalah target runtime yang dipertahankan dari rancangan awal.

## Kriteria keberhasilan

- [ ] Halaman Temukan Saham berjalan end-to-end.
- [ ] Halaman Detail Perusahaan menampilkan lima komponen nilai dan alasan.
- [ ] Fitur Bandingkan berjalan untuk beberapa saham sejenis.
- [ ] Nilai Prioritas Riset dihitung oleh sistem dan dapat dijelaskan.
- [ ] Sectors API menjadi sumber data inti tanpa membocorkan API key.
- [ ] Cache dan batas penggunaan API credit dipikirkan pada setiap use case.
- [ ] Tidak ada rekomendasi investasi atau klaim BUY/HOLD/SELL.
- [ ] Test penting untuk scoring dan integrasi provider tersedia.

## Batas implementasi berikutnya

Keputusan produk di atas sudah ditutup; jangan mewawancarai ulang tanpa konflik
baru. [DECISIONS.md](DECISIONS.md) mengindeks keputusan dan bukti persetujuan.
Spesifikasi teknis per increment masih harus memvalidasi kontrak data Sectors,
kelengkapan aktual, periode/basis rasio, aksi korporasi dan kalender bursa,
estimasi credit seluruh peer, precision/index/FK, serta migrasi users.
Pilihan provider/model/budget AI ditunda sampai integrasi opsional diperlukan.
Ini bukan izin memulai coding, memasang dependency, atau melakukan Git delivery.
