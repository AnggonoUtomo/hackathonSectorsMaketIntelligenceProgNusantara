# Keputusan Aktif

Dokumen ini menjadi indeks keputusan aktif. ADR lengkap berada di
`docs/decisions/`.

| ADR | Status | Keputusan |
| --- | ------ | --------- |
| [ADR-001](decisions/ADR-001-DDD-LITE-MODULAR-MONOLITH-HEXAGONAL.md) | Accepted | Gunakan DDD-lite Modular Monolith dengan Hexagonal Architecture. |
| [ADR-002](decisions/ADR-002-BASELINE-LARAVEL-12-MYSQL-NUSALENS.md) | Accepted | Laravel 12, MySQL, dan module langsung mengikuti rancangan NusaLens. |
| [ADR-003](decisions/ADR-003-AKSES-ULID-PERSISTENCE-MVP.md) | Accepted; syarat email direvisi 8 Oktober 2026 | Login tanpa verifikasi email, ULID, ownership persistence dan snapshot immutable. |

ADR-002 memperjelas baseline sesuai instruksi user tanggal 2026-09-19 dan
melengkapi ADR-001. ADR-003 dan keputusan wawancara 2026-09-20 menutup keputusan
MVP yang dahulu terbuka. Accepted berarti disetujui, bukan sudah diimplementasikan.

## Baseline aktif yang disetujui

Revisi user 2026-10-06: kelengkapan minimum turun dari 70% ke 60%, tanpa mengubah
bobot/peer/validitas. Formula `nusalens-v1.1.0`; snapshot historis tetap memakai
versi saat disimpan. Rincian: [Ambang 60%](modules/Intelligence/work-items/ambang-kelengkapan-60/README.md).

| Area | Keputusan | Acuan |
| --- | --- | --- |
| Akses dan identitas | Registrasi umum; login tanpa verifikasi email untuk riset (revisi user, 8 Oktober 2026); ULID termasuk users/FK. | [Security](SECURITY.md), [Akses dan Identitas](work-items/akses-dan-identitas/README.md) |
| Tampilan dan compare | Recharts; maksimal 3 saham; simpan manual privat dan berversi. | [Project](PROJECT.md) |
| Scoring v1.1 | Bank/nonbank, bobot 30/25/20/15/10, metrik setara per komponen, peer minimal 5 lainnya, average rank, threshold 60%, 2 desimal tanpa kategori. | [Scoring](SCORING.md) |
| Periode dan data invalid | Periode/basis kompatibel, fallback maksimal satu periode; hilang bukan nol, outlier valid dipertahankan. | [Scoring](SCORING.md) |
| Bukti dan reuse | Snapshot immutable, hasil identik dapat dibagi, retensi 30 hari kecuali masih dirujuk. | [Model Data](DATA-MODEL.md) |
| Credit | 1.000 sekali pakai, cadangan 600 perlu izin; 20/akun/hari reset WIB, satu retry maksimal. | [Alur Data](DATA-FLOW.md) |
| Cache/fallback | TTL profil 7 hari, fundamental 24 jam, pasar/screener 1 jam; fallback pasar 24 jam dengan pengecualian bursa tutup, laporan 7 hari. | [Alur Data](DATA-FLOW.md) |
| AI dan masa depan | Aturan wajib, AI opsional setelah inti stabil; paid plans/BYOK masa depan, bukan MVP. | [Roadmap](ROADMAP.md) |

Detail teknis (kontrak provider, DDL/precision, kalender, biaya peer) tetap gate
implementasi. Provider/model/budget AI baru dipilih saat integrasi diperlukan.
Riwayat dokumentasi: [Penetapan keputusan MVP](work-items/penetapan-keputusan-mvp/README.md).

## Aturan

- Tambahkan ADR hanya untuk keputusan yang mahal atau sulit dibalik.
- Jangan mengubah keputusan arsitektur besar lewat asumsi dalam implementasi.
- Jika keputusan berubah, buat ADR baru yang menjelaskan penggantiannya.
