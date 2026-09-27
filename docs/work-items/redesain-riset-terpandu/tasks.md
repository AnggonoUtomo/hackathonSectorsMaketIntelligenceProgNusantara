# Tasks: Redesain Riset Terpandu

## Sebelum mulai

- [x] Scope desain dan batas implementasi jelas.
- [x] Baca baseline produk, keputusan, alur data dan API.
- [x] Periksa source pencarian, detail, navigasi dan dependency chart.
- [x] Pertahankan folder referensi dan file user setelah rollback.

## Increment 0: Dokumentasi

- [x] Catat observasi Sectors dan pembeda produk.
- [x] Rancang alur, menu, desktop/mobile Temukan Saham.
- [x] Tentukan autocomplete, tabel, compare selection dan URL state.
- [x] Rancang wizard, grafik, states dan informasi sumber.
- [x] Pisahkan scope MVP dari peluang fitur lanjutan.
- [x] Catat validasi provider/logo/credit yang belum selesai.
- [x] Susun dependency, acceptance dan verifikasi increment.
- [x] Verifikasi link, konsistensi dokumen dan diff akhir.
- [x] User memberi instruksi lanjut implementasi.

## Increment 1: Search sampai detail real

- [x] Validasi pencarian nama/kode, overview dan pagination live; tarif memakai
      estimasi dokumentasi, bukan rekonsiliasi tagihan provider.
- [x] Validasi asset logo publik; fallback tetap tersedia untuk logo yang gagal.
- [x] Implementasikan autocomplete dan tabel hasil real dengan state lengkap.
- [x] Hubungkan detail identitas real dan back navigation.
- [x] Hapus pemakaian fake dari route Discover/Company dan verifikasi live.
      Class FakeCompanySnapshot tetap diperlukan Research; fake/UI lama yang
      tersisa tidak dipakai route Discover/Company baru. Tidak menghapus consumer lain.

## Increment 2: Detail dan grafik

- [x] Scope, endpoint, periode, cache dan estimasi credit dicatat sebelum coding.
- [x] Uji kontrak endpoint internal, auth, null, periode, cache dan batas credit.
- [x] Validasi periode, unit, seri harga/keuangan bank dan nonbank.
- [x] Implementasikan Recharts dan alternatif tabel dari data real.
- [x] Verifikasi grafik, mobile, data parsial, error dan credit.

## Increment 3: Wizard

- [ ] Audit kalkulator, populasi peer dan kelengkapan input.
- [ ] Implementasikan stepper bebas, penjelasan aturan, dan rincian bukti.
- [ ] Verifikasi formula/threshold, waktu sumber dan alur pengguna.

## UX-2A: Company Cockpit

- [x] Scope cockpit ditetapkan sebelum coding: detail perusahaan saja, tanpa
      endpoint baru dan tanpa auto-fetch section berbiaya.
- [x] Tambahkan shortcut riset di detail perusahaan untuk Ringkasan Riset,
      Profil, Harga, Keuangan, dan Valuasi.
- [x] Pertahankan tab keyboard accessible dan lazy fetch data real.
- [x] Verifikasi desktop/mobile, console, network section, typecheck dan build.
- [x] Perbarui handoff setelah verifikasi.

## UX-2B: Pintu masuk riset

- [x] Scope ditetapkan: UI hasil `/temukan-saham` saja, tanpa endpoint baru dan
      tanpa enrichment per baris.
- [x] Ubah action hasil pencarian menjadi aksi riset/cockpit yang jelas.
- [x] Bersihkan URL konteks balik agar keyword kosong tidak ikut dikirim.
- [x] Verifikasi tidak ada fetch tambahan saat daftar ditampilkan.
- [x] Jalankan typecheck, lint scoped, build dan browser QA desktop/mobile.
- [x] Perbarui handoff setelah verifikasi.

## UX-2C: Navigasi utama tanpa placeholder

- [x] Scope ditetapkan: sidebar dan shortcut dashboard saja; route lama tidak
      dihapus.
- [x] Sembunyikan menu utama yang masih fake/placeholder.
- [x] Sesuaikan shortcut dashboard agar hanya mempromosikan alur real.
- [x] Verifikasi route lama tetap ada.
- [x] Jalankan typecheck, lint scoped dan build.
- [x] Perbarui handoff setelah verifikasi.

## Increment 4: Eksplorasi

- [ ] Tentukan kriteria tujuan riset dan cakupan urutan hasil.
- [ ] Implementasikan sektor, tujuan, filter/sort, pagination dan selection.
- [ ] Verifikasi pola ContohUI dan hasil di luar halaman pertama.

## Increment 5: Compare dan navigasi

- [ ] Audit persistence/route lalu hubungkan compare dan snapshot real.
- [ ] Terapkan menu ringkas sambil mempertahankan akses route lama.
- [ ] Verifikasi ownership, versi, batas tiga dan alur lengkap.

## Hasil

- Perubahan: dokumen desain/increment dan alur pencarian sampai profil real.
- Otomatis final: `php artisan test --compact` lulus 107 test / 479 assertion;
  `npm run typecheck`, ESLint file TS/TSX berubah, `npm run build`,
  `vendor/bin/pint --dirty`, dan `git diff --check` lulus. Link relatif
  dokumentasi terverifikasi; route pencarian/perusahaan tetap terdaftar.
- Browser: desktop 1440px (sidebar expanded/collapsed), mobile 390px/320px;
  overflow horizontal halaman ditemukan lalu diperbaiki. Overflow tabel tetap
  berada dalam kontainer. Logo real berhasil, tanpa console warning/error
  pada halaman riset yang diuji.
- Interaksi real: autocomplete "central" = 4 hasil; keyboard memilih BBCA;
  Back mempertahankan ketikan; direktori = 962 emiten pada saat uji; page 2
  mengambil emiten berikutnya; BBCA, ADES dan AADI membuka profil real.
  Query tanpa kecocokan menampilkan empty state, bukan data contoh.
- Lingkungan: registrasi QA gagal mengirim email karena Mailpit 1025 mati;
  akun uji diverifikasi lokal. Ini temuan terpisah dari alur riset.
- Ledger validasi: 13 credit direservasi, terdiri dari 9 request Companies,
  2 overview BBCA, 1 ADES, dan 1 AADI (termasuk smoke kontrak awal).
  Ini estimasi ledger, bukan angka billing provider yang direkonsiliasi.
- Akun `nusalens-ui-qa-20260924@example.test` dipertahankan sebagai referensi
  ledger; verifikasi dicabut, password diacak ulang, dan sesi QA dihapus setelah
  pengujian. Script lokal sementara sudah dihapus. Data user lain tidak diubah.
- Risiko: cakupan logo tidak dijamin; biaya tercatat berupa reservasi konservatif,
  belum rekonsiliasi billing provider. Grafik/wizard dan compare real belum dibuat.
- Delivery increment 1: commit `0914215`, belum push; folder Hackaton dan dua
  file LSP tidak disentuh. Increment 2 dilanjutkan atas instruksi user.

## Hasil increment 2

- Pemeriksaan final: `php artisan test --compact` lulus 115 test / 533 assertion;
  typecheck, ESLint file berubah, build, Pint dan `git diff --check` lulus.
  Instalasi Recharts 3.10.1 menghasilkan audit npm tanpa vulnerability.
- Tab Profil/Harga/Keuangan/Valuasi, lazy fetch per section, Recharts line/bar,
  rentang 30/90 hari, pilihan metrik, tooltip dan alternatif tabel angka lengkap.
- Real BBCA/ADES: masing-masing 61 titik harga (29 Juni-23 September 2026),
  empat kuartal (30 September 2025-30 Juni 2026), lima tahun valuasi (2022-2026).
  Tahun berjalan diberi konteks belum final. Tidak membuat TTM, pertumbuhan,
  rekomendasi atau rasio risiko baru dari seri ini.
- Mapping null tetap null, utang ADES nol tetap nol; metrik bank hanya muncul
  bila sumber menyediakan angka. Satuan harga IDR, keuangan IDR (grafik miliar),
  rasio x. Angka tampil dua desimal. Tidak ada fallback fake.
- Credit: smoke analytics 12 reservasi credit (Daily 2, kuartal 8, valuasi 2),
  ditambah overview browser BBCA/ADES 2 credit. Kuota akun pengembangan
  tercapai pada smoke; sisa validasi memakai akun QA yang sudah tersedia,
  tanpa mengubah quota/global budget atau ledger. Cache hit tidak menambah biaya.
  Total tambahan 14 adalah estimasi ledger, bukan rekonsiliasi billing provider.
  Pengulangan smoke memakai cache: tambahan reservasi 0.
- Browser: desktop 1440px, mobile 390px/320px, grafik nyata dan tooltip,
  tabel empat kuartal, rentang 30 hari berisi 21 sesi, pilihan metrik bank,
  nonbank tanpa opsi bank, offline error dan retry online berhasil.
  Tidak ada horizontal overflow halaman; tabel punya scroll sendiri. Tab dapat
  dinavigasi keyboard, label kuartal menyertakan tahun. Sidebar expanded/collapsed,
  light/dark teruji; console halaman final tanpa warning/error.
- Client menolak JSON rusak/null/scalar/objek kosong, bukan menganggap seri kosong;
  test regresi ditambahkan. Shared request menjaga perilaku direktori sebelumnya.
- Increment 2 belum commit/push. Wizard, compare real, scoring dan navigasi
  berikutnya tidak dikerjakan. Folder Hackaton, file LSP dan data user tetap.
- Akun QA dinonaktifkan kembali (verifikasi dicabut, password diacak, sesi
  dihapus); ledger tetap. Script smoke/setup sementara dihapus setelah verifikasi.

## Hasil UX-2A

- Perubahan: `CompanyAnalysis` sekarang menampilkan shortcut cockpit berupa grid
  tab berikon untuk Ringkasan Riset, Profil, Harga, Keuangan dan Valuasi. Tab
  tetap memakai `role=tablist`, Arrow/Home/End tetap berpindah section, dan
  section analitik tetap lazy fetch.
- Verifikasi otomatis: `npm run typecheck`, ESLint scoped
  `resources/js/components/company-analysis.tsx`, `npm run build`, dan
  `git diff --check` lulus.
- Browser QA: harness lokal sementara dengan SQLite memory dan HTTP fake, tidak
  menyentuh `.env`, DB user, atau credit Sectors. Desktop 1365px menampilkan
  lima shortcut, console bersih, dan tidak ada XHR/fetch sebelum section dibuka.
  Klik Harga baru memanggil `/nusalens/companies/BBCA/analysis?section=prices`
  dan grafik tampil.
- Mobile QA: viewport 320px tanpa horizontal overflow; console bersih; reload
  awal tidak memanggil XHR/fetch section; ArrowRight dari tab Ringkasan berpindah
  ke Profil dengan fokus tetap pada tab.
- Cleanup: server QA dihentikan dan router sementara `storage/framework/testing/ux2-router.php`
  dihapus. Tidak ada endpoint baru, data fake baru, atau commit/push otomatis.

## Hasil UX-2B

- Perubahan: tabel `/temukan-saham` memakai kolom `Riset` dengan tombol
  ikon+teks `Riset` menuju Company Cockpit. Link nama perusahaan tetap membuka
  detail yang sama. URL `from` sekarang hanya menyertakan `keyword` ketika ada
  isinya, sambil tetap membawa `page` dan `limit`.
- Verifikasi otomatis: `npm run typecheck`, ESLint scoped
  `resources/js/pages/nusalens/discover.tsx`, `npm run build`, dan
  `git diff --check` lulus.
- Browser QA: harness lokal sementara dengan SQLite memory dan HTTP fake, tidak
  menyentuh `.env`, DB user, atau credit Sectors. Desktop 1365px menampilkan
  kolom `Riset`, link membawa `/temukan-saham?page=1&limit=10&keyword=bank`,
  console bersih, dan tidak ada XHR/fetch/prefetch tambahan saat daftar dibuka.
- Mobile QA: viewport 320px tanpa horizontal overflow halaman; dua link riset
  tersedia; console bersih; tidak ada XHR/fetch/prefetch tambahan.
- Cleanup: server QA dihentikan dan router sementara `storage/framework/testing/ux2b-router.php`
  dihapus. Tidak ada endpoint baru, provider call baru, commit, atau push
  otomatis untuk UX-2B.

## Hasil UX-2C

- Perubahan: sidebar utama hanya menampilkan Dashboard dan Temukan Saham. Menu
  Bandingkan, Jelaskan Nilai dan Kandidat Menarik tidak dihapus routenya, tetapi
  tidak lagi dipromosikan sebagai menu utama sampai alurnya real. Dashboard hanya
  menampilkan shortcut Temukan Saham.
- Verifikasi otomatis: `npm run typecheck`, ESLint scoped
  `resources/js/components/app-sidebar.tsx resources/js/pages/dashboard.tsx`,
  `npm run build`, `php artisan route:list | Select-String -Pattern
'bandingkan|jelaskan-nilai|kandidat-menarik'`, dan `git diff --check` lulus.
- Browser smoke: tertunda. Chrome DevTools MCP tidak tersedia setelah interup
  lanjutan dan Playwright tidak terpasang lokal, sehingga tidak ada klaim browser
  PASS untuk UX-2C pada handoff ini.
- Cleanup: server QA sementara dihentikan dan router `storage/framework/testing/ux2c-router.php`
  dihapus. Tidak ada endpoint/provider call baru, tidak ada route lama yang
  dihapus, dan UX-2C belum commit/push.

Jangan menambah scope ke checklist tanpa instruksi user.
