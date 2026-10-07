# Specification: Comparison

## Status

Aktif sampai Increment5: compare real, score on-demand dan snapshot manual.
Implementasi diselesaikan melalui work item Penuntasan MVP.

## Tujuan, scope, dan non-scope

Comparison membantu pengguna membandingkan maksimal 3 saham dalam satu tampilan
agar angka utama, kelengkapan data, freshness, dan catatan riset mudah dipindai.

Scope implementasi saat ini:

- route dan UI Bandingkan memulai dari pilihan saham pengguna;
- validasi query `symbols` maksimal 3 ticker;
- autocomplete perusahaan untuk membantu user awam memilih saham;
- profil ringkas real/cache untuk setiap symbol terpilih;
- error provider ditampilkan per saham, bukan menghapus semua pilihan;
- section harga, keuangan, dan valuasi dimuat on-demand setelah user meminta;
- Recharts dan tabel alternatif tersedia untuk section yang sudah dimuat;
- snapshot manual privat menyimpan profil dan section yang sudah tersedia di
  halaman;
- link dari detail perusahaan menuju Bandingkan dengan symbol terpilih;
- payload tetap tanpa skor fake agar tidak mencampur contoh dengan data real.

Non-scope implementasi awal:

- formula Intelligence final;
- scoring comparison sebelum gate peer/scoring;
- menghitung percentile atau peer di module Comparison;
- sharing publik, watchlist, billing, BYOK, dan AI.

## Arsitektur

- Module: `app/Modules/Comparison/`.
- Inbound adapter: route Inertia compare di `routes/web.php`; snapshot manual
  memakai `ComparisonSnapshotController` di Presentation.
- Use case: `ComparisonSelectionBuilder` di Application untuk normalisasi dan
  validasi pilihan saham serta memuat profil ringkas lewat contract
  `CompanyDirectory`.
- Snapshot use cases: `SaveComparisonSnapshot`, `ListComparisonSnapshots`, dan
  `GetComparisonSnapshot`.
- Aturan Domain: maksimal 3 saham pada MVP; tidak ada rekomendasi beli/jual.
- Outbound/read port: memakai contract `CompanyDirectory` dari MarketData sebagai
  boundary profil internal dan endpoint analytics Company/MarketData untuk
  section on-demand.
- Persistence port: `ComparisonSnapshotStore`.
- Outbound adapter: adapter Sectors/cache/ledger milik MarketData.
- Persistence adapter: `EloquentComparisonSnapshotStore`.
- Composition root: binding `ComparisonSnapshotStore` di `AppServiceProvider`.

## Contract dan data

Input awal:

- `GET /bandingkan?symbols=BBCA,TLKM,ICBP`
- `symbols` opsional, dipisah koma, uppercase normalization, maksimal 3 item,
  hanya alfanumerik pendek.

Output Inertia awal:

- `comparison.symbols`: symbol yang diminta setelah normalisasi;
- `comparison.companies`: kartu profil ringkas per saham dengan `status`
  `ready|error`;
- `comparison.metrics`: kosong sampai hasil Intelligence real tersedia;
- `comparison.meta`: source `profile`, limit `3`, state `ready|partial|empty`,
  `liveProvider`, dan `estimatedCredits`.

Unknown symbol tidak disamarkan sebagai data kosong global. Untuk increment
awal, unknown symbol ditolak sebagai validation/session error agar input buruk
jelas terlihat.

Section on-demand:

- `GET /nusalens/companies/{symbol}/analysis?section=prices|financials|valuation`
  memuat section per saham saat user meminta.
- Harga dan valuasi cold cache sampai 1 credit per saham; keuangan empat kuartal
  sampai 4 credit per saham.
- Data section sukses diingat di state browser agar ikut tersimpan bila user
  menekan Simpan Snapshot.

Snapshot manual:

- `GET /bandingkan/snapshots` menampilkan daftar snapshot milik user.
- `POST /bandingkan/snapshots` menyimpan snapshot tanpa refresh provider.
- `GET /bandingkan/snapshots/{snapshot}` menampilkan detail read-only bila owner
  cocok; user lain mendapat 404.
- Update versi lama membuat row baru dengan `created_from_snapshot_id`, bukan
  menimpa payload lama.

## Authorization, audit, dan UI

Route Bandingkan wajib `auth` dan `verified`. UI menampilkan:

- autocomplete perusahaan;
- batas maksimal 3 saham;
- chip saham terpilih dengan logo bila tersedia;
- kartu profil berisi logo, nama, sektor/subsektor, harga terakhir, freshness,
  dan link ke Company Cockpit;
- kontrol section harga, keuangan, valuasi dengan estimasi credit;
- tombol Simpan Snapshot serta link daftar snapshot;
- daftar snapshot privat dan detail read-only;
- empty state saat belum memilih saham;
- skor Intelligence, kelengkapan, bukti peer dan alasan unavailable;
- disclaimer bahwa hasil bukan rekomendasi investasi.

## Dependency

Selection flow memakai pencarian perusahaan internal yang sudah tersedia. Profil
ringkas memakai `CompanyDirectory::profile()` sehingga cache dan ledger tetap
ditangani MarketData. Section harga/keuangan/valuasi memakai endpoint analytics
internal existing. Integrasi scoring menggunakan hasil Intelligence,
bukan JSON vendor langsung dan bukan kalkulator di Comparison.

## Acceptance dan verifikasi

- [x] Verified user dapat membuka halaman Bandingkan tanpa symbol dan melihat
      empty state.
- [x] Verified user dapat membuka `?symbols=BBCA,TLKM` dan melihat profil
      ringkas real/cache tanpa matrix fake.
- [x] Lebih dari 3 symbol ditolak.
- [x] Format symbol buruk ditolak secara eksplisit.
- [x] UI dari detail perusahaan dapat menuju Bandingkan dengan symbol terkait.
- [x] Automated test tidak melakukan live Sectors call.
- [x] Harga, keuangan, dan valuasi dimuat on-demand dengan grafik/tabel.
- [x] Snapshot manual privat dapat disimpan, dibuka, dan dibuat versi baru.
- [x] User tidak dapat membuka snapshot milik user lain.

## Risiko dan keputusan terbuka

- Score tersimpan menyalin input dan hasil Intelligence tanpa hitung/HTTP saat dibuka.
- Valuasi peer live lengkap dan momentum tetap mengikuti gap sumber Intelligence.
- Versi lama tanpa receipt diberi penanda integritas legacy; tidak ditulis ulang.
