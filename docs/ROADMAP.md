# Roadmap NusaLens

## Kedudukan rencana

Rencana ini berasal dari keputusan user pada wawancara MVP. Ini urutan target,
bukan daftar fitur yang sudah tersedia, deadline, atau izin otomatis memulai
pekerjaan berikutnya. Starter Laravel/Inertia sudah ada; modul bisnis belum ada.
Setiap increment tetap membutuhkan scope, acceptance, dan verifikasi.

## MVP yang disepakati

| Urutan | Hasil pengguna | Prasyarat dan verifikasi |
| --- | --- | --- |
| 1. Fondasi | Registrasi publik, login, email terverifikasi sebelum riset. | ULID users/FK dengan migrasi aman; MySQL/Redis, auth dan ownership test. |
| 2. Data | Akses data Sectors terkendali dan error yang jujur. | Adapter, cache, ledger/reservasi 1.000 credit dan kuota 20/hari, fake HTTP. |
| 3. Temukan Saham | Kriteria terstruktur, shortlist, pagination/filter URL. | Allowlist query, estimasi biaya, loading/empty/error; tidak membuat riwayat otomatis. |
| 4. Penilaian v1 | Lima komponen, total, kelengkapan, dan bukti. | Semua peer valid, periode/basis sama, ambang 70%, unit test rumus dan snapshot. |
| 5. Detail | Profil, angka sumber, freshness dan penjelasan nilai. | Recharts, input terverifikasi, penjelasan aturan, QA desktop/mobile. |
| 6. Bandingkan | Maksimal 3 saham, simpan manual privat, nama/hapus, versi baru. | Snapshot immutable, akses hanya pemilik, referensi/retensi dan test lintas akun. |
| 7. Kandidat dan demo | Kandidat riset, alur end-to-end, disclaimer. | Cache hemat, fallback, error state, QA dan checklist submission. |

Pengurutan berbasis skor baru tersedia setelah mesin skor. Perusahaan bank dan
nonbank memakai paket metrik [SCORING.md](SCORING.md); data yang tidak cukup
tidak dipaksakan menjadi nilai. Batas data/credit ada di [DATA-FLOW.md](DATA-FLOW.md).

## Pelengkap opsional setelah inti stabil

AI explainer merangkum bukti hasil, atas permintaan pengguna. Penjelasan aturan
tetap berfungsi ketika AI tidak tersedia; AI tidak mengubah skor atau memberi
BUY/HOLD/SELL. Provider, model, limit, dan budget AI memerlukan keputusan sebelum
integrasi; tidak memakai asumsi bahwa credit Sectors membiayai AI.

## Arah pasca-MVP

- Paket berbayar: user menyampaikan arah monetisasi. Paket, harga, billing,
  entitlement, kuota, dan waktu peluncuran belum ditentukan.
- BYOK: pengguna memasukkan API key sendiri ke aplikasi. Belum dibuat; perlu
  enkripsi backend, rotasi/pencabutan key, pemisahan budget, redaksi log, dan
  evaluasi izin provider serta isolasi cache antar pemilik key/paket.

Keduanya dapat menjadi alternatif atau kombinasi; bukan komitmen bahwa keduanya
langsung dibangun. Budget 1.000 credit hackathon dan kuota MVP bukan desain
pricing. Cadangan 600 tetap memerlukan izin penggunaan tersendiri.

## Di luar komitmen saat ini

Watchlist, riwayat screener otomatis, laporan tersimpan terpisah, sharing publik,
portfolio accounting, broker/order execution, prediksi harga, dan aplikasi mobile
tidak termasuk MVP. Penyebutan di sini bukan backlog yang telah disetujui.

## Gate teknis yang masih perlu dibuktikan

Ketersediaan aktual field dan periode Sectors, konsistensi aksi korporasi,
kalender bursa/publish data, biaya seluruh peer, precision/index/FK, migrasi
ULID tanpa kehilangan data, dan kesiapan MySQL/Redis diuji saat work item terkait.
Jangan mengubah keputusan produk untuk menutupi keterbatasan provider.
