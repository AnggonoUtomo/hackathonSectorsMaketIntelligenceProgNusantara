# ADR-001: Gunakan DDD-lite Modular Monolith dengan Hexagonal Architecture

## Status

Accepted

## Date

2026-09-19

## Context

NusaLens perlu membangun aplikasi hackathon yang cukup cepat dibuat, tetapi
tetap mudah diuji dan tidak mencampur aturan penilaian dengan detail Laravel,
React, Sectors API, database relasional, Redis, atau AI.

Produk juga memiliki constraint penting:

- Sectors API v2 adalah sumber data inti.
- Scoring harus jelas, konsisten, dan dapat ditelusuri.
- AI tidak boleh menentukan nilai.
- Credit API harus dihemat.
- MVP harus selesai end-to-end tanpa arsitektur berlebihan.

## Decision

Gunakan DDD-lite Modular Monolith dengan Hexagonal Architecture.

Stack dan susunan module diperjelas oleh
[ADR-002](ADR-002-BASELINE-LARAVEL-12-MYSQL-NUSALENS.md). ADR ini tetap berlaku
untuk gaya arsitektur dan arah dependency.

Setiap module menjadi boundary tanggung jawab. Dependency utama:

```text
Presentation -> Application -> Domain
Infrastructure -> Application -> Domain
```

Integrasi Sectors, database, cache, dan AI ditempatkan di Infrastructure.
Aturan bisnis dan scoring tidak boleh berada di controller, React component,
atau adapter provider.

## Alternatives Considered

### Laravel MVC sederhana tanpa batas module

- Pros: paling cepat untuk prototype kecil.
- Cons: risiko aturan scoring, integrasi Sectors, dan UI bercampur; sulit
  diuji dan dijelaskan.
- Rejected: scoring dan penggunaan credit membutuhkan boundary yang lebih jelas.

### Microservice

- Pros: isolasi deploy dan scaling lebih kuat.
- Cons: terlalu berat untuk hackathon, menambah operasi, komunikasi jaringan,
  dan kompleksitas deployment.
- Rejected: tidak sesuai kebutuhan MVP.

### Modular monolith tanpa hexagonal boundary

- Pros: lebih sederhana dibanding full hexagonal.
- Cons: detail provider mudah menyebar ke use case dan domain.
- Rejected: NusaLens perlu menjaga Sectors API sebagai adapter yang dapat
  diuji/fake tanpa membocorkan detail vendor.

## Consequences

- Kode lebih mudah diuji dengan fake HTTP untuk Sectors.
- Rumus scoring dapat dijaga tetap deterministik dan terpisah dari AI.
- Integrasi provider dan cache lebih mudah diamati dan diganti.
- Ada overhead struktur, sehingga folder dan abstraction hanya dibuat saat ada
  kebutuhan nyata.
