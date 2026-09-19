# Work Item: Dependency npm dan TypeScript

## Status dan scope

Perbaikan dan verifikasi lokal selesai, 2026-09-20. Pekerjaan lintas module
untuk baseline frontend; bukti commit/push dilaporkan pada handoff Git.
User meminta evaluasi warning npm, perbaikan empat error TypeScript, lalu
commit dan push. Scope mencakup manifest/lockfile npm, tipe form autentikasi,
blend mode ilustrasi welcome, dan pemeriksaan compiler di CI.

Tidak mengubah Laravel/PHP dependency, schema database, model autentikasi,
route, arsitektur module, maupun rumus NusaLens.

## Kondisi awal

- `npm audit --json`: 23 temuan, terdiri dari 3 low, 6 moderate, 12 high,
  dan 2 critical. Ini temuan pada dependency tree, bukan bukti seluruh
  advisory dapat dieksploitasi melalui aplikasi.
- Critical mencakup `form-data` dan `shell-quote`. Dependency lain yang
  terpengaruh mencakup Axios, Vite/esbuild/Rollup, serta dependency lint/build.
- `npm explain lodash.isequal`: dependency dari `@inertiajs/react@2.0.3`.
  Warning deprecated berbeda dari temuan vulnerability; `npm fund` hanya
  informasi pendanaan package.
- Tiga interface form tidak memenuhi constraint `Record<string,
  FormDataConvertible>` pada useForm yang terpasang.
- `mixBlendMode: 'plus-darker'` tidak diterima oleh tipe CSS yang terpasang.

## Perbaikan

Naikkan minimum Inertia React ke 2.3.28, Vite ke 6.4.3, dan ESLint ke 9.39.5.
Perbarui dependency transitif rentan dalam rentang kompatibel; pertahankan
major version stack dan jangan memakai `--force` atau mengabaikan audit.
Inertia React 2.3.28 memakai lodash-es, sehingga dependency deprecated
lodash.isequal dapat dihilangkan melalui pembaruan induknya.

Form memakai type alias dengan field yang tetap spesifik, tanpa `any`, cast,
atau mematikan strict mode. Ilustrasi memakai blend mode standar `darken`.
Tambahkan `npm run typecheck` dan `npm run lint:check`, serta jalankan compiler
di workflow tests setelah dependency Composer tersedia untuk tipe Ziggy.

## Risiko tersisa

ESLint 9.39.5 berstatus EOL sejak 2026-08-06 dan masih mengeluarkan warning
deprecated saat instalasi. Ini berbeda dari vulnerability audit yang telah
diperbaiki. Peer dependency `eslint-plugin-react@7.37.5` (versi terbaru saat
pemeriksaan) hanya mendukung ESLint sampai `^9.7`, bukan ESLint 10. Pertahankan
jalur kompatibel untuk scope ini; migrasi lint ke ESLint 10 memerlukan evaluasi
plugin/rules tersendiri. Tidak memakai `--legacy-peer-deps`, override paksa,
atau menghapus pemeriksaan React untuk menyembunyikan warning.

## Acceptance criteria

- [x] Audit tidak lagi melaporkan vulnerability pada lockfile hasil perbaikan.
- [x] lodash.isequal tidak ada dalam dependency tree atau fresh install.
- [x] Empat error TypeScript teratasi dan lint lulus.
- [x] Build client/SSR dan test backend lulus.
- [x] Halaman welcome dan form autentikasi lolos pemeriksaan browser.
- [x] Instalasi dari lockfile dapat diulang; perubahan siap dikirim.

## Referensi

- [npm audit](https://docs.npmjs.com/cli/v11/commands/npm-audit/): pembaruan kompatibel dan dry-run.
- [Manifest Inertia React 2.3.28](https://github.com/inertiajs/inertia/blob/v2.3.28/packages/react/package.json): dependency dan peer React.
- [Rilis Inertia React 2.3.28](https://github.com/inertiajs/inertia/releases/tag/v2.3.28): pembaruan dependency dan perbaikan adapter.
- [Advisory Vite Windows](https://github.com/vitejs/vite/security/advisories/GHSA-fx2h-pf6j-xcff): perbaikan pada jalur Vite 6.
- [Compositing and Blending](https://www.w3.org/TR/compositing-1/#mix-blend-mode): blend mode standar.
- [Dukungan versi ESLint](https://eslint.org/version-support/): status EOL dan versi aktif.

Hasil aktual dicatat pada tasks.md. Audit adalah snapshot advisory registry
pada waktu pemeriksaan, bukan jaminan tidak ada kerentanan di masa depan.
