# Checklist Submission dan Demo

## Produk

- [x] Temukan Saham, Detail Perusahaan, dan Bandingkan berjalan end-to-end lokal.
- [x] Rumus lima komponen, bobot dan alasan unavailable dapat ditelusuri.
- [x] Sectors benar-benar menjadi sumber data inti.
- [x] Tidak ada trading otomatis atau rekomendasi BUY/HOLD/SELL.
- [x] Disclaimer tersedia.
- [x] Registrasi umum, login dan verifikasi email membatasi akses fitur riset.
- [x] Compare maksimal3 saham; simpan privat dan versi pembaruan teruji.
- [x] Skor2 desimal, kelengkapan minimal60%, dan alasan unavailable terlihat.
- [x] Penjelasan aturan tersedia tanpa AI; AI bukan syarat demo.

Angka semua komponen tidak dijanjikan tersedia: momentum/kalender belum terbukti,
valuasi populasi lengkap belum smoke live. AADI fundamental65% pada smoke lama
tidak menampilkan total di v1.0.0; analisis baru v1.1.0 dengan kelengkapan yang
sama memenuhi ambang60%. Checklist bukan pernyataan seluruh data provider lengkap.

## Teknis

- [ ] Repository publik bersih, tanpa API key/secret, dengan riwayat commit wajar.
- [x] README/setup sesuai Laravel12/MySQL dan command lokal yang diuji.
- [x] Test suite dan build relevan lulus; detail terbaru pada Kesiapan Publish.
- [x] Error Sectors ditangani; cache dan credit diuji.
- [x] Cache AADI diperiksa pada smoke; freshness terlihat, bukan jaminan fresh saat demo nanti.
- [x] Budget sekali pakai dan kuota harian diuji tanpa memakai cadangan otomatis.
- [ ] Tampilan 1080p, loading, empty, error, dan fallback diperiksa.

Publikasi repository, commit/tag, push, dan submission dilakukan ketika diminta
user. Checklist ini tidak mengizinkan tindakan tersebut secara otomatis.

Implementasi dipush atas instruksi user pada `1d83595`. Deployment publik, review
1080p lengkap dan submission belum dilakukan. Bukti lokal:
[Penuntasan MVP](work-items/penuntasan-mvp/README.md).
Persiapan rilis dilanjutkan di [Kesiapan Publish](work-items/kesiapan-publish/README.md)
dan [Deployment](DEPLOYMENT.md). Jangan memakai SHA lama sebagai release final
sebelum perubahan lanjutan dicommit atas instruksi user.

## Storyboard tiga menit

| Waktu | Adegan |
| --- | --- |
| 0:00-0:20 | Masalah: terlalu banyak saham dan angka membuat riset awal memakan waktu. |
| 0:20-0:45 | Temukan Saham: ketik nama perusahaan, pilih autocomplete dan buka detail. |
| 0:45-1:20 | Periksa grafik/angka dan tanggal sumber; buka analisis perusahaan sejenis. |
| 1:20-1:50 | Buka bukti komponen: rumus, peer, kelengkapan dan alasan total belum tersedia. |
| 1:50-2:20 | Lihat kandidat sejenis lalu bandingkan maksimal tiga perusahaan; muat bagian yang perlu. |
| 2:20-2:45 | Simpan snapshot privat, buka Riset Tersimpan; tunjukkan waktu bukti dan versi. |
| 2:45-3:00 | Tutup dengan manfaat riset: menentukan apa yang layak dipelajari lebih lanjut. |

Gunakan alur nyata, tunjukkan sumber Sectors dan manfaat pengguna. Penjelasan
stack/arsitektur cukup singkat. Fallback saat provider lambat tidak boleh
memalsukan data.

Periksa kelengkapan dan versi aktual saat demo; smoke lama bukan jaminan data
terkini. Jangan menampilkan AI yang belum tersedia. Diferensiasi: alur riset terpandu, perhitungan
transparan dan bukti yang dapat dibuka kembali, bukan meniru screener umum.
Siapkan akun demo terverifikasi secara privat; jangan menaruh password di README.

## Prioritas dua hari terakhir

1. Sediakan hosting sesuai Deployment, isi secret secara privat dan jalankan
   seluruh gate server. Source rilis sudah di-push 7 Oktober 2026 atas instruksi
   user; ini belum berarti aplikasi sudah terpasang pada hosting publik.
2. Review satu alur nyata lengkap, rekam demo dengan tanggal sumber terlihat,
   periksa aturan resmi, URL akses dan persyaratan video sebelum submission.

Jika belum ada hosting saat rekaman, demo lokal tetap bisa direkam dengan
jujur; jangan menyatakan sudah publik atau memenuhi syarat URL penyelenggara.
URL demo/video dan formulir submission belum tersedia/diisi oleh pekerjaan ini.

## Final freeze

- [ ] Periksa aturan resmi hackathon yang berlaku saat akan submit.
- [ ] Jalankan verifikasi terakhir, cek deployment demo dan README.
- [ ] Periksa link video serta syarat social post yang berlaku.
- [ ] Siapkan backup repository dan tag commit final jika diminta user.
- [ ] Setelah submission resmi, patuhi aturan freeze yang berlaku.

Referensi dari dokumen asal:
[Official Rules](https://hackathon.sectors.app/rules) dan
[Market Intelligence Track](https://hackathon.sectors.app/tracks/market-intelligence).
Aturan eksternal tersebut belum diverifikasi ulang dalam pekerjaan dokumentasi
ini; checklist internal bukan pernyataan bahwa semua syarat resmi telah dipenuhi.
