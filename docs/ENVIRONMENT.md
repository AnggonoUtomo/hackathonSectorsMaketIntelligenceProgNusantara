# Setup Environment

## Status

Source Laravel/Inertia, file dependency, migration, dan command project belum
tersedia. Dokumen ini menetapkan persiapan target; belum ada hasil instalasi
atau pengujian koneksi. Stack aktif: Laravel 12 dan MySQL.

## Prasyarat

- PHP 8.4+ sebagai target runtime dari rancangan awal, bukan klaim minimum Laravel.
- Composer.
- Node.js yang kompatibel dengan toolchain frontend yang dipilih.
- MySQL sebagai database utama.
- Redis untuk cache dan queue.
- Git saat repository disiapkan.

Versi MySQL, Redis, Node.js, dan package dikunci saat fondasi berdasarkan
environment yang tersedia dan kompatibilitas dependency. Docker boleh dipakai
jika membantu, tetapi bukan kebutuhan wajib produk.

## Rancangan environment

Contoh untuk environment lokal, tanpa credential. Ini belum menggantikan
`.env.example` yang akan dibuat bersama source:

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

OPENAI_API_KEY=
AI_EXPLAINER_ENABLED=false
```

Sesuaikan host, port, URL, dan credential secara lokal. Jangan masukkan nilai
secret ke repository atau output. Key OpenAI hanya diperlukan jika AI diaktifkan.

## Urutan setup setelah source tersedia

1. Pasang dependency backend/frontend yang sudah dipilih untuk Laravel 12.
2. Siapkan environment lokal dan generate app key.
3. Siapkan database MySQL lalu jalankan migration yang sudah disetujui.
4. Verifikasi Redis dapat diakses untuk cache dan queue.
5. Isi Sectors API key di backend.
6. Jalankan test dengan fake HTTP dan pemeriksaan frontend.
7. Jalankan server aplikasi serta frontend development server.
8. Uji satu use case end-to-end; gunakan API live hanya saat pemeriksaan manual
   memang membutuhkan data nyata.

Command executable dan versi yang benar-benar digunakan dicatat pada
[QUALITY.md](QUALITY.md) dan README setelah scaffolding. Pekerjaan dokumentasi
ini tidak memasang dependency, membuat database, atau menjalankan migration.
