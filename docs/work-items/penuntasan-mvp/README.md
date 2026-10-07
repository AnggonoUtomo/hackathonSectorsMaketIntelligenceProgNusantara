# Penuntasan MVP NusaLens

Hasil smoke di dokumen ini memakai formula v1.0.0 (ambang 70%) pada saat diuji.
Sejak 6 Oktober 2026, ambang aktif menjadi 60% pada v1.1.0; hasil historis tidak
ditulis ulang. Lihat [revisi ambang](../../modules/Intelligence/work-items/ambang-kelengkapan-60/README.md).

Otorisasi: user pada 2026-10-02 meminta penyelesaian MVP tanpa konfirmasi per
increment. Arsitektur, formula v1, batas credit, dan scope MVP tetap berlaku.
Menggantikan status menunggu persetujuan pada kesiapan-peer-scoring dan Compare 5.

Hasil: pencarian real -> cockpit -> bukti nilai/peer -> bandingkan -> snapshot
privat yang dapat dibuka, dinamai ulang, dihapus, dan diperbarui sebagai versi baru.
Data tidak sah tetap unavailable dengan alasan, tanpa angka contoh di alur aktif.

Lihat [plan](plan.md) dan [tasks](tasks.md). AI, billing dan BYOK tetap roadmap.

## Hasil dan verifikasi 3 Oktober 2026

- Implementasi dipush pada `1d83595` ke `origin/main`.
- Saat commit:177 test, 1.003 assertion lulus. Setelah review batas tahun:
  178 test, 1.006 assertion lulus; fake HTTP untuk suite otomatis.
- `npm run typecheck`, `npm run lint:check`, `npm run build:ssr`,
  `php vendor/bin/pint --test --dirty`, dan whitespace check lulus.
- MySQL lokal: migration comparison_snapshots dan intelligence_evidence telah
  dijalankan secara additive; tidak ada reset data.
- Browser Chromium/Playwright dengan akun QA sementara, aplikasi Laragon real/cache:
  login, dashboard, analisis AADI, grafik nonblank, bukti peer, pencarian kandidat,
  compare, snapshot terpercaya, versi2, rename, pencarian snapshot dan delete lulus.
  Viewport desktop1440 dan mobile390 diuji; tidak ada page overflow atau console error.
  Screenshot diperiksa di storage/framework/testing/mvp-*.png (tidak masuk Git).
- Arsitektur Domain pure PHP dan larangan import adapter pada Application diuji.
- Snapshot angka browser yang diubah tidak dipercaya; ownership lintas akun,
  replay immutable, retensi30 hari dan salinan historis diuji.
- Akun QA, credential file dan helper sementara sudah dibersihkan; screenshot
  tetap lokal. URL login Laragon memberi HTTP200 pada pemeriksaan terakhir.
- Lanjutan setelah push: sinkronisasi dokumen/task dan koreksi pemilihan tahun
  laporan agar Januari-April tetap mencoba tahun terakhir sebelum fallback.
  Regresi dibuktikan RED lalu GREEN. Perubahan lanjutan belum di-commit/push.

## Bukti sumber dan credit

Probe awal memakai6 credit terarah melalui adapter/ledger, bukan unduh seluruh IDX.
Structured projection terverifikasi untuk AADI/BBCA, termasuk rasio tahunan,
field bank/nonbank dan periode kuartalan eksplisit. Query symbol membutuhkan `.JK`;
projection `latest_close_date` ditolak, sehingga tidak dipakai.
Referensi sumber:
[Companies Screener](https://docs.sectors.app/api-references/v2/indonesia/screener/companies)
dan [Daily](https://docs.sectors.app/api-references/v2/indonesia/transaction/daily).

Adapter mengambil91 perusahaan sektor Energy lengkap untuk AADI dengan2 call cold
(identifikasi target + populasi). Cache hit tidak mengubah waktu sumber atau ledger.
Hasil smoke fundamental AADI: Quality83,33; Growth42,43; Risk64,29;
kelengkapan65,00%, total null. Growth memakaiQ2-2026 karenaQ3 belum valid.
Jumlah91 adalah populasi sumber, bukan91 peer valid setiap metrik.

Pembacaan ledger terakhir pada penutupan:136 credit reserved/committed dari1000,
sisa anggaran aplikasi864; bukan klaim saldo dashboard provider. Nilai mencakup
riwayat project dan pengujian manual. Kuota20/hari tidak dinaikkan, cadangan600
tidak diaktifkan. Test otomatis tidak memakai credit provider.

## Batas yang tetap eksplisit

- Valuasi peer lengkap diuji fake HTTP (TTM/MRQ, tanggal, preflight, freshness),
  belum smoke live seluruh populasi. Jika kuota tidak cukup, tidak ada sampling.
- Momentum real belum diberi angka karena basis aksi korporasi belum terbukti.
- Pengecualian usia data saat bursa tutup belum dipakai tanpa kalender terverifikasi.
- Alur terpandu ada pada cockpit/kandidat/compare, bukan wizard modal tambahan.
- AI, billing, BYOK, sharing publik dan watchlist tidak dibuat.
- Deployment publik, SMTP produksi, aktivasi scheduler, video dan submission resmi
  belum dilakukan. Jangan menyamakan hasil lokal dengan siap-submit tanpa review.
- File user `Hackaton/` dan `storage/framework/lsp-*.php` tidak disentuh/dipush.

Panduan pengguna: [USER-GUIDE](../../USER-GUIDE.md). Rencana pasca-MVP:
[ROADMAP](../../ROADMAP.md). Checklist submission: [SUBMISSION](../../SUBMISSION.md).
