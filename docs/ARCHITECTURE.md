# Arsitektur

## Gaya arsitektur

NusaLens memakai **DDD-lite Modular Monolith dengan Hexagonal Architecture**.
Tujuannya membuat kode rapi, mudah dipahami, mudah diuji, dan tidak mencampur
aturan bisnis dengan detail framework atau provider.

- Framework reusable: tidak ada pada baseline awal.
- Module aplikasi: `app/Modules/{Module}`, sesuai rancangan NusaLens.
- Runtime: Laravel 12, PHP 8.4+, MySQL, dan Redis.
- Frontend: mengikuti struktur Laravel/Inertia yang akan dibuat.
- Struktur canonical: [FOLDER-STRUCTURE.md](FOLDER-STRUCTURE.md).

## Hexagon pada setiap module

```text
React / Inertia / HTTP / Console / Queue
            |
            v
Presentation (inbound adapter)
            |
            v
Application (use case dan port)
            |
            v
Domain (aturan bisnis)
            ^
            |
Infrastructure (outbound adapter)
            |
Sectors API v2 / MySQL / Redis / AI opsional
```

Diagram menunjukkan alur kerja, bukan izin Domain mengimpor Infrastructure.
`ServiceProvider.php` atau composition root project menghubungkan port
Application dengan adapter Infrastructure. Use case tidak mengakses adapter
konkret secara langsung.

## Tanggung jawab layer

| Layer          | Tanggung jawab                                                        | Tidak boleh                                          |
| -------------- | --------------------------------------------------------------------- | ---------------------------------------------------- |
| Domain         | Entity, value object, domain service, event, invariant, dan scoring   | Bergantung pada Laravel, HTTP, database, Redis, API  |
| Application    | Use case, command, query, DTO, port, dan orchestration                | Mengimpor adapter Infrastructure atau detail HTTP/UI |
| Infrastructure | Persistence, Sectors adapter, cache, framework/package/layanan luar   | Menjadi pemilik business rule                        |
| Presentation   | Controller, request, resource, policy, middleware, command UI/CLI     | Menulis persistence atau business mutation langsung  |

Domain boleh tidak ada pada capability sederhana yang belum memiliki business
rule murni. Jika digunakan, Domain harus bebas dari detail framework dan
persistence.

## Arah dependency

```text
Presentation ------> Application ------> Domain
Infrastructure ----> Application ------> Domain
```

- Domain tidak mengimpor layer lain.
- Application tidak mengimpor Infrastructure.
- Presentation memanggil Application.
- Infrastructure mengimplementasikan port milik Application atau Domain.
- Binding port-adapter dilakukan oleh composition root.
- Route mengarah ke Presentation.

## Modul target

| Module       | Tanggung jawab utama |
| ------------ | -------------------- |
| MarketData   | Ambil data provider, mapping, cache, freshness, dan detail Sectors. |
| Company      | Identitas perusahaan, sektor/subsektor, profil, dan snapshot internal. |
| Screening    | Kriteria pencarian, shortlist, filter, dan urutan kandidat. |
| Intelligence | Normalisasi, percentile, lima komponen nilai, dan Nilai Prioritas Riset. |
| Comparison   | Perbandingan beberapa perusahaan berdampingan. |
| Research     | Bukti, penjelasan nilai, dan AI explainer opsional. |

Jangan membuat semua module sekaligus tanpa work item nyata. Mulai dari alur
end-to-end terkecil yang dapat dicoba.

## Port dan adapter

Sectors API tidak boleh tersebar di seluruh aplikasi. Akses data pasar harus
melewati kontrak internal, misalnya:

```php
interface MarketDataProvider
{
    public function screen(ScreenCriteria $criteria): ScreenResult;
    public function companyOverview(Ticker $ticker): CompanyOverview;
    public function companyFinancials(Ticker $ticker): CompanyFinancials;
    public function companyValuation(Ticker $ticker): CompanyValuation;
    public function subsectorContext(SubsectorCode $subsector): SubsectorContext;
    public function dailyMarket(Ticker $ticker, DateRange $range): DailyMarketSeries;
}
```

Adapter Sectors berada di Infrastructure dan bertanggung jawab atas HTTP,
retry, timeout, DTO vendor, mapper, cache, error mapping, dan observability.

Use case meminta data lewat port dan meneruskan model internal ke kalkulator
Domain. Domain tidak memanggil HTTP, Redis, database, atau port milik Application.
Lokasi port untuk orkestrasi use case adalah Application; port yang benar-benar
dibutuhkan Domain harus dimiliki Domain. Kontrak di atas adalah rancangan,
bukan API PHP yang sudah tersedia.

## Alur data dan penilaian

```text
Input pengguna -> Presentation -> Application
  -> MarketDataProvider -> cache / adapter Sectors
  -> Infrastructure DTO -> Mapper -> model internal NusaLens
  -> konteks perusahaan dan kelompok pembanding
  -> Intelligence Domain: normalisasi, percentile, lima nilai, nilai gabungan
  -> ScoreBreakdown -> tampilan / perbandingan / penjelasan Research
```

Cache dicek sebelum panggilan upstream; snapshot MySQL disimpan jika diperlukan.
Lima nilai dan Nilai Prioritas Riset dihitung dengan pure PHP yang deterministik.
MarketData tidak menghitung nilai NusaLens. Research hanya menjelaskan hasil
yang sudah dihitung, dengan AI opsional setelah fitur inti stabil.

Rincian tahapan dan credit ada di [DATA-FLOW.md](DATA-FLOW.md), rancangan
penyimpanan di [DATA-MODEL.md](DATA-MODEL.md), dan aturan perhitungan di
[SCORING.md](SCORING.md).

## Komunikasi lintas module

Public boundary yang disarankan:

- `Application/Contracts`;
- `Application/DTO`;
- `Application/Events`.

Import model, repository, controller, policy, adapter, atau Domain privat module
lain dilarang. Dependency nyata dicatat pada dokumentasi module.

## Read model, repository, dan Shared

- Query/read model khusus boleh digunakan untuk halaman yang menggabungkan
  banyak data. Akses database tetap berada di Infrastructure dan diorkestrasi
  Application; kebutuhan tampilan tidak harus dipaksakan menjadi entity Domain.
- Repository hanya dibuat untuk kebutuhan penyimpanan domain yang jelas.
- `app/Shared/` dipakai seminimal mungkin, misalnya value object atau exception
  yang benar-benar dipakai bersama. Konsep dengan owner module tetap berada
  di module tersebut; hindari class `Helper` atau service generik tanpa tugas jelas.

## Aturan scoring

Lima komponen nilai awal:

| Komponen UI        | Bobot |
| ------------------ | ----- |
| Kesehatan Bisnis   | 30%   |
| Pertumbuhan        | 25%   |
| Harga Saham        | 20%   |
| Kekuatan Pasar     | 15%   |
| Keamanan Keuangan  | 10%   |

Semua nilai memakai skala `0..100`. Nilai akhir berarti lebih menarik untuk
diteliti lebih lanjut, bukan rekomendasi membeli atau menjual.

Bobot awal adalah hipotesis produk yang harus dapat dikonfigurasi. Bandingkan
metrik pada kelompok perusahaan sejenis, bukan angka mentah lintas sektor.
Data hilang tidak otomatis bernilai nol. Simpan angka asli, percentile, bobot,
kontribusi, peer group, dan waktu pengambilan agar alasan nilai dapat ditelusuri.

## Aturan kesederhanaan

- Jangan membuat folder atau placeholder agar struktur terlihat lengkap.
- Jangan membuat port, event, repository, service, atau adapter tanpa kebutuhan.
- Gunakan lokasi canonical ketika concern benar-benar diperlukan.
- Perubahan arah dependency, modul, atau struktur canonical memerlukan ADR atau
  persetujuan eksplisit.

## Gap conformance

- Source Laravel/Inertia belum dibuat.
- Command discovery dan validation module belum tersedia.
- Strategi identifier final belum ditetapkan.
- Detail rumus percentile, bobot metrik, dan schema belum diputuskan; lihat
  pertanyaan terbuka di [SCORING.md](SCORING.md) dan [DATA-MODEL.md](DATA-MODEL.md).
