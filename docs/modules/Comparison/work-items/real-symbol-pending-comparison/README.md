# Work Item: Pending comparison untuk ticker real

## Status dan owner

- Status: Done.
- Owner: Comparison.
- Target: `/bandingkan`.

## Kondisi awal

Flow Discover dan Company sudah dapat membawa ticker real dari Sectors seperti
`ADES` atau `AADI`, tetapi Comparison masih menolak symbol yang tidak ada pada
dataset fake internal.

## Scope dan non-scope

Scope: Comparison menerima maksimal 3 ticker valid, termasuk ticker real yang
belum punya data fake, lalu menampilkan nilai pending secara jujur.

Non-scope: mengambil data Sectors baru, menyimpan comparison manual, scoring real,
dan versi snapshot privat.

## Acceptance criteria

- [x] `/bandingkan?symbols=ADES,AADI` render 200 dan tidak redirect error.
- [x] Ticker pending tampil pada matrix dengan nilai `-` dan catatan data belum
      dimuat.
- [x] Limit maksimal 3 ticker dan validasi format tetap berlaku.
- [x] UI Comparison mengikuti pola tabel/dashboard yang sama.
- [x] `/bandingkan` tanpa query menampilkan default MVP `BBCA,TLKM,ICBP`.

## Dependency dan keputusan

Aturan UI tabel di `AGENTS.md` berlaku. Detail real tetap on-demand agar credit
Sectors hemat.

## Handoff

- Perubahan: ticker valid yang belum ada dataset fake menjadi pending comparison,
  `/bandingkan` memakai dashboard matrix, dan halaman awal menampilkan default
  MVP `BBCA,TLKM,ICBP`.
- Verifikasi: focused compare route tests, typecheck, lint, dan build lulus.
- Risiko terbuka: nilai real belum tersedia sampai modul Intelligence/Company
  real dikerjakan.
