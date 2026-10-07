# Deployment NusaLens

Status 6 Oktober 2026: persiapan source, hardening, build dan panduan tersedia.
Belum ada hosting. Langkah server di bawah adalah runbook, bukan bukti bahwa
DNS, TLS, SMTP, backup/restore atau deployment publik sudah lulus.
Hasil lokal: [Kesiapan Publish](work-items/kesiapan-publish/README.md).

## 1. Kebutuhan hosting

Pilih satu server Linux atau layanan Laravel yang menyediakan PHP 8.4+,
Composer 2, MySQL, Redis, HTTPS, SSH, cron dan proses queue persisten. PHP CLI
dan PHP-FPM harus memakai versi/ekstensi sama: PDO MySQL, mbstring, OpenSSL,
cURL, DOM/XML, fileinfo dan Redis (phpredis). Node 22 diperlukan pada mesin
build; bukan syarat runtime web jika artifact sudah dibangun di CI.

Jangan memilih shared hosting yang tidak menyediakan Redis/worker atau hanya
membolehkan document root repository. Tidak perlu mengubah arsitektur,
memasang microservice, atau menambah layanan penyimpanan untuk MVP.
Kapasitas host belum dibenchmark; periksa RAM, disk, koneksi database dan
latensi sebelum membuka akses luas. MySQL/Redis harus di jaringan privat,
bukan port publik. Siapkan SMTP dengan domain pengirim yang terverifikasi.

## 2. Bekukan artifact dan data

1. Jalankan quality gate dan audit dari lockfile. Setelah user meminta commit,
   catat SHA release dan gunakan checkout bersih SHA tersebut. Jangan deploy
   HEAD lama yang belum mencakup perubahan kesiapan publish.
2. Jangan mengunggah seluruh workspace: `Hackaton/`, file LSP, `.env` lokal,
   log, test credentials dan `node_modules` bukan artifact publik. Install
   dependency dari lockfile dan bawa `public/build` hasil build release.
3. Hentikan trafik/worker lama selama pemindahan database. Backup lengkap
   MySQL, storage yang dibutuhkan dan APP_KEY melalui kanal privat terenkripsi.
4. Restore database ke host tujuan sebelum membuka trafik. Pertahankan ULID,
   pengguna, snapshot, bukti, ledger, reservasi credit dan APP_KEY. Migration
   berikutnya hanya additive; jangan `migrate:fresh`, reset atau truncate.

**Ledger tidak boleh dimulai lagi dari nol untuk API key yang sudah dipakai.**
Budget 1.000 credit adalah sekali pakai, bukan per instalasi. Jangan menjalankan
dua deployment dengan database ledger terpisah memakai key yang sama. Cocokkan
pemakaian di provider dengan ledger sebelum cutover; penggunaan key di luar
NusaLens tidak otomatis diketahui aplikasi. Cadangan 600 tidak diaktifkan.
Redis bukan sumber kebenaran budget. Cache kosong dapat memicu request berbayar
baru, sehingga pergantian cache memerlukan pemeriksaan budget sebelum demo.

## 3. Environment produksi

Gunakan [.env.production.example](../.env.production.example) sebagai daftar
variabel. Isi pada secret store/server, jangan commit hasilnya. Ganti domain
contoh, password database, SMTP dan key Sectors. Simpan `.env` di luar public
root dengan akses hanya untuk operator/runtime. Jangan menaruh secret pada
variabel `VITE_*` atau command line yang terekam history.

- `APP_ENV=production`, `APP_DEBUG=false`, `APP_URL=https://domain-aktual`.
- Gunakan APP_KEY instalasi asal ketika memindahkan data. `key:generate` hanya
  untuk instalasi benar-benar baru tanpa data/key, bukan langkah rutin deploy.
- Database user khusus aplikasi, bukan root; password wajib. Batasi hak ke
  database NusaLens, termasuk DDL saat migration.
- `CACHE_STORE=redis`, `QUEUE_CONNECTION=redis`; gunakan namespace terpisah
  dari aplikasi lain. `REDIS_QUEUE_RETRY_AFTER=120` lebih besar dari timeout
  worker 90 detik. Aktifkan persistence Redis untuk queue pada host.
- Session server-side, cookie secure/HttpOnly/SameSite=lax. Jangan melayani
  aplikasi produksi lewat HTTP karena cookie tidak akan dikirim.
- SMTP STARTTLS: `MAIL_SCHEME=smtp`, port 587, `MAIL_REQUIRE_TLS=true`.
  Untuk implicit TLS gunakan `smtps` dan port 465 sesuai penyedia. Jangan
  meninggalkan `MAIL_URL` lama yang menimpa setting SMTP; gunakan satu sumber.
- `MARKETDATA_PROVIDER_MODE=real`; budget/kuota/retry tetap 1000/20/2.
- `INERTIA_SSR_ENABLED=false` pada template: client rendering sudah cukup.
  Build SSR diuji, tetapi daemon SSR bukan syarat MVP. Jangan mengaktifkannya
  tanpa proses SSR terkelola dan pengujian tersendiri.
- Log `daily`/`info`, retensi 14 hari. Jangan mengaktifkan log payload/header
  provider atau debug di production.

## 4. Build dan aktivasi

Contoh layout: `/var/www/nusalens/releases/<sha>` untuk kode immutable,
`/var/www/nusalens/shared` untuk `.env` dan storage, lalu symlink `current`
menunjuk release aktif. Hubungkan shared storage/env dengan akses minimum;
`storage` dan `bootstrap/cache` writable oleh runtime, source tidak. Jangan
`chmod 777`. Build/install dijalankan oleh deploy user, bukan root.

Pada checkout/build machine, dengan dev dependencies:

```sh
composer install --no-interaction --prefer-dist
npm ci
php artisan test --compact
php vendor/bin/pint --test
npm run typecheck
npm run lint:check
npm run build:ssr
composer audit --locked
npm audit --audit-level=moderate
```

Test suite memakai database testing terisolasi dan fake HTTP, bukan database
produksi. Pada release server yang sudah terhubung ke env/data tujuan:

```sh
composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader
composer check-platform-reqs --no-dev
php artisan config:clear
php artisan nusalens:preflight
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan nusalens:preflight --check-services
```

Hentikan deploy pada exit code nonzero; jangan mengabaikan FAIL preflight.
Jalankan Artisan dengan akun yang memiliki permission setara runtime. Perintah
preflight tanpa opsi memeriksa konfigurasi, key, ekstensi, cookie, artifact,
namespace dan budget. Opsi services memeriksa MySQL, migration, Redis dan
cache lock; tidak memanggil Sectors atau mengirim email, tidak mencetak secret.
Ia tidak membuktikan domain/sertifikat/mailbox/kapasitas benar.

Pastikan `public/hot` tidak ada: itu penunjuk Vite lokal, bukan artifact rilis.
Jangan menjalankan `npm run dev` atau `php artisan serve` sebagai server publik.
Jangan menjalankan `optimize:clear`/`cache:clear` sebagai kebiasaan deploy karena
cache data berbayar dapat hilang. Bersihkan config/route/view secara spesifik
jika perlu. Cache database ledger tidak diganti oleh operasi tersebut.

Setelah preflight lulus, alihkan symlink `current`, reload PHP-FPM/opcache,
restart worker melalui supervisor dan jalankan smoke. Pada update berikutnya,
gunakan maintenance mode selama cutover yang tidak kompatibel; `artisan up`
hanya setelah health dan service checks lulus. Simpan SHA release sebelumnya.

## 5. Web server dan HTTPS

Document root wajib `/var/www/nusalens/current/public`. Jangan mengandalkan
`.htaccess` root untuk melindungi repository. Contoh Nginx berikut perlu
disesuaikan socket, domain dan sertifikat, lalu diuji `nginx -t` di host.
Aktifkan sertifikat valid dahulu; jangan copy contoh path sebagai file nyata.

```nginx
server {
    listen 80;
    server_name nusalens.example.com;
    return 301 https://nusalens.example.com$request_uri;
}
server {
    listen 443 ssl;
    server_name nusalens.example.com;
    root /var/www/nusalens/current/public;
    index index.php;
    ssl_certificate /etc/letsencrypt/live/nusalens.example.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/nusalens.example.com/privkey.pem;
    ssl_protocols TLSv1.2 TLSv1.3;
    server_tokens off;
    charset utf-8;

    location / { try_files $uri $uri/ /index.php?$query_string; }
    location = /index.php {
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $realpath_root/index.php;
        fastcgi_param DOCUMENT_ROOT $realpath_root;
        fastcgi_param HTTPS on;
        fastcgi_pass unix:/run/php/php8.4-fpm.sock;
        fastcgi_hide_header X-Powered-By;
    }
    location ~ \.php$ { return 404; }
    location ~ /\.(?!well-known).* { deny all; }
}
```

Konfigurasikan default virtual host untuk menolak host yang tidak dikenal.
Contoh memakai TLS langsung di Nginx. Jika hosting memakai reverse proxy,
verifikasi trusted proxy dan HTTPS forwarding dengan allowlist host/IP milik
penyedia; jangan mempercayai semua forwarded headers secara membabi buta.

Middleware menambahkan nosniff, SAMEORIGIN, referrer/permissions policy dan CSP
dasar (base/object/frame). HSTS hanya untuk request HTTPS di production. CSP
ini bukan full script/style allowlist atau jaminan bebas XSS. Respons static
dan error web server perlu header setara di konfigurasi host bila diperlukan;
hindari CSP ganda yang memblokir Inertia/Recharts. Periksa header respons nyata.

## 6. Queue, scheduler dan pemantauan

Contoh Supervisor; sesuaikan user, binary PHP dan direktori:

```ini
[program:nusalens-worker]
directory=/var/www/nusalens/current
command=/usr/bin/php artisan queue:work redis --sleep=3 --tries=1 --timeout=90 --max-time=3600
user=www-data
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
stopwaitsecs=120
redirect_stderr=true
stdout_logfile=/var/www/nusalens/shared/storage/logs/worker.log
stdout_logfile_maxbytes=10MB
stdout_logfile_backups=3
```

Restart proses ini setiap release; `queue:restart` juga bisa dipakai jika
namespace cache tetap sama. Jangan retry semua failed job tanpa menilai
potensi side effect/credit; retry provider sudah dibatasi adapter internal.

Cron milik runtime, satu scheduler aktif:

```cron
* * * * * cd /var/www/nusalens/current && /usr/bin/php artisan schedule:run >> /var/www/nusalens/shared/storage/logs/scheduler.log 2>&1
```

Rotasikan log cron/worker. `schedule:list` harus menunjukkan retensi bukti
01.00 WIB. Prune hanya bukti bersama kedaluwarsa >30 hari; tidak menghapus
snapshot privat/ledger. Periksa worker hidup, `queue:failed`, disk, error rate,
SMTP dan credit tersisa. `/up` memeriksa aplikasi dapat boot, bukan pengganti
pengujian MySQL/Redis/mail. Tinjau log tanpa menyalin credential/payload pribadi.

## 7. Backup dan rollback

Backup MySQL harian dan sebelum release. Contoh Linux memakai credential file
privat permission 0600 (bukan password pada command line); pastikan exit code
dump sukses, enkripsi hasilnya, pindahkan offsite dan tetapkan retensi:

```sh
mysqldump --defaults-extra-file=/secure/nusalens-backup.cnf --single-transaction --no-tablespaces --routines --triggers nusalens > /secure/nusalens.sql
```

Backup harus menyertakan ledger/reservasi, users dan snapshot terkait secara
konsisten; simpan APP_KEY/env terenkripsi secara terpisah. Uji restore ke
database terisolasi, hitung jumlah entitas penting dan buka snapshot tanpa
memanggil provider. Jangan menguji restore dengan menimpa database aktif.

Jika release bermasalah: hentikan trafik/worker, kembalikan symlink ke SHA
sebelumnya, rebuild cache konfigurasi/view/route, reload PHP-FPM, restart worker,
lalu smoke sebelum `up`. Pertahankan database forward jika kompatibel; jangan
otomatis rollback migration atau restore backup lama.

**Restore backup lama dapat menghilangkan pemakaian credit setelah backup.**
Jika pemulihan database tak terhindarkan, blokir panggilan Sectors, rekonsiliasi
ledger/reservasi dengan catatan terbaru/provider dahulu. Jangan membuka trafik
atau menganggap budget tersedia hanya karena angka backup lebih kecil.

## 8. Gate sebelum membagikan URL

- [ ] Semua FAIL preflight/services selesai pada user runtime dan env tujuan.
- [ ] HTTP redirect ke HTTPS; sertifikat valid dan cookie secure bekerja.
- [ ] `/.env`, `/.git/config`, `/composer.json`, `/storage/logs/laravel.log`
  tidak membocorkan isi berkas; `public/hot` tidak ada; debug/error generik.
- [ ] Registrasi, inbox verifikasi, login/logout dan reset password diuji nyata.
- [ ] Pengguna belum terverifikasi tidak dapat mengakses riset; snapshot tidak
  dapat dibuka akun lain. Console browser dan request asset tidak error.
- [ ] Worker/scheduler, backup/restore terisolasi dan rollback kode teruji.
- [ ] Periksa saldo provider dan ledger; jalankan satu smoke perusahaan real
  terarah setelah budget biaya disetujui. Jangan mengambil seluruh IDX.
- [ ] Alur detail, compare dan snapshot diperiksa desktop/mobile dengan cache
  yang tanggalnya terlihat. Jangan mengakali kuota atau mengisi data contoh.
- [ ] URL demo, video dan persyaratan penyelenggara di [Submission](SUBMISSION.md)
  diperiksa; repo/artifact final tidak membawa secret.

Sumber konfigurasi web/optimasi:
[Laravel 12 Deployment](https://laravel.com/docs/12.x/deployment).
Runbook ini menambahkan batas operasional NusaLens: ledger tidak direset,
data diambil bertahap, dan setiap smoke live tetap memakai credit.
