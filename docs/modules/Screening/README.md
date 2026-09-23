# Module: Screening

## Tujuan dan boundary

Screening mengatur pencarian perusahaan dan hasil paginated.
`Application/SearchCompanies` memakai public contract CompanyDirectory milik
MarketData. Controller Presentation menyediakan halaman Inertia dan JSON
autocomplete, dengan login, verifikasi, validasi identitas dan rate limit.

## Status implementasi

`/temukan-saham` dan `/perusahaan` menyajikan direktori real. JSON saran tersedia
di `/nusalens/companies/search` dengan q, page dan limit. Query UI bukan ekspresi
SQL bebas; partial nama/kode diterjemahkan adapter ke structured Companies.
Pagination diteruskan ke provider; tidak memotong pencarian pada halaman pertama.

Autocomplete menampilkan delapan saran dan tautan seluruh hasil; tabel menerima
maksimal 25 baris per halaman. Ini batas per halaman, bukan batas total emiten.
Filter sektor/tujuan riset dijadwalkan terpisah. Tidak menampilkan skor demo.

## Verifikasi

CompanyDirectoryTest dan NusaLensNavigationTest mencakup pencarian, pagination,
auth, cache, quota dan error. QA browser mencakup debounce, keyboard, logo,
empty state dan back navigation. Lihat [work item](../../work-items/redesain-riset-terpandu/README.md).
