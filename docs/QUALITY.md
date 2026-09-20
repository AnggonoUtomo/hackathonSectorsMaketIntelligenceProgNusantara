# Quality dan Verifikasi

Mulai dari test paling spesifik dan gunakan gate luas sesuai risiko. Baseline
Laravel/Inertia sudah tersedia; test domain NusaLens ditambahkan per work item.

| Perubahan            | Verifikasi minimum                                      |
| -------------------- | ------------------------------------------------------- |
| Dokumentasi          | Tautan Markdown, placeholder scan, dan whitespace check |
| Backend behavior     | Focused unit/feature test                               |
| Boundary atau module | Architecture test dan module validation bila tersedia   |
| Frontend behavior    | Focused component test, typecheck, dan lint             |
| UI/alur pengguna     | Focused browser test setelah test frontend lulus        |
| Migration            | Focused migration test pada database disposable         |
| API contract         | Focused API test dan pembaruan `API.md`                 |
| Sectors integration  | Fake HTTP test, error mapping test, cache hit/miss      |
| Scoring              | Unit test untuk normalisasi, percentile, bobot, data hilang |

## Command project

- Backend focused test: `php vendor/bin/phpunit --filter NamaTest`.
- Seluruh test backend: `php vendor/bin/phpunit`.
- Frontend focused test: belum tersedia.
- Typecheck: `npm run typecheck` (perlu dependency Composer untuk tipe Ziggy).
- Lint tanpa mengubah file: `npm run lint:check`.
- Build client: `npm run build`; client dan SSR: `npm run build:ssr`.
- Instalasi reproducible: `npm ci`; audit dependency: `npm audit`.
- Module validation: belum tersedia.
- Gate baseline: typecheck, lint, build client/SSR, dan seluruh test backend.

`npm run lint` dan `npm run format` mengubah file. Jangan memakai command ini
untuk pemeriksaan read-only. Audit mencakup advisory yang diketahui saat itu;
status deprecated/EOL diperiksa terpisah dari jumlah vulnerability.

## QA penting NusaLens

- Unit test wajib untuk perhitungan nilai, percentile, reweighting, dan data
  hilang.
- Feature/application test wajib untuk screener, detail perusahaan, compare,
  cache hit/miss, dan error mapping Sectors.
- Integration test Sectors memakai fake HTTP.
- Test otomatis tidak boleh menghabiskan credit real Sectors API.
- UI penting harus menampilkan waktu terakhir data diperbarui.
- Sebelum demo, pastikan API key tidak terlihat di browser, network log, source,
  atau error output.

## Kasus uji penilaian

- Semua metrik tersedia, satu metrik hilang, dan banyak metrik hilang.
- Normalisasi, percentile, kalkulator, reweighting, dan kelengkapan data.
- Nilai ekstrem dan pertumbuhan negatif.
- Target + 5 peer valid; 4 peer tidak cukup; seluruh peer valid di kelompok,
  average rank/ties (semua sama =50), dan lower-is-better dibalik.
- Hierarki subindustry -> industry -> subsector -> sector, tanpa pencampuran
  bank/nonbank, independen dari filter screener, fallback periode terbaru dahulu.
- Kelengkapan tepat 70%, kurang dari 70%, komponen kosong, dan bobot awal sebelum
  reweighting; pembulatan 2 desimal tidak mempengaruhi sorting/ambang.
- Bank, perusahaan nonkeuangan, dan keuangan nonbank tanpa metrik risiko paksa.
- Periode/basis tidak kompatibel, denominator nol/negatif, turnaround,
  momentum 20 sesi dan konsistensi aksi korporasi, tanpa clipping outlier valid.
- Data dan konfigurasi yang sama menghasilkan nilai yang sama.

Formula v1 disetujui pada [SCORING.md](SCORING.md). Expected result diturunkan
dari formula tersebut; kontrak/precision dan data provider tetap diverifikasi
dengan fixture, bukan menganggap contoh dokumentasi sebagai data live lengkap.

## Kasus akses, persistence, dan credit

- Guest/unverified tidak dapat mengakses riset; registrasi/verifikasi tetap berfungsi.
- Migrasi ULID users dan FK, tanpa reset data; ULID bukan pengganti authorization.
- Perbandingan maksimal 3 saham, privat, rename/delete, update sebagai versi baru.
- Snapshot immutable/reproducible, reuse lintas akun tanpa bocor metadata privat,
  retensi 30 hari dengan referensi aktif dan cleanup yang tidak merusak bukti.
- TTL 7 hari/24 jam/1 jam, batas fallback pasar 24 jam dan keuangan 7 hari,
  kalender bursa, metadata observasi vs fetched_at, dan respons campuran.
- Budget 1.000 sekali pakai, kuota 20/hari reset WIB, reservasi bersamaan,
  retry maksimal satu, timeout charging, serta Redis hilang tidak mereset ledger.
- AI gagal/disabled tidak menghilangkan penjelasan berbasis aturan.

## Skenario manual end-to-end

1. Login dengan email terverifikasi, buka Temukan Saham dan pilih sektor Financials.
2. Periksa shortlist, pagination, dan filter di URL.
3. Buka BBCA atau kandidat lain yang datanya tersedia.
4. Lihat lima nilai, Nilai Prioritas Riset, data, freshness, dan penjelasannya.
5. Bandingkan tiga perusahaan sejenis beserta metrik asli dan hasil nilai.
6. Ulangi dengan cache terisi dan periksa berkurangnya panggilan upstream.
7. Simulasikan timeout/error Sectors dan bedakan dari hasil pencarian kosong.

Gunakan fake HTTP untuk simulasi, bukan panggilan yang menghabiskan credit.
Manual live hanya ketika memang memerlukan verifikasi provider nyata.

## Performa dan kesiapan demo

- Halaman dengan cache harus responsif; ukur pada implementasi, bukan klaim awal.
- Panggilan Sectors memiliki timeout; hindari N+1 pada database maupun API.
- Gunakan pagination untuk data besar serta loading, empty, dan error state.
- Periksa responsive layout, accessibility dasar, dan resolusi rekaman 1080p.
- Siapkan ticker dan cache demo; fallback memakai data tersimpan yang ditandai
  freshness-nya, tanpa membuat atau memalsukan data ketika provider lambat.
- Gunakan [SUBMISSION.md](SUBMISSION.md) sebelum rekaman dan pengiriman.

## Verifikasi dokumentasi

Periksa tautan lokal, stack Laravel 12/MySQL, path `app/Modules/{Module}`, dan
placeholder di dokumen aktif. Placeholder hanya boleh berada di template yang
memang belum diisi. Dokumen aktif harus tetap dapat dibaca tanpa tautan ke
`docs/NusaLens/` atau `docs/templateDocs/`; kedua folder telah dihapus atas
instruksi user setelah adaptasi selesai.
