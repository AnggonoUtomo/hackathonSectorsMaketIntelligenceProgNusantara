# Model Data Awal

## Status dan tujuan

Daftar berikut diambil dari blueprint NusaLens untuk menyimpan data internal,
bukti, dan hasil penilaian. Database aktif adalah MySQL. Ini model konseptual,
bukan schema migration yang telah disetujui atau tabel yang sudah tersedia.

| Konsep tabel | Kegunaan |
| --- | --- |
| `companies` | Identitas perusahaan dan klasifikasi sektor/subsektor. |
| `financial_snapshots` | Gambaran data keuangan internal pada saat pengambilan. |
| `market_snapshots` | Gambaran data pasar internal pada saat pengambilan. |
| `scores` | Hasil nilai perusahaan. |
| `score_components` | Rincian komponen pembentuk nilai. |
| `screening_runs` | Riwayat kriteria/hasil screener jika diperlukan. |
| `comparisons` | Perbandingan tersimpan jika diperlukan. |
| `research_reports` | Penjelasan/ringkasan riset bila perlu disimpan. |
| `api_cache_metadata` | Informasi pengambilan dan freshness data cache. |

## Hubungan konseptual

Data perusahaan memberi konteks bagi snapshot keuangan/pasar dan kelompok
pembanding. Intelligence mengolah data internal menjadi nilai dan rincian
komponen. Comparison menyandingkan beberapa perusahaan; Research menjelaskan
nilai menggunakan data yang sama. Riwayat screener dan perbandingan tidak
harus disimpan untuk setiap request.

Redis melayani cache dan queue; MySQL menyimpan data yang memang memerlukan
persistence. Snapshot di MySQL tidak menggantikan aturan cache atau menjadi
alasan mengambil seluruh dataset provider.

## Integritas dan boundary

- Vendor JSON dipetakan dan divalidasi sebelum digunakan sebagai model internal.
- Snapshot menyimpan `fetched_at` dan informasi sumber/provider bila diperlukan.
- Jangan mencampur periode atau data baru/lama tanpa keterangan yang jelas.
- Hasil scoring harus dapat ditelusuri ke angka asli, peer group, bobot,
  kontribusi, dan waktu pengambilan sesuai [SCORING.md](SCORING.md).
- Model persistence berada di Infrastructure; Domain tidak bergantung Eloquent.
- Akses data lintas module menggunakan contract/read model publik yang nyata,
  bukan import model privat atau query yang melewati ownership.

## Keputusan sebelum migration

Tentukan ownership tabel/migration per module, identifier, hubungan dan foreign
key, tipe numerik/precision, periode data, retensi snapshot, serta indeks untuk
use case yang akan dibuat. Rancangan awal belum mengunci pilihan ini.

Company memiliki identitas/profil dan gambaran keuangan; Intelligence memiliki
aturan nilai; MarketData memiliki integrasi serta metadata freshness. Detail
penyimpanan di antara ketiganya harus dirinci pada specification module sebelum
schema fundamental dibuat. Jangan membuat semua tabel sekaligus hanya karena
tercantum dalam blueprint.
