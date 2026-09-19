# Keputusan Aktif

Dokumen ini menjadi indeks keputusan aktif. ADR lengkap berada di
`docs/decisions/`.

| ADR | Status | Keputusan |
| --- | ------ | --------- |
| [ADR-001](decisions/ADR-001-DDD-LITE-MODULAR-MONOLITH-HEXAGONAL.md) | Accepted | Gunakan DDD-lite Modular Monolith dengan Hexagonal Architecture. |
| [ADR-002](decisions/ADR-002-BASELINE-LARAVEL-12-MYSQL-NUSALENS.md) | Accepted | Laravel 12, MySQL, dan module langsung mengikuti rancangan NusaLens. |

ADR-002 memperjelas baseline sesuai instruksi user tanggal 2026-09-19 dan
melengkapi ADR-001. Rumus rinci, identifier/schema, authentication, chart
library, dan batas final compare belum diputuskan; lihat [PROJECT.md](PROJECT.md).

## Aturan

- Tambahkan ADR hanya untuk keputusan yang mahal atau sulit dibalik.
- Jangan mengubah keputusan arsitektur besar lewat asumsi dalam implementasi.
- Jika keputusan berubah, buat ADR baru yang menjelaskan penggantiannya.
