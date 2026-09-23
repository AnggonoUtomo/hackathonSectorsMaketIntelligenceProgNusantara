# Work Item: Rule-based Research Explainer

## Status dan owner

- Status: Done.
- Owner: Research.
- Target: `/jelaskan-nilai`.

## Kondisi awal

Menu `/jelaskan-nilai` masih placeholder. MVP membutuhkan penjelasan nilai yang
berbasis aturan, tidak memakai AI, dan tidak memberi rekomendasi beli/jual.

## Scope dan non-scope

Scope: halaman explainer menerima ticker valid, menampilkan snapshot metrik yang
tersedia, menjelaskan batas data, dan menampilkan pending state untuk ticker real
yang belum punya scoring.

Non-scope: AI provider, Company Report real, scoring Intelligence real, dan
penyimpanan riwayat explainer.

## Acceptance criteria

- [x] `/jelaskan-nilai` tampil dengan default symbol `BBCA`.
- [x] `/jelaskan-nilai?symbol=ADES` render 200 sebagai pending data.
- [x] Format symbol invalid ditolak dengan session error.
- [x] UI memakai pola dashboard/table yang sama.
- [x] Tidak ada klaim BUY/HOLD/SELL.

## Dependency dan keputusan

Menggunakan snapshot Company yang sudah ada. AI tetap opsional dan belum aktif
sesuai keputusan MVP.

## Handoff

- Perubahan: `/jelaskan-nilai` memiliki explainer rule-based untuk snapshot
  ready dan pending.
- Verifikasi: focused research tests, typecheck, lint, dan build lulus.
- Risiko terbuka: penjelasan real menunggu scoring Intelligence.
