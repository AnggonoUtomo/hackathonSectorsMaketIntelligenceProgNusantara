# Module: [Nama Module]

## Tujuan dan boundary

- Source: `app/Modules/{Module}/`.
- Tanggung jawab: [data dan perilaku yang dimiliki].
- Di luar tanggung jawab: [boundary module lain].

## Public contract dan dependency

[Contract/DTO/event yang memiliki consumer nyata, input, output, dan failure.]
[Module/layanan yang digunakan beserta alasan; hindari import class privat.]

## Operasi dan authorization

[Konfigurasi, persistence, migration, queue, cache/credit, dan freshness relevan.]
[Authentication/permission jika sudah ditetapkan; jangan mengasumsikan framework.]

## Verifikasi

[Focused test/command yang benar-benar tersedia dan hasilnya.]

Source mengikuti `docs/ARCHITECTURE.md` dan `docs/FOLDER-STRUCTURE.md`.
