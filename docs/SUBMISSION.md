# Checklist Submission dan Demo

## Produk

- [ ] Temukan Saham, Detail Perusahaan, dan Bandingkan berjalan end-to-end.
- [ ] Lima nilai dan Nilai Prioritas Riset dapat dijelaskan lewat data/bobot.
- [ ] Sectors benar-benar menjadi sumber data inti.
- [ ] Tidak ada trading otomatis atau rekomendasi BUY/HOLD/SELL.
- [ ] Disclaimer tersedia.
- [ ] Registrasi umum, login dan verifikasi email membatasi akses fitur riset.
- [ ] Compare maksimal 3 saham; simpan manual privat dan versi pembaruan teruji.
- [ ] Skor 2 desimal, kelengkapan minimal 70%, dan alasan unavailable terlihat.
- [ ] Penjelasan aturan tersedia tanpa AI; AI bukan syarat demo.

## Teknis

- [ ] Repository publik bersih, tanpa API key/secret, dengan riwayat commit wajar.
- [ ] README setup sesuai Laravel 12, MySQL, dan command yang benar-benar diuji.
- [ ] Test suite dan build yang relevan lulus.
- [ ] Error Sectors ditangani; cache bekerja dan credit terkendali.
- [ ] Ticker/cache demo tersedia dan freshness terlihat.
- [ ] Budget sekali pakai dan kuota harian diuji tanpa memakai cadangan otomatis.
- [ ] Tampilan 1080p, loading, empty, error, dan fallback diperiksa.

Publikasi repository, commit/tag, push, dan submission dilakukan ketika diminta
user. Checklist ini tidak mengizinkan tindakan tersebut secara otomatis.

## Storyboard tiga menit

| Waktu | Adegan |
| --- | --- |
| 0:00-0:20 | Masalah: terlalu banyak saham dan angka membuat riset awal memakan waktu. |
| 0:20-0:45 | Temukan Saham dan filter untuk membentuk shortlist. |
| 0:45-1:20 | Detail Perusahaan, lima nilai, dan Nilai Prioritas Riset. |
| 1:20-1:50 | "Mengapa Nilainya Seperti Ini?" beserta bukti pembentuk nilai. |
| 1:50-2:20 | Bandingkan tiga perusahaan sejenis. |
| 2:20-2:45 | Ringkasan AI jika tersedia; bila tidak, lanjutkan penjelasan berbasis bukti. |
| 2:45-3:00 | Tutup dengan manfaat riset: menentukan apa yang layak dipelajari lebih lanjut. |

Gunakan alur nyata, tunjukkan sumber Sectors dan manfaat pengguna. Penjelasan
stack/arsitektur cukup singkat. Fallback saat provider lambat tidak boleh
memalsukan data.

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
