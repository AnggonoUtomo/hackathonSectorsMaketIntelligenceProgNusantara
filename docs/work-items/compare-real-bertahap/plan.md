# Plan: Compare Real Bertahap

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

## Increment 4: Save snapshot manual

- Perubahan: simpan perbandingan privat manual sebagai snapshot immutable dan
  update sebagai versi baru.
- Prasyarat: increment 2-3 stabil; schema persistence disetujui.
- Endpoint/credit: simpan snapshot membaca data yang sudah tersedia; tidak
  otomatis refresh provider.
- Acceptance: ownership per user, snapshot privat, versi baru saat update,
  fetched_at dan periode sumber tersimpan.
- Verifikasi: feature test auth/ownership/versioning dan migration test.

## Increment 5: Score comparison setelah gate peer/scoring

- Perubahan: tampilkan Nilai Prioritas Riset dan komponen scoring bila
  Intelligence real sudah tersedia.
- Prasyarat: work item peer/scoring disetujui dan kalkulator real siap.
- Endpoint/credit: mengikuti kebutuhan peer valid semua metrik, bukan hanya tiga
  saham yang dibandingkan.
- Acceptance: tidak ada skor bila kelengkapan <70%; peer dan periode dapat
  ditelusuri; tidak ada label BUY/HOLD/SELL.
- Verifikasi: unit kalkulator, peer fallback, partial data, dan snapshot bukti.

## Batas berhenti dan pemulihan

Setiap increment berhenti pada state yang dapat digunakan. Bila kontrak provider
atau budget tidak cukup, tampilkan status unavailable dan catat gap; jangan
mengisi matrix dengan fake atau nol. Jangan menjalankan smoke real sebelum
estimasi credit increment terkait disetujui.
