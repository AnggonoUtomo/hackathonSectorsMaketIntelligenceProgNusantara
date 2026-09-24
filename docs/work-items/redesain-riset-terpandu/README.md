# Work Item: Redesain Riset Terpandu

## Status dan owner

- Status: increment 0-2 selesai; increment 3-5 belum dimulai.
- Tanggal: 2026-09-24.
- Owner: lintas module Screening, Company, MarketData, Intelligence,
  Research, Comparison, dan frontend.
- Target tahap ini: dokumentasi alur produk serta autocomplete, direktori
  paginated, profil serta grafik/keuangan perusahaan real yang dapat diuji end-to-end.

## Kondisi awal

User mengalami kesulitan memahami penggunaan aplikasi dan meminta pencarian
autocomplete nama/kode dengan logo, informasi real, grafik interaktif, serta
wizard untuk analisis pengguna awam. Folder `Hackaton/` menjadi referensi
informasi dan UX Sectors; arah NusaLens harus memiliki nilai tambah tersendiri.

Pemeriksaan source pada tanggal di atas:

- `routes/web.php`: mode real Discover memfilter `symbol` dengan equality,
  membatasi pilihan sektor, dan mengambil halaman pertama.
- Detail memakai `FakeCompanySnapshot`; compare memakai `FakeComparisonBuilder`.
- Sidebar memiliki enam pintu utama termasuk Jelaskan Nilai dan Kandidat Menarik.
- `package.json` belum memuat Recharts.
- Pekerjaan autocomplete yang terhenti sudah di-rollback. Dua commit lokal
  sebelumnya tetap dipertahankan; rollback tidak menghapus fitur committed.

## Scope dan non-scope

Tahap awal menyusun desain; user kemudian menginstruksikan implementasi.
Increment 1 mengganti route Discover dan Company dengan alur data real.
Rancangan sampai perbandingan tersimpan tetap dicatat sebagai target berikutnya.

Arsitektur module, formula v1, auth, dan database tidak diubah. Berita,
kepemilikan, foreign flow, dan volume spike dari referensi adalah peluang
lanjutan, bukan otomatis scope MVP. Recharts dipasang sesuai persetujuan.
Increment 1 sudah di-commit; increment 2 belum commit dan tidak ada push.
Smoke API berbayar dijalankan pada implementasi melalui adapter/ledger internal.

## Acceptance criteria tahap desain

- [x] Pembeda NusaLens terhadap referensi Sectors dijelaskan.
- [x] Alur pengguna dan rancangan desktop/mobile Temukan Saham tersedia.
- [x] Perilaku autocomplete, tabel, pemilihan compare, dan error ditentukan.
- [x] Kebutuhan grafik terhubung ke pertanyaan riset dan data sumber.
- [x] Ketidakpastian logo, pencarian provider, biaya, dan data ditandai.
- [x] Increment memiliki hasil pengguna, dependency, serta verifikasi.
- [x] User menginstruksikan "ok, lakukan bro" setelah rancangan ditulis.

## Dependency dan keputusan

User menyetujui arah riset terpandu dan melanjutkan perancangan setelah rollback.
Rincian dalam [PRD](prd.md) menjadi acuan increment 1 setelah instruksi lanjutan.
Baseline [keputusan](../../DECISIONS.md), [scoring](../../SCORING.md), dan
[alur data](../../DATA-FLOW.md) tetap berlaku. Permintaan kebebasan eksplorasi
berarti cakupan perusahaan tidak dibatasi ke contoh; pagination dan kebijakan
credit tetap diterapkan tanpa menyembunyikan hasil yang belum dimuat.

Sebagian dokumen baseline masih memuat status historis "module belum dibuat".
Status itu bukan inventaris source terkini. Temuan di atas berasal dari source;
penyelarasan status global dilakukan dalam scope terpisah, bukan mengubah
struktur aplikasi pada pekerjaan ini.

## Dokumen

- [PRD dan rancangan layar](prd.md).
- [Plan increment](plan.md).
- [Task dan hasil verifikasi](tasks.md).

## Handoff

- Perubahan: autocomplete nama/kode, logo dengan fallback, pagination server,
  cache satu jam, reservasi unik tiap fetch, profil overview real, error eksplisit,
  serta perbaikan lebar konten mobile. Route/nama lama dipertahankan.
- Verifikasi: test backend, typecheck, lint, build, browser desktop/mobile,
  input keyboard, back navigation, logo dan smoke real. Rincian di [tasks](tasks.md).
- Increment 2: tab harga 30/90 hari, keuangan empat kuartal dan valuasi historis;
  grafik Recharts, tabel lengkap, unit/periode, null, cache dan error eksplisit.
- Batas: wizard, filter sektor/tujuan, compare real dan menu ringkas belum
  diimplementasikan. Compare/Research lama masih menggunakan sumber fake;
  Discover/Company baru tidak mengarah ke alur tersebut sebagai kelanjutan analisis.
- Risiko: logo asset publik bukan kontrak API yang menjamin cakupan. Overview
  tidak menyediakan narasi bisnis sehingga tidak dibuat narasi sintetis.
- Lingkungan: Mailpit port 1025 tidak aktif saat registrasi QA; verifikasi akun
  QA dilakukan lokal. Tidak mengubah mail/auth aplikasi.
