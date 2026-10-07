# Tampilan Bukti Perhitungan

Otorisasi user 6 Oktober 2026: URL evidence harus nyaman dibaca, kemudian commit
dan push. Status: implementasi dan verifikasi selesai; delivery Git menyusul.
Owner Intelligence; tidak mengubah formula/TTL.

Browser membuka halaman Inertia pada URL yang sama. `Accept: application/json`
tetap menerima kontrak JSON existing. Halaman menampilkan identitas, versi/waktu
bukti, total/kelengkapan, grafik, komponen, input dan rumus, peer serta eksklusi.
Hasil historis tidak dihitung ulang. Akses tetap login/verified dan rate limited.

Pembacaan hanya dari ScoreEvidence; tidak memakai endpoint Sectors atau credit.
Tidak ada migration, dependency baru, atau perubahan data/snapshot pengguna.
File Hackaton/LSP dan secret tidak masuk commit. Perubahan rilis/ambang60% yang
sebelumnya selesai ikut delivery setelah verifikasi.

Lihat [plan](plan.md) dan [tasks](tasks.md).

## Verifikasi 7 Oktober 2026

- Suite penuh: 197 test, 1.180 assertion lulus, termasuk empat test halaman bukti.
- Kontrak HTML/Inertia/JSON, 404, login/verifikasi email, no-store, serta tidak
  adanya panggilan HTTP/penambahan reservasi credit diperiksa.
- Typecheck, ESLint, Pint dan build client/SSR lulus. Audit npm/Composer bersih
  pada waktu pemeriksaan, termasuk patch transitif shell-quote 1.12.0.
- Browser Chrome terisolasi pada 1440x1000 dan 390x844: URL contoh menampilkan
  halaman HTML, grafik dengan bar terlihat, input/rumus, tabel peer dan JSON
  eksplisit tetap tersedia. Tidak ada overflow halaman, error console/page,
  permintaan scoring atau provider. Screenshot tersimpan lokal, tidak di Git.
- Bukti contoh BACA versi nusalens-v1.0.0 tetap menyimpan kelengkapan 65% dan
  alasan minimum 70% historis. Tidak dihitung ulang memakai kebijakan 60%.
- Akun QA, sesi akun QA dan credential sementara dihapus setelah pemeriksaan;
  akun pengguna, bukti, snapshot, usia cache dan ledger tidak diubah.
- Pemakaian credit Sectors untuk pekerjaan ini: 0. Hosting tidak disentuh.
