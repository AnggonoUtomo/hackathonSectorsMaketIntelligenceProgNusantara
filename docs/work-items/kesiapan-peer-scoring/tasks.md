# Tasks: Kesiapan Peer dan Scoring

## Dokumentasi aktif, 2026-09-26

- [x] Baca baseline scoring, data-flow, model data, arsitektur dan struktur.
- [x] Inventaris data source lokal dan gap; pisahkan dari validasi live.
- [x] Catat kebutuhan persistence bukti pada 3B, bukan hanya versi compare pada 5.
- [x] Tetapkan gate, ownership, credit/cache, acceptance dan increment end-to-end.
- [x] Pisahkan proposal UX-1 dari kesiapan peer/scoring.
- [x] Verifikasi tautan lokal, whitespace, dan scope diff sebelum handoff.

## 3B.0: Audit setelah persetujuan

- [ ] Verifikasi dokumentasi resmi terbaru, entitlement, field, unit dan periode.
- [ ] Buktikan klasifikasi lengkap, kompatibilitas bisnis dan pagination peer.
- [ ] Audit annual ROE/ROA, quarterly YoY, P/E TTM dan P/B MRQ.
- [ ] Audit CAR/NPL, DER utang berbunga/current ratio, dan kasus keuangan nonbank.
- [ ] Audit basis harga/aksi korporasi dan tanggal 20 sesi antar-peer.
- [ ] Hitung union request cold/warm/fallback serta biaya aktual dan retry.
- [ ] Periksa sisa ledger/budget; minta persetujuan scope smoke real terarah.
- [ ] Catat hasil smoke sanitasi atau gap; jangan menyebut audit live sudah lulus.
- [ ] Ajukan contract, struktur Intelligence, schema bukti dan precision untuk review.
- [ ] Dapatkan persetujuan implementasi 3B.1; jika tidak layak, laporkan hambatan.

## 3B.1: Satu komponen end-to-end, belum diotorisasi

- [ ] Tetapkan komponen berdasarkan hasil audit dan consumer contract nyata.
- [ ] Buat test gagal dahulu untuk input, eligibility, peer dan hasil komponen.
- [ ] Implementasi adapter/contract yang diperlukan, tanpa placeholder module lain.
- [ ] Implementasi kalkulator pure PHP dan persistence immutable non-destruktif.
- [ ] Hubungkan bukti komponen ke Ringkasan Riset; total ditahan bila belum cukup.
- [ ] Verifikasi unit/feature, lint, typecheck/build, dan browser responsif/keyboard.
- [ ] Handoff bukti, biaya, gap dan hasil uji pengguna sebelum increment berikutnya.

## 3B.2: Paket v1, belum diotorisasi

- [ ] Lengkapi komponen yang terverifikasi; catat alasan unavailable sisanya.
- [ ] Implementasi kelengkapan sebelum reweighting, total dan versi formula.
- [ ] Uji seluruh matriks di bawah dan reproduksi hasil dari snapshot MySQL.
- [ ] Uji pemahaman bukti/kelengkapan bersama user; handoff tanpa lanjut otomatis.

## Matriks pengujian wajib saat implementasi

Automated test memakai fake HTTP terisolasi; bukan panggilan API berbayar.

| Area | Kasus dan hasil yang harus dibuktikan |
| --- | --- |
| Validitas | Null, non-finite, denominator nol/negatif, unit/periode tidak cocok; negatif yang valid tetap dipakai. |
| Peer | Kurang dari lima peer lain, tepat lima, lebih dari lima, semua ties, duplikat symbol, target tidak dihitung dua kali. |
| Kelompok/periode | Hierarki benar, tidak campur jenis bisnis, periode terbaru dicoba dahulu, fallback maksimal satu periode. |
| Populasi | Pagination selesai, gagal sebagian, quota habis, filter pencarian berubah; tidak ada sampling terselubung. |
| Rumus | Average rank, arah percentile, bobot per metrik/komponen, outlier valid tidak dipotong. |
| Kelengkapan | Di bawah/tepat/di atas 70% sebelum pembulatan, satu metrik hilang, komponen kosong, semua kosong. |
| Jenis perusahaan | Risk bank vs industri; keuangan nonbank kehilangan 10 poin kelengkapan, bukan denominator baru. |
| Harga/growth | 21 closes, tanggal sama, corporate action tidak terbukti, QoQ tidak menggantikan YoY. |
| Cache/credit | Hit nol credit, fetched_at tidak berubah, stale sesuai baseline, deduplikasi/reservasi concurrent, error tidak menjadi empty. |
| Bukti | Snapshot immutable, input/config/peer berubah membuat versi baru, replay tanpa API, freshness pada reuse terkini. |
| Akses/UX | Login/verified, isolation data privat, loading/error/retry, bukti/tabel/grafik terbaca desktop/mobile dan keyboard. |

## Handoff tahap dokumentasi

- Verifikasi: 57 tautan lokal pada sembilan dokumen valid; pemeriksaan whitespace
  lulus. Perubahan hanya dokumentasi; test aplikasi tidak dijalankan ulang.
- Implementasi, audit provider live, migration, dan konsumsi credit belum dilakukan.
- Ketidakpastian utama: data/entitlement peer, basis harga, biaya populasi lengkap,
  dan rancangan persistence. Baseline diterima bukan bukti provider siap.
- Navigasi duplikat ditangani oleh UX-1 di work item induk; tidak menunggu 3B.
