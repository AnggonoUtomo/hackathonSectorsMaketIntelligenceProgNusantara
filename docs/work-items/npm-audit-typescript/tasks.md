# Tasks: Dependency npm dan TypeScript

- [x] Reproduksi 23 temuan audit dan empat error compiler.
- [x] Telusuri warning deprecated ke Inertia React 2.0.3.
- [x] Verifikasi pilihan versi kompatibel melalui registry dan sumber resmi.
- [x] Perbaiki tipe form, blend mode, serta command/gate TypeScript.
- [x] Perbarui dan audit dependency tree.
- [x] Verifikasi instalasi lockfile dan pemeriksaan aplikasi.
- [x] Periksa browser dan scope diff.

Pengiriman: stage hanya file pekerjaan, periksa whitespace/secret pada index,
commit dan push, lalu laporkan hash remote serta status worktree pada handoff.

## Hasil

Pemeriksaan lokal pada 2026-09-20 (Node 24.12.0, PHP 8.4.16):

| Pemeriksaan | Hasil |
| --- | --- |
| `npm ci --no-fund` | PASS, 418 package terpasang; hanya warning EOL ESLint tersisa |
| `npm audit --json` | PASS, 0 low/moderate/high/critical, turun dari 23 temuan |
| `npm ls --all --json` | PASS, tidak ada masalah dependency/peer |
| Pemeriksaan lockfile | lodash.isequal hilang; resolved URL seluruhnya registry.npmjs.org; native Rollup Linux cocok 4.63.4 |
| `npm run typecheck` | PASS, 0 error setelah fresh install |
| `npm run lint:check` | PASS |
| Prettier pada source/config yang diubah | PASS |
| `npm run build:ssr` | PASS, client dan SSR berhasil dibangun |
| `php vendor/bin/phpunit --no-progress` | PASS, 26 test dan 63 assertion sebelum/sesudah perbaikan |
| `git diff --check` | PASS |

Pembaruan dependency dilakukan tanpa `--force`, `--legacy-peer-deps`, atau
override. Audit fix pertama masih menyisakan brace-expansion; perbaikan kedua
memperbaruinya ke 1.1.21. Dua package critical awal berubah dari form-data
4.0.2 ke 4.0.6 dan shell-quote 1.8.2 ke 1.10.0.

## Browser

Chrome DevTools MCP memakai isolated browser context dan server localhost
khusus dengan database SQLite disposable. Database kerja, `.env`, dan data
pengguna tidak diubah; tidak mengirim email atau memanggil Sectors API.

- PASS: welcome desktop dan mobile 390x844, ilustrasi terlihat, blend mode
  `darken` diterapkan, dan tidak ada horizontal overflow.
- PASS: register menampilkan error konfirmasi password, mengosongkan password,
  mengaktifkan kembali tombol, lalu registrasi valid menuju dashboard.
- PASS: logout, login salah menampilkan error, password dikosongkan, lalu
  login valid menuju dashboard pada viewport desktop 1440x900.
- PASS: reset password menampilkan error token invalid, mengosongkan kedua
  password, dan mengaktifkan kembali tombol. Jalur token valid dicakup test
  backend; pengiriman email nyata tidak diuji.
- PASS: tidak ada console error/warning pada halaman/alur yang diperiksa.

Workflow CI telah ditambah gate TypeScript setelah Composer install. Hasil
lokal bukan klaim bahwa GitHub Actions atau instalasi Linux sudah dijalankan.
Warning EOL ESLint tetap menjadi risiko terbuka, sebagaimana dicatat di README.
