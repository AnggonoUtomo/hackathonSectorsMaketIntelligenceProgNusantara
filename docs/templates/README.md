# Template Dokumentasi NusaLens

Pola ini diadaptasi dari template kerja awal, dengan arsitektur dan istilah
module mengikuti NusaLens. Gunakan hanya template yang diperlukan:

| Template | Tujuan |
| --- | --- |
| [MODULE-README.md](MODULE-README.md) | Tujuan, boundary, contract, dan operasi module. |
| [MODULE-SPECIFICATION.md](MODULE-SPECIFICATION.md) | Perilaku, data, dependency, dan acceptance module. |
| [WORK-ITEM.md](WORK-ITEM.md) | Scope pekerjaan signifikan atau lintas module. |
| [IMPLEMENTATION-PLAN.md](IMPLEMENTATION-PLAN.md) | Increment, prasyarat, dan verifikasi. |
| [TASKS.md](TASKS.md) | Checklist pekerjaan dan hasil. |
| [PRD.md](PRD.md) | Kebutuhan produk baru atau requirement belum jelas. |
| [ADR.md](ADR.md) | Keputusan yang mahal atau sulit dibalik. |

Dokumentasi module: `docs/modules/{Module}/`. Bagian module:
`docs/modules/{Module}/work-items/{nama-pekerjaan}/`. Pekerjaan lintas module:
`docs/work-items/{nama-pekerjaan}/`.

Placeholder di folder template ini disengaja. Isi atau hapus bagian yang tidak
relevan sebelum hasil salinan menjadi dokumen aktif. Jangan mengarang contract,
permission, schema, atau hasil command agar dokumen tampak lengkap. Rujuk
[WORKFLOW.md](../WORKFLOW.md) dan [FOLDER-STRUCTURE.md](../FOLDER-STRUCTURE.md).
