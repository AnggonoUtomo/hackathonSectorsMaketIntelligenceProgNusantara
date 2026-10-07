# Ambang Kelengkapan Berbobot 60%

Otorisasi user 6 Oktober 2026: turunkan kelengkapan minimum dari 70% menjadi 60%.
Status: selesai dan terverifikasi lokal. Owner: Intelligence. Arsitektur dan bobot komponen tetap.

Formula baru `nusalens-v1.1.0` menampilkan total pada kelengkapan >=60% sebelum
pembulatan; di bawahnya tetap unavailable, bukan nol. Minimum lima peer lain,
validitas input, normalisasi bobot dan dua desimal tampilan tidak berubah.
Ini perubahan kebijakan produk, bukan klaim statistik cakupan semua emiten IDX.

Tidak ada endpoint Sectors baru, perubahan TTL atau migration. Perhitungan
ulang memakai cache sesuai usia sumber; versi baru memisahkan fingerprint bukti.
Bukti/snapshot lama tetap immutable. Test fake HTTP, pemakaian credit real nol.

Lihat [plan](plan.md), [tasks](tasks.md), dan [Scoring](../../../../SCORING.md).

## Verifikasi 6 Oktober 2026

- RED: 7 kasus gagal pada formula lama, termasuk total 60%/65% dan versi bukti.
- GREEN: 19 tes ResearchPriority, 139 assertion; seluruh suite 193 tes,
  1.131 assertion lulus, termasuk consumer Comparison dan privasi snapshot.
- Bukti formula lama dan snapshot privat sama persis sebelum/sesudah hitung;
  formula baru menghasilkan ID berbeda, lalu reuse ID yang sama untuk input identik.
- Cache input tetap dipakai tanpa tambahan HTTP/ledger pada kalkulasi ulang;
  fetchedAt/expiresAt sumber tidak berubah. Test memakai fake HTTP.
- Pint seluruh source, `git diff --check`, dan link internal 106 Markdown lulus.
- Tidak mengubah frontend: panel membaca score/reason/formulaVersion backend,
  sehingga alasan unavailable otomatis menyebut 60%. Build/browser tidak diulang.
- Tidak ada panggilan real, migration, penghapusan cache, atau perubahan snapshot
  pengguna. Tidak melakukan commit/push; perubahan rilis terdahulu tetap utuh.

Untuk memakai kebijakan baru pada aplikasi, muat/perbarui analisis aktif.
Hasil tersimpan lama tetap historis; buat versi baru untuk perbandingan terbaru.
Pengambilan real saat pengguna memperbarui tetap mengikuti TTL dan kuota normal.

## Delivery 7 Oktober 2026

Perubahan dikomit sebagai `f5d6a5e` dan berhasil di-push ke `origin/main` bersama
pekerjaan rilis dan tampilan bukti. Suite gabungan 197 test / 1.180 assertion
lulus. Pernyataan belum commit/push di verifikasi 6 Oktober adalah riwayat tahap
tersebut, bukan status delivery sekarang.
