# Module: Comparison

## Tujuan dan boundary

- Source: `app/Modules/Comparison/`.
- Tanggung jawab: alur Bandingkan, maksimal 3 saham, perbandingan privat,
  snapshot manual, dan versi tersimpan.
- Di luar tanggung jawab: mengambil data provider Sectors secara langsung,
  menghitung skor Intelligence, menyimpan fakta perusahaan milik Company, dan
  membuat rekomendasi BUY/HOLD/SELL.

## Public contract dan dependency

Implementasi aktif memakai `ComparisonSelectionBuilder` untuk menyusun payload
awal halaman Bandingkan dari daftar symbol yang dipilih user. Profil ringkas
diambil melalui contract Company/MarketData yang sudah ada, bukan lewat adapter
Sectors langsung dari Comparison.

Snapshot manual memakai contract `ComparisonSnapshotStore` dengan adapter
Eloquent di Infrastructure. Snapshot menyimpan data yang sudah tersedia di
halaman saat user menekan tombol simpan; aksi simpan tidak melakukan refresh
provider dan tidak menambah credit API.

Dependency target:

- Company: identitas dan snapshot perusahaan internal.
- Intelligence: hasil nilai dan bukti input saat scoring sudah tersedia. Belum
  dihubungkan ke compare aktif.
- MarketData: tidak dipanggil langsung oleh Comparison; data provider masuk
  melalui module pemilik data.

## Operasi dan authorization

Semua halaman Bandingkan berada di balik login dan email verified. Snapshot
manual bersifat privat per user. Membuka snapshot user lain menghasilkan 404.
Update snapshot lama membuat versi baru, bukan menimpa data lama.

## Verifikasi

Verifikasi aktif:

- feature test route Bandingkan menerima maksimal 3 symbol;
- invalid/unknown symbol ditangani eksplisit;
- profil compare, analytics harga/keuangan/valuasi, dan snapshot memakai test
  fake HTTP atau data lokal;
- `ComparisonSnapshotTest` membuktikan ownership privat, daftar milik user,
  detail read-only, versioning, dan validasi maksimal tiga saham;
- typecheck, lint scoped, build, Pint, dan `git diff --check` lulus pada
  increment terkait.

Source mengikuti `docs/ARCHITECTURE.md` dan `docs/FOLDER-STRUCTURE.md`.
