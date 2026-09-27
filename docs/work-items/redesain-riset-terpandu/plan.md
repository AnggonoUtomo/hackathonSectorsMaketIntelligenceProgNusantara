# Plan: Redesain Riset Terpandu

## Scope

Rancangan selesai dan user menginstruksikan kelanjutan implementasi increment 1.
User menyetujui increment 2 setelah commit increment 1 (`0914215`).
Increment 3-5 tidak otomatis dikerjakan. Pada 2026-09-26 user menyetujui UX-2A:
rapikan detail perusahaan menjadi Company Cockpit sebelum scoring/wizard penuh.
Setelah UX-2A di-commit, UX-2B dilanjutkan untuk memperjelas aksi riset pada
`/temukan-saham`. Setelah UX-2B di-commit, UX-2C dilanjutkan untuk menyembunyikan
entry utama yang masih fake/placeholder dari navigasi. Setelah UX-2C di-commit,
UX-2D merapikan deep link `/jelaskan-nilai` tanpa symbol agar kembali ke
`/temukan-saham` dan tidak menjadi halaman pencarian kedua. UX-2E melanjutkan
pola yang sama untuk `/kandidat-menarik`, karena halaman itu masih placeholder
dan belum punya data real. UX-2F merapikan Company Cockpit agar user mendapat
langkah lanjut setelah Ringkasan Riset tanpa masuk ke compare/scoring fake.
Formula, auth, persistence snapshot, dan batas module tetap mengikuti baseline.

## Increment 0: Rancangan pengalaman

- Perubahan: PRD, wireframe, states, peta kebutuhan data, plan/tasks dan indeks.
- Prasyarat: persetujuan arah desain ulang setelah rollback; sudah diberikan.
- Acceptance: alur dan layar dapat ditinjau tanpa membaca kode.
- Verifikasi: cocokkan keputusan aktif, source, referensi lokal, link dan diff.
- Hasil: dokumentasi selesai; instruksi lanjut implementasi sudah diterima.

## Increment 1: Cari perusahaan sampai detail real

- Status: selesai. Pencarian parsial terstruktur dan overview tervalidasi live;
  logo memakai asset publik Sectors dengan fallback. Sektor tidak tersedia pada
  payload search teruji, sehingga tabel awal hanya identitas. Klasifikasi ada
  pada detail; filter sektor masuk increment 4. Tidak ada data contoh pengganti.
- Hasil pengguna: ketik nama/kode, lihat logo yang tersedia, pilih emiten dan
  langsung baca profil real; kembali ke hasil tanpa kehilangan state.
- Owner: Screening mengatur pencarian; Company menyajikan profil;
  MarketData menangani sumber, mapping, cache dan credit.
- Prasyarat: review desain; verifikasi dokumentasi resmi dan payload terarah
  untuk pencarian parsial, pagination, identitas, logo dan overview.
- Perubahan: adapter/contract sesuai arsitektur, autocomplete, hasil paginated,
  detail identitas, status data/error. Hilangkan fake dari alur yang diganti
  setelah consumer audit; jangan mengembalikan fake saat real gagal.
- Endpoint kandidat: Companies dan Company Report section overview; sumber
  logo belum ditetapkan. Structured search lebih diutamakan bila mendukung nama.
- Estimasi baseline: jika terbukti structured, satu halaman + satu overview
  sekitar 2 credit tanpa retry/cache. Natural query akan berbeda; hitung dan
  catat estimasi aktual sebelum smoke. Jangan panggil report per hasil saran.
- Cache: hasil screener 1 jam; identitas 7 hari jika terpisah dari data pasar.
- Acceptance: nama parsial dan ticker real bekerja, hasil tidak dibatasi ke
  contoh, pagination benar, detail tidak fake, logo/fallback jujur.
- Verifikasi: kontrak HTTP terkendali, auth, escaping, respons datang terbalik,
  cache/ledger/error; browser desktop/mobile/keyboard; smoke real terukur.

## Increment 2: Data perusahaan dan grafik real

- Status: selesai; hasil verifikasi dan batas data tercatat di tasks.
- Implementasi: tab Harga, Keuangan dan Valuasi dimuat atas permintaan;
  overview tetap ringan. Endpoint internal tervalidasi dan wajib auth/verified.
- Batas fetch: Daily 90 hari (1 credit), empat kuartal terbaru (4 credit),
  Company Report hanya valuation (1 credit). Maksimal 6 credit cold per emiten
  di luar overview; rentang 30/90 hari memakai seri sama tanpa fetch tambahan.
- Financials memakai nilai kuartalan IDR sesuai kontrak provider, bukan TTM
  atau pertumbuhan hasil asumsi. Neraca adalah posisi pada tanggal laporan.
  Metrik bank dari financials_sector_metrics hanya tampil jika tersedia.
- Cache harga/valuasi 1 jam, kuartalan 24 jam; waktu fetch tidak berubah saat hit.
  Tidak melakukan retry otomatis berbiaya atau fallback fake.
- Smoke terarah BBCA dan ADES maksimal 12 credit sebelum overview, quota tetap.
- Referensi: [Daily](https://docs.sectors.app/api-references/v2/indonesia/transaction/daily),
  [Quarterly](https://docs.sectors.app/api-references/v2/indonesia/report/quarterly-financials),
  [Company Report](https://docs.sectors.app/api-references/v2/indonesia/report/company-report).

- Hasil pengguna: memahami tren harga dan kinerja melalui Recharts serta tabel.
- Owner: Company, MarketData, frontend.
- Prasyarat: increment 1; validasi seri, periode dan unit; dependency Recharts
  dipasang pada tahap implementasi sesuai persetujuan penggunaan library.
- Endpoint: Daily, Quarterly Financials, Company Report financials/valuation
  sesuai kebutuhan; tanpa semua section default.
- Estimasi baseline cold cache: Daily 1 + q kuartal + s section credit;
  q/s ditetapkan sebelum smoke dan bertambah hanya atas kebutuhan data.
- Cache: fundamental 24 jam, pasar/valuasi berbasis harga 1 jam.
- Acceptance: profil, harga, kinerja, valuasi dan risiko menampilkan data
  tersedia; grafik mempunyai periode/unit dan alternatif tabel; tidak ada
  seri sintetis atau nol pengganti data hilang.
- Verifikasi: mapping bank/nonbank, periodisasi, null, typecheck/build,
  screenshot desktop/mobile, tooltip/rentang grafik dan smoke terarah.

## Increment 3: Wizard dan penjelasan berbukti

- Hasil pengguna: mengikuti lima langkah, membaca ringkasan dan membuka bukti.
- Owner: Intelligence menghitung; Research menjelaskan; Company/MarketData
  menyediakan input; frontend mengelola navigasi wizard.
- Prasyarat: increment 2 dan audit kalkulator/peer yang sudah tersedia;
  wiring yang kurang dilengkapi tanpa mengubah formula v1.
- Endpoint: section/seri untuk input dan semua peer valid yang diperlukan;
  daftar konkret ditentukan setelah audit kebutuhan metrik.
- Credit: belum dapat ditetapkan per satu perusahaan tanpa cakupan peer;
  wajib breakdown cold/warm cache sebelum eksekusi, bukan janji <=10 credit.
- Cache: TTL input mengikuti baseline, skor reuse pada input/peer/formula sama.
- Acceptance: langkah bebas, nilai total hanya saat layak, sumber/peer/periode
  dapat ditelusuri; penjelasan aturan wajib, AI tidak diperlukan.
- Verifikasi: kalkulator bank/nonbank, peer tidak cukup, threshold 70%,
  keselarasan periode, error parsial, serta alur wizard di browser.

## UX-2A: Company Cockpit sebelum wizard

- Status: selesai.
- Hasil pengguna: dari halaman detail, pengguna langsung melihat jalur riset
  ringkas dan bisa membuka Ringkasan Riset, Profil, Harga, Keuangan, atau
  Valuasi tanpa bingung mencari tab.
- Owner: Company, Research, MarketData dan frontend.
- Prasyarat: increment 1-2 dan 3A sudah tersedia; UX-1 sudah menghapus duplikasi
  menu `/perusahaan`.
- Perubahan: tambahkan shortcut riset di area detail perusahaan, perkuat tab
  sebagai cockpit data real, dan pertahankan lazy fetch tiap section.
- Endpoint/credit: tidak ada endpoint baru. Overview tetap diambil saat detail
  dibuka; Ringkasan Riset, Harga, Keuangan, dan Valuasi tetap hanya fetch saat
  user membuka section terkait atau menekan tombol muat ringkasan.
- Cache: tidak berubah; mengikuti cache profile, research, daily, quarterly dan
  valuation yang sudah ada.
- Acceptance: cockpit tidak memuat data berbayar baru secara otomatis, tab tetap
  accessible, tidak ada data fake baru, dan mobile/desktop tidak overflow.
- Verifikasi: typecheck, build/lint scoped bila perlu, browser desktop/mobile,
  console/network untuk memastikan section belum terbuka tidak fetch otomatis.
- Hasil: shortcut riset menggantikan tab tipis menjadi grid cockpit dengan ikon,
  label dan deskripsi pendek. Ringkasan Riset tetap default dan tidak auto-fetch;
  Harga/Keuangan/Valuasi tetap lazy fetch saat section dibuka.

## UX-2B: Pintu masuk riset dari Temukan Saham

- Status: selesai setelah UX-2A di-commit.
- Hasil pengguna: dari hasil pencarian, pengguna melihat aksi "Riset" yang jelas
  untuk membuka Company Cockpit perusahaan terpilih.
- Owner: Screening, Company dan frontend.
- Prasyarat: UX-1 alias route selesai; UX-2A cockpit tersedia.
- Perubahan: ubah bahasa/action table dari detail generik menjadi riset/cockpit,
  bersihkan URL `from` agar hanya membawa keyword saat ada isinya, dan pertahankan
  pagination/limit.
- Endpoint/credit: tidak ada endpoint atau provider call baru. Pencarian tetap
  memakai request yang sama; detail tetap mengambil overview saat user membuka
  perusahaan.
- Acceptance: aksi kanan jelas, link nama tetap dapat dibuka, URL balik bersih,
  tabel tetap padat dan mobile tetap memiliki overflow internal.
- Verifikasi: typecheck, ESLint scoped, build, browser desktop/mobile dan network
  untuk memastikan tidak ada fetch tambahan saat hanya melihat daftar.
- Hasil: kolom kanan berubah menjadi aksi `Riset` menuju Company Cockpit,
  tooltip dan `aria-label` menjelaskan tujuan aksi, serta query `from` tidak
  mengirim `keyword` kosong.

## UX-2C: Navigasi utama tanpa placeholder

- Status: selesai setelah UX-2B di-commit.
- Hasil pengguna: menu utama tidak mengajak pengguna membuka compare, kandidat,
  atau jelaskan nilai yang belum menjadi alur real penuh.
- Owner: frontend, Screening, Comparison dan Research sebagai route yang tetap
  dipertahankan.
- Prasyarat: UX-1 sampai UX-2B selesai.
- Perubahan: sidebar menampilkan Dashboard dan Temukan Saham sebagai alur utama
  yang siap diuji; dashboard shortcut mengikuti alur real yang sama. Route lama
  tetap ada untuk deep link dan pekerjaan lanjutan.
- Endpoint/credit: tidak ada endpoint atau provider call baru.
- Acceptance: menu utama tidak mengarah ke fake/placeholder, dashboard tidak
  mempromosikan compare/kandidat, route lama tidak rusak, dan menu aktif tetap
  benar pada detail perusahaan.
- Verifikasi: typecheck, ESLint scoped, build, route lama, dan browser smoke
  dashboard/sidebar bila tool browser tersedia.
- Hasil: sidebar utama hanya menampilkan Dashboard dan Temukan Saham; dashboard
  hanya mempromosikan alur Temukan Saham yang real. Route compare/research/
  kandidat tetap tersedia untuk deep link dan pekerjaan lanjutan.

## UX-2D: Entry legacy riset tanpa duplikasi pencarian

- Status: selesai.
- Hasil pengguna: deep link `/jelaskan-nilai` tanpa emiten membawa pengguna ke
  pintu masuk riset yang sama, yaitu `/temukan-saham`, bukan ke layar pencarian
  lain yang membuat alur terasa dobel.
- Owner: Research route dan frontend navigation contract.
- Prasyarat: UX-2C selesai dan route lama tetap dipertahankan.
- Perubahan: redirect `/jelaskan-nilai` tanpa `symbol` ke route `discover`;
  pertahankan redirect symbol valid ke detail perusahaan dan validasi symbol
  invalid yang sudah ada.
- Endpoint/credit: tidak ada endpoint, adapter, atau provider call baru.
- Acceptance: no-symbol redirect ke `/temukan-saham`, symbol valid redirect ke
  `/perusahaan/{symbol}`, invalid symbol tetap error, dan test navigasi lulus.
- Verifikasi: RED/GREEN test feature untuk route research dan regresi route
  NusaLens.
- Hasil: `/jelaskan-nilai` tanpa symbol redirect ke `/temukan-saham`, sementara
  symbol valid tetap redirect ke detail perusahaan dan invalid symbol tetap
  memakai validasi route sebelumnya.

## UX-2E: Entry legacy kandidat tanpa placeholder

- Status: selesai.
- Hasil pengguna: deep link `/kandidat-menarik` membawa pengguna ke daftar saham
  real yang dapat dicari, bukan ke placeholder kandidat yang belum berbasis data.
- Owner: Screening route contract dan frontend navigation contract.
- Prasyarat: UX-2C selesai dan route lama tetap dipertahankan.
- Perubahan: redirect `/kandidat-menarik` ke route `discover`; tidak menghapus
  route bernama `candidates`.
- Endpoint/credit: tidak ada endpoint, adapter, atau provider call baru.
- Acceptance: route redirect ke `/temukan-saham`, auth/verified tetap berlaku,
  route name tetap ada, dan test navigasi lulus.
- Verifikasi: RED/GREEN test feature untuk route candidates dan regresi route
  NusaLens.
- Hasil: `/kandidat-menarik` redirect ke `/temukan-saham` tanpa provider call
  dan tanpa menghapus route bernama `candidates`.

## UX-2F: Langkah lanjut Company Cockpit

- Status: selesai.
- Hasil pengguna: setelah membuka satu perusahaan dan membaca Ringkasan Riset,
  pengguna melihat aksi lanjutan yang jelas untuk memeriksa data real atau
  kembali mencari pembanding.
- Owner: Company, Research, MarketData dan frontend.
- Prasyarat: UX-2A sampai UX-2E selesai; Ringkasan Riset dan section analitik
  real sudah tersedia.
- Perubahan: tambahkan panel langkah lanjut pada Company Cockpit dan action row
  pada Ringkasan Riset. Aksi internal mengganti tab ke Profil, Harga, Keuangan,
  atau Valuasi; aksi eksternal kembali ke Temukan Saham.
- Endpoint/credit: tidak ada endpoint baru. Ringkasan tetap hanya fetch saat
  tombol buka ditekan; Harga/Keuangan/Valuasi tetap lazy fetch saat section
  dipilih.
- Acceptance: pengguna tidak mentok setelah Ringkasan Riset, tidak ada promosi
  compare fake, tidak ada auto-fetch baru, dan UI tetap accessible/mobile-safe.
- Verifikasi: typecheck, ESLint scoped, build, diff check, serta browser smoke
  bila tool tersedia.
- Hasil: Company Cockpit menampilkan panel langkah berikutnya dan Ringkasan
  Riset menampilkan action row setelah checks. Aksi menuju Profil, Harga,
  Keuangan, Valuasi, dan Temukan Saham; compare fake tidak dipromosikan.

## Increment 4: Eksplorasi dengan tujuan riset

- Hasil pengguna: menemukan kandidat dengan kriteria terlihat dan tabel lengkap.
- Owner: Screening, Intelligence, MarketData, frontend.
- Prasyarat: increment 3; definisi kriteria untuk setiap tujuan dan cakupan
  pengurutan global tervalidasi. Jangan mengurutkan halaman lokal seolah global.
- Endpoint: Companies dan enrichment yang terbukti diperlukan; biaya mengikuti
  pagination dan input yang belum tersedia. Cache screener 1 jam.
- Acceptance: sektor real, tujuan berbasis formula, URL state, reset, summary,
  kolom, pagination dan seleksi compare lintas halaman konsisten.
- Verifikasi: filter/sort global, batas data yang baru dimuat, back navigation,
  null dan loading/error; QA pola tabel ContohUI.

## Increment 5: Compare real dan penyederhanaan navigasi

- Hasil pengguna: bandingkan maksimal tiga, simpan manual, buka dan perbarui
  snapshot sebagai versi baru; navigasi utama mengikuti alur yang disetujui.
- Owner: Comparison, Company, Research, Intelligence dan frontend.
- Prasyarat: audit persistence/route dan consumer; jangan mengasumsikan fitur
  tersimpan sudah lengkap dari DTO/builder yang ada.
- Endpoint: reuse data company/score, fetch hanya input kurang/kedaluwarsa;
  estimasi berdasarkan gabungan kebutuhan tiga saham dan peer yang overlap.
- Cache: tetap mempertahankan waktu sumber; penyimpanan tidak menyegarkan data.
- Acceptance: data compare real, konteks bank/nonbank jujur, snapshot privat
  immutable, route lama tidak rusak, menu baru tidak menuju placeholder.
- Verifikasi: ownership antar-user, batas tiga, versi baru, tanggal snapshot,
  navigasi lama dan alur ujung-ke-ujung desktop/mobile.

## Batas berhenti dan pemulihan

Setiap increment selesai dengan hasil verifikasi dan dokumentasi yang diperbarui.
Tidak otomatis lanjut increment/commit/push. Jika kontrak provider tidak
mendukung requirement, catat gap dan alternatif yang nyata sebelum memperluas
implementasi. Jangan menutup gap dengan fake, mengubah rumus, atau menghapus data.

Automated test mengikuti AGENTS.md dengan fake HTTP. Smoke real memakai adapter
internal, mencatat endpoint, credit dan cache tanpa secret; tidak dijalankan pada
increment 0. Acceptance real diuji pada increment implementasi terkait.
