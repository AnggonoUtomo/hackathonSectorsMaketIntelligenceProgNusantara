# Plan: Ruang Riset Berbukti

## Scope

User menginstruksikan lanjut pada 2026-09-25 setelah kajian login dihentikan.
Scope aktif adalah increment 3A. Nomor 3A dan seterusnya mengacu pada
kelanjutan increment 0-2 redesain riset terpandu, bukan restart proyek.
Increment 3B-5 tetap usulan, bukan otorisasi implementasi sekaligus.

Pada 2026-09-26 user meminta dokumentasi lanjutan dan melaporkan duplikasi menu,
lalu menyetujui implementasi UX-1. UX-1 selesai; scope 3B masih dokumentasi.

## Tahap 0: Kajian dan rancangan

- Perubahan: inventaris referensi, PRD, plan, tasks, serta tautan indeks.
- Prasyarat: baca dokumen aktif, inspeksi source, dan halaman guest Sectors.
- Acceptance: observasi dibedakan dari asumsi; gap, biaya, dan keputusan jelas.
- Verifikasi: browser read-only, inspeksi source, tautan Markdown lokal,
  `git diff --check`, serta status diff docs-only.
- Biaya API NusaLens: tidak ada panggilan provider pada tahap ini.

## Increment 3A: Ringkasan Riset satu perusahaan

### Rincian implementasi sebelum coding

- Public contract `Company/Application/Contracts/CompanyResearchData` memiliki
  consumer nyata `Research/Application/GetResearchSummary`; implementasinya
  menggabungkan overview dan financials melalui port MarketData existing.
- `Research/Domain/ResearchSummary` adalah aturan murni tanpa Laravel/HTTP;
  waktu evaluasi diberikan use case. Binding ada di AppServiceProvider.
- Endpoint JSON `/nusalens/companies/{symbol}/research` wajib auth/verified dan
  throttle. UI default tab Ringkasan Riset pada detail, dengan tombol pengambilan
  eksplisit dan estimasi hingga 5 credit saat semua sumber cold. Perpindahan tab
  tidak mengulang request ringkasan. Profil/harga/keuangan/valuasi tetap tersedia.
- Route `/jelaskan-nilai` tetap bernama `research`: tanpa symbol menjadi pintu
  pencarian, dengan symbol valid menuju detail real. Tidak lagi mengirim skor fake.
  Compare legacy di luar scope dan tidak ditautkan sebagai hasil real.
- Sumber resmi diperiksa 2026-09-25:
  [Quarterly Financials](https://docs.sectors.app/api-references/v2/indonesia/report/quarterly-financials)
  menyatakan revenue/earnings kuartalan IDR dan aset/ekuitas IDR. Basis cash flow
  quarterly vs YTD belum eksplisit: tidak membuat kesimpulan laba-versus-kas.
- Aturan pertama: tanda laba terbaru; margin laba hanya perusahaan nonkeuangan
  terklasifikasi dengan revenue positif; tanda ekuitas pada tanggal laporan.
  Tiap aturan menyertakan input, rumus/kondisi, periode, sumber, keterbatasan,
  dan pemeriksaan berikutnya. Tidak menghitung growth, skor, atau ranking.
- Tidak ada fallback ke periode lama jika metrik laporan terbaru null. Missing,
  basis/unit tidak cocok, cache kedaluwarsa dan error provider dibedakan. Tanggal
  fetchedAt tidak menyatakan laporan merupakan periode terbaru bursa.
- Pengujian HTTP memakai fake terisolasi; production tetap adapter real.
  Browser QA boleh memakai response fixture terisolasi tanpa mengubah DB user
  atau konfigurasi provider. Tidak ada smoke berbayar pada scope ini.

- Prasyarat: persetujuan alur PRD; bukti mapping periode, basis, unit, dan field.
- Ownership: Research menyusun temuan deterministik; Company menyediakan public
  contract data; MarketData mempertahankan pengambilan, mapping, cache, dan credit.
  Tidak ada import adapter konkret lintas module. Audit consumer legacy sebelum
  melepas ketergantungan snapshot fake; jangan membangun ulang grafik real.
- Perubahan: ringkasan pada konteks perusahaan, panel bukti, keterbatasan, serta
  pemeriksaan berikutnya. Mulai dari fakta yang tersedia, tanpa syarat nilai
  total, peer, AI, atau persistence baru. Bank dan nonbank tetap dapat dibuka;
  aturan interpretasi mengikuti jenis perusahaan dan ketersediaan data.
- Endpoint: overview Company Report dan quarterly yang sudah dipakai adapter.
  Harga/valuation hanya bila dibutuhkan; tidak menambah section secara default.
- Credit/cache: estimasi cold overview 1 + empat kuartal 4; cache valid 0 untuk
  input tersebut. Overview bercampur harga memakai TTL 1 jam, financial 24 jam.
  Audit fallback terhadap DATA-FLOW; jangan menganggap semua fallback sudah ada.
- Acceptance: temuan bisa ditelusuri, input sama menghasilkan output sama,
  unavailable tidak menjadi nol, tidak ada fakta fake pada jalur baru, dan
  loading/error tidak berubah menjadi kesimpulan bisnis.
- Verifikasi: unit aturan untuk bank/nonbank/keuangan nonbank, null, nol,
  negatif, beda unit/periode/YTD, dan freshness; feature auth/verified,
  provider error, cache/credit; typecheck/build; browser desktop/mobile,
  keyboard, tooltip, grafik dan panel bukti. Smoke real hanya setelah estimasi
  dan scope disetujui; tidak memanggil API pada setiap render/prefetch.
- Batas berhenti: handoff satu alur end-to-end, evaluasi pengguna, lalu review
  hasil sebelum increment berikutnya.

## Increment UX-1: Satu pintu pencarian, selesai

User menyetujui implementasi UX-1 pada 2026-09-26. Scope tetap navigasi;
audit live dan implementasi scoring 3B belum diotorisasi. Tidak commit/push otomatis.

- Tujuan: hilangkan pilihan menu yang identik, tanpa mengubah akses detail atau
  rumus. Increment kecil ini dapat dikerjakan sebelum audit scoring 3B.
- Bukti source: dua route menuju `CompanySearchController::index` dan
  `nusalens/discover`; sidebar memiliki dua entry; Dashboard memiliki shortcut
  Temukan Saham dan Detail Perusahaan yang sama-sama menuju daftar.
- Perubahan yang diusulkan: satu entry/sidebar dan shortcut Temukan Saham;
  `/perusahaan` menjadi redirect 302 protected ke `/temukan-saham`. Pertahankan
  nama route existing, detail `/perusahaan/{symbol}`, dan consumer detail.
- Query: whitelist keyword/page/limit dengan validasi yang konsisten pada daftar.
  Tidak meneruskan target redirect eksternal atau query arbitrer. Redirect tidak
  menjalankan use case pencarian atau reservasi credit.
- Consumer audit: `app-sidebar.tsx`, `nav-main.tsx`, `pages/dashboard.tsx`,
  `pages/nusalens/company-profile.tsx`, `discover.tsx`, `research-start.tsx`,
  serta link detail legacy. Sesuaikan fallback breadcrumb/back; pertahankan
  parameter `from` yang sudah divalidasi untuk kembali ke hasil.
- Active state: gunakan pathname terurai, bukan persamaan seluruh URL termasuk
  query. Kenali daftar canonical dan segmen detail yang tepat; jangan memakai
  prefix longgar yang mengaktifkan route tidak terkait.
- Audit prefetch pada entry pencarian dan alias agar hover/render tidak memicu
  fetch berbiaya; batasi perubahan pada consumer terkait, bukan refactor navigasi umum.
- Ownership: Presentation route dan frontend. Tidak mengubah module Company,
  contract MarketData, schema, autentikasi, atau kebijakan pencarian.
- Endpoint/credit/cache: tidak ada endpoint Sectors baru. Redirect nol call;
  halaman tujuan memakai adapter/cache/quota yang sudah ada. Tidak menjanjikan
  seluruh kunjungan nol credit bila data tujuan memang cache miss.
- Test: ubah ekspektasi dua halaman identik pada `NusaLensNavigationTest`
  menjadi canonical render + redirect; uji query valid/invalid, auth/verified,
  tidak ada fetch pada redirect, serta detail 200/404 dan whitelist return URL.
- Browser: expanded/collapsed sidebar, mobile drawer, keyboard, active state
  dengan query, Dashboard, back/forward dan kembali ke hasil. Pastikan hanya
  satu pintu daftar dan tidak ada fetch akibat prefetch navigasi.
- Acceptance: alur PRD berjalan, bookmark kompatibel, filter/page terjaga,
  tidak ada API call tambahan dari alias. Verifikasi typecheck, lint/build,
  feature test terarah dan browser; default HTTP fake terisolasi.
- Batas berhenti: review dan persetujuan sebelum coding; handoff UX-1 sebelum
  memulai increment lain. Tidak menghapus route/detail atau file user.

## Increment 3B: Peer dan nilai yang dapat dijelaskan

Rincian audit, ownership/persistence, biaya, dan increment 3B.0-3B.2 ada pada
[Kesiapan Peer dan Scoring](../kesiapan-peer-scoring/README.md). Dokumentasinya
diaktifkan 2026-09-26; audit live dan implementasi belum diotorisasi.

- Prasyarat: audit ketersediaan endpoint/entitlement, biaya semua peer, basis
  data dan rumus sesuai SCORING. Module Intelligence belum ada: pembuatan module
  serta contract perlu proposal struktur terlebih dahulu.
- Perubahan: kalkulator pure PHP, bukti input/peer/versi formula, dan penjelasan
  hasil ke Ringkasan Riset. Tidak mengubah bobot atau ambang v1.
- Endpoint: belum dikunci. Verifikasi kebutuhan current/prior-year quarter,
  annual, P/E TTM, P/B MRQ, CAR/NPL, dan daily yang sesuai terlebih dahulu.
  Historical valuation atau empat kuartal existing belum mencukupi semua metrik.
- Credit/cache: hitung berdasarkan union cache miss target dan seluruh peer,
  endpoint, periode, serta pagination. Belum ada estimasi total yang terbukti;
  tidak boleh menjanjikan cukup dalam quota 20. Cache per jenis DATA-FLOW.
- Acceptance: minimal lima peer lain yang sah per metrik; semua peer valid
  dipakai, tanpa quota-based sampling; kelengkapan berbobot dan alasan tidak
  tersedianya skor benar. Harga untuk momentum memakai basis yang konsisten.
- Verifikasi: unit formula, ties, negative/zero denominators, peer eligibility,
  period fallback, completeness, determinisme, dan feature bukti sampai UI.
- Batas berhenti: bila data/biaya tidak memenuhi syarat, scoring belum tersedia.
  Ringkasan 3A tetap berjalan; jangan melonggarkan rumus untuk mengejar demo.

## Increment 4: Bandingkan alasan

- Prasyarat: 3A stabil. Bukti skor hanya ditambahkan jika gate 3B lulus; compare
  fakta tidak perlu memalsukan atau menunggu skor yang tidak tersedia.
- Ownership: Comparison mengatur pilihan maksimal tiga; Research menyediakan
  hasil penjelasan melalui contract, bukan akses adapter/persistence lintas module.
- Perubahan: migrasi consumer FakeComparisonBuilder ke data real; tampilkan
  persamaan, perbedaan, trade-off dan keterbatasan. Pertahankan route existing.
- Endpoint/credit/cache: gunakan kembali input terverifikasi 3A/3B, fetch hanya
  kekurangan union perusahaan. Tidak memakai perkiraan tiga perusahaan sebagai
  jaminan biaya peer atau seluruh analisis di bawah quota.
- Acceptance: batas tiga, mismatch jenis/periode ditandai, tidak memaksakan
  pemenang, error per perusahaan jelas, dan bukti dapat diperiksa satu per satu.
- Verifikasi: feature selection/state/auth, real adapter dengan fake HTTP,
  unit comparability, typecheck/build, browser desktop/mobile dan pagination.
- Batas berhenti: compare real selesai tanpa menambah penyimpanan diam-diam.

## Increment 5: Simpan manual dan tinjau perubahan versi

- Prasyarat: compare real stabil, persetujuan desain schema/ownership snapshot.
  Belum ada migration comparison; baseline accepted bukan bukti implementasi.
- Perubahan: persistence perbandingan privat manual dengan ULID, immutable
  evidence/version, dan pembaruan sebagai versi baru. Bukan notebook baru.
- Endpoint/credit/cache: membaca versi tersimpan tidak memanggil provider;
  pembaruan eksplisit mengikuti input/estimasi/cache yang sudah diverifikasi.
- Acceptance: hanya pemilik dapat membaca/memperbarui; versi lama tidak berubah;
  delta membedakan perubahan input, formula, aturan, dan peer. Jika basis beda,
  tampilkan tidak sebanding, bukan klaim kinerja memburuk/membaik.
- Verifikasi: migration non-destruktif, ownership/IDOR, validasi snapshot,
  transaksi/concurrency, integritas versi, delta periodisasi, serta browser.
- Batas berhenti: tidak memperluas ke watchlist, sharing, atau refresh terjadwal.

## Batas berhenti dan pemulihan

Tahap terbaru berhenti setelah implementasi UX-1, verifikasi dan handoff;
dokumentasi kesiapan 3B tetap tersedia. Tidak commit/push atau menjalankan audit live
dan implementasi berikutnya secara otomatis. Pada implementasi,
pertahankan perubahan kecil, URL/consumer, dan data existing; tidak ada reset
database atau rollback file user. Risiko di luar scope dilaporkan terpisah.
