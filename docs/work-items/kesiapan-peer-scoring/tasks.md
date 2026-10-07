# Tasks: Kesiapan Peer dan Scoring

Catatan revisi 6 Oktober 2026: matriks 70% di bawah mencatat baseline lama.
Regresi ambang 60% dan formula v1.1.0 dicatat pada
[work item revisi](../../modules/Intelligence/work-items/ambang-kelengkapan-60/README.md).

Status3 Oktober 2026: implementasi diteruskan melalui
[Penuntasan MVP](../penuntasan-mvp/README.md) atas otorisasi menyeluruh user.
Audit historis tetap disimpan. Checklist di bawah membedakan implementasi yang
selesai, bukti live terbatas, dan validasi sumber yang belum selesai.

## Dokumentasi aktif, 2026-09-26 sampai 2026-09-27

- [x] Baca baseline scoring, data-flow, model data, arsitektur dan struktur.
- [x] Inventaris data source lokal dan gap; pisahkan dari validasi live.
- [x] Catat kebutuhan persistence bukti pada 3B, bukan hanya versi compare pada 5.
- [x] Tetapkan gate, ownership, credit/cache, acceptance dan increment end-to-end.
- [x] Pisahkan proposal UX-1 dari kesiapan peer/scoring.
- [x] Verifikasi tautan lokal, whitespace, dan scope diff sebelum handoff.
- [x] Catat audit awal dokumentasi resmi Sectors dan source lokal pada
  [Audit 3B.0](audit-3b0.md).

## 3B.0: Audit setelah persetujuan

- [x] Verifikasi dokumentasi resmi terbaru untuk endpoint kandidat dan biaya.
- [x] Verifikasi entitlement/projection/unit/periode pada probe AADI dan BBCA.
- [x] Klasifikasi dan populasi lengkap sektor AADI; pagination/partial diuji fake HTTP.
- [x] Audit annual ROE/ROA, quarterly YoY dan mapping risiko bank/nonbank.
- [x] Implementasi TTM/MRQ dari denominator eksplisit; test fake HTTP.
- [ ] Smoke live valuasi seluruh peer; tidak disamakan dengan valuasi historis.
- [ ] Audit basis harga/aksi korporasi dan tanggal 20 sesi antar-peer.
- [x] Hitung estimasi awal union request cold untuk kandidat endpoint resmi.
- [x] Cold fundamental AADI2 credit, warm0; retry/quota diuji fake HTTP.
- [x] Periksa ledger/budget; probe awal dibatasi6 credit dalam otorisasi user.
- [x] Catat hasil smoke sanitasi dan gap di work item Penuntasan MVP.
- [x] Contract, struktur, schema bukti dan precision dicatat sebelum implementasi.
- [x] Otorisasi penyelesaian user menggantikan gate persetujuan3B.1.

## 3B.1: Komponen end-to-end, selesai

- [x] Tetapkan komponen berdasarkan audit dan consumer contract nyata.
- [x] Test input, eligibility, peer dan hasil komponen.
- [x] Adapter/contract yang diperlukan, tanpa placeholder module lain.
- [x] Kalkulator pure PHP dan persistence immutable non-destruktif.
- [x] Bukti komponen pada cockpit; total ditahan bila belum cukup.
- [x] Unit/feature, lint, typecheck/build dan browser desktop/mobile.
- [x] Handoff bukti, biaya dan gap di Penuntasan MVP; review user terpisah.

## 3B.2: Paket v1, implementasi tersedia dengan gap sumber

- [x] Lengkapi komponen terverifikasi; alasan unavailable untuk sisanya.
- [x] Kelengkapan sebelum reweighting, total dan versi formula.
- [x] Uji rumus/peer/cache/kuota/replay; snapshot real tersimpan di MySQL.
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

## Handoff tahap dokumentasi 27 September (arsip, bukan status terkini)

- Verifikasi: 57 tautan lokal pada sembilan dokumen valid; pemeriksaan whitespace
  lulus. Perubahan hanya dokumentasi; test aplikasi tidak dijalankan ulang.
- Implementasi, audit provider live, migration, dan konsumsi credit belum dilakukan.
- Ketidakpastian utama: data/entitlement peer, basis harga, biaya populasi lengkap,
  dan rancangan persistence. Baseline diterima bukan bukti provider siap.
- Navigasi duplikat ditangani oleh UX-1 di work item induk; tidak menunggu 3B.
