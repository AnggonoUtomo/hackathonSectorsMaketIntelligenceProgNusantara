# Work Item: Compare Real Bertahap

## Status dan owner

- Status: increment 0 selesai; increment 1 selesai; increment 2 selesai;
  increment 3 selesai; increment 4 selesai.
- Owner: lintas module Comparison, Company, MarketData, Intelligence, dan frontend.
- Target: route `/bandingkan`, kontrak payload compare, dan UI compare real.

## Kondisi awal

Sebelum increment 1, `/bandingkan` memakai `FakeComparisonBuilder` dan merender
`nusalens/placeholder` dengan `ComparisonDashboard`. Payload berisi default
`BBCA, TLKM, ICBP`, skor contoh, dan label `backend_fake`. Test saat itu juga
mengunci perilaku fake tersebut.

Ini bertentangan dengan arah UX terbaru: pengguna awam harus memakai data real,
tidak melihat placeholder/fake, dan tidak dipaksa tahu ticker sejak awal. Pada
sisi lain, compare penuh tiga saham bisa mahal bila seluruh section dimuat
otomatis: overview sampai 3 credit, harga sampai 3 credit, keuangan empat
kuartal sampai 12 credit, dan valuasi sampai 3 credit. Total cold cache dapat
mencapai 21 credit untuk tiga saham bila semuanya dimuat sekaligus, melewati
kuota harian akun 20 credit.

## Scope dan non-scope

Scope proposal ini adalah mengganti compare fake menjadi compare real bertahap:

- `/bandingkan` tanpa query menampilkan state kosong dan pemilihan saham, bukan
  default fake.
- Pemilihan saham memakai autocomplete nama/kode dengan logo, maksimal tiga saham.
- Query `?symbols=BBCA,ADES,AADI` tetap didukung sebagai deep link.
- Data real dimuat dalam kelompok bertahap: profil ringkas, harga, keuangan, dan
  valuasi. Setiap kelompok menampilkan status fresh/cache/error sendiri.
- UI menjelaskan estimasi credit sebelum memuat kelompok data yang mahal.
- Angka yang belum dimuat tampil sebagai "belum dimuat", bukan `-` fake atau nol.

Non-scope tahap awal:

- Nilai Prioritas Riset, percentile peer, dan komponen scoring.
- AI explainer.
- Compare global lintas semua emiten atau ranking kandidat.
- Mengubah batas maksimal tiga saham.

## Acceptance criteria

- [x] `/bandingkan` tanpa symbol tidak menampilkan saham default fake.
- [x] User dapat mencari dan memilih sampai tiga perusahaan memakai autocomplete
      real yang sama dengan Temukan Saham.
- [x] Query symbol valid membuka halaman compare dengan daftar saham terpilih,
      tanpa metric palsu.
- [x] Query lebih dari tiga symbol atau format invalid tetap ditolak.
- [x] Profil ringkas memakai data real/cache internal dan menampilkan freshness.
- [x] Kelompok harga, keuangan, dan valuasi dimuat on-demand dengan estimasi
      credit serta status per kelompok.
- [x] Total cold load awal untuk tiga saham tidak melebihi 3 credit sebelum user
      meminta kelompok data tambahan.
- [x] Tidak ada fetch semua section Company Report secara default.
- [x] Tidak ada skor, ranking, atau rekomendasi beli/jual sebelum scoring real siap.
- [x] User dapat menyimpan snapshot privat manual tanpa refresh provider otomatis.
- [x] Snapshot lama tidak ditimpa; update membuat versi baru.
- [x] Automated test memakai fake HTTP; smoke real hanya setelah estimasi credit
      dan instruksi eksekusi disetujui.

## Kontrak UI yang diusulkan

Halaman compare memiliki empat area utama:

- Pemilih saham: autocomplete nama/kode, chip saham terpilih, tombol reset, dan
  validasi maksimal tiga.
- Ringkasan: kartu tiap saham berisi logo, nama, sektor/subsektor, status data,
  dan link ke Company Cockpit.
- Matrix data: tab atau segmented control untuk Profil, Harga, Keuangan, dan
  Valuasi. Setiap tab punya loading/error/empty state sendiri.
- Visualisasi: grafik Recharts untuk harga dan mini bar per metric keuangan atau
  valuasi bila data cukup; tabel tetap menjadi alternatif utama.

Payload route setelah increment 2:

```text
comparison: {
  symbols: string[],
  companies: [
    {
      symbol,
      name,
      logoUrl,
      sector,
      subSector,
      industry,
      price,
      priceDate,
      fetchedAt,
      freshness,
      status: "ready|error",
      error
    }
  ],
  metrics: [],
  meta: {
    source: "profile",
    state: "empty|ready|partial",
    limit: 3,
    liveProvider,
    estimatedCredits
  }
}
```

Section harga, keuangan, valuasi, dan scoring tetap belum dimuat dan tidak
diisi metric fake. Error provider pada profil tampil per saham; saham lain yang
berhasil tetap ditampilkan.

Section harga, keuangan, dan valuasi pada Increment 3 dimuat dari frontend hanya
setelah user menekan tombol muat section. Payload section tidak digabung ke
payload awal route compare, sehingga deep link compare tetap hemat credit.

Snapshot manual pada Increment 4 menyimpan `comparison.companies` dan section
on-demand yang sudah berhasil dimuat di browser. Snapshot tidak mengambil data
baru dari provider; data yang belum dimuat tetap tidak muncul dalam snapshot.

## Dependency dan keputusan

- Maksimal tiga saham dan snapshot manual privat sudah disetujui di
  [Keputusan](../../DECISIONS.md).
- Cache, kuota 20 credit per akun per hari, dan hard budget mengikuti
  [Alur Data](../../DATA-FLOW.md).
- Scoring v1 dan peer minimal 5 lainnya mengikuti [Scoring](../../SCORING.md),
  tetapi belum masuk tahap compare real awal.
- Recharts sudah dipakai di detail perusahaan dan boleh dipakai ulang.
- Slice Increment 3A memakai endpoint analitik existing untuk harga agar tidak
  menambah contract backend baru sebelum pola UX on-demand terbukti nyaman.
- Slice Increment 3B memakai endpoint analitik existing untuk keuangan empat
  kuartal, tetap on-demand karena cold cache dapat memakan sampai 4 credit per
  saham.
- Slice Increment 3C memakai endpoint analitik existing untuk valuasi historis,
  tetap on-demand dengan estimasi cold cache sampai 1 credit per saham.

## Increment 4: Snapshot manual

Tujuan increment 4 adalah menyimpan perbandingan yang sudah dibuka user sebagai
snapshot privat dan immutable. Snapshot tidak melakukan refresh provider otomatis;
ia menyimpan data yang sudah tersedia di halaman saat user menekan tombol simpan.

Implementasi:

- Tabel `comparison_snapshots` menyimpan owner user, judul opsional, daftar
  symbol, ringkasan profil, data section yang sudah dimuat, versi, dan
  `created_from_snapshot_id` bila update membuat versi baru.
- Snapshot privat per user; user lain tidak boleh membaca atau mengubahnya.
- Update snapshot lama tidak menimpa data lama, melainkan membuat versi baru.
- Data sumber menyimpan `fetchedAt`, section, dan periode/range agar dapat
  ditelusuri.
- Simpan snapshot tidak menambah credit API; refresh data tetap aksi terpisah.
- UI minimal tersedia: tombol Simpan Snapshot pada `/bandingkan`, daftar snapshot
  privat di `/bandingkan/snapshots`, dan detail read-only di
  `/bandingkan/snapshots/{snapshot}`.

## Handoff

- Perubahan: proposal compare real bertahap ditulis; increment 1 menghilangkan
  default/matrix fake; increment 2 memuat profil ringkas real/cache untuk saham
  terpilih dan menampilkan error per saham; increment 3 menambahkan harga,
  keuangan, dan valuasi on-demand dengan Recharts dan tabel alternatif; increment
  4 menambahkan snapshot manual privat berversi.
- Verifikasi: source compare fake, route, frontend dashboard, keputusan, scoring,
  dan alur data sudah dibaca. Unit/feature compare, typecheck, lint scoped, build,
  dan diff check dijalankan pada increment 1. Focused test profil compare,
  unit builder, dan typecheck lulus pada increment 2. Increment 3 diverifikasi
  dengan `CompanyAnalyticsTest`, focused compare route test, typecheck, lint,
  build, dan diff check. Increment 4 diverifikasi dengan
  `ComparisonSnapshotTest`, focused compare/profile analytics test, typecheck,
  lint scoped, dan build.
- Risiko terbuka: scoring real belum masuk. Browser smoke belum dijalankan bila
  tool browser tidak tersedia.
