# Plan Kesiapan Publish

1. Audit dependency PHP/JS, konfigurasi production, route dan CI. Pertahankan
   perubahan sebelumnya dan file user Hackaton/LSP. Patch dependency existing
   hanya pada rentang kompatibel; jangan memakai audit fix force.
2. Test dahulu untuk rate limit registrasi, reset, compare/snapshot; tambahkan
   header keamanan. Session/login/verified serta public registration tidak berubah.
3. Command nusalens:preflight memeriksa konfigurasi/artifact tanpa API provider;
   opsi check-services memeriksa MySQL, migration, Redis dan cache lock saja.
4. Siapkan environment produksi kosong dari secret, runbook Linux Nginx/PHP-FPM,
   queue/scheduler, deployment additive, backup ledger/APP_KEY dan rollback code.
5. CI read-only: deterministic install, audit, tests, typecheck, lint dan build.
6. Jalankan audit ulang, seluruh test/build dan smoke HTTP lokal. Catat batas
   yang belum teruji: DNS/TLS/SMTP/backup restore dan kapasitas host tujuan.

Threat boundary: trafik publik dapat membanjiri registrasi/email dan request
riset; payload provider/secret tidak boleh keluar lewat debug/error. Perbandingan
tetap privat. Budget permanen dan cache tidak direset saat rilis/migrasi hosting.
Tidak ada endpoint Sectors tambahan; estimasi pemakaian credit: nol.
