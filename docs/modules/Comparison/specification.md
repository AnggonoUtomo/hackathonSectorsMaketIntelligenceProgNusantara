# Specification: Comparison

## Status

Draft untuk selection-first comparison flow.

## Tujuan, scope, dan non-scope

Comparison membantu pengguna membandingkan maksimal 3 saham dalam satu tampilan
agar angka utama, kelengkapan data, freshness, dan catatan riset mudah dipindai.

Scope implementasi saat ini:

- route dan UI Bandingkan memulai dari pilihan saham pengguna;
- validasi query `symbols` maksimal 3 ticker;
- autocomplete perusahaan untuk membantu user awam memilih saham;
- profil ringkas real/cache untuk setiap symbol terpilih;
- error provider ditampilkan per saham, bukan menghapus semua pilihan;
- payload tetap tanpa matriks fake agar tidak mencampur contoh dengan data real;
- link dari detail perusahaan menuju Bandingkan dengan symbol terpilih.

Non-scope implementasi awal:

- live Sectors call atau konsumsi credit real;
- matriks side-by-side berbasis profil/harga/keuangan/valuasi real;
- persistence perbandingan privat;
- versioning snapshot tersimpan;
- formula Intelligence final;
- sharing publik, watchlist, billing, BYOK, dan AI.

## Arsitektur

- Module: `app/Modules/Comparison/`.
- Inbound adapter: route Inertia saat ini di `routes/web.php`; controller
  Presentation dapat diekstrak saat behavior bertambah.
- Use case: `ComparisonSelectionBuilder` di Application untuk normalisasi dan
  validasi pilihan saham serta memuat profil ringkas lewat contract
  `CompanyDirectory`.
- Aturan Domain: maksimal 3 saham pada MVP; tidak ada rekomendasi beli/jual.
- Outbound port: memakai contract `CompanyDirectory` dari MarketData sebagai
  boundary data provider internal.
- Outbound adapter: adapter Sectors/cache/ledger milik MarketData.
- Composition root: Laravel container auto-resolve class sederhana.

## Contract dan data

Input awal:

- `GET /bandingkan?symbols=BBCA,TLKM,ICBP`
- `symbols` opsional, dipisah koma, uppercase normalization, maksimal 3 item,
  hanya alfanumerik pendek.

Output Inertia awal:

- `comparison.symbols`: symbol yang diminta setelah normalisasi;
- `comparison.companies`: kartu profil ringkas per saham dengan `status`
  `ready|error`;
- `comparison.metrics`: kosong sampai section harga/keuangan/valuasi real dibuat;
- `comparison.meta`: source `profile`, limit `3`, state `ready|partial|empty`,
  `liveProvider`, dan `estimatedCredits`.

Unknown symbol tidak disamarkan sebagai data kosong global. Untuk increment
awal, unknown symbol ditolak sebagai validation/session error agar input buruk
jelas terlihat.

## Authorization, audit, dan UI

Route Bandingkan wajib `auth` dan `verified`. UI menampilkan:

- autocomplete perusahaan;
- batas maksimal 3 saham;
- chip saham terpilih dengan logo bila tersedia;
- kartu profil berisi logo, nama, sektor/subsektor, harga terakhir, freshness,
  dan link ke Company Cockpit;
- empty state saat belum memilih saham;
- placeholder eksplisit bahwa harga historis, keuangan, valuasi, dan scoring
  belum dimuat pada increment ini;
- disclaimer bahwa hasil bukan rekomendasi investasi.

## Dependency

Selection flow memakai pencarian perusahaan internal yang sudah tersedia. Profil
ringkas memakai `CompanyDirectory::profile()` sehingga cache dan ledger tetap
ditangani MarketData. Integrasi matriks nyata nanti harus menggunakan data
Company/Intelligence internal, bukan JSON vendor langsung.

## Acceptance dan verifikasi

- [x] Verified user dapat membuka halaman Bandingkan tanpa symbol dan melihat
      empty state.
- [x] Verified user dapat membuka `?symbols=BBCA,TLKM` dan melihat profil
      ringkas real/cache tanpa matrix fake.
- [x] Lebih dari 3 symbol ditolak.
- [x] Format symbol buruk ditolak secara eksplisit.
- [x] UI dari detail perusahaan dapat menuju Bandingkan dengan symbol terkait.
- [x] Automated test tidak melakukan live Sectors call.

## Risiko dan keputusan terbuka

- Desain persistence perbandingan manual privat belum dikerjakan.
- Format snapshot/versioning final menunggu model Company dan Intelligence.
- Matriks real harga/keuangan/valuasi belum dikerjakan.
