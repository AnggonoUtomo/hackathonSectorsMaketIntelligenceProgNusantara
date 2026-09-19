# Plan: Penyelarasan Dokumentasi

## Scope

Menjadikan dokumen aktif sesuai NusaLens dengan koreksi Laravel 12/MySQL dan
pola kerja template, tanpa mengubah atau menghapus folder sumber.

## Increment 1: Baseline dan arsitektur

- Perubahan: README, AGENTS, PROJECT, ARCHITECTURE, FOLDER-STRUCTURE, MODULES,
  serta catatan keputusan baseline.
- Prasyarat: baca sumber NusaLens dan template kerja.
- Acceptance: stack konsisten dan module langsung mengikuti NusaLens.
- Verifikasi: bandingkan sumber dan scan stack/path yang sudah tidak berlaku.

## Increment 2: Analisis dan pola pekerjaan

- Perubahan: scoring, alur data, model data, API, security, environment,
  quality, submission, workflow, dan template aktif.
- Prasyarat: baseline increment 1.
- Acceptance: materi asal tercakup; keputusan yang belum jelas tidak dikarang.
- Verifikasi: matriks pemetaan sumber serta review aturan/bobot/TTL/credit.

## Increment 3: Pemeriksaan dan handoff

- Perubahan: hasil di tasks dan status work item.
- Acceptance: tautan valid, tidak bergantung pada folder sumber, folder sumber
  tetap utuh, placeholder hanya pada template.
- Verifikasi: scan Markdown, whitespace, dan perbandingan hash SHA-256 sumber.

## Batas berhenti dan pemulihan

Berhenti setelah penyelarasan dan pemeriksaan dokumentasi selesai. Penghapusan
sumber serta implementasi aplikasi adalah pekerjaan terpisah. Jika ditemukan
ketidaksesuaian, koreksi hanya dokumen aktif berdasarkan sumber dan instruksi
user; pertahankan file sumber sebagai bahan pembanding.
