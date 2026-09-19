# Workflow Kerja

## Tentukan jenis pekerjaan

| Jenis pekerjaan          | Dokumentasi minimum                                   |
| ------------------------ | ----------------------------------------------------- |
| Modul baru               | Folder module, README, specification, plan, dan tasks |
| Capability signifikan    | Folder work item, README, plan, dan tasks             |
| Pekerjaan lintas modul   | Folder global work item, README, plan, dan tasks      |
| Bug kecil atau satu file | Tidak perlu folder baru                               |

Gunakan PRD bila pekerjaan memperkenalkan kebutuhan produk baru atau requirement
belum jelas. Gunakan ADR hanya untuk keputusan yang mahal atau sulit dibalik.

## Sebelum mengubah

1. Tentukan file dan perilaku yang terdampak.
2. Tetapkan acceptance criteria dan verifikasi yang paling spesifik.
3. Tanyakan user jika ada keputusan yang mengubah arah atau scope.
4. Untuk pekerjaan signifikan, gunakan [template aktif](templates/README.md):
   - module: `modules/{Module}/`;
   - bagian module: `modules/{Module}/work-items/{nama-pekerjaan}/`;
   - lintas module: `work-items/{nama-pekerjaan}/`.

Untuk module, generator, atau struktur baru, periksa module terkait terlebih
dahulu dengan command project yang tersedia. Jika command belum tersedia,
lakukan inventory read-only dan catat keterbatasannya. Jangan mengarang hasil
discovery atau validation.

Sebelum coding, cocokkan rencana dengan `ARCHITECTURE.md` dan
`FOLDER-STRUCTURE.md`. Jika lokasi, dependency, atau test structure bertentangan
dengan kode/generator, hentikan perubahan struktural dan minta keputusan.

## Fase pengembangan NusaLens

Urutan memakai workflow operasional NusaLens. Blueprint menyebut data perusahaan
dan peer comparison sebagai fondasi; keduanya dikerjakan sesuai kebutuhan fase
integrasi, Discover, dan scoring, bukan sebagai perluasan scope terpisah.

Fase 0: siapkan repository baru untuk hackathon, README, dan license bila
diperlukan. Jangan menyalin source project lama. Operasi Git dilakukan hanya
ketika user memintanya.

1. Fondasi Laravel 12/Inertia React TypeScript, MySQL, Redis, lint/format/test dasar, struktur
   modul, dan `.env.example`.
2. Integrasi Sectors: client, auth header, timeout, retry, DTO, adapter/port,
   cache wrapper, dan fake HTTP test.
3. Temukan Saham: criteria screener, halaman Discover, shortlist, pagination,
   dan filter di URL.
4. Mesin Penilaian v1: percentile, lima nilai utama, Nilai Prioritas Riset,
   alasan nilai, dan unit test.
5. Detail Perusahaan: overview, kartu nilai, data pendukung, peer context, dan
   panel "Mengapa Nilainya Seperti Ini?".
6. Bandingkan: target awal 3 saham, matriks angka asli/metrik ternormalisasi,
   perbandingan nilai, dan teks kelebihan relatif berbasis aturan tetap.
   Scope produk 3-5 saham; batas final diputuskan di work item Comparison.
7. Temukan Kandidat: kelompok kandidat yang ramah cache.
8. AI Explainer opsional setelah fase inti stabil.
9. Perapihan: error state, loading, empty state, responsive, accessibility,
   disclaimer, dan data demo.
10. Bekukan submission sesuai [SUBMISSION.md](SUBMISSION.md).

Fase 1 selesai ketika aplikasi berjalan, migration MySQL berhasil, Redis dapat
diakses, dan test dasar berjalan. Setup dirinci pada [ENVIRONMENT.md](ENVIRONMENT.md).
Fase 3 boleh menampilkan hasil screener sebelum scoring; pengurutan berdasarkan
Nilai Prioritas Riset diaktifkan setelah fase 4. Fase 8 hanya dimulai jika fase
1-7 stabil. Setiap increment mencakup backend, UI bila relevan, dan test yang
dapat dicoba end-to-end.

## Saat mengubah

- Kerjakan satu increment kecil pada satu waktu.
- Jalankan focused test setelah increment tersebut.
- Tambahkan test bila behavior berubah atau bug diperbaiki.
- Untuk alur pengguna, verifikasi UI, permission, loading/error state, dan
  browser bila perubahan menyentuh frontend.
- Semua API call baru harus menjawab endpoint, frekuensi, cache, kebutuhan
  fitur, dan alternatif yang lebih hemat.
- Terapkan class/function kecil, type jelas, value object ketika membantu,
  dan hindari god service, generic Helper, serta side effect tersembunyi.

## Setelah mengubah

- Jalankan pemeriksaan relevan, misalnya test terfokus, lint, typecheck, build,
  atau validasi modul.
- Perbarui dokumentasi bila contract, operasi, atau keputusan berubah.
- Laporkan risiko yang masih terbuka. Jangan otomatis mengerjakannya.
- Berhenti setelah scope work item terpenuhi dan tunggu arahan berikutnya.
- Commit dan push hanya jika user memintanya.

## Definition of done

- Use case scope berjalan end-to-end dan verifikasi relevan lulus.
- Secret tidak bocor, dependency memiliki alasan nyata.
- Credit, cache, freshness, dan error state sudah ditangani.
- UI dapat digunakan dan dokumentasi perilaku diperbarui pada perubahan yang sama.

Jika operasi Git diminta, gunakan alur sederhana `main`, `feature/<short-name>`,
atau `fix/<short-name>`. Squash merge boleh untuk tim kecil. Commit memakai
Conventional Commit seperti `feat(screening): add structured company screener`.

## Penyelesaian dokumentasi sumber

Arsitektur/analisis produk mengikuti rancangan NusaLens, sedangkan format kerja
mengikuti template yang sudah diadaptasi di `docs/templates/`. Tautan aktif
harus tetap berfungsi tanpa folder sumber. Folder `docs/NusaLens/` dan
`docs/templateDocs/` telah dihapus atas instruksi user setelah penyelarasan
selesai. Pemetaan sumber dipertahankan sebagai riwayat dalam work item.

## Catatan bukti

Untuk perubahan penting, catat singkat: apa yang berubah, alasan, command yang
dijalankan, hasil, dan risiko. Tidak perlu membuat execution log panjang untuk
perubahan kecil yang sudah tercakup oleh test dan Git history.
