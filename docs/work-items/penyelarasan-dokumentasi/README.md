# Work Item: Penyelarasan Dokumentasi NusaLens

## Status dan owner

- Status: Done, penyelarasan dan verifikasi dokumentasi selesai.
- Tanggal: 2026-09-19.
- Owner: lintas module, dokumentasi fondasi project.
- Target: README root, AGENTS, dan dokumen aktif di `docs/`.

## Kondisi awal

README/dokumen aktif memakai stack dari bahan awal. Struktur module mengikuti
template dua tingkat, sedangkan NusaLens memakai module langsung. Beberapa
aturan scoring, data, keamanan, setup, dan demo belum terbawa ke dokumen aktif.

User menetapkan Laravel 12 dan MySQL, meminta arsitektur/analisis sistem
mengikuti NusaLens, serta menunda penghapusan folder sumber sampai instruksi
berikutnya.

## Scope

- Selaraskan stack, struktur module, dependency, workflow, dan AGENTS.
- Bawa materi analisis sistem dari sumber Markdown NusaLens ke dokumen aktif.
- Adaptasi pola template kerja menjadi `docs/templates/`.
- Catat keputusan yang sudah jelas dan pertanyaan implementasi yang masih terbuka.
- Pastikan tautan aktif tidak bergantung pada folder yang akan dihapus nanti.

## Tidak dikerjakan pada tahap penyelarasan

Scaffolding aplikasi, instalasi dependency, pembuatan schema/migration, perubahan
formula scoring, operasi Git, panggilan live provider, dan penghapusan folder
sumber. PDF pendamping blueprint dipertahankan; pencocokan isi menggunakan
dokumen Markdown yang tersedia.

## Acceptance criteria

- [x] Stack aktif konsisten Laravel 12 dan MySQL.
- [x] Module langsung di `app/Modules/{Module}` dan dokumentasi mengikuti.
- [x] Scope, scoring, alur data/credit, keamanan, QA, setup, dan demo tercakup.
- [x] Template aktif tersedia tanpa struktur module generik yang bertentangan.
- [x] Semua tautan lokal aktif valid dan tidak menuju kedua folder sumber.
- [x] Kedua folder sumber tetap utuh sampai verifikasi penyelarasan selesai.

## Pemetaan sumber ke dokumen aktif

Nama sumber berikut dicatat sebagai provenance, bukan tautan yang wajib tersedia
agar dokumentasi aktif dapat digunakan. Setelah penyelarasan selesai, folder
sumber dihapus atas instruksi user; pemetaan ini tetap menjadi riwayat materi.

| Sumber awal | Materi | Tujuan aktif |
| --- | --- | --- |
| `NusaLens/00_README.md` | Tujuan, stack, prinsip, istilah UI. | [Indeks](../../README.md), [Project](../../PROJECT.md). |
| `NusaLens/01_PRODUCT_SCOPE.md` | Scope, pengguna, fitur, batas produk. | [Project](../../PROJECT.md). |
| `NusaLens/02_ARCHITECTURE.md` | Layer, module langsung, port, read model, Shared/repository. | [Arsitektur](../../ARCHITECTURE.md), [Struktur](../../FOLDER-STRUCTURE.md). |
| `NusaLens/03_DOMAIN_MODULES.md` | Ownership enam module dan mapping data vendor. | [Module](../../MODULES.md). |
| `NusaLens/04_SECTORS_API_INTEGRATION.md` | Endpoint/section, error, retry, logging. | [API](../../API.md). |
| `NusaLens/05_DATA_FLOW_AND_CREDIT_BUDGET.md` | Tiga tingkat pengambilan, TTL, credit, larangan boros. | [Alur Data](../../DATA-FLOW.md). |
| `NusaLens/06_SCORING_ENGINE_SPEC.md` | Bobot, metrik, strategi industri, data hilang, bukti. | [Scoring](../../SCORING.md). |
| `NusaLens/07_DEVELOPMENT_WORKFLOW.md` | Fase 0-10, increment end-to-end, Git conventions. | [Workflow](../../WORKFLOW.md). |
| `NusaLens/08_CODEX_WORKING_AGREEMENT.md` | Guardrail, pertanyaan sebelum coding, definition of done. | [AGENTS](../../../AGENTS.md), [Workflow](../../WORKFLOW.md). |
| `NusaLens/09_TESTING_QA.md` | Test scoring/provider, skenario manual, performa/demo. | [Quality](../../QUALITY.md). |
| `NusaLens/10_SECURITY_COMPLIANCE.md` | Secret, screener allowlist, snapshot, disclaimer, AI. | [Keamanan](../../SECURITY.md). |
| `NusaLens/11_ENVIRONMENT_SETUP.md` | Prasyarat, environment, setup lokal. | [Environment](../../ENVIRONMENT.md), dengan MySQL. |
| `NusaLens/12_SUBMISSION_CHECKLIST.md` | Produk, teknis, storyboard, final freeze. | [Submission](../../SUBMISSION.md). |
| `NusaLens/NusaLens_Blueprint_Bahasa_Sederhana.md` | Alur sistem, kalkulator, model penyimpanan, fokus hackathon. | [Project](../../PROJECT.md), [Struktur](../../FOLDER-STRUCTURE.md), [Model Data](../../DATA-MODEL.md). |
| `NusaLens/NusaLens_Konsep_Sederhana_Market_Intelligence.md` | Pertanyaan lima aspek, peer subsektor/industri/sektor, bahasa UI. | [Project](../../PROJECT.md), [Scoring](../../SCORING.md). |
| `templateDocs/` | Indeks docs, module/work item, plan/tasks, PRD/ADR. | [Workflow](../../WORKFLOW.md), [Template](../../templates/README.md), indeks module/work item/keputusan. |

## Penyesuaian dan batas sumber

- Stack Laravel 12/MySQL mengikuti koreksi user; target PHP 8.4+ dipertahankan.
- Arsitektur module langsung mengikuti NusaLens; format kerja mengikuti template.
- Workflow operasional menentukan urutan fase; data perusahaan dan peer dari
  blueprint menjadi kebutuhan dalam fase integrasi/Discover/scoring.
- Target demo compare adalah tiga saham; batas final dalam rentang scope 3-5
  belum dikunci. Ilustrasi skor/label bukan formula/ambang kategori final.
- Endpoint, biaya API, dan aturan submission diteruskan sebagai rujukan desain,
  bukan klaim sudah diverifikasi live.

## Handoff

Hasil verifikasi dicatat di [tasks.md](tasks.md). Risiko implementasi yang masih
terbuka adalah rincian rumus/metrik/peer, schema/identifier, auth, chart library,
dan batas compare.

## Tindak lanjut penghapusan

Pada 2026-09-19, user menginstruksikan penghapusan setelah adaptasi selesai.
Folder `docs/NusaLens/` dan `docs/templateDocs/` beserta 37 file sumber telah
dihapus. Dokumen aktif dan template dipertahankan; nama sumber pada matriks
di atas hanya provenance. Source Laravel yang disalin user tidak menjadi
bagian perubahan ini.
