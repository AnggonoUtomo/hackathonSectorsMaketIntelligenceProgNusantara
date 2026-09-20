# Tasks: Fondasi Akses dan ULID

## Persiapan

- [x] Baca dokumen aktif, template, source auth, migration, consumer ID dan test.
- [x] Periksa koneksi/schema MySQL aktif dengan query read-only dan tanpa secret.
- [x] Bedakan TCP Redis, client PHP, konfigurasi mail dan delivery sesungguhnya.
- [x] Susun scope, acceptance, dependency, checkpoint dan batas pemulihan.
- [x] User menyetujui plan implementasi.

## Increment 1: ULID

- [x] Preflight ulang target database; berhenti bila bukan fresh install lagi.
- [x] Ubah migration awal, model dan tipe frontend dalam satu increment konsisten.
- [x] Uji ULID valid/unik, registrasi/login/session/logout dengan fixture.
- [x] Uji migration/session MySQL disposable dan typecheck; tanpa menghapus data user.

## Increment 2: Akses verified

- [x] Aktifkan kontrak verifikasi dan middleware dashboard, pertahankan auth recovery.
- [x] Uji notifikasi registrasi, guest/unverified/verified, signed link dan resend.
- [x] Uji email berubah/tetap, link lama, password reset, dan logout.
- [x] Jalankan PHPUnit, lint/typecheck/build.
- [ ] Jalankan QA browser terarah.

## Increment 3: Lingkungan

- [x] Dapatkan izin client Redis/environment dan siapkan transport email uji.
- [x] Verifikasi Redis via aplikasi, cache/queue isolated tanpa flush global.
- [x] Verifikasi pengiriman email nyata ke sink/inbox uji, bukan hanya log.
- [x] Catat konfigurasi target tanpa secret dan hasil QA/risk yang tersisa.

## Hasil saat increment 2

ULID users, akses dashboard wajib verified, Redis CLI/dev, cache Redis, queue
Redis, dan mail delivery lokal via Mailpit selesai. Data aplikasi aktif tidak
di-reset, tidak di-migrate, dan tidak di-seed. Sectors belum dipakai. Browser QA
manual dan restart Apache/Laragon belum dijalankan.
