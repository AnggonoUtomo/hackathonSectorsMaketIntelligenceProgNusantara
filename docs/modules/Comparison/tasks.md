# Tasks: Comparison

## Baseline dan dokumentasi

- [x] Scope dan non-scope module Comparison dicatat.
- [x] Dependency dengan Company, MarketData, dan Intelligence dicatat.
- [x] Work item historis fake flow dipertahankan sebagai arsip, bukan baseline
      aktif.
- [x] Work item compare real bertahap menjadi acuan aktif `/bandingkan`.

## Increment 0: Fake flow historis

- [x] Backend fake comparison payload pernah dibuat.
- [x] Validasi `symbols` maksimal 3 pernah dibuat.
- [x] Matrix fake dan link detail ke Bandingkan pernah dibuat.
- [x] Flow ini digantikan oleh selection-first compare.

## Increment 1: Selection-first compare

- [x] Ganti fake comparison payload menjadi `ComparisonSelectionBuilder`.
- [x] `/bandingkan` tanpa query menampilkan empty state, bukan default saham.
- [x] Gunakan autocomplete perusahaan dan chip pilihan maksimal 3 saham.
- [x] Hapus matrix/metrik fake dari payload compare.
- [x] Unit dan feature test mengunci state `empty|ready|partial`.

## Increment 2: Profil ringkas real/cache

- [x] Load profil ringkas lewat contract internal `CompanyDirectory`.
- [x] Tampilkan logo, nama, sektor/subsektor, harga terakhir, dan freshness.
- [x] Tampilkan error provider per saham tanpa menghapus saham lain.
- [x] Pastikan cold initial tiga saham maksimal 3 credit.
- [x] Verifikasi cache hit tidak menambah ledger credit.

## Increment 3: Section data on-demand

- [x] Tambah section harga on-demand dengan Recharts dan tabel.
- [x] Tambah section keuangan on-demand dengan Recharts dan tabel.
- [x] Tambah section valuasi on-demand dengan Recharts dan tabel.
- [x] Tampilkan estimasi credit dan status per section/per saham.
- [x] Verifikasi null, angka nol valid, error quota, dan stale cache.

## Increment 4: Snapshot manual

- [x] Buat migration `comparison_snapshots`.
- [x] Buat contract `ComparisonSnapshotStore` dan adapter Eloquent.
- [x] Buat use case save/list/get snapshot.
- [x] Buat route/controller daftar, simpan, dan detail snapshot.
- [x] Tambah tombol Simpan Snapshot pada `/bandingkan`.
- [x] Tambah halaman daftar snapshot privat dan detail read-only.
- [x] Verifikasi ownership privat, versioning, validasi maksimal 3 saham, dan
      simpan tanpa refresh provider.

## Increment 5: Score comparison

- [x] Dokumentasikan proposal dan gate sebelum coding.
- [x] Selesaikan atau setujui gate peer/scoring 3B.0.
- [x] Definisikan contract/payload hasil Intelligence untuk compare.
- [x] Tampilkan state "Nilai belum tersedia" tanpa angka fake.
- [x] Baca hasil Intelligence tersimpan yang masih fresh.
- [x] Tampilkan bukti peer, kelengkapan, periode, bobot, dan formula version.
- [x] Trigger hitung score on-demand hanya setelah estimasi credit dan populasi
      peer valid disetujui.
- [x] Simpan score yang sudah dimuat ke snapshot compare tanpa menghitung ulang
      saat detail snapshot dibuka.

## Verifikasi terakhir yang relevan

- [x] `php artisan test --compact tests\Feature\Comparison\ComparisonSnapshotTest.php`
- [x] `php artisan test --compact tests\Feature\Comparison\CompareProfileTest.php tests\Feature\MarketData\CompanyAnalyticsTest.php`
- [x] `php artisan test --compact --filter='compare_page'`
- [x] `npm run typecheck`
- [x] ESLint scoped file compare/snapshot
- [x] `npm run build`
- [x] `vendor\bin\pint --dirty`
- [x] `git diff --check`

## Risiko terbuka

- Momentum/kalender dan smoke valuasi populasi penuh masih membutuhkan bukti sumber.
- Verifikasi/browser terbaru dicatat pada Penuntasan MVP; bukan lagi gate contract.
