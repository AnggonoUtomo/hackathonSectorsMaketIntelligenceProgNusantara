# Alur Data dan Anggaran Credit

## Status dan alur

Kebijakan berikut disetujui pada 2026-09-20; belum diimplementasikan. MarketData
memiliki integrasi, cache/freshness, ledger credit dan reservasi kuota.

```text
Pengguna login + verified -> use case -> cek cache dan usia sumber
  fresh: gunakan data internal
  miss/expired: cek dan reservasi budget + kuota secara atomik
    -> Sectors -> map + validate -> snapshot Company + cache
    -> catat biaya/status di ledger
  gagal/tidak ada budget: fallback tersimpan yang masih layak, beri tanda
  tidak ada fallback layak: tampilkan error/unavailable, bukan data kosong palsu
  -> Intelligence menghitung/reuse hasil -> tampilan/Research/Comparison
```

Implementasi HTTP/Redis/MySQL berada di Infrastructure; Application mengatur
proses dan Domain menghitung input internal. Jangan mengunduh seluruh IDX dari
semua endpoint atau mengambil semua section Company Report secara default.

## Pengambilan bertahap

1. Structured Companies Screener: kriteria, pagination, shortlist, dan klasifikasi.
2. Ambil field/section kandidat serta semua peer valid yang dibutuhkan scoring,
   memanfaatkan cache dan periode eksplisit; bukan hanya peer pilihan pengguna.
3. Keuangan kuartalan dan seri harga hanya sesuai kebutuhan metrik/detail/compare.

Foreign flow, broker, forecast, berita, dan filing tidak masuk skor v1; jangan
menjadikannya panggilan wajib MVP. Semua peer valid berarti perlu estimasi biaya
sebelum enrichment. Jika kuota tidak cukup, jangan menyusutkan populasi menjadi
peer pertama yang kebetulan terambil; tampilkan keterbatasan hasil.

## Cache normal

| Jenis data | TTL normal |
| --- | --- |
| Identitas/profil perusahaan dan klasifikasi industri | 7 hari |
| Laporan keuangan dan rasio fundamental tanpa harga pasar | 24 jam |
| Harga harian, PE/PB, dan hasil screener | 1 jam |
| Skor | Reuse selama snapshot input/peer dan versi formula identik serta data sumber layak |

TTL adalah kebijakan internal, bukan janji pembaruan provider. Respons campuran
tidak boleh membuat harga ikut TTL profil 7 hari: pisahkan field/section atau
gunakan TTL terpendek yang relevan. Key cache meliputi parameter terstruktur,
section, simbol, rentang/periodisasi, pagination, dan versi mapping yang relevan.

Refresh hanya on-demand bila dibutuhkan dan cache kedaluwarsa, tanpa sweep
periodik seluruh saham. Cache expiry tidak otomatis memakai credit. Revalidasi
sumber tidak sama dengan menyalin cache; membaca cache/menghitung skor/menyimpan
perbandingan tidak mengubah fetched_at atau waktu observasi pasar.

## Fallback saat pembaruan gagal atau budget habis

| Data | Batas fallback |
| --- | --- |
| Data pasar dan valuasi berbasis harga | 24 jam dari observasi pasar sebenarnya |
| Laporan keuangan | 7 hari sejak pengambilan provider terakhir yang berhasil |

Saat bursa tutup, data penutupan sesi terakhir yang selesai boleh dipakai dengan
tanggal aslinya. Ini pengecualian kalender bursa, bukan 24 jam perdagangan atau
izin memperbarui timestamp. Verifikasi kalender/libur dan batas setelah sesi
berikutnya/provider publish sebelum implementasi; jangan mengasumsikan data baru
tersedia tepat saat jam tutup. Cache fresh tetap tidak menghalalkan input pasar
yang melewati batas usia observasi yang berlaku.

Tampilkan label data lama, periode laporan, waktu observasi pasar, fetched_at,
dan alasan fallback. Periode fiskal berbeda dari usia pengambilan. Data melewati
batas boleh tampil sebagai sejarah, tetapi tidak menjadi input skor terkini baru.
Tidak tersedia fallback layak berarti error eksplisit atau skor unavailable,
bukan nol/hasil pencarian kosong. Pemakaian profil lama juga harus ditandai dan
tidak boleh menyamarkan klasifikasi yang belum terverifikasi untuk peer terkini.

## Budget dan kuota disetujui

- 1.000 credit Sectors sekali pakai selama hackathon, bukan kuota berlangganan
  yang reset. Seluruhnya boleh dipakai; tidak ada alokasi cadangan internal 200.
- Cadangan tambahan 600 credit milik user TIDAK termasuk budget aplikasi;
  penggunaannya membutuhkan persetujuan eksplisit baru.
- Hard stop: tidak ada request upstream baru setelah budget habis. Data tersimpan
  tetap dapat dibaca sesuai freshness/fallback dan aturan akses.
- 20 credit per akun per hari, reset pukul 00.00 WIB (`Asia/Jakarta`), bukan
  rolling 24 jam. Kuota ini biaya provider aktual, bukan 20 HTTP request.
- Pembacaan cache/skor tersimpan tidak menghabiskan credit, tetapi rate limit
  aplikasi tetap berlaku. Request yang dipicu pengguna harus lolos kedua batas.
- Ledger dan reservasi persisten di MySQL; Redis bukan sumber kebenaran budget.
  Request bersamaan, retry, dan timeout tidak boleh menyebabkan overspend atau
  refund optimistis atas biaya yang belum dapat dipastikan. Rekonsiliasi tarif
  dan ketidakpastian charging ditetapkan pada adapter dengan bukti provider.
- Maksimal satu retry (dua attempt total), hanya kegagalan sementara. Setiap
  attempt harus lolos reservasi budget/kuota. Ikuti Retry-After untuk 429;
  jangan retry autentikasi/izin, query invalid, atau data tidak ditemukan.

Billing dan BYOK bukan MVP. Desain ledger ini tidak otomatis mengizinkan cache
berbagi lintas API key atau paket pengguna di masa depan.

## Estimasi dan verifikasi

Jangan menyamakan satu request dengan satu credit. Dokumentasi resmi menyebut
structured screener 1 credit (natural query 3), Company Report per section,
quarterly financials per kuartal yang dikembalikan, dan Daily 1 credit.
Rujukan dan batas kontrak ada di [API.md](API.md).

Target lama Discover <=5, Detail <=10, Compare <=20 hanyalah estimasi awal,
bukan jaminan untuk seluruh populasi peer. Hitung estimasi endpoint, pagination,
section, rentang kuartal, dan retry pada setiap use case sebelum live request.
Kebutuhan melebihi kuota tidak boleh diam-diam menaikkan batas yang disetujui.

Uji dengan fake HTTP dan jam terkontrol: hit/miss, TTL, fallback, libur bursa,
reset WIB, hard budget, concurrency/reservasi, retry, dan Redis kehilangan data.
Tidak ada panggilan live atau penggunaan credit pada pekerjaan dokumentasi ini.
