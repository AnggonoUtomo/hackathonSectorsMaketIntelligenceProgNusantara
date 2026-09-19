# Daftar Module

Module berada langsung di `app/Modules/{Module}`, sesuai rancangan NusaLens.
Semua masih `Planned` karena source aplikasi belum dibuat. Dokumentasi module
akan berada di `docs/modules/{Module}/` saat implementasi dimulai.

| Module | Tanggung jawab | Kolaborasi yang dibutuhkan |
| --- | --- | --- |
| MarketData | Mengambil data eksternal, mapping, cache, freshness, menyembunyikan detail provider. Tidak menghitung nilai. | Sectors API v2 dan Redis melalui Infrastructure. |
| Company | Identitas, sektor/subsektor, profil, dan gambaran data keuangan internal. | Data internal hasil mapping MarketData. |
| Screening | Kriteria pencarian, filter, pengurutan, shortlist. UI: Temukan Saham. | Data MarketData/Company; hasil Intelligence saat pengurutan berdasarkan nilai tersedia. |
| Intelligence | Normalisasi, posisi terhadap peer, lima nilai, Nilai Prioritas Riset, dan alasan pembentukannya. | Data internal perusahaan dan pembanding; tidak mengakses JSON vendor langsung. |
| Comparison | Data beberapa perusahaan berdampingan, keunggulan relatif, dan penyimpanan bila diperlukan. UI: Bandingkan. | Data Company dan hasil Intelligence. |
| Research | Bukti penilaian, penjelasan yang mudah dibaca, dan ringkasan AI opsional. | ScoreBreakdown yang sudah dihitung Intelligence. |

Kolaborasi pada tabel adalah kebutuhan use case, bukan izin mengimpor class
privat module lain. Kontrak/DTO publik dan arah dependency yang benar-benar
dipakai dicatat ketika module dibuat; jangan menambah event atau kontrak hanya
untuk memenuhi diagram.

## Konsep Intelligence

`MetricValue`, `Percentile`, `Score`, `ScoreComponent`, `ScoreWeight`,
`ScoreBreakdown`, dan `ResearchPriority` mewakili konsep penilaian. Kalkulator
merupakan pure PHP dan aturan lengkap ada di [SCORING.md](SCORING.md).

Istilah `Percentile` boleh digunakan di kode; UI menggunakan
**Posisi Dibanding Perusahaan Sejenis**.

## Batas data

```text
Sectors JSON -> Infrastructure DTO -> Mapper -> model internal NusaLens
```

MarketData menangani detail API; module lain bekerja dengan data internal.
Ownership tabel dan migration belum final. Daftar konsep penyimpanan dari
blueprint dicatat di [DATA-MODEL.md](DATA-MODEL.md).

## Pembaruan indeks

Saat module dibuat, catat path source, contract publik, consumer nyata,
dependency, dan verifikasinya. Gunakan status `Planned`, `Active`, `Deprecated`,
atau `Disabled`. Jangan membuat seluruh module sekaligus; ikuti increment
end-to-end pada [WORKFLOW.md](WORKFLOW.md).
