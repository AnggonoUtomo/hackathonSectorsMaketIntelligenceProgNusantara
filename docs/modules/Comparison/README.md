# Module: Comparison

## Tujuan dan boundary

- Source: `app/Modules/Comparison/`.
- Tanggung jawab: alur Bandingkan, maksimal 3 saham, perbandingan privat,
  snapshot manual, dan versi tersimpan pada tahap MVP berikutnya.
- Di luar tanggung jawab: mengambil data provider Sectors secara langsung,
  menghitung skor Intelligence, menyimpan fakta perusahaan milik Company, dan
  membuat rekomendasi BUY/HOLD/SELL.

## Public contract dan dependency

Belum ada contract publik final. Increment awal memakai provider fake internal
untuk mempelajari UI/UX Bandingkan tanpa live call Sectors.

Dependency target:

- Company: identitas dan snapshot perusahaan internal.
- Intelligence: hasil nilai dan bukti input saat scoring sudah tersedia.
- MarketData: tidak dipanggil langsung oleh Comparison; data provider masuk
  melalui module pemilik data.

## Operasi dan authorization

Semua halaman Bandingkan berada di balik login dan email verified. MVP final
akan menyimpan perbandingan manual secara privat dan berversi. Increment fake
flow belum membuat persistence atau policy ownership karena belum ada simpan
manual.

## Verifikasi

Verifikasi awal untuk fake flow:

- feature test route Bandingkan menerima maksimal 3 symbol;
- invalid/unknown symbol ditangani eksplisit;
- UI menampilkan matriks side-by-side dari backend fake;
- `php artisan test`, typecheck, lint, build, dan sensitive-data scan tetap lulus.

Source mengikuti `docs/ARCHITECTURE.md` dan `docs/FOLDER-STRUCTURE.md`.
