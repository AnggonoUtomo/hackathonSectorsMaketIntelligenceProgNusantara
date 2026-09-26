# Work Items

Folder ini dipakai untuk pekerjaan lintas module:

```text
docs/work-items/{nama-pekerjaan}/
|-- README.md
|-- plan.md
`-- tasks.md
```

Gunakan work item lintas module bila perubahan menyentuh lebih dari satu module
atau membentuk fondasi project secara umum.

Untuk pekerjaan kecil, typo, dokumentasi sederhana, atau perubahan satu file,
folder work item tidak wajib.

Gunakan [template aktif](../templates/README.md). Untuk pekerjaan di dalam
satu module, gunakan `docs/modules/{Module}/work-items/{nama-pekerjaan}/`.

## Indeks

- [Kesiapan Peer dan Scoring](kesiapan-peer-scoring/README.md): dokumentasi
  increment 3B, audit data/biaya yang direncanakan, ownership bukti dan task;
  belum ada audit live atau implementasi.
- [Ruang Riset Berbukti](ruang-riset-berbukti/README.md): kajian pembeda terhadap
  tool Sectors dan implementasi Ringkasan Riset real 3A; UX-1 menghilangkan
  menu pencarian duplikat dengan alias URL kompatibel. Tahap berikutnya menunggu.
- [Redesain riset terpandu](redesain-riset-terpandu/README.md): desain alur UX,
  autocomplete/profil real pada increment 1 dan grafik real pada increment 2;
  increment berikutnya perlu review bersama proposal Ruang Riset Berbukti.
- [Fondasi akses dan ULID](fondasi-akses-ulid/README.md): ULID users fresh
  install, akses dashboard wajib verified, Redis, dan Mailpit lokal selesai
  untuk CLI/dev.
- [Penetapan keputusan MVP](penetapan-keputusan-mvp/README.md): hasil wawancara,
  README produk, roadmap, ADR akses/ULID/persistence, dan delivery dokumentasi.
- [Dependency npm dan TypeScript](npm-audit-typescript/README.md): evaluasi
  audit/deprecation, pembaruan dependency kompatibel, dan perbaikan compiler.

- [Penyelarasan dokumentasi](penyelarasan-dokumentasi/README.md): Laravel 12,
  MySQL, arsitektur NusaLens, dan kemandirian dokumen aktif dari folder sumber.
