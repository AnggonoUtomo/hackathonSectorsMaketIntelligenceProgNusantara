# Work Item: Fondasi Akses dan ULID

## Status dan owner

- Status: Increment 1 dan 2 selesai; increment 3 belum dimulai.
- Owner: lintas module, fondasi auth starter.
- Target: model User, migration starter, tipe frontend, middleware akses dan test.
- Scope implementasi selesai: ULID users fresh install, tipe frontend, akses
  dashboard wajib verified, dan test auth/session terkait. Tanpa Git delivery,
  kecuali diminta terpisah.

## Kondisi awal terverifikasi

Inspeksi 2026-09-20, tanpa membaca data pribadi atau menampilkan credential:

| Area | Bukti |
| --- | --- |
| Runtime CLI | PHP 8.4.16, Laravel 12.69.2. |
| Database | Koneksi MySQL lokal berhasil; query information_schema dibatasi TABLE_SCHEMA = DATABASE() menghasilkan 0 tabel. |
| Schema starter | users.id integer; sessions.user_id foreignId, nullable dan indexed. Belum diterapkan pada database aktif. |
| Model/frontend | User belum HasUlids/MustVerifyEmail; resources/js/types/index.ts memakai id: number. |
| Auth | Registrasi publik, Registered event, verification notice/signed handler/resend tersedia. Dashboard hanya auth. |
| Settings | Perubahan email mengosongkan email_verified_at; settings hanya auth. |
| Test | Factory default verified, state unverified tersedia; PHPUnit memakai SQLite memory, mail/cache/session array. |
| Cache/queue/session | Ketiganya memakai database; tabel pendukung belum ada. |
| Redis | Client terpilih phpredis, extension CLI tidak tersedia; Predis juga tidak terpasang. Port lokal terjangkau TCP, bukan bukti protokol/auth Redis berhasil. |
| Email | Mailer log; belum membuktikan pengiriman ke inbox. |

## Hasil increment 1

Implementasi 2026-09-20:

- Migration awal memakai `users.id` ULID primary dan `sessions.user_id`
  `foreignUlid`, tetap nullable dan indexed tanpa menambah FK constraint baru.
- Model `User` memakai trait `HasUlids` bawaan Laravel.
- Tipe frontend `User.id` diubah dari number menjadi string.
- Test auth menegaskan registrasi menghasilkan ULID valid, factory membuat ULID
  unik, login tetap berjalan, session database menyimpan ULID user, dan logout
  membersihkan session database.

Verifikasi:

- `php artisan test tests/Feature/Auth/RegistrationTest.php tests/Feature/Auth/AuthenticationTest.php`
- `php artisan test tests/Feature/Auth/EmailVerificationTest.php tests/Feature/DashboardTest.php`
- `php artisan test`
- `npm run typecheck`
- `npm run lint:check`
- `npm run build`
- `git diff --check`
- MySQL disposable `hackatonsectors_ulid_test_*`: migration berhasil,
  `users.id` dan `sessions.user_id` menjadi `char(26)`, user baru menghasilkan
  ULID valid, lalu database sementara dihapus.

## Hasil increment 2

Implementasi 2026-09-20:

- Model `User` mengimplementasikan kontrak `MustVerifyEmail` bawaan Laravel.
- Route `dashboard` memakai middleware `auth` dan `verified`, sehingga pintu
  fitur riset tidak dapat dibuka pengguna yang belum verifikasi email.
- Route registrasi, login, logout, password reset, settings, verification
  notice, signed handler, dan resend tetap memakai struktur starter.
- Test auth/profile diperluas untuk notifikasi registrasi, redirect unverified,
  dashboard verified, signed link valid/expired/hash salah/akun lain, resend,
  throttle resend, already verified, perubahan email, link lama, password reset,
  dan logout.

Verifikasi:

- Focused RED sebelum implementasi membuktikan gap notifikasi registrasi dan
  redirect unverified.
- `php artisan test tests/Feature/Auth/RegistrationTest.php tests/Feature/Auth/EmailVerificationTest.php tests/Feature/DashboardTest.php tests/Feature/Settings/ProfileUpdateTest.php`
- `php artisan test`
- `npm run typecheck`
- `npm run lint:check`
- `npm run build`
- `git diff --check`

Catatan: test notifikasi memakai fake/array mail sesuai konfigurasi testing.
Ini membuktikan aplikasi mengirim notifikasi Laravel, bukan bukti email sampai
ke inbox nyata. Browser QA manual juga belum dijalankan pada increment ini.
Pengiriman nyata tetap masuk increment 3.

Pemeriksaan schema wajib dibatasi ke database aktif. Inventaris tanpa pembatas
schema dapat mencakup database lain pada server lokal dan tidak boleh dipakai
untuk menyimpulkan kondisi NusaLens. Tidak ada tabel/database lain yang diubah.

## Scope dan non-scope

Target: akun baru memakai ULID, login tetap berfungsi, dan pengguna unverified
tidak dapat membuka dashboard yang menjadi pintu fitur riset. Auth, logout,
reset password, verifikasi dan settings pemulihan akun tidak terjebak redirect.
Tetap memakai struktur auth starter; tidak membuat module Identity baru atau
memindahkan controller hanya untuk merapikan arsitektur.

Tidak mengimplementasikan Sectors, scoring, compare, chart, paid plans, atau AI.
Tidak memasang package, mengubah php.ini, mail provider, maupun menjalankan
migration pada turn plan. Tidak membuat/menghapus database atau seed akun default.
File user dan file runtime LSP dipertahankan.

## Acceptance criteria implementasi

- [x] User ID ULID string, unik, dan konsisten hingga tipe frontend/session.
- [x] Registrasi mengirim notifikasi verifikasi; guest/unverified ditolak dari riset.
- [x] Signed link ULID, resend, perubahan email, reset password dan logout teruji.
- [x] MySQL disposable diuji; kesiapan Redis/mail dibedakan dari hasil test fake.
- [x] Tidak ada reset data, akses provider Sectors, atau scope fitur lain.

## Dependency dan keputusan

[ADR-003](../../decisions/ADR-003-AKSES-ULID-PERSISTENCE-MVP.md),
[Security](../../SECURITY.md), dan [Model Data](../../DATA-MODEL.md).
Plan memakai jalur database aktif kosong, bukan migrasi konversi akun existing.
Kondisi ini harus diperiksa ulang sebelum implementasi; database berisi data
atau migration lama memerlukan plan konversi tersendiri dan persetujuan.

## Handoff increment 2

Source dan test berubah untuk ULID dan akses verified. Database aplikasi aktif
tetap tidak di-migrate dan tidak di-seed; schema write hanya dilakukan pada
database MySQL disposable saat increment 1. Tidak memasang Redis client,
menjalankan Sectors API, atau membuktikan email inbox nyata. Increment 3
menunggu instruksi terpisah. Rincian ada pada [plan](plan.md).
