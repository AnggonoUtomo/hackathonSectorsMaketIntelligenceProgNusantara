# Daftar Module

Module berada langsung di `app/Modules/{Module}`, sesuai rancangan NusaLens.
Semua module bisnis masih `Planned`; starter Laravel/Inertia sudah tersedia.
Dokumentasi module berada di `docs/modules/{Module}/` saat implementasi dimulai.

| Module | Tanggung jawab | Kolaborasi yang dibutuhkan |
| --- | --- | --- |
| MarketData | Provider, mapping, cache, freshness, ledger/reservasi credit. Tidak menghitung nilai. | Sectors, Redis, dan ledger MySQL melalui Infrastructure. |
| Company | Identitas, klasifikasi, profil, snapshot keuangan/pasar internal. | Data internal hasil mapping MarketData. |
| Screening | Kriteria pencarian, filter, pengurutan, shortlist. UI: Temukan Saham. | Data MarketData/Company; hasil Intelligence saat pengurutan berdasarkan nilai tersedia. |
| Intelligence | Normalisasi, posisi peer, lima nilai, total, versi formula dan bukti input/peer immutable. | Data internal perusahaan dan pembanding; tidak mengakses JSON vendor langsung. |
| Comparison | Maksimal 3 saham, simpan manual privat dan berversi. UI: Bandingkan. | Data Company dan hasil Intelligence. |
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
Ownership sudah disetujui pada [DATA-MODEL.md](DATA-MODEL.md): MarketData memiliki
ledger/reservasi credit, Company memiliki snapshot fakta keuangan/pasar,
Intelligence memiliki hasil/versi formula/bukti input dan peer, Comparison
memiliki perbandingan privat berversi maksimal 3 saham. Screening tidak membuat
riwayat otomatis dan Research tidak menyimpan laporan terpisah pada MVP.
DDL dan wiring migration masih perlu spesifikasi implementasi.

## Pembaruan indeks

Saat module dibuat, catat path source, contract publik, consumer nyata,
dependency, dan verifikasinya. Gunakan status `Planned`, `Active`, `Deprecated`,
atau `Disabled`. Jangan membuat seluruh module sekaligus; ikuti increment
end-to-end pada [WORKFLOW.md](WORKFLOW.md).
