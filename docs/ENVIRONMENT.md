# Setup Environment

## Status

Starter Laravel 12/Inertia React, dependency manifest/lock, migration, dan
command project tersedia. Module bisnis, ULID users, kewajiban email verified,
dan konfigurasi Sectors belum diimplementasikan. MySQL/Redis adalah target;
pekerjaan dokumentasi ini tidak membuktikan koneksi lokal keduanya.

## Prasyarat

- PHP 8.4+ sebagai target runtime dari rancangan awal, bukan klaim minimum Laravel.
- Composer.
- Node.js yang kompatibel dengan toolchain frontend yang dipilih.
- MySQL sebagai database utama.
- Redis untuk cache dan queue.
- Git untuk repository yang sudah tersedia.

Versi MySQL, Redis, Node.js, dan package dikunci saat fondasi berdasarkan
environment yang tersedia dan kompatibilitas dependency. Docker boleh dipakai
jika membantu, tetapi bukan kebutuhan wajib produk.

## Rancangan environment

Contoh target lokal, tanpa credential. `.env.example` starter sudah tersedia;
contoh ini bukan klaim semua variabel produk sudah dihubungkan ke konfigurasi:

```env
APP_NAME=NusaLens
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=nusalens
DB_USERNAME=
DB_PASSWORD=

CACHE_STORE=redis
QUEUE_CONNECTION=redis
REDIS_HOST=127.0.0.1
REDIS_PORT=6379

SECTORS_API_BASE_URL=https://api.sectors.app/v2
SECTORS_API_KEY=
SECTORS_API_TIMEOUT=10

AI_EXPLAINER_ENABLED=false
```

Sesuaikan host, port, URL, dan credential secara lokal. Jangan masukkan nilai
secret ke repository atau output. Provider/model AI belum dipilih; variabel key
AI baru ditambahkan sesuai integrasi yang disetujui. Email verification juga
memerlukan transport mail yang diuji pada increment auth.

## Setup starter

Jalankan dari root project. Instalasi reproducible memakai lockfile yang ada:

```powershell
composer install
npm ci
```

Siapkan `.env` lokal berdasarkan `.env.example` bila belum ada, pilih database
MySQL yang benar, dan jangan menimpa credential atau APP_KEY instalasi yang ada.
`php artisan key:generate` hanya untuk instalasi baru tanpa key. Setelah database
siap dan rencana migration diperiksa, `php artisan migrate` menjalankan migration
starter; command ini belum mengubah ID users menjadi ULID.

```powershell
php vendor/bin/phpunit
npm run typecheck
npm run lint:check
npm run build
```

Development: `composer dev` menjalankan server, queue, log, dan Vite sesuai
script project. Jika salah satu helper tidak didukung environment, jalankan
`php artisan serve` dan `npm run dev` pada terminal terpisah; gunakan port lain
bila port default terpakai. Halaman awal masih starter, bukan fitur riset MVP.

Untuk Laragon/Apache, document root yang dituju adalah `public/`. File `.htaccess`
root buatan user mengarahkan path ke `public/` saat akses lewat root repository;
tidak menggantikan konfigurasi document root yang benar. Pastikan mod_rewrite
dan AllowOverride sesuai; tanpa rewrite jangan sajikan root repository karena
berkas nonpublik dapat terekspos. Dukungan Apache belum diuji runtime pada
pekerjaan dokumentasi ini.

## Verifikasi fondasi produk berikutnya

1. Verifikasi dependency backend/frontend dari lockfile yang tersedia.
2. Verifikasi environment lokal dan app key; jangan regenerasi key yang sudah digunakan.
3. Siapkan database MySQL lalu jalankan migration yang sudah disetujui.
4. Verifikasi Redis dapat diakses untuk cache dan queue.
5. Isi Sectors API key di backend.
6. Jalankan test dengan fake HTTP dan pemeriksaan frontend.
7. Jalankan server aplikasi serta frontend development server.
8. Uji satu use case end-to-end; gunakan API live hanya saat pemeriksaan manual
   memang membutuhkan data nyata.

Command pemeriksaan tersedia pada [QUALITY.md](QUALITY.md). Pekerjaan dokumentasi
ini tidak memasang dependency, membuat database, atau menjalankan migration.
