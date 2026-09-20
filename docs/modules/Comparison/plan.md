# Plan: Comparison Fake Flow

## Scope

Bangun alur Bandingkan minimal berbasis backend fake internal. Tujuannya membuat
alur navigasi dari Detail Perusahaan ke Bandingkan dan tampilan matrix side-by-
side dapat dipelajari tanpa live call Sectors atau persistence.

## Increment 1: Dokumentasi dan kontrak fake flow

- Perubahan: module docs, specification, plan, tasks, dan work item.
- Prasyarat: keputusan MVP maksimal 3 saham sudah accepted pada docs aktif.
- Acceptance: scope/non-scope, input route, acceptance, dan task increment
  tertulis sebelum coding.
- Verifikasi: review dokumen dan `git diff --check`.

## Increment 2: Backend fake comparison payload

- Perubahan: class Application kecil di `app/Modules/Comparison/`, validasi
  query `symbols`, route `/bandingkan`, dan feature/unit test.
- Prasyarat: tidak ada live Sectors call.
- Acceptance: empty state, valid symbols maksimal 3, unknown symbol, dan lebih
  dari 3 symbol teruji.
- Verifikasi: focused PHPUnit untuk Comparison dan navigation.

## Increment 3: UI matrix dan link dari detail

- Perubahan: halaman placeholder menerima `comparison` props, menampilkan matrix
  side-by-side, form symbol, dan link dari detail perusahaan ke Bandingkan.
- Prasyarat: backend fake payload hijau.
- Acceptance: typecheck/lint/build lulus; UI tidak memakai data hardcoded lokal
  saat props comparison tersedia.
- Verifikasi: `npm run typecheck`, `npm run lint:check`, `npm run build`, dan
  focused feature test.

## Batas berhenti dan pemulihan

Scope selesai saat alur fake Bandingkan berjalan end-to-end dan committed.
Jika validasi atau UI matrix melebar terlalu besar, hentikan pada backend fake
payload dan laporkan sebelum membuat persistence atau integrasi data nyata.
