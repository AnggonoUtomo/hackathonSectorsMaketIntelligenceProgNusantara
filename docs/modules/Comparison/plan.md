# Plan: Comparison

## Scope aktif

Module Comparison sekarang menangani alur `/bandingkan` berbasis pilihan user,
data real/cache bertahap, dan snapshot manual privat. Flow fake sudah menjadi
riwayat implementasi dan tidak lagi menjadi baseline aktif.

## Increment 0: Fake flow historis

- Status: selesai dan digantikan.
- Perubahan: payload fake, validasi maksimal 3 symbol, matrix UI awal, dan link
  dari detail perusahaan ke Bandingkan.
- Catatan: dokumentasi historis tetap ada di
  `docs/modules/Comparison/work-items/fake-comparison-flow/`.

## Increment 1: Selection-first compare

- Status: selesai.
- Perubahan: `/bandingkan` tanpa query menjadi empty state, autocomplete real,
  chip pilihan maksimal tiga saham, dan payload tanpa matrix/metrik fake.
- Acceptance: tidak ada default `BBCA,TLKM,ICBP`; invalid/lebih dari tiga symbol
  ditolak; query valid menjadi canonical state.
- Verifikasi: unit builder, focused route test, typecheck, lint, build, dan
  diff check pada increment terkait.

## Increment 2: Profil ringkas real/cache

- Status: selesai.
- Perubahan: `ComparisonSelectionBuilder` memuat profil ringkas lewat contract
  `CompanyDirectory`, menampilkan logo, sektor/subsektor, harga terakhir,
  freshness, dan error per saham.
- Acceptance: cold initial untuk tiga saham maksimal 3 credit; cache hit tidak
  menambah ledger; partial failure tetap menampilkan saham lain.
- Verifikasi: `CompareProfileTest`, focused compare route test, dan frontend
  checks pada increment terkait.

## Increment 3: Section harga, keuangan, dan valuasi on-demand

- Status: selesai.
- Perubahan: UI compare memuat harga, keuangan, dan valuasi hanya setelah user
  menekan tombol section. Grafik Recharts dan tabel alternatif tersedia.
- Acceptance: halaman awal tidak mengambil section mahal; loading/error/status
  per saham; angka dua desimal; tidak ada seri sintetis.
- Verifikasi: `CompanyAnalyticsTest`, focused compare route test, typecheck,
  lint scoped, build, dan `git diff --check`.

## Increment 4: Snapshot manual privat

- Status: selesai.
- Perubahan: `comparison_snapshots`, contract `ComparisonSnapshotStore`, use case
  save/list/get, controller Inertia, daftar snapshot, dan detail read-only.
- Acceptance: snapshot privat per user; user lain mendapat 404; update membuat
  versi baru; simpan snapshot tidak memanggil provider.
- Verifikasi: `ComparisonSnapshotTest`, focused compare/profile analytics test,
  typecheck, lint scoped, build, Pint, dan diff check.

## Increment 5: Score comparison

- Status: proposal; belum coding.
- Perubahan target: Comparison menjadi consumer hasil Intelligence real untuk
  menampilkan Nilai Prioritas Riset, komponen, kelengkapan, bukti peer, periode,
  bobot, dan versi formula.
- Prasyarat: gate peer/scoring 3B.0 atau slice komponen yang datanya terbukti
  layak. Comparison tidak menghitung percentile atau formula sendiri.
- Acceptance target: tidak ada total saat kelengkapan <70%; tidak ada label
  BUY/HOLD/SELL; credit peer lengkap diestimasi sebelum aksi hitung aktif.

## Batas berhenti

Jangan mengaktifkan scoring compare sampai contract Intelligence, bukti
immutable, dan estimasi credit peer disetujui. Jika data/credit tidak cukup,
tampilkan unavailable dengan alasan, bukan angka fake atau subset peer.
