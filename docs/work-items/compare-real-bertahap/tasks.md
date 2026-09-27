# Tasks: Compare Real Bertahap

## Sebelum mulai

- [x] Scope dan non-scope jelas.
- [x] Dependency/keputusan terbuka diketahui.
- [x] Acceptance dan cara verifikasi ditetapkan.
- [x] Source compare fake, route, frontend, dan test saat ini dibaca.
- [x] Credit cold cache untuk compare tiga saham dihitung secara konservatif.

## Increment 0: Proposal dan kontrak

- [x] Catat kondisi awal `/bandingkan` masih fake.
- [x] Tetapkan prinsip: no default fake, pilih maksimal tiga, muat data bertahap.
- [x] Tentukan payload awal dan section state.
- [x] Pisahkan profile initial dari prices/financials/valuation on-demand.
- [x] Catat non-scope snapshot, scoring, AI, dan ranking kandidat.
- [x] Verifikasi terhadap keputusan, alur data, scoring, dan source.

## Increment 1: Hilangkan default fake dan pilih saham real

- [x] Tulis test RED bahwa `/bandingkan` tanpa symbol tidak boleh mengembalikan
      default `BBCA, TLKM, ICBP`.
- [x] Ganti builder fake dengan contract compare awal atau use case real minimal.
- [x] Ubah UI input menjadi autocomplete company dengan logo dan chip terpilih.
- [x] Pertahankan query `symbols` sebagai canonical URL state.
- [x] Verifikasi validasi maksimal tiga dan invalid symbol.
- [x] Jalankan typecheck, lint scoped, build, feature test, dan diff check.

## Increment 2: Profil ringkas compare real

- [x] Tulis test fake HTTP untuk tiga symbol valid cold cache.
- [x] Ambil profil ringkas lewat adapter/cache internal.
- [x] Tampilkan logo, nama, sektor/subsektor, freshness, dan error per saham.
- [x] Pastikan cache hit tidak menambah ledger credit.
- [x] Verifikasi partial failure tidak menghapus saham lain.
- [x] Kontrak payload: `companies[]` berisi status per saham, `meta.state`
      menjadi `ready|partial|empty`, dan `metrics` tetap kosong.

## Increment 3: Section data on-demand

- [x] Tentukan endpoint internal atau reuse contract analytics existing.
- [x] Slice 3A: tambah kontrol "Muat harga" on-demand dengan estimasi credit.
- [x] Slice 3A: render Recharts harga dan tabel alternatif.
- [x] Slice 3B: tambah kontrol "Muat keuangan" on-demand dengan estimasi credit.
- [x] Slice 3B: render Recharts keuangan dan tabel alternatif.
- [x] Slice 3C: tambah kontrol "Muat valuasi" on-demand dengan estimasi credit.
- [x] Slice 3C: render Recharts valuasi dan tabel alternatif.
- [x] Tulis test section harga, keuangan, valuasi dengan fake HTTP.
- [x] Verifikasi null, angka nol valid, error quota, dan stale cache.

## Increment 4: Snapshot manual

- [x] Rancang schema snapshot privat dan versioning.
- [x] Dokumentasikan gate approval sebelum migration.
- [x] Tulis migration dan feature test ownership.
- [x] Simpan snapshot tanpa refresh provider otomatis.
- [x] Tampilkan daftar snapshot milik user bila scope disetujui.

## Increment 5: Score comparison

- [ ] Tunggu gate peer/scoring.
- [ ] Hubungkan hasil Intelligence real ke compare.
- [ ] Tampilkan bukti peer, kelengkapan, periode, dan formula version.

## Hasil

- [x] Proposal dokumentasi selesai.
- [x] Increment 1 selesai.
- [x] Increment 2 selesai.
- [x] Increment 3 selesai.
- [x] Increment 4 proposal selesai.
- [x] Increment 4 selesai.
- Perubahan: work item compare real bertahap dibuat; `/bandingkan` tidak lagi
  memakai default fake atau matrix skor contoh. Builder baru bernama
  `ComparisonSelectionBuilder`; UI memakai autocomplete real dan chip pilihan.
  Increment 2 menambahkan profil ringkas real/cache, freshness, estimasi cold
  credit profil, dan error per saham. Increment 3 menambahkan section harga,
  keuangan, dan valuasi on-demand. Increment 4 menambahkan snapshot manual
  privat, daftar snapshot, detail read-only, dan versioning.
- Verifikasi: source compare fake, frontend compare, test compare, route, dan
  dokumen keputusan/data/scoring sudah dibaca. RED test gagal pada builder lama,
  lalu `php artisan test --compact tests\Unit\Comparison\ComparisonSelectionBuilderTest.php`,
  `php artisan test --compact --filter='compare_page'`, typecheck, lint scoped,
  build dan `git diff --check` lulus pada increment 1. Increment 2 menambahkan
  test `tests\Feature\Comparison\CompareProfileTest.php` untuk success/cache/partial.
  Increment 3 diverifikasi dengan `tests\Feature\MarketData\CompanyAnalyticsTest.php`
  dan focused compare route test. Increment 4 diverifikasi dengan
  `tests\Feature\Comparison\ComparisonSnapshotTest.php`, focused compare/profile
  analytics tests, typecheck, lint scoped, dan build.
- Risiko: scoring real belum masuk. Browser QA belum dijalankan bila tool browser
  tidak tersedia.

Jangan menambah scope baru ke checklist tanpa instruksi user.
