# Module: Company

## Tujuan dan boundary

Company menyajikan identitas, klasifikasi dan profil perusahaan.
`Application/GetCompanyProfile` memakai public contract CompanyDirectory dari
MarketData; controller Presentation menangani Inertia dan error halaman.
Tidak menghitung skor atau memanggil HTTP provider secara langsung.

## Status implementasi

Route `/perusahaan/{symbol}` menyajikan overview real, termasuk tanggal harga,
profil dan nilai yang tersedia. Null tetap null; tautan website divalidasi skema.
Route `/perusahaan` memakai direktori pencarian yang sama dengan Temukan Saham.
Tidak ada snapshot persistence baru pada increment ini.

`FakeCompanySnapshot` masih diperlukan consumer Research lama. Jangan menghapus
class ini sebelum consumer tersebut dimigrasikan; detail baru tidak memakainya.

## Verifikasi

CompanyDirectoryTest menguji section overview, unit persen, data hilang,
tautan aman, 404 dan konteks kembali. Smoke browser: BBCA, ADES dan AADI.
Lihat [work item](../../work-items/redesain-riset-terpandu/README.md).
