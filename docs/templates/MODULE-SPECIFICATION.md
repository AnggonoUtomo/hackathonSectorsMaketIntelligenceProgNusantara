# Specification: [Nama Module]

## Status

[Draft / Disetujui / Implemented]

## Tujuan, scope, dan non-scope

[Perilaku yang dimiliki module serta batasnya.]

## Arsitektur

- Module: `app/Modules/{Module}/`.
- Inbound adapter: [Presentation HTTP/console/queue yang diperlukan].
- Use case: [Application action/query dan DTO internal].
- Aturan Domain: [aturan bisnis murni dan invariant].
- Outbound port: [kontrak yang dibutuhkan beserta owner layer].
- Outbound adapter: [Infrastructure yang memenuhi kontrak].
- Composition root: [provider yang benar-benar digunakan].

## Contract dan data

[Input, output, validasi, error publik, dan consumer.]
[Konsep data, ownership persistence, identifier, dan migration yang disetujui.]
[Endpoint Sectors, estimasi credit, cache, freshness, dan mapping bila relevan.]

## Authorization, audit, dan UI

[Aturan akses/log yang relevan tanpa secret.]
[Page, routing, loading/empty/error, accessibility, dan istilah produk.]

## Dependency

[Kontrak publik module lain beserta alasannya.]

## Acceptance dan verifikasi

- [ ] [Perilaku positif dan cara membuktikannya.]
- [ ] [Failure handling dan cara membuktikannya.]

## Risiko dan keputusan terbuka

[Hal yang belum diputuskan dan kapan dibutuhkan.]
