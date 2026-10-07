# Kesiapan Publish NusaLens

Otorisasi user: menyiapkan sampai siap publish, hosting belum tersedia.
Tanggal pengerjaan: 4-7 Oktober 2026. Status: persiapan lokal selesai;
deployment dan gate server menunggu hosting.

Scope: hardening aplikasi yang ada, patch dependency rentan, quality gate CI,
preflight produksi, template environment dan runbook deployment/backup/rollback.
Arsitektur modular, formula, autentikasi session, registrasi publik, dan kuota
tetap dipertahankan. Tidak membeli hosting, deploy, commit/push atau memakai
credit Sectors pada pekerjaan ini. Tidak memasang dependency baru.

Acceptance: audit lockfile bebas advisory yang ditemukan, suite/build lulus,
preflight menolak konfigurasi lokal sebagai produksi tanpa membocorkan secret,
endpoint publik terlindungi rate limit, dan langkah deployment dapat diikuti.
Siap dipasang tidak sama dengan telah lulus pengujian hosting publik.

Lihat [plan](plan.md), [tasks](tasks.md), dan [panduan deployment](../../DEPLOYMENT.md).

## Hasil

- Rate limit registrasi/login/reset dan compare/snapshot, security headers
  serta HSTS pada HTTPS production. Autentikasi/verified dan formula tidak diubah.
- `nusalens:preflight` memeriksa konfigurasi/artifact; `--check-services`
  memeriksa MySQL/migration/Redis/lock, tanpa mengirim email atau memakai Sectors.
- Template environment produksi tanpa secret, SMTP TLS wajib dengan timeout,
  dan panduan deployment, worker/scheduler, backup/restore serta rollback kode.
- CI memakai Node 22/PHP 8.4, install lockfile, audit, suite, typecheck, lint
  dan build. Linter tidak mengubah source atau meng-commit otomatis.
- Path Inertia diselaraskan ke `resources/js/pages` (huruf kecil) agar sesuai
  checkout case-sensitive di Linux. Regresi terbukti gagal sebelum konfigurasi
  diperbaiki, lalu lulus; pemeriksaan keberadaan komponen tidak dinonaktifkan.
- Dependency existing diperbarui: CommonMark 2.10.3, typescript-eslint 8.71.0
  beserta transitifnya, dan source-map-js 1.2.2. Manifest dependency tidak berubah.
  Pembaruan source-map-js menutup advisory yang muncul pada audit ulang 6 Oktober:
  [GHSA-68fv-2mgg-jv7q](https://github.com/advisories/GHSA-68fv-2mgg-jv7q).
- Format PHP baseline diperbaiki pada file yang dilaporkan Pint agar gate penuh
  lulus; tidak mengubah perilaku auth. Dokumen lama dan indeks disinkronkan.

## Verifikasi 6 Oktober 2026

| Pemeriksaan | Hasil |
| --- | --- |
| `php artisan test --compact` | 191 test, 1.105 assertion lulus; fake HTTP, tanpa API berbayar. |
| `php vendor/bin/pint --test` | Lulus seluruh source PHP. |
| `npm run typecheck`, `npm run lint:check` | Lulus. |
| `npm run build:ssr` | Build client dan SSR lulus setelah patch source-map-js. |
| `composer audit --locked`, `npm audit` | 0 advisory/vulnerability yang dilaporkan pada waktu pemeriksaan. |
| `composer validate --strict`, `check-platform-reqs --no-dev` | Lulus pada PHP lokal 8.4.16. |
| `php artisan nusalens:preflight` pada env lokal | Exit 1 sesuai harapan: env/debug/HTTP/mail/runtime lokal bukan production. Env lokal tidak diubah demi hasil hijau. |
| Preflight test | Konfigurasi valid/tidak valid, artifact rusak, services sukses, lock dilepas, services tidak dihubungi jika config gagal, dan exception DB/Redis tidak membocorkan credential. |
| `migrate:status`, `schedule:list` | Semua 6 migration MySQL lokal sudah Ran; retensi terjadwal 01.00 WIB. Tidak ada migration/reset data pada pekerjaan ini. |
| `route:cache` | Lulus dengan artifact probe terisolasi; artifact dibersihkan, cache route lokal tidak diubah. |
| Browser Chromium/Playwright | Login/register/forgot-password HTTP 200 pada 1920x1080 dan 390x844; input/tombol render, tanpa overflow horizontal, page/console/HTTP error; guest ke riset redirect login. |
| HTTP `/up` | 200 pada Laragon lokal; bukan pemeriksaan semua layanan eksternal. |
| Secret scan terbatas | Nilai secret aktif panjang >=12 karakter tidak ditemukan pada candidate source/build frontend; `.env` tidak tracked. Bukan audit seluruh history Git atau file tangkapan user. |
| Dokumentasi/whitespace | Link internal dan `git diff --check` diperiksa. |

Screenshot lokal `storage/framework/testing/release-*.png` tidak masuk Git.
Helper browser sementara dibersihkan. Smoke tidak membuat akun, mengirim email,
atau memanggil Sectors. Pemakaian credit pekerjaan kesiapan publish: **0**.
QA riset login/cockpit/compare/snapshot sebelumnya tetap tercatat terpisah pada
[Penuntasan MVP](../penuntasan-mvp/README.md), bukan diakui sebagai smoke baru.
Pada verifikasi 6 Oktober perubahan belum di-push; perintah ekuivalen CI
dijalankan lokal. Status delivery terbaru dicatat di bawah.

## Pemeriksaan ulang 7 Oktober 2026

Audit ulang menemukan advisory transitif shell-quote 1.10.0 dari concurrently.
Lockfile diperbarui ke shell-quote 1.12.0 dalam rentang dependency existing,
tanpa menambah dependency atau mengubah manifest:
[GHSA-pqg4-j6r4-53mv](https://github.com/advisories/GHSA-pqg4-j6r4-53mv).
Audit npm dan Composer kembali bersih. Suite setelah pekerjaan ambang 60% dan
tampilan bukti: 197 test / 1.180 assertion; typecheck, lint, Pint dan build SSR
lulus. Rincian browser riset ada pada
[Tampilan Bukti](../../modules/Intelligence/work-items/tampilan-bukti/README.md).

## Batas rilis

Source siap dibawa ke tahap deployment, **belum merupakan aplikasi publik**.
Domain/TLS, SMTP inbox, layanan host, backup/restore, rollback server dan smoke
provider setelah cutover wajib ditutup mengikuti Deployment. Konfigurasi Linux
di runbook belum dieksekusi pada hosting tujuan. Batas momentum/kalender dan
valuasi populasi live pada Penuntasan MVP tetap berlaku; tidak dibuat angka palsu.

User belum memiliki hosting; pembelian, upload dan video/submission resmi
tidak dilakukan. File `Hackaton/` dan LSP milik user tidak disentuh.

## Delivery 7 Oktober 2026

Atas instruksi lanjutan user, persiapan rilis dikirim melalui `4dfda69`, bersama
perubahan ambang 60% `f5d6a5e` dan tampilan bukti `5b557ba`. Push `origin/main`
berhasil dan HEAD remote dikonfirmasi. Hasil verifikasi di atas adalah lokal;
hasil GitHub Actions dan pengujian hosting tidak disamakan dengan hasil lokal.
