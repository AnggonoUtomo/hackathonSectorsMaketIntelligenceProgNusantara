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

`GetCompanyAnalytics` memakai public contract `MarketData/CompanyAnalytics`.
Endpoint `/nusalens/companies/{symbol}/analysis?section=prices|financials|valuation`
memuat satu tab sesuai permintaan, dengan auth, verifikasi email dan throttle.
Frontend menyediakan Recharts dan tabel angka lengkap, periode/satuan serta
loading, error/retry dan empty state. Tidak menghitung rekomendasi investasi.

Public contract `Application/Contracts/CompanyResearchData` digunakan Research
untuk memperoleh profil dan financials. `GetCompanyResearchData` menggabungkan
port MarketData existing dan menyertakan basis field yang terverifikasi.
Tidak memanggil endpoint/section tambahan atau menghitung nilai Research.

`FakeCompanySnapshot` dan explainer lama masih ada sebagai source legacy;
route research aktif sudah dimigrasikan dan tidak memakainya. Pembersihan
class legacy menunggu audit consumer terpisah, bukan bagian dari increment 3A.

## Verifikasi

CompanyDirectoryTest menguji section overview, unit persen, data hilang,
tautan aman, 404 dan konteks kembali. Smoke browser: BBCA, ADES dan AADI.
Lihat [work item](../../work-items/redesain-riset-terpandu/README.md).
