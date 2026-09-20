# Model Data dan Ownership MVP

## Status

Ownership, ULID, dan kebijakan snapshot disetujui pada 2026-09-20. MySQL adalah
database target. Nama tabel berikut tetap konseptual, bukan migration atau tabel
bisnis yang sudah tersedia. Persetujuan dokumentasi tidak mengizinkan migrasi
data atau perubahan source pada pekerjaan ini.

## Ownership

| Konsep | Owner | Kegunaan/status MVP |
| --- | --- | --- |
| `companies` | Company | Identitas, profil, sektor/subsektor/industri/subindustri. |
| `financial_snapshots`, `market_snapshots` | Company | Fakta keuangan/pasar yang telah dinormalisasi dan metadata sumber. |
| `scores`, `score_components`, snapshot bukti input/peer | Intelligence | Hasil immutable, versi formula, dan bukti reproduksi. |
| `api_cache_metadata`, ledger/reservasi credit | MarketData | Freshness, konsumsi credit, reservasi global dan per akun. |
| `comparisons` dan versi/item terkait | Comparison | Perbandingan tersimpan manual, privat, maksimal 3 saham. |
| Kriteria/hasil screener | Screening | Pemrosesan pencarian; tidak membuat riwayat otomatis MVP. |
| Penjelasan/laporan | Research | Penjelasan aturan/AI; laporan tersimpan terpisah bukan MVP. |

Company memiliki fakta perusahaan. Intelligence memiliki bukti fakta mana yang
dipakai pada kalkulasi tertentu, termasuk seluruh anggota peer dan input metrik,
bobot, validitas, periode, serta versi formula. Referensi immutable boleh
mengurangi duplikasi hanya jika bukti tetap lengkap dan dapat direproduksi.
Jangan menyimpan seluruh JSON vendor tanpa kebutuhan perhitungan yang nyata.

Antar module mengakses contract/DTO atau read model publik yang benar-benar
dibutuhkan. Jangan mengimpor model privat atau melakukan query langsung ke tabel
module lain untuk melewati ownership. Persistence model berada di Infrastructure;
Domain tidak bergantung Eloquent. Migration dimiliki module pemilik data.

## Identifier dan kondisi starter

- Entitas internal termasuk users memakai ULID; FK terkait harus sesuai,
  termasuk `sessions.user_id`.
- Saat inspeksi, migration starter memakai `users.id` integer dan
  `sessions.user_id` foreignId; model User belum memakai ULID. Migrasi belum dilakukan.
- ULID entitas tidak berarti semua key teknis framework harus diganti: session
  ID, reset token, dan tabel infrastruktur mengikuti kebutuhan kontraknya.
- Inventarisasi data aktual dan seluruh consumer ID sebelum perubahan; rencana
  konversi, compatibility, FK/index, rollback, dan test migration wajib disiapkan.
  Jangan memakai reset/drop database sebagai asumsi karena awalnya fresh install.

## Snapshot, reuse, dan retensi

- Snapshot fakta dan bukti perhitungan immutable. Input berubah menghasilkan
  snapshot baru; jangan menimpa hasil yang sudah ditampilkan/disimpan.
- Identitas reuse mencakup input, anggota/nilai peer, periode, basis, dan versi
  konfigurasi. Jangan hanya memakai ticker atau tanggal pengambilan.
- Skor identik dapat dibagi antar pengguna untuk data pasar yang sama; data
  perbandingan pribadi dan identitas pemilik tidak ikut dibagikan.
- Retensi historis 30 hari, kecuali snapshot masih dirujuk hasil aktif atau
  perbandingan tersimpan. Pertahankan seluruh rantai bukti, bukan hanya skor.
- Menghapus perbandingan tidak boleh menghapus bukti yang masih dirujuk pengguna
  lain/hasil aktif. Data yang tak lagi dirujuk mengikuti cleanup retensi.
- Freshness skor terkini mengikuti usia sumber pada [DATA-FLOW.md](DATA-FLOW.md),
  bukan masa retensi atau tanggal kalkulasi.
- Simpan periode laporan, waktu observasi pasar, fetched_at, asal data, alasan
  unavailable/fallback, dan versi mapping yang relevan secara terpisah.

## Perbandingan pribadi

Pengguna dapat menyimpan manual, memberi nama, membuka kembali, dan menghapus.
Setiap versi berisi maksimal 3 saham dan referensi snapshot saat disimpan.
Aksi "Perbarui data" menghasilkan versi baru sesuai cache/kuota, tidak mengubah
versi lama. Hanya pemilik boleh mengakses/mengubah; tidak ada sharing publik.
Tidak ada riwayat screener otomatis, watchlist, atau laporan terpisah pada MVP.

## MySQL dan Redis

MySQL menyimpan identitas, snapshot, skor, versi perbandingan, serta ledger dan
reservasi credit. Redis untuk cache/queue, bukan sumber kebenaran sisa budget.
Hilangnya Redis tidak boleh mereset 1.000 credit atau kuota harian.
Reservasi dan rekonsiliasi biaya harus tahan concurrency; rinciannya di use case
MarketData. Retensi 30 hari snapshot tidak otomatis berlaku pada ledger: jangan
hapus bukti konsumsi yang masih diperlukan untuk hard budget sekali pakai.

## Gate sebelum migration

Keputusan ownership/ULID sudah final untuk MVP. DDL, nama tabel rinci, FK/index,
tipe dan precision numerik, deduplication key, mekanisme pin/cleanup referensi,
serta reservasi atomik harus dirinci dan diuji pada work item implementasi.
Dua desimal UI bukan alasan menyimpan seluruh nilai mentah dengan scale 2.
Tidak ada semua tabel/module sekaligus atau abstraction placeholder.
