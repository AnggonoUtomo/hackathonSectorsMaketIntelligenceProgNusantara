# Module Intelligence

Status: implementasi MVP pada 3 Oktober 2026. Acuan pekerjaan:
[Penuntasan MVP](../../work-items/penuntasan-mvp/README.md).
Revisi 6 Oktober 2026: [ambang kelengkapan 60%](work-items/ambang-kelengkapan-60/README.md),
formula v1.1.0. Bukti/snapshot formula terdahulu tetap immutable.
URL evidence kini memiliki [halaman bukti terbaca](work-items/tampilan-bukti/README.md),
sedangkan request JSON tetap kompatibel.

Owner normalisasi, peer/periode, percentile, lima komponen, total, kelengkapan,
versi formula dan bukti immutable. Domain pure PHP: `MetricNormalizer`,
`ValuationPeerPlanner`, `ResearchPriorityCalculator`. Rumus ada di
[SCORING](../../SCORING.md), tidak diduplikasi atau diubah oleh UI/AI.

Application memakai public contract MarketData `PeerFinancialData` dan
`PeerMarketData`, serta port persistence `ScoreEvidence`. Binding ada di
`AppServiceProvider`. Adapter database berada di Infrastructure/Persistence.
Presentation menyediakan endpoint score/evidence. Screening mengonsumsi
`PeerResearch` untuk kandidat fundamental; Comparison memakai `ScoreEvidence`.

Semua sumber melalui MarketData; tidak ada HTTP dalam Domain/Application.
`intelligence_evidence` menyimpan input/result JSON dengan ULID/fingerprint unik.
Input dan versi formula identik menghasilkan bukti sama; perubahan salah satunya
menghasilkan bukti baru.
`PruneResearchEvidence` membersihkan bukti kedaluwarsa >30 hari. Comparison
menyimpan salinan lengkap yang tidak ikut dibersihkan.

Fundamental: dua tahun, enam kuartal, seluruh halaman sektor, TTL24 jam. Harga
peer optional: Daily per perusahaan, cache1 jam, observasi maksimum24 jam.
Tidak ada sampel parsial diam-diam, tidak ada zero-fill, tidak ada angka contoh
pada alur aktif. Momentum real dan kalender pengecualian bursa masih dibatasi
bukti provider. Valuasi lengkap sudah diuji fake HTTP, belum smoke populasi live.

Lihat [spesifikasi](specification.md), [plan](plan.md), dan [tasks](tasks.md).
