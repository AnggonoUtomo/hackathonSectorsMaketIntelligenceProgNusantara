# Work Item: Fake Comparison Flow

## Status dan owner

- Status: In progress.
- Owner: Comparison.
- Target: `app/Modules/Comparison`, route `/bandingkan`, dan UI Inertia
  placeholder NusaLens.

## Kondisi awal

Temukan Saham sudah menampilkan hasil backend fake. Detail Perusahaan dapat
dibuka dari ticker dan menampilkan snapshot backend fake. Halaman Bandingkan
masih placeholder umum dan belum menerima payload dari backend.

## Scope dan non-scope

Scope:

- backend fake comparison payload;
- validasi query maksimal 3 symbol;
- matrix comparison side-by-side di UI;
- link dari detail perusahaan ke Bandingkan.

Non-scope:

- live Sectors call;
- konsumsi credit real;
- simpan manual privat;
- versioning snapshot;
- scoring final Intelligence;
- AI explainer atau sharing publik.

## Acceptance criteria

- [ ] `/bandingkan` dapat dibuka user verified tanpa symbol dan menampilkan
  empty state.
- [ ] `/bandingkan?symbols=BBCA,TLKM` menampilkan matrix fake dari backend.
- [ ] Query lebih dari 3 symbol ditolak.
- [ ] Unknown symbol ditolak eksplisit.
- [ ] Detail perusahaan menyediakan jalan ke Bandingkan untuk symbol terkait.
- [ ] Tidak ada test yang melakukan live call Sectors.

## Dependency dan keputusan

- `docs/DECISIONS.md`: Compare maksimal 3 saham untuk MVP.
- `docs/SECURITY.md`: fitur riset wajib login dan verified.
- `docs/DATA-FLOW.md`: pembacaan cache/skor tersimpan tidak memakai credit;
  fake flow tidak melakukan upstream request.

## Handoff

- Perubahan: belum dikerjakan.
- Verifikasi: belum dijalankan.
- Risiko terbuka: persistence dan snapshot berversi Comparison belum dirancang
  pada increment ini.
