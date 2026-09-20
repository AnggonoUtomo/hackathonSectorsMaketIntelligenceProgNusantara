# Specification: Comparison

## Status

Draft untuk fake comparison flow awal.

## Tujuan, scope, dan non-scope

Comparison membantu pengguna membandingkan maksimal 3 saham dalam satu tampilan
agar angka utama, kelengkapan data, freshness, dan catatan riset mudah dipindai.

Scope implementasi awal:

- route dan UI Bandingkan memakai data fake backend internal;
- validasi query `symbols` maksimal 3 ticker;
- matriks side-by-side dengan metrik contoh dan status data;
- link dari detail perusahaan menuju Bandingkan dengan symbol terpilih.

Non-scope implementasi awal:

- live Sectors call atau konsumsi credit real;
- persistence perbandingan privat;
- versioning snapshot tersimpan;
- formula Intelligence final;
- sharing publik, watchlist, billing, BYOK, dan AI.

## Arsitektur

- Module: `app/Modules/Comparison/`.
- Inbound adapter: route Inertia saat ini di `routes/web.php`; controller
  Presentation dapat diekstrak saat behavior bertambah.
- Use case: fake comparison query di Application untuk menyusun payload halaman.
- Aturan Domain: maksimal 3 saham pada MVP; tidak ada rekomendasi beli/jual.
- Outbound port: belum dibuat sampai ada consumer data nyata.
- Outbound adapter: belum ada.
- Composition root: Laravel container auto-resolve class sederhana.

## Contract dan data

Input awal:

- `GET /bandingkan?symbols=BBCA,TLKM,ICBP`
- `symbols` opsional, dipisah koma, uppercase normalization, maksimal 3 item,
  hanya alfanumerik pendek.

Output Inertia awal:

- `comparison.symbols`: symbol yang diminta setelah normalisasi;
- `comparison.companies`: daftar perusahaan fake yang ditemukan;
- `comparison.metrics`: metrik baris matriks;
- `comparison.meta`: source `backend_fake`, limit `3`, state `ready|empty`.

Unknown symbol tidak disamarkan sebagai data kosong global. Untuk increment
awal, unknown symbol ditolak sebagai validation/session error agar input buruk
jelas terlihat.

## Authorization, audit, dan UI

Route Bandingkan wajib `auth` dan `verified`. UI menampilkan:

- input symbol;
- batas maksimal 3 saham;
- matrix angka side-by-side;
- empty state saat belum memilih saham;
- disclaimer bahwa hasil bukan rekomendasi investasi.

## Dependency

Fake flow dapat memakai dataset internal kecil. Integrasi nyata nanti harus
menggunakan data Company/Intelligence internal, bukan JSON vendor langsung.

## Acceptance dan verifikasi

- [ ] Verified user dapat membuka halaman Bandingkan tanpa symbol dan melihat
  empty state.
- [ ] Verified user dapat membuka `?symbols=BBCA,TLKM` dan melihat matrix fake.
- [ ] Lebih dari 3 symbol ditolak.
- [ ] Unknown symbol ditolak secara eksplisit.
- [ ] UI dari detail perusahaan dapat menuju Bandingkan dengan symbol terkait.
- [ ] Automated test tidak melakukan live Sectors call.

## Risiko dan keputusan terbuka

- Desain persistence perbandingan manual privat belum dikerjakan.
- Format snapshot/versioning final menunggu model Company dan Intelligence.
- Metrik fake belum mencerminkan formula final; hanya placeholder UI/UX.
