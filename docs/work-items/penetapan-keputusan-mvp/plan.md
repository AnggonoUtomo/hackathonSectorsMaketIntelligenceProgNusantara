# Plan: Penetapan Keputusan MVP

## Scope

Dokumentasi keputusan hasil wawancara, koreksi status starter, README produk,
roadmap, dan Git delivery sesuai instruksi lanjutan. Tidak coding fitur.

## Increment 1: Baseline keputusan

- Perubahan: Project, Scoring, Data Flow/Model, Security, Architecture, AGENTS,
  indeks keputusan dan ADR-003.
- Prasyarat: konfirmasi user atas rangkuman wawancara.
- Acceptance: tidak ada keputusan disetujui yang masih dinyatakan terbuka.
- Verifikasi: pembacaan silang dan pencocokan source starter secara read-only.

## Increment 2: Pengantar produk dan roadmap

- Perubahan: README tujuan/penggunaan/fungsi/hitungan, roadmap MVP/AI/BYOK/paid,
  setup/API/quality/submission yang selaras.
- Acceptance: fitur target tidak diklaim sudah tersedia; contoh rumus konsisten.
- Verifikasi: tautan lokal, contoh aritmetika, placeholder, dan whitespace.

## Increment 3: Delivery

- Perubahan: commit dokumentasi dan commit terpisah `.htaccess` user.
- Prasyarat: permintaan eksplisit commit/push terbaru.
- Acceptance: hanya scope yang diminta masuk staging; remote main sama dengan HEAD.
- Verifikasi: staged diff/nama/secret, branch/remote, push, ls-remote/divergence,
  status akhir. File runtime LSP dibiarkan di workspace, tidak di-commit.

## Batas berhenti dan pemulihan

Berhenti setelah dokumen dan delivery selesai. Tidak reset data atau revert
perubahan user. Konflik keputusan/source dilaporkan, bukan disembunyikan dengan
perubahan implementasi. Commit history memisahkan dokumentasi dari konfigurasi user.
