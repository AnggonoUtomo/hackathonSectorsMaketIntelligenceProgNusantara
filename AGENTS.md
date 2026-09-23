# Aturan Kerja Project NusaLens

## Konfigurasi project

- Nama project: `NusaLens`.
- Tujuan: aplikasi market intelligence untuk membantu pengguna menyaring,
  membandingkan, dan memahami saham Indonesia yang layak diteliti lebih lanjut.
- Stack target: Laravel 12, PHP 8.4+, Inertia.js, React + TypeScript,
  MySQL, Redis, Tailwind CSS, Sectors Financial API v2, dan Recharts.
- Lokasi module backend: `app/Modules/{Module}`, mengikuti arsitektur NusaLens.
- Lokasi frontend: `resources/js/`, mengikuti starter Laravel/Inertia yang ada.
- Framework reusable: tidak ada pada baseline awal.
- Strategi identifier: ULID untuk entitas, termasuk users dan foreign key terkait.
  Migrasi dari ID integer starter belum dilakukan; jangan reset data tanpa izin.
- Mekanisme route frontend: Inertia.js.
- Command discovery/validation module: belum tersedia. Starter Laravel/Inertia
  sudah ada; module NusaLens belum diimplementasikan.
- Bahasa dokumentasi dan handoff: Bahasa Indonesia.
- Commit message: gaya Conventional Commit, contoh
  `feat(screening): add structured company screener`.

## Mulai dari konteks yang cukup

Sebelum mengubah kode atau dokumentasi aktif, baca `docs/README.md`, lalu hanya
dokumen yang relevan dengan pekerjaan.

Untuk pekerjaan produk NusaLens, prioritaskan dokumen aktif di `docs/`.
Arsitektur dan analisis sistem berasal dari rancangan NusaLens. Koreksi user
menetapkan Laravel 12 dan MySQL; template tidak menggantikan arsitektur produk.
Template kerja aktif tersedia di `docs/templates/`.

Folder sumber `docs/NusaLens/` dan `docs/templateDocs/` telah dihapus atas
instruksi user setelah adaptasi selesai. Gunakan dokumen aktif di `docs/`
dan template di `docs/templates/`; jangan mengandalkan path sumber lama.

Sebelum membuat atau mengubah module, contract, port, adapter, generator, atau
struktur folder, wajib membaca `docs/ARCHITECTURE.md` dan
`docs/FOLDER-STRUCTURE.md`. Jika kode, generator, dan dokumen tidak selaras,
hentikan perubahan struktural dan laporkan konfliknya.

Tentukan scope, acceptance criteria, dan cara verifikasi secara singkat. Jika
requirement atau keputusan penting belum jelas, tanyakan langsung kepada user.

## Cara bekerja

- Utamakan perubahan kecil dan terfokus.
- Bangun sedikit demi sedikit tetapi langsung end-to-end.
- Jangan memperluas scope atau menutup risiko lain tanpa persetujuan user.
- Pecah perubahan multi-file menjadi increment yang dapat diverifikasi.
- Jalankan test atau pemeriksaan yang proporsional dengan risiko perubahan.
- Pertahankan perubahan user yang tidak terkait.
- Jangan membuat branch, commit, push, atau memasang dependency tanpa permintaan
  eksplisit user.
- Jangan otomatis memulai work item berikutnya setelah scope selesai.

## Aturan UI tabel

- Untuk UI module yang memiliki tabel atau daftar operasional, gunakan pola
  `ContohUI` sebagai baseline UX: summary cards, shortcut bar, filter/search
  bar, tabel padat, empty state, loading state, reset filter, pagination, dan
  aksi berbasis ikon dengan tooltip.
- Search/filter tabel harus mempertahankan state lewat query URL/Inertia,
  memakai debounce untuk live search bila sesuai, dan tetap menyediakan tombol
  terapkan/reset yang eksplisit.
- Tabel harus mudah dipindai: header uppercase kecil, spacing rapat namun
  terbaca, badge status, angka tabular, horizontal overflow pada layar kecil,
  dan aksi kanan berbasis ikon familiar.
- Halaman tabel tidak dibuat sebagai hero/landing page. Utamakan workflow kerja
  pengguna: ringkasan, filter, daftar, aksi, dan status data.
- Gunakan gaya `dashboard-*` yang sudah ada atau pola setara dari `ContohUI`;
  jangan membuat variasi visual baru yang tidak konsisten tanpa alasan domain.

## Arsitektur dan keamanan

- Gunakan DDD-lite Modular Monolith dengan Hexagonal Architecture.
- Arah dependency internal adalah `Presentation -> Application -> Domain` dan
  `Infrastructure -> Application -> Domain`.
- Domain tidak bergantung pada layer luar. Application tidak mengimpor adapter
  konkret Infrastructure.
- `Presentation` adalah inbound adapter, `Application` berisi use case dan port,
  `Infrastructure` berisi outbound adapter, dan `ServiceProvider` menjadi
  composition root.
- Dependensi konkret lintas module dilarang. Gunakan public contract, DTO, atau
  event yang memang memiliki consumer nyata.
- Ikuti `docs/FOLDER-STRUCTURE.md`. Jangan membuat folder, port, event, service,
  repository, atau adapter sebagai placeholder.
- Backend adalah security authority. Frontend permission hanya untuk UX.
- Semua panggilan Sectors harus melewati client/adapter internal NusaLens.
- API key Sectors hanya boleh berada di backend dan tidak boleh masuk source,
  frontend, log, test output, dokumentasi, atau error page.
- Automated test default harus memakai fake HTTP, bukan real Sectors API.
- Kalkulator Intelligence berupa pure PHP. Read model boleh dipakai untuk
  kebutuhan tampilan; repository hanya untuk kebutuhan persistence nyata.
- `app/Shared/` digunakan seminimal mungkin untuk konsep yang benar-benar
  dipakai lintas module, bukan tempat class yang ownership-nya belum jelas.

## Aturan produk NusaLens

- Keputusan MVP telah disetujui; baca `docs/DECISIONS.md`. Status accepted
  bukan bukti implementasi. Perubahan terhadap keputusan tetap perlu persetujuan.
- Registrasi publik; semua fitur riset wajib login dan verifikasi email.
- Compare maksimal 3 saham. Riwayat MVP hanya perbandingan tersimpan manual,
  privat, berbasis snapshot, dan pembaruan sebagai versi baru.
- Scoring mencakup bank dan nonbank sesuai metrik v1 di `docs/SCORING.md`.
  Minimal 5 peer lain per metrik, kelengkapan berbobot 70%, tampilan 2 desimal,
  tanpa label kategori skor. Jangan mencampur bank dan nonbank sebagai peer.
- NusaLens adalah alat informasi dan riset, bukan penasihat investasi, broker,
  trading bot, atau pemberi rekomendasi BUY/HOLD/SELL.
- Sectors API v2 adalah sumber data inti.
- Sistem menghitung nilai; AI hanya boleh menjelaskan hasil yang sudah dihitung.
- AI tidak boleh menentukan nilai, mengganti rumus, atau membuat klaim tanpa
  bukti data input.
- Credit API harus dihemat. Ambil data bertahap sesuai kebutuhan pengguna.
- Budget Sectors 1.000 credit sekali pakai; cadangan 600 membutuhkan persetujuan.
  Kuota per akun 20 credit/hari, reset 00.00 WIB. Ledger permanen di MySQL,
  tidak boleh ter-reset karena Redis hilang. Maksimal satu retry berbiaya.
- TTL, fallback, dan pengecualian bursa tutup mengikuti `docs/DATA-FLOW.md`;
  membaca cache atau menghitung ulang tidak memperbarui usia data sumber.
- Penjelasan berbasis aturan wajib; AI opsional setelah inti stabil, dijalankan
  atas permintaan. Provider/model/budget AI belum dipilih dan terpisah dari Sectors.
- Billing, BYOK, watchlist, berbagi publik, dan riwayat screener otomatis di luar MVP.
- Jangan mengunduh seluruh data IDX dari semua endpoint sekaligus.
- Jangan mengambil semua section Company Report secara default.
- Jangan menyamarkan error Sectors sebagai data kosong.
- Ikuti `docs/SCORING.md`: bandingkan perusahaan sejenis, jangan mengubah data
  hilang menjadi nol, sesuaikan bobot metrik tersedia, dan tampilkan kelengkapan.
- Ikuti `docs/DATA-FLOW.md` untuk cache/credit dan `docs/SECURITY.md` untuk
  validasi query screener, rate limit, secret, dan integritas snapshot.
- Jangan menambah microservice, MinIO, Kafka, Elasticsearch, Kubernetes, atau
  dependency besar lain tanpa instruksi eksplisit.

## Modul target

Modul awal yang menjadi batas tanggung jawab:

- `MarketData`: integrasi provider, mapping, cache, freshness, ledger dan reservasi credit.
- `Company`: identitas perusahaan, sektor/subsektor, profil, dan snapshot data.
- `Screening`: kriteria pencarian, shortlist, filter, dan pengurutan kandidat.
- `Intelligence`: normalisasi metrik, percentile, lima komponen nilai, dan
  Nilai Prioritas Riset, snapshot bukti input/peer, dan versi formula.
- `Comparison`: perbandingan maksimal 3 perusahaan dan versi tersimpan privat.
- `Research`: bukti, penjelasan nilai, dan AI explainer opsional.

Jangan membuat semua modul sekaligus bila belum ada work item yang membutuhkan.
Mulai dari alur end-to-end terkecil yang bisa dicoba.

## Sebelum coding

Codex harus bisa menjawab:

- Fitur/use case apa yang sedang dibuat?
- Modul mana yang bertanggung jawab?
- Aturan bisnis apa yang berubah?
- Endpoint Sectors apa yang dipakai?
- Kira-kira berapa credit yang dipakai?
- Bagaimana kebijakan cache-nya?
- Test apa yang perlu dibuat?

Jika perubahan menyentuh batas modul, skema database fundamental, rumus nilai,
provider data eksternal, model authentication, atau alur UX utama, berhenti di
tahap proposal/plan dulu dan minta persetujuan.

## Dokumentasi pekerjaan

- Modul baru memakai `docs/modules/{Module}/`.
- Bagian module yang signifikan memakai
  `docs/modules/{Module}/work-items/{nama-pekerjaan}/`.
- Pekerjaan lintas module memakai `docs/work-items/{nama-pekerjaan}/`.
- Gunakan nama folder work item `kebab-case`; nama Module mengikuti source code.
- Gunakan template aktif di `docs/templates/` dan hapus bagian yang tidak relevan.
- Work item cukup memiliki `README.md`, `plan.md`, dan `tasks.md`.
- Tambahkan PRD untuk kebutuhan produk baru atau requirement yang belum jelas.
- Tambahkan ADR hanya untuk keputusan yang mahal atau sulit dibalik.
- Bug kecil, typo, dokumentasi sederhana, dan perubahan satu file tidak wajib
  memiliki folder kerja baru.

## Handoff

Pada handoff, laporkan secara ringkas:

- perubahan yang dilakukan;
- verifikasi yang dijalankan dan hasilnya;
- hal yang sengaja tidak disentuh;
- risiko terbuka atau keputusan yang masih perlu user.
