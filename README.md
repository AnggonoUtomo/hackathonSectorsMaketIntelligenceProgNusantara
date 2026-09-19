# NusaLens

NusaLens adalah aplikasi **Indonesian Market Intelligence** untuk membantu
pengguna menyaring, membandingkan, dan memahami saham Indonesia yang layak
diteliti lebih lanjut berdasarkan data Sectors Financial API v2.

NusaLens bukan penasihat investasi, broker, trading bot, atau pemberi
rekomendasi BUY/HOLD/SELL.

## Target stack

- Laravel 12 dan PHP 8.4+ (target runtime project)
- Inertia.js
- React + TypeScript
- MySQL
- Redis
- Tailwind CSS
- Sectors Financial API v2
- Apache ECharts atau Recharts

## Dokumentasi

Mulai dari [`docs/README.md`](docs/README.md).

Dokumen aktif project berada di `docs/`. Arsitektur dan analisis sistem mengikuti
rancangan NusaLens; pola dokumentasi mengikuti template kerja yang sudah
disesuaikan pada [`docs/templates/`](docs/templates/README.md).

Materi sumber telah diadaptasi ke dokumen aktif. Folder `docs/NusaLens/` dan
`docs/templateDocs/` telah dihapus atas instruksi pemilik project setelah
penyelarasan selesai. Stack aktif adalah Laravel 12 dan MySQL.

## Arsitektur

DDD-lite Modular Monolith dengan Hexagonal Architecture. Module berada langsung
di `app/Modules/{Module}`: MarketData, Company, Screening, Intelligence,
Comparison, dan Research.

Aturan bisnis dan scoring berupa pure PHP. Integrasi Sectors, MySQL, Redis, dan
AI ditempatkan di Infrastructure. Rincian tersedia pada
[`docs/ARCHITECTURE.md`](docs/ARCHITECTURE.md).

## Prinsip MVP

- Sectors API v2 adalah sumber data inti.
- Sistem menghitung nilai; AI hanya menjelaskan hasil yang sudah dihitung.
- Credit API harus dihemat melalui cache dan pengambilan data bertahap.
- Fokus alur: Temukan Saham, Detail Perusahaan, Bandingkan, Jelaskan Nilai, dan
  Temukan Kandidat Menarik.

## Status

Baseline dokumentasi telah diselaraskan dengan rancangan NusaLens dan koreksi
stack. Source Laravel/Inertia belum dibuat, sehingga aplikasi belum bisa
dijalankan. Persiapan environment ada di
[`docs/ENVIRONMENT.md`](docs/ENVIRONMENT.md); command executable akan dicatat
setelah source dan toolchain tersedia.
