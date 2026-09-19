# Project NusaLens

## Status

Baseline dokumentasi aktif, diselaraskan dengan rancangan NusaLens dan koreksi
user tanggal 2026-09-19. Implementasi aplikasi belum dimulai.

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

- Temukan Saham: screener/filter berdasarkan sektor, ukuran, kesehatan bisnis,
  pertumbuhan, harga saham, utang, dan Nilai Prioritas Riset.
- Detail Perusahaan: profil, lima komponen nilai, Nilai Prioritas Riset, dan
  penjelasan mengapa nilai terbentuk.
- Bandingkan: target awal 3 saham berdampingan; scope produk menyebut batas
  3-5 saham. Batas final ditetapkan pada work item Comparison.
- Jelaskan Nilai: rumus, bobot, data, posisi dibanding perusahaan sejenis, dan
  kontribusi komponen.
- Temukan Kandidat: kelompok kandidat seperti bisnis sehat, pertumbuhan menarik,
  harga relatif menarik, dan aktivitas pasar kuat.
- Ringkasan AI opsional setelah fase inti stabil.

## Alur pengguna dan istilah

1. Buka Temukan Saham, pilih sektor/subsektor dan kriteria terstruktur.
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

- Integrasi broker, eksekusi order, trading otomatis, dan rekomendasi
  BUY/HOLD/SELL.
- Akuntansi portofolio lengkap, billing/subscription, dan enterprise
  multi-tenant.
- Crypto, forex, backtesting kompleks, prediksi harga machine learning,
  scraping sentimen media sosial, dan multi-agent AI.
- Aplikasi mobile, Elasticsearch, Kafka, Kubernetes, dan MinIO.

## Stack dan constraint

- Laravel 12, PHP 8.4+, Inertia.js, React + TypeScript, MySQL, Redis,
  Tailwind CSS.
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

## Pertanyaan terbuka

- Strategi identifier final belum ditetapkan.
- Pilihan chart library final antara Apache ECharts atau Recharts belum
  dikunci.
- Strategi authentication belum ditetapkan untuk MVP awal.
- Batas final perbandingan, aturan rinci percentile, peer minimum, bobot metrik,
  dan kategori label belum ditetapkan; lihat [SCORING.md](SCORING.md).
- Schema, identifier, dan ownership persistence dirinci sebelum migration;
  konsep awal ada di [DATA-MODEL.md](DATA-MODEL.md).
- Source Laravel/Inertia belum dibuat, sehingga command project final belum
  tersedia.
