# Work Item: Penetapan Keputusan MVP

## Status dan owner

- Status: Done.
- Owner: lintas module.
- Target: README, AGENTS, dokumentasi aktif, ADR-003, dan roadmap.

## Kondisi awal

Wawancara satu-per-satu telah menutup keputusan produk dan user mengizinkan
pencatatan. Dokumen masih memuat keputusan terbuka dan status source belum ada,
padahal starter Laravel/Inertia telah tersedia. Pekerjaan sempat terinterupsi.

## Scope dan non-scope

Catat keputusan disetujui, koreksi baseline source, dan selaraskan aturan kerja.
Instruksi lanjutan meminta README berfokus tujuan/penggunaan/fungsi/perhitungan,
catatan rencana fitur, serta commit/push termasuk `.htaccess` buatan user.
File `.htaccess` dipertahankan tanpa perubahan isi; file sementara LSP tidak
disertakan. Tidak mengubah source fitur, dependency, database, atau menjalankan
API berbiaya. Tidak otomatis memulai implementasi roadmap.

## Acceptance criteria

- [x] Keputusan akses, ULID, scoring, cache/credit, ownership, riwayat dan AI tercatat.
- [x] Status accepted dipisahkan dari implemented dan kontrak provider belum teruji.
- [x] README berorientasi produk dengan contoh hitungan; roadmap sesuai rencana user.
- [x] Tautan lokal, stale statement, placeholder, dan whitespace diperiksa.
- [x] Staged scope diperiksa sebelum commit.
- [x] Commit/push terverifikasi dengan hash remote; file sementara tidak ikut.

## Dependency dan keputusan

[DECISIONS](../../DECISIONS.md), [ADR-003](../../decisions/ADR-003-AKSES-ULID-PERSISTENCE-MVP.md),
serta persetujuan akhir wawancara 2026-09-20. Instruksi commit/push diberikan
setelah persetujuan awal yang sebelumnya membatasi scope pada dokumentasi.

## Handoff

- Perubahan: dokumen aktif, README produk, roadmap, aturan agent, dan ADR.
- Verifikasi: 119 tautan lokal dalam 41 Markdown valid; placeholder aktif tidak
  ditemukan; `git diff --check` lulus. Contoh README =70,00 dan ties =50 diperiksa.
  Pernyataan lama pada ADR-002/work item adaptasi dipertahankan sebagai riwayat.
  Tidak menjalankan ulang test runtime karena tidak ada perubahan source fitur.
- Delivery: `6812aa7` mencatat dokumentasi dan `dbc4a5f` mencatat `.htaccess`
  user tanpa perubahan isi. Push ke `origin/main` berhasil; `git ls-remote`
  mengonfirmasi hash `dbc4a5f73a3434f80ed396083a21e8a002a77d63` sama dengan HEAD
  pada pemeriksaan delivery. Commit penutupan catatan ini menyusul; hash final
  dilaporkan pada handoff. Dua file `storage/framework/lsp-*.php` tetap untracked.
- Risiko: kontrak field/periode/aksi korporasi dan kalender provider, estimasi
  credit seluruh peer, DDL/precision/migrasi ULID, dan MySQL/Redis masih harus
  dibuktikan saat implementasi. Provider/model/budget AI belum dipilih.
- Apache: review statis `.htaccess` saja; document root `public/`, mod_rewrite,
  dan AllowOverride perlu verifikasi deployment. Tidak mengklaim uji HTTP Apache.
