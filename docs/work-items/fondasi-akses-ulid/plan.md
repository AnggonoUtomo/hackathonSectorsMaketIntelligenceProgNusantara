# Plan: Fondasi Akses dan ULID

## Prinsip dan urutan

Plan belum merupakan izin coding. Pertahankan nama URL/route, controller dan
halaman starter. Tidak menambahkan module atau abstraction tanpa consumer.
Urutan: preflight schema -> ULID end-to-end -> akses verified -> kesiapan layanan.

## Increment 1: Akun baru dengan ULID

- Prasyarat: persetujuan implementasi dan preflight ulang database aktif.
- File utama: migration create_users, app/Models/User.php,
  resources/js/types/index.ts, dan test fitur ULID/session.
- Jika database masih kosong tanpa migrations, sesuaikan migration awal untuk
  users.id ULID primary dan sessions.user_id foreignUlid nullable/indexed.
  Pertahankan perilaku starter tanpa menambah FK constraint/cascade baru hanya
  karena mengganti tipe. Session ID dan reset token tidak diubah menjadi ULID.
- Gunakan trait HasUlids bawaan dan tipe User.id string di frontend. Jangan
  membuat generator sendiri atau mengasumsikan huruf ULID harus uppercase.
- Acceptance: pendaftaran menghasilkan ULID valid/unik; lookup/login, database
  session dan logout konsisten. Factory/seeder tidak mengunci ID numerik.
- Verifikasi: focused ULID/registration/auth/session test, typecheck; ulangi
  migration/session pada MySQL disposable, bukan hanya SQLite memory.

Checkpoint: jangan meneruskan model ULID ke database integer existing.
Jika users/migrations/tabel lain sudah muncul atau target koneksi berubah,
berhenti sebelum DDL. Tidak menjalankan migrate:fresh, reset, truncate, atau
drop database. Bukti kosong lokal tidak membuktikan deployment lain kosong;
catat perubahan migration awal sebagai fresh-install-only.

Jalankan migration pada database aplikasi hanya setelah otorisasi implementasi
yang mencakup inisialisasi database dan preflight. Tidak seed akun contoh.
Rollback/drop test hanya pada database disposable yang jelas terisolasi;
setelah ada data pengguna, jangan memperlakukan rollback sebagai penghapusan aman.

## Increment 2: Verifikasi email sebelum riset

- Prasyarat: increment 1 lulus. File utama: User.php, routes/web.php,
  tests/Feature/DashboardTest.php, Auth/RegistrationTest.php dan
  Auth/EmailVerificationTest.php; tambah test profil sebagai subtask terpisah.
- Implementasikan MustVerifyEmail dan gunakan middleware auth + verified untuk
  dashboard; semua route riset kelak wajib mengikuti boundary yang sama.
- Gunakan Registered event dan notifikasi bawaan yang sudah disediakan. Redirect
  registrasi/login ke dashboard dapat dipertahankan: middleware mengarahkan
  unverified ke verification.notice tanpa mengubah kontrak route.
- Pertahankan auth-only untuk settings, logout, notice/resend/handler verifikasi
  dan alur pemulihan. Handler tetap signed, resend tetap throttle:6,1.
- Acceptance: guest ke login, unverified ke notice, verified ke dashboard;
  registrasi mengirim VerifyEmail; tidak ada loop pada logout/settings/reset.
- Verifikasi: Notification::fake/Event fake secara terarah, link valid/expired,
  signature/hash salah, ID milik akun lain, resend throttling, sudah verified,
  dan perubahan email yang mencabut akses riset sampai verifikasi ulang.

Subtask profil: tambahkan kasus email berubah vs tidak berubah di
tests/Feature/Settings/ProfileUpdateTest.php. Gunakan resend flow yang ada untuk
email baru; jangan menambahkan email otomatis ganda. Pastikan link email lama
tidak dapat memverifikasi alamat baru. Test password update/reset tetap lulus.

Checkpoint: seluruh PHPUnit, typecheck/lint/build, lalu browser flow
register -> notice -> verify -> dashboard -> ubah email -> notice -> logout.
Gunakan mail sink lokal yang disetujui, jangan menampilkan signed link/token
atau credential pada handoff. UI yang ada dipertahankan kecuali ditemukan
hambatan alur nyata; tidak sekaligus redesign/terjemahkan seluruh starter.

## Increment 3: Kesiapan Redis dan mail

- Prasyarat: keputusan lingkungan/client dan izin sebelum instalasi/ubah php.ini.
- Rekomendasi awal: aktifkan phpredis yang kompatibel pada PHP CLI dan Apache
  Laragon; jangan memasang dependency Predis tanpa izin. Cek versi/arsitektur
  dan thread safety PHP sebelum menentukan binary. Ini pekerjaan environment,
  tidak perlu mengubah dependency aplikasi jika phpredis dapat dipakai.
- Setelah client tersedia, uji PING via aplikasi, lalu key TTL/queue isolated
  dengan prefix unik pada environment test yang disetujui. TCP terbuka saja
  belum lulus. Jangan flush Redis atau mengganggu key/aplikasi lain.
- Alihkan cache/queue target ke Redis hanya setelah test berhasil; session
  database tetap dipertahankan. Driver database yang ada bukan keputusan
  mengganti target Redis, melainkan kondisi awal yang belum dikonfigurasi.
- Mail log cukup untuk observasi lokal terbatas, bukan bukti email sampai inbox.
  Pilih mail sink lokal untuk QA; SMTP/provider publik membutuhkan konfigurasi
  terpisah sebelum demo publik. Credential dimasukkan lokal, tidak diminta di chat.
- Acceptance: cache/queue benar-benar memakai client target dan notifikasi
  verifikasi tiba pada sink/inbox uji yang disetujui; tidak ada secret di output.
- Verifikasi: smoke test isolated dan alur browser, dengan hasil/keterbatasan
  dicatat di work item. Kegagalan tahap ini tidak ditutupi klaim fondasi selesai.

## Gate dan penghentian

Tidak ada API Sectors atau konsumsi credit dalam seluruh increment ini.
Automated test default tetap SQLite memory + fake notifikasi; test MySQL
memakai database disposable dengan pemeriksaan isolasi sebelum schema writes.
MySQL DDL tidak diasumsikan rollback transaksi seperti data biasa.
Tidak commit/push atau mulai integrasi Sectors tanpa permintaan lanjutan.

## Referensi

- [Laravel 12 Email Verification](https://laravel.com/framework/docs/12.x/verification): kontrak, Registered event, middleware dan signed route.
- [Laravel 12 Eloquent](https://laravel.com/framework/docs/12.x/eloquent#uuid-and-ulid-keys): HasUlids bawaan.
- [Laravel 12 Migrations](https://laravel.com/framework/docs/12.x/migrations#column-method-foreignUlid): tipe referensi ULID.
- Source vendor terpasang diperiksa: HasUlids dan EmailVerificationRequest
  memakai identifier string; tidak perlu custom handler ULID.
