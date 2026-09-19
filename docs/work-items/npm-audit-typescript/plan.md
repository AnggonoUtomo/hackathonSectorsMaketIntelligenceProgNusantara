# Plan: Dependency npm dan TypeScript

1. Reproduksi audit, telusuri lodash.isequal, dan konfirmasi empat error compiler.
2. Perbaiki minimum versi dependency langsung dan perbarui lockfile kompatibel.
3. Sesuaikan tipe form/blend mode; tambahkan command compiler dan gate CI.
4. Verifikasi audit, instalasi lockfile, typecheck, lint, build client/SSR,
   test backend, dan browser form.
5. Catat hasil, stage hanya scope pekerjaan, commit, push, lalu cek sinkronisasi.

Berhenti setelah scope ini selesai. Tidak melakukan migrasi framework,
refactor arsitektur, atau perubahan domain. Pemulihan dapat dilakukan dengan
revert commit pekerjaan dan instalasi ulang lockfile sebelumnya bila diperlukan.
