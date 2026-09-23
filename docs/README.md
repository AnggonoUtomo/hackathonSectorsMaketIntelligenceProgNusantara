# Dokumentasi NusaLens

Dokumentasi ini adalah acuan aktif untuk membangun **NusaLens - Indonesian
Market Intelligence** pada Sectors Hackathon 2026.

NusaLens membantu pengguna menyaring, membandingkan, dan memahami saham
Indonesia yang layak diteliti lebih lanjut berdasarkan data keuangan dan pasar
dari Sectors Financial API v2.

NusaLens bukan penasihat investasi, broker, trading bot, atau pemberi
rekomendasi BUY/HOLD/SELL.

## Mulai di sini

1. Baca [Project](PROJECT.md) untuk tujuan, scope, dan batas produk.
2. Baca [Arsitektur](ARCHITECTURE.md) untuk batas dependency dan modul.
3. Baca [Struktur Folder](FOLDER-STRUCTURE.md) sebelum mengubah struktur.
4. Baca [Daftar Module](MODULES.md) untuk ownership dan dependency.
5. Baca [Workflow](WORKFLOW.md) sebelum memulai pekerjaan.
6. Pilih pemeriksaan dari [Quality](QUALITY.md) sesuai risiko.
7. Baca [Keputusan](DECISIONS.md) saat menyentuh keputusan aktif.
8. Baca [API](API.md) saat mengubah endpoint publik atau integrasi.

## Acuan pekerjaan khusus

- [Redesain riset terpandu](work-items/redesain-riset-terpandu/README.md):
  rancangan UX dan increment data real. Increment 1 menghubungkan autocomplete,
  direktori dan profil real; grafik/wizard dijadwalkan pada increment berikutnya.
- [Scoring](SCORING.md): metrik, bobot, peer group, data hilang, dan penjelasan nilai.
- [Alur Data](DATA-FLOW.md): pengambilan bertahap, cache, dan anggaran credit.
- [Model Data](DATA-MODEL.md): konsep penyimpanan dari blueprint dan batas keputusan schema.
- [Keamanan](SECURITY.md): input screener, secret, integritas data, dan AI.
- [Environment](ENVIRONMENT.md): persiapan Laravel 12, MySQL, dan Redis.
- [Submission](SUBMISSION.md): checklist produk, teknis, dan alur demo.
- [Roadmap](ROADMAP.md): urutan MVP, AI opsional, dan arah paket berbayar/BYOK.
- [Template](templates/README.md), [module](modules/README.md), dan
  [work item](work-items/README.md): pola dokumentasi pekerjaan.

## Kedudukan sumber

Arsitektur, scope, analisis sistem, dan aturan bisnis diadopsi dari dokumen
NusaLens. Template kerja hanya mengatur cara mencatat pekerjaan; struktur
module mengikuti `app/Modules/{Module}` dari rancangan NusaLens.

Koreksi user tanggal 2026-09-19 menetapkan Laravel 12 dan MySQL. PHP tetap pada
target 8.4+ dari rancangan awal. Dokumen aktif ini menjadi acuan implementasi.
Keputusan wawancara MVP disetujui pada 2026-09-20 dan dicatat pada
[Keputusan](DECISIONS.md). Bedakan keputusan accepted, kondisi source starter,
dan verifikasi implementasi yang masih harus dilakukan.

Folder sumber `docs/NusaLens/` dan `docs/templateDocs/` telah dihapus atas
instruksi user setelah adaptasi dan verifikasi selesai. Pemetaan materi asal
ke dokumen aktif tetap dicatat sebagai riwayat pada
[work item penyelarasan](work-items/penyelarasan-dokumentasi/README.md).
Dokumen aktif dan template kerja tidak membutuhkan tautan ke kedua folder itu.

## Stack target

- Backend: Laravel 12 dan PHP 8.4+ (target runtime project).
- Frontend: Inertia.js, React + TypeScript, Tailwind CSS.
- Database: MySQL. Cache dan queue: Redis.
- Data provider: Sectors Financial API v2.
- Visualisasi: Recharts, belum dipasang.
- AI: opsional, hanya menjelaskan hasil analisis. Provider/model/budget belum
  dipilih; penjelasan berbasis aturan tetap wajib.

## Prinsip

- Sectors API v2 adalah sumber data utama.
- Sistem menghitung nilai; AI hanya menjelaskan.
- Nilai harus jelas, konsisten, dan dapat ditelusuri.
- Credit API harus dihemat melalui pengambilan data bertahap dan cache.
- Jangan membuat arsitektur lebih rumit dari kebutuhan hackathon.
- Fokus MVP: Temukan Saham, Detail Perusahaan, Bandingkan, Jelaskan Nilai, dan
  Temukan Kandidat Menarik.
