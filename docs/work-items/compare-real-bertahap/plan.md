# Plan: Compare Real Bertahap

Catatan revisi 6 Oktober 2026: acceptance 70% di rencana ini sudah digantikan
ambang 60% pada formula v1.1.0; lihat [Scoring](../../SCORING.md).

## Scope

Tujuan pekerjaan ini adalah mengganti `/bandingkan` dari matrix fake menjadi alat
compare real yang hemat credit dan mudah dipahami pengguna awam. Implementasi
dilakukan bertahap agar setiap slice bisa diuji dan tidak langsung membuka
seluruh endpoint provider.

## Increment 0: Proposal dan kontrak

- Status: selesai pada dokumen ini.
- Perubahan: dokumentasi scope, acceptance, payload, estimasi credit, dan batas.
- Prasyarat: user menyetujui arah compare real bertahap.
- Acceptance: rencana jelas sebelum coding dan tidak mengubah source.
- Verifikasi: cocokkan dengan source compare fake, `DECISIONS.md`, `DATA-FLOW.md`,
  dan `SCORING.md`.

## Increment 1: Hilangkan default fake dan pilih saham real

- Status: selesai.
- Perubahan: `/bandingkan` tanpa symbol menjadi empty state; input ticker diganti
  autocomplete company real dengan logo; query `symbols` tetap canonical.
- Prasyarat: endpoint autocomplete Temukan Saham yang sudah real.
- Endpoint/credit: autocomplete mengikuti endpoint search existing; tidak memuat
  profile/analytics sebelum user memilih atau membuka deep link.
- Acceptance: tidak ada default `BBCA, TLKM, ICBP`; maksimal tiga saham; invalid
  symbols ditolak; tidak ada metric palsu.
- Verifikasi: feature test route, component typecheck, lint, build, dan browser
  desktop/mobile bila tool tersedia.
- Hasil: `FakeComparisonBuilder` diganti `ComparisonSelectionBuilder`. Payload
  compare hanya berisi `symbols`, `companies: []`, `metrics: []`, dan meta
  `source: selection`. UI menampilkan autocomplete serta chip saham terpilih.
  Tidak ada provider call tambahan selain autocomplete search saat user mengetik.

## Increment 2: Profil ringkas compare real

- Status: selesai.
- Perubahan: route compare mengambil identitas/profil ringkas untuk symbol yang
  dipilih, memakai cache dan adapter internal. Tampilkan logo, nama, sektor,
  subsektor, freshness, dan status error per saham.
- Prasyarat: symbol sudah tervalidasi dan maksimal tiga.
- Endpoint/credit: Company Report overview atau contract internal setara, maksimal
  1 credit per saham saat cold cache. Total initial cold cache maksimal 3 credit.
- Cache: profil/identitas mengikuti TTL 7 hari atau TTL overview yang sudah
  diterapkan pada Company.
- Acceptance: deep link tiga saham menampilkan profil real tanpa metric fake;
  error satu saham tidak menghapus saham lain; cache hit tidak menambah ledger.
- Verifikasi: fake HTTP untuk success/partial/error/cache dan browser matrix.
- Hasil: `ComparisonSelectionBuilder` memakai contract `CompanyDirectory` untuk
  memuat overview real/cache maksimal tiga saham. Payload company memuat
  `status: ready|error`, profil ringkas, harga terakhir, tanggal harga,
  `fetchedAt`, dan error per saham. `metrics` tetap kosong.

## Increment 3: Section harga/keuangan/valuasi on-demand

- Status: selesai.
- Perubahan: tambah endpoint internal compare section atau reuse endpoint analitik
  yang ada untuk memuat prices, financials, dan valuation per saham terpilih.
  UI menampilkan estimasi credit sebelum memuat section.
- Prasyarat: increment 2.
- Endpoint/credit:
    - Harga: Daily 90 hari, sampai 1 credit per saham.
    - Keuangan: empat kuartal, sampai 4 credit per saham.
    - Valuasi: Company Report valuation, sampai 1 credit per saham.
- Cache: harga/valuasi 1 jam; keuangan 24 jam.
- Acceptance: section tidak otomatis dimuat saat halaman awal; loading/error
  per section; angka dua desimal; tidak ada seri sintetis; Recharts dan tabel
  tersedia.
- Verifikasi: fake HTTP untuk partial data, null, stale/cache, quota exceeded,
  typecheck, lint, build, dan browser desktop/mobile.
- Slice 3A: harga memakai endpoint analitik existing
  `/nusalens/companies/{symbol}/analysis?section=prices` dari frontend setelah
  user klik. Section keuangan dan valuasi belum masuk slice ini.
- Slice 3B: keuangan memakai endpoint analitik existing
  `/nusalens/companies/{symbol}/analysis?section=financials` dari frontend
  setelah user klik. Data empat kuartal ditampilkan sebagai grafik batang per
  metrik dan tabel pembanding. Section valuasi belum masuk slice ini.
- Slice 3C: valuasi memakai endpoint analitik existing
  `/nusalens/companies/{symbol}/analysis?section=valuation` dari frontend
  setelah user klik. Rasio historis ditampilkan sebagai grafik garis per metrik
  dan tabel pembanding.
- Hasil: harga, keuangan, dan valuasi sudah tersedia sebagai section on-demand
  pada halaman compare. Masing-masing section menampilkan estimasi credit,
  loading/error per saham, Recharts, tabel alternatif, dan tidak diambil saat
  halaman pertama dibuka.

## Increment 4: Save snapshot manual

- Status: selesai.
- Perubahan: simpan perbandingan privat manual sebagai snapshot immutable,
  tampilkan daftar/detail read-only, dan update sebagai versi baru.
- Prasyarat: increment 2-3 stabil; schema persistence disetujui.
- Endpoint/credit: simpan snapshot membaca data yang sudah tersedia; tidak
  otomatis refresh provider.
- Acceptance: ownership per user, snapshot privat, versi baru saat update,
  fetched_at dan periode sumber tersimpan.
- Verifikasi: feature test auth/ownership/versioning, typecheck, lint, build,
  dan regression compare.
- Schema:
    - `comparison_snapshots.id` ULID.
    - `comparison_snapshots.user_id` owner.
    - `comparison_snapshots.title` judul snapshot.
    - `comparison_snapshots.symbols` JSON list maksimal tiga.
    - `comparison_snapshots.payload` JSON berisi profil dan section yang sudah
      dimuat.
    - `comparison_snapshots.version` integer.
    - `comparison_snapshots.created_from_snapshot_id` nullable untuk versioning.
    - timestamp Laravel standar.
- UI: tombol Simpan Snapshot pada `/bandingkan`, daftar snapshot privat di
  `/bandingkan/snapshots`, dan detail snapshot read-only di
  `/bandingkan/snapshots/{snapshot}`.
- Hasil: snapshot menyimpan profil compare dan section on-demand yang sudah
  berhasil dimuat di browser. Simpan snapshot tidak memanggil provider.

## Increment 5: Score comparison setelah gate peer/scoring

- Status: terimplementasi melalui Penuntasan MVP. Rincian 5A-5C di bawah adalah
  rencana yang telah dijalankan, bukan permintaan persetujuan baru.
- Perubahan: compare menjadi consumer hasil Intelligence real untuk menampilkan
  Nilai Prioritas Riset, komponen, kelengkapan, dan bukti.
- Prasyarat: work item peer/scoring minimal menyelesaikan audit 3B.0 atau user
  menyetujui satu slice komponen yang datanya sudah terbukti layak.
- Endpoint/credit: mengikuti kebutuhan seluruh peer valid per metrik, bukan hanya
  tiga saham yang dibandingkan. Estimasi credit harus dihitung sebelum tombol
  memuat skor aktif.
- Acceptance: tidak ada skor total bila kelengkapan <70%; peer/periode/bobot
  dapat ditelusuri; tidak ada label BUY/HOLD/SELL; Comparison tidak menghitung
  percentile sendiri.
- Verifikasi: unit kalkulator Intelligence, feature contract compare, partial
  data/unavailable, snapshot bukti, typecheck, lint, build, dan browser UX.

### Increment 5A: Contract dan UX score preview

- Tujuan: mendefinisikan payload score yang dibutuhkan compare tanpa mengambil
  data provider baru.
- Files likely touched: dokumen compare, contract Intelligence, test payload
  consumer, dan UI placeholder score unavailable bila contract belum punya data.
- Acceptance: halaman compare bisa menampilkan status "Nilai belum tersedia"
  beserta alasan/gate, tanpa angka fake.
- Gate: lanjut coding hanya setelah contract hasil Intelligence disetujui.

### Increment 5B: Consume hasil Intelligence tersimpan

- Tujuan: compare membaca hasil skor yang sudah dihitung dan masih fresh, bukan
  melakukan fan-out provider sendiri.
- Files likely touched: use case Comparison, contract Intelligence, route/props,
  dan UI section Nilai Riset.
- Acceptance: cache/reuse score tidak menambah credit; hasil expired atau input
  berubah tampil perlu refresh; bukti bisa dibuka per komponen.
- Gate: perlu persistence bukti Intelligence siap.

### Increment 5C: Trigger hitung score on-demand

- Tujuan: tombol on-demand meminta Intelligence menghitung skor setelah estimasi
  credit dan populasi peer valid diketahui.
- Files likely touched: action/controller baru, use case Intelligence, ledger
  MarketData, UI loading/error, dan tests.
- Acceptance: quota/budget tidak cukup menghasilkan unavailable jelas; tidak ada
  subset peer diam-diam; snapshot compare menyimpan hasil yang sudah dimuat.
- Gate: perlu audit endpoint/credit peer selesai dan disetujui.

## Batas berhenti dan pemulihan

Setiap increment berhenti pada state yang dapat digunakan. Bila kontrak provider
atau budget tidak cukup, tampilkan status unavailable dan catat gap; jangan
mengisi matrix dengan fake atau nol. Jangan menjalankan smoke real sebelum
estimasi credit increment terkait disetujui.
