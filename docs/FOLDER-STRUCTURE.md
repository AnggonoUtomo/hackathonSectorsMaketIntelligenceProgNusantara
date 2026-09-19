# Struktur Folder Canonical

Struktur mengikuti arsitektur NusaLens. Ini adalah target, karena source
Laravel/Inertia belum dibuat. Buat folder hanya saat ada concern nyata.

## Module backend

```text
app/
|-- Modules/
|   |-- MarketData/
|   |-- Company/
|   |-- Screening/
|   |-- Intelligence/
|   |-- Comparison/
|   `-- Research/
`-- Shared/                   # hanya bila ada kebutuhan lintas module nyata
```

Setiap module langsung berada di `app/Modules/{Module}`. Nama module sudah
mewakili area tanggung jawab; tidak ditambah lapisan folder kategori/domain.

```text
app/Modules/{Module}/
|-- Domain/                   # aturan bisnis, value object, kalkulator murni
|-- Application/              # use case, kontrak, DTO internal, query
|-- Infrastructure/           # adapter API, persistence, cache
`-- Presentation/             # controller, request, response, inbound adapter
```

Blueprint awal Comparison hanya membutuhkan Domain, Application, dan
Presentation. Infrastructure ditambahkan jika kelak ada kebutuhan nyata seperti
menyimpan perbandingan. Jangan membuat layer kosong untuk melengkapi diagram.

Port use case ditempatkan di `Application/Contracts`, DTO internal di
`Application/DTO`. Port yang memang dimiliki Domain tetap berada di Domain.
Binding dilakukan oleh service provider Laravel sebagai composition root;
provider per module hanya dibuat bila diperlukan. Mekanisme registrasi route,
migration, dan provider ditetapkan saat fondasi, bukan diasumsikan sudah ada
module loader, manifest, generator, atau permission framework.

## Integrasi Sectors

```text
app/Modules/MarketData/Infrastructure/Sectors/
|-- SectorsApiClient.php
|-- SectorsMarketDataAdapter.php
|-- DTO/
|-- Mapper/
|-- Exception/
`-- Cache/
```

Alur mapping: `Sectors JSON -> Infrastructure DTO -> Mapper -> model internal`.
DTO vendor tidak menjadi model bisnis module lain. HTTP, retry, timeout,
mapping, dan cache tetap berada di Infrastructure MarketData.

## Mesin penilaian

Contoh class dari blueprint, dibuat ketika use case membutuhkannya:

```text
app/Modules/Intelligence/Domain/
|-- Score.php
|-- ScoreComponent.php
|-- Percentile.php
|-- QualityScoreCalculator.php
|-- GrowthScoreCalculator.php
|-- ValueScoreCalculator.php
|-- RiskScoreCalculator.php
|-- MarketScoreCalculator.php
`-- ResearchPriorityCalculator.php
```

Kalkulator berupa pure PHP; tidak mengimpor Laravel, Eloquent, HTTP, Redis,
atau AI. Konsep lengkap dan strategi industri dibahas di [SCORING.md](SCORING.md).

## Shared kernel

```text
app/Shared/
|-- Domain/
|   |-- ValueObject/
|   `-- Exception/
|-- Application/
`-- Infrastructure/
```

Ini pilihan lokasi ketika ada kebutuhan berbagi yang terbukti. Jangan
memindahkan class ke Shared hanya karena owner-nya belum diputuskan.

## Frontend dan test

Frontend memakai React + TypeScript melalui Inertia pada `resources/js/`.
Lokasi pages, components, hooks, dan types mengikuti scaffolding Laravel 12
yang dipilih. Rancangan NusaLens belum menetapkan struktur frontend per module;
jangan mengunci path atau kapitalisasi sebelum source tersedia.

Target test backend adalah `tests/Unit` untuk kalkulator dan `tests/Feature`
untuk use case/integrasi. Lokasi test frontend/browser mengikuti runner yang
dipilih. Adapter Sectors diuji dengan fake HTTP tanpa credit upstream.

## Dokumentasi pekerjaan

```text
docs/
|-- templates/                # pola kerja aktif yang sudah disesuaikan
|-- modules/{Module}/
|   |-- README.md
|   |-- specification.md
|   |-- plan.md
|   |-- tasks.md
|   `-- work-items/{nama-pekerjaan}/
|       |-- README.md
|       |-- plan.md
|       `-- tasks.md
|-- work-items/{nama-pekerjaan}/
|   |-- README.md
|   |-- plan.md
|   `-- tasks.md
`-- decisions/               # ADR keputusan yang mahal/sulit dibalik
```

Nama module mengikuti source, misalnya `docs/modules/MarketData/`. Nama work
item memakai `kebab-case`. PRD dan ADR ditambahkan sesuai kebutuhan, bukan
otomatis untuk semua pekerjaan. Gunakan [template aktif](templates/README.md).

Folder sumber `docs/NusaLens/` dan `docs/templateDocs/` telah dihapus setelah
adaptasi selesai dan user menginstruksikan penghapusan. Gunakan struktur
dokumentasi aktif di atas.

## Aturan pembuatan

- Inventarisasi source dan generator sebelum mengubah struktur.
- Jangan membuat folder kosong, `.gitkeep`, atau abstraction tanpa kebutuhan.
- Repository hanya dibuat untuk persistence domain yang benar-benar dibutuhkan.
- Jika source/generator bertentangan dengan dokumen, laporkan konflik sebelum
  perubahan struktural; jangan memindahkan module berdasarkan template generik.
