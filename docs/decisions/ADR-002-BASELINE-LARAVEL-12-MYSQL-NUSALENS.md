# ADR-002: Baseline Laravel 12, MySQL, dan Arsitektur NusaLens

## Status

Accepted berdasarkan instruksi user; melengkapi ADR-001.

## Date

2026-09-19

## Context

Dokumen aktif sebelumnya membawa versi framework/database dari bahan awal dan
struktur module dua tingkat dari template kerja. User menetapkan Laravel 12
dan MySQL serta meminta arsitektur dan analisis sistem mengikuti NusaLens.
Template dipakai untuk pola kerja dokumentasi.

## Decision

- Gunakan Laravel 12 dan MySQL; pertahankan target PHP 8.4+, Inertia, React
  TypeScript, Redis, Tailwind, dan Sectors API v2 dari rancangan.
- Module langsung di `app/Modules/{Module}`: MarketData, Company, Screening,
  Intelligence, Comparison, dan Research.
- Pertahankan DDD-lite Modular Monolith dengan Hexagonal Architecture dari
  ADR-001, Domain murni, read model sesuai kebutuhan, repository selektif,
  serta Shared kernel minimal.
- Dokumentasi module mengikuti `docs/modules/{Module}/`; work item berisi
  README, plan, dan tasks. PRD/ADR ditambahkan sesuai kebutuhan.
- Materi analisis sistem dan pola template dikonsolidasikan ke dokumen aktif.
  Folder sumber tetap dipertahankan sampai user meminta penghapusan setelah
  dokumentasi selesai.

## Alternatives Considered

- Mempertahankan stack dari bahan awal: tidak mengikuti koreksi user.
- Memakai kategori/domain tambahan pada path module: tidak mengikuti struktur
  module NusaLens dan menambah level yang belum dibutuhkan.
- Hanya mengubah README: meninggalkan konflik aturan pada AGENTS dan dokumen
  yang akan dipakai saat implementasi.

## Consequences

Fondasi nanti memakai konfigurasi MySQL dan dependency kompatibel Laravel 12.
Belum ada source atau data aplikasi yang perlu dimigrasikan. Angka scoring,
schema, dan kebijakan produk yang belum diputuskan tetap terbuka; penyelarasan
dokumen tidak menetapkan keputusan baru di area tersebut.

Dokumen aktif dan template harus dapat digunakan tanpa rujukan wajib ke folder
sumber. Selesainya konsolidasi tidak memberi izin otomatis menghapus folder.

## Verification

Tindak lanjut: setelah penyelarasan selesai, user menginstruksikan penghapusan
folder sumber pada 2026-09-19. Kedua folder telah dihapus; dokumen aktif dan
template tetap tersedia. Keputusan mempertahankan sumber selama adaptasi di
atas dicatat sebagai riwayat.

Periksa stack/path di README, AGENTS, dan dokumen aktif; periksa pemetaan materi
sumber dan tautan lokal. Implementasi runtime baru diverifikasi setelah
scaffolding tersedia.

## References

- [Arsitektur](../ARCHITECTURE.md).
- [Struktur folder](../FOLDER-STRUCTURE.md).
- [Penyelarasan dokumentasi](../work-items/penyelarasan-dokumentasi/README.md).
