# Work Item: Redesain Riset Terpandu

## Status dan owner

- Status: increment 0-2 selesai; UX-1 selesai; UX-2A selesai; UX-2B selesai; UX-2C selesai; UX-2D selesai; UX-2E selesai; UX-2F selesai.
- Kelanjutan 2026-09-26 UX-2: setelah menu `/perusahaan` digabung ke
  `/temukan-saham`, user menyetujui rekomendasi untuk merapikan alur riset utama
  sebelum masuk scoring besar. Tahap ini fokus pada Company Cockpit di detail
  perusahaan agar data real yang sudah ada lebih mudah dibaca oleh pengguna awam.
  Tidak menambah endpoint Sectors, tidak menjalankan fetch otomatis baru, dan
  tidak mengubah formula.
- Kelanjutan 2026-09-26: increment 3A Ringkasan Riset real selesai pada
  [work item Ruang Riset Berbukti](../ruang-riset-berbukti/README.md).
  Wizard/scoring lama tidak otomatis dikerjakan; 3B-5 masih menunggu gate.
- Review lanjutan 2026-09-25: rencana increment 3-5 perlu ditinjau bersama
  [proposal Ruang Riset Berbukti](../ruang-riset-berbukti/README.md) sebelum coding.
  Detail proposal belum menggantikan keputusan accepted tanpa persetujuan user.
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

UX-2A hanya menyusun ulang pengalaman membaca detail perusahaan dari data yang
sudah tersedia: Ringkasan Riset, Profil, Harga, Keuangan, dan Valuasi. Scope ini
tidak membuat skor prioritas, tidak menghubungkan compare real, tidak membuka
semua data otomatis, dan tidak membuat wizard multi-langkah penuh.

UX-2B merapikan `/temukan-saham` sebagai pintu masuk riset. Scope ini hanya
memperjelas aksi dari hasil pencarian ke Company Cockpit dan menjaga konteks
query balik. Tidak menambah filter, sort, endpoint Sectors, compare real, atau
enrichment per baris.

UX-2C merapikan navigasi utama agar tidak mempromosikan modul yang masih fake
atau placeholder. Route lama tetap dipertahankan untuk kompatibilitas, tetapi
sidebar dan shortcut dashboard hanya menampilkan alur real yang siap diuji.

UX-2D merapikan entry legacy `/jelaskan-nilai` tanpa symbol agar tidak membuka
halaman pencarian kedua yang mirip `/temukan-saham`. Entry tanpa symbol diarahkan
ke `/temukan-saham`, sementara `?symbol=` tetap mengarah ke detail perusahaan.

UX-2E merapikan entry legacy `/kandidat-menarik` agar tidak membuka placeholder.
Route lama tetap dipertahankan, tetapi diarahkan ke `/temukan-saham` sampai
eksplorasi tujuan riset real tersedia.

UX-2F merapikan Company Cockpit agar setelah membaca Ringkasan Riset pengguna
melihat langkah lanjutan yang nyata: cek Harga, Keuangan, Valuasi, Profil, atau
kembali mencari pembanding. Scope ini tidak mempromosikan compare fake dan tidak
menghitung Nilai Prioritas Riset final.

Arsitektur module, formula v1, auth, dan database tidak diubah. Berita,
kepemilikan, foreign flow, dan volume spike dari referensi adalah peluang
lanjutan, bukan otomatis scope MVP. Recharts dipasang sesuai persetujuan.
Increment 1 sudah di-commit; increment 2 di-commit sebagai `4a1571c`.
Belum ada push untuk rangkaian commit lokal tersebut pada handoff kajian ini.
Smoke API berbayar dijalankan pada implementasi melalui adapter/ledger internal.

## Acceptance criteria tahap desain

- [x] Pembeda NusaLens terhadap referensi Sectors dijelaskan.
- [x] Alur pengguna dan rancangan desktop/mobile Temukan Saham tersedia.
- [x] Perilaku autocomplete, tabel, pemilihan compare, dan error ditentukan.
- [x] Kebutuhan grafik terhubung ke pertanyaan riset dan data sumber.
- [x] Ketidakpastian logo, pencarian provider, biaya, dan data ditandai.
- [x] Increment memiliki hasil pengguna, dependency, serta verifikasi.
- [x] User menginstruksikan "ok, lakukan bro" setelah rancangan ditulis.

## Acceptance criteria UX-2A

- [x] Detail perusahaan terasa sebagai cockpit riset, bukan kumpulan tab yang
      berdiri sendiri.
- [x] Shortcut riset mengarah ke data real yang sudah ada: ringkasan, profil,
      harga, keuangan, dan valuasi.
- [x] Tab lama tetap keyboard accessible dan tidak memicu fetch sebelum user
      membuka section terkait.
- [x] Tidak ada endpoint Sectors baru, tidak ada data fake baru, dan tidak ada
      credit tambahan hanya karena halaman detail pertama kali dibuka.
- [x] Browser desktop/mobile tidak overflow dan tetap menampilkan tabel/grafik
      dengan fallback yang sudah ada.

## Acceptance criteria UX-2B

- [x] Hasil pencarian menampilkan aksi riset yang jelas menuju Company Cockpit.
- [x] Link detail tetap membawa konteks keyword, halaman, dan limit tanpa query
      kosong yang tidak perlu.
- [x] Tidak ada provider call tambahan dari perubahan UI ini.
- [x] Pola tabel tetap sesuai aturan ContohUI: padat, mudah dipindai, aksi kanan
      berbasis ikon atau ikon+teks yang jelas.
- [x] Typecheck, build/lint scoped, dan browser desktop/mobile lulus.

## Acceptance criteria UX-2C

- [x] Sidebar utama hanya menampilkan alur yang real dan siap diuji.
- [x] Dashboard tidak mempromosikan compare/kandidat yang masih fake atau
      placeholder.
- [x] Route `/bandingkan`, `/jelaskan-nilai`, dan `/kandidat-menarik` tidak
      dihapus atau diubah kontraknya pada tahap ini.
- [x] Tidak ada endpoint atau provider call baru.
- [x] Typecheck dan build/lint scoped lulus; browser smoke tertunda karena
      Chrome DevTools MCP tidak tersedia setelah interup dan Playwright tidak
      terpasang lokal.

## Acceptance criteria UX-2D

- [x] `/jelaskan-nilai` tanpa symbol redirect ke `/temukan-saham`.
- [x] `/jelaskan-nilai?symbol=ADES` tetap redirect ke `/perusahaan/ADES` tanpa
      provider call.
- [x] Validasi symbol invalid tetap mengembalikan error seperti sebelumnya.
- [x] Tidak ada endpoint Sectors, provider call, atau route baru.
- [x] Test navigasi NusaLens lulus.

## Acceptance criteria UX-2E

- [x] `/kandidat-menarik` redirect ke `/temukan-saham`.
- [x] Route bernama `candidates` tetap ada untuk kompatibilitas deep link.
- [x] Guest dan unverified user tetap mengikuti proteksi auth/verified.
- [x] Tidak ada endpoint Sectors, provider call, atau route baru.
- [x] Test navigasi NusaLens lulus.

## Acceptance criteria UX-2F

- [x] Company Cockpit menampilkan jalur langkah lanjut yang jelas dari satu saham.
- [x] Aksi lanjut hanya mengarah ke data real yang sudah tersedia: Profil, Harga,
      Keuangan, Valuasi, dan kembali ke Temukan Saham.
- [x] Ringkasan Riset tetap default dan tidak auto-fetch sebelum user menekan
      tombol buka ringkasan.
- [x] Membuka aksi Harga/Keuangan/Valuasi tetap lazy fetch sesuai section yang
      dipilih, tanpa endpoint baru.
- [x] UI tetap keyboard accessible lewat button/link native dan tablist existing.
- [x] Typecheck, lint scoped, build, dan diff check lulus.

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
