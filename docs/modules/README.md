# Dokumentasi Module

Dokumentasi module aktif berada di:

```text
docs/modules/{Module}/
```

Minimal untuk module baru:

```text
README.md
specification.md
plan.md
tasks.md
```

Untuk pekerjaan signifikan di dalam module:

```text
docs/modules/{Module}/work-items/{nama-pekerjaan}/
|-- README.md
|-- plan.md
`-- tasks.md
```

Gunakan nama folder work item `kebab-case`. Module mengikuti source di
`app/Modules/{Module}`, misalnya `docs/modules/MarketData/`. Gunakan
[template aktif](../templates/README.md) dan buat dokumentasi module saat work
item implementasinya dimulai.
