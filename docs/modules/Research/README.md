# Module: Research

## Tujuan dan boundary

Research menyusun penjelasan deterministik beserta bukti dan keterbatasannya.
`GetResearchSummary` memakai public contract CompanyResearchData, bukan adapter
konkret atau snapshot fake lintas module. `Domain/ResearchSummary` adalah PHP
murni; waktu evaluasi diberikan use case. Tidak ada AI, scoring, atau persistence.

## Implementasi 3A

- `/nusalens/companies/{symbol}/research`: endpoint JSON auth/verified, throttle
  60/menit; simbol empat alfanumerik dan dinormalisasi uppercase.
- `/perusahaan/{symbol}`: tab default Ringkasan Riset, pengambilan eksplisit,
  bukti/aturan, grafik dan tabel Recharts existing, keterbatasan, serta retry.
- `/jelaskan-nilai`: pintu pencarian nama/ticker; symbol valid menuju detail real.
  Path dan route name `research` dipertahankan, tanpa default perusahaan fake.
- Aturan `research-facts-v1`: tanda laba kuartalan, margin untuk nonkeuangan
  terklasifikasi bila revenue positif, dan tanda ekuitas pada tanggal laporan.
- Null/basis/unit salah tidak menjadi nol; latest null tidak diganti periode
  lama. Profil lebih dari/sama dengan 1 jam tidak dipakai untuk klasifikasi;
  financials lebih dari/sama dengan 24 jam tidak menghasilkan temuan.
- Cash flow tidak diinterpretasikan terhadap laba karena basis belum pasti.
  YoY, peer, skor, prediksi, dan rekomendasi transaksi tidak dihitung.
- Sumber menyertakan endpoint/section, fetchedAt, periode, unit, field asli,
  rumus/kondisi, serta versi aturan. Tidak ada credential pada response.

Pengambilan memakai overview dan empat quarterly via MarketData, estimasi
maksimal 5 credit cold untuk input ini, cache valid 0. Biaya search terpisah.
Menghitung ulang dari cache tidak mengubah usia sumber. Error provider tidak
diubah menjadi kosong; fallback stale existing belum diperluas.

## Legacy dan verifikasi

RuleBasedResearchExplainer dan halaman research-dashboard lama tidak lagi
menjadi consumer route aktif; belum dihapus pada scope ini. Compare/kandidat
legacy tidak termasuk migrasi 3A. Automated tests memakai fake HTTP, sedangkan
runtime memakai adapter Sectors real. Lihat [work item dan hasil QA](../../work-items/ruang-riset-berbukti/tasks.md).
