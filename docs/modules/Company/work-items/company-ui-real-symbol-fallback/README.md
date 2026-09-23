# Work Item: Company UI dan fallback ticker real

## Status dan owner

- Status: Done.
- Owner: Company.
- Target: `/perusahaan` dan `/perusahaan/{symbol}`.

## Kondisi awal

Discover real mode dapat menampilkan ticker dari Sectors seperti `ADES` dan
`AADI`, tetapi detail perusahaan masih bergantung pada tiga snapshot fake sehingga
route detail mengembalikan 404.

## Scope dan non-scope

Scope: buat halaman daftar perusahaan mengikuti pola UI tabel `ContohUI`, dan
pastikan detail ticker dari Discover tidak 404 dengan fallback snapshot informatif.

Non-scope: integrasi Company Report real, scoring real, penyimpanan snapshot, dan
AI explainer.

## Acceptance criteria

- [x] `/perusahaan` menampilkan tabel perusahaan dengan pola dashboard/table.
- [x] `/perusahaan/{symbol}` untuk ticker selain dataset fake tetap render 200
      dengan status data yang jujur.
- [x] Link dari Discover ke detail perusahaan tidak menghasilkan 404 untuk ticker
      real.
- [x] Test route Company dan build frontend lulus.

## Dependency dan keputusan

Aturan UI tabel pada `AGENTS.md` menjadi baseline. Data real detail perusahaan
belum diambil agar credit Sectors tetap hemat.

## Handoff

- Perubahan: daftar Company memakai tabel dashboard dan detail ticker unknown
  memakai snapshot pending.
- Verifikasi: focused Company route tests, typecheck, lint, dan build lulus.
- Risiko terbuka: detail real masih pekerjaan berikutnya.
