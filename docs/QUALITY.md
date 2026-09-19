# Quality dan Verifikasi

Mulai dari test paling spesifik dan gunakan gate luas sesuai risiko. Source
aplikasi belum dibuat, sehingga command final akan diisi setelah scaffolding
Laravel/Inertia tersedia.

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

- Backend focused test: belum tersedia.
- Frontend focused test: belum tersedia.
- Typecheck/lint/build: belum tersedia.
- Module validation: belum tersedia.
- Full quality gate: belum tersedia.

Setelah source dibuat, ganti bagian ini dengan command executable yang benar.
Jangan mengarang command yang belum ada.

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
- Peer kosong dan peer terlalu sedikit sesuai kebijakan yang ditetapkan.
- Pemilihan strategi bank/perusahaan umum jika digunakan.
- Data dan konfigurasi yang sama menghasilkan nilai yang sama.

Kebijakan yang belum final pada [SCORING.md](SCORING.md) harus diputuskan
sebelum test mengunci hasilnya. Jangan memakai angka dari contoh konsep sebagai
expected result untuk formula yang belum ditetapkan.

## Skenario manual end-to-end

1. Buka Temukan Saham dan pilih sektor Financials.
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
