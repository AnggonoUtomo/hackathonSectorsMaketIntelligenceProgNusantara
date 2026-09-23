# Plan: Redesain Riset Terpandu

## Scope

Rancangan selesai dan user menginstruksikan kelanjutan implementasi increment 1.
Increment 2-5 tetap merupakan urutan berikutnya; tidak otomatis dikerjakan.
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
