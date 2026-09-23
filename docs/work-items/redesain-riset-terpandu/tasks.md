# Tasks: Redesain Riset Terpandu

## Sebelum mulai

- [x] Scope desain dan batas implementasi jelas.
- [x] Baca baseline produk, keputusan, alur data dan API.
- [x] Periksa source pencarian, detail, navigasi dan dependency chart.
- [x] Pertahankan folder referensi dan file user setelah rollback.

## Increment 0: Dokumentasi

- [x] Catat observasi Sectors dan pembeda produk.
- [x] Rancang alur, menu, desktop/mobile Temukan Saham.
- [x] Tentukan autocomplete, tabel, compare selection dan URL state.
- [x] Rancang wizard, grafik, states dan informasi sumber.
- [x] Pisahkan scope MVP dari peluang fitur lanjutan.
- [x] Catat validasi provider/logo/credit yang belum selesai.
- [x] Susun dependency, acceptance dan verifikasi increment.
- [x] Verifikasi link, konsistensi dokumen dan diff akhir.
- [x] User memberi instruksi lanjut implementasi.

## Increment 1: Search sampai detail real

- [x] Validasi pencarian nama/kode, overview dan pagination live; tarif memakai
  estimasi dokumentasi, bukan rekonsiliasi tagihan provider.
- [x] Validasi asset logo publik; fallback tetap tersedia untuk logo yang gagal.
- [x] Implementasikan autocomplete dan tabel hasil real dengan state lengkap.
- [x] Hubungkan detail identitas real dan back navigation.
- [x] Hapus pemakaian fake dari route Discover/Company dan verifikasi live.
  Class FakeCompanySnapshot tetap diperlukan Research; fake/UI lama yang
  tersisa tidak dipakai route Discover/Company baru. Tidak menghapus consumer lain.

## Increment 2: Detail dan grafik

- [ ] Validasi periode, unit, seri harga/keuangan bank dan nonbank.
- [ ] Implementasikan Recharts dan alternatif tabel dari data real.
- [ ] Verifikasi grafik, mobile, data parsial, error dan credit.

## Increment 3: Wizard

- [ ] Audit kalkulator, populasi peer dan kelengkapan input.
- [ ] Implementasikan stepper bebas, penjelasan aturan, dan rincian bukti.
- [ ] Verifikasi formula/threshold, waktu sumber dan alur pengguna.

## Increment 4: Eksplorasi

- [ ] Tentukan kriteria tujuan riset dan cakupan urutan hasil.
- [ ] Implementasikan sektor, tujuan, filter/sort, pagination dan selection.
- [ ] Verifikasi pola ContohUI dan hasil di luar halaman pertama.

## Increment 5: Compare dan navigasi

- [ ] Audit persistence/route lalu hubungkan compare dan snapshot real.
- [ ] Terapkan menu ringkas sambil mempertahankan akses route lama.
- [ ] Verifikasi ownership, versi, batas tiga dan alur lengkap.

## Hasil

- Perubahan: dokumen desain/increment dan alur pencarian sampai profil real.
- Otomatis final: `php artisan test --compact` lulus 107 test / 479 assertion;
  `npm run typecheck`, ESLint file TS/TSX berubah, `npm run build`,
  `vendor/bin/pint --dirty`, dan `git diff --check` lulus. Link relatif
  dokumentasi terverifikasi; route pencarian/perusahaan tetap terdaftar.
- Browser: desktop 1440px (sidebar expanded/collapsed), mobile 390px/320px;
  overflow horizontal halaman ditemukan lalu diperbaiki. Overflow tabel tetap
  berada dalam kontainer. Logo real berhasil, tanpa console warning/error
  pada halaman riset yang diuji.
- Interaksi real: autocomplete "central" = 4 hasil; keyboard memilih BBCA;
  Back mempertahankan ketikan; direktori = 962 emiten pada saat uji; page 2
  mengambil emiten berikutnya; BBCA, ADES dan AADI membuka profil real.
  Query tanpa kecocokan menampilkan empty state, bukan data contoh.
- Lingkungan: registrasi QA gagal mengirim email karena Mailpit 1025 mati;
  akun uji diverifikasi lokal. Ini temuan terpisah dari alur riset.
- Ledger validasi: 13 credit direservasi, terdiri dari 9 request Companies,
  2 overview BBCA, 1 ADES, dan 1 AADI (termasuk smoke kontrak awal).
  Ini estimasi ledger, bukan angka billing provider yang direkonsiliasi.
- Akun `nusalens-ui-qa-20260924@example.test` dipertahankan sebagai referensi
  ledger; verifikasi dicabut, password diacak ulang, dan sesi QA dihapus setelah
  pengujian. Script lokal sementara sudah dihapus. Data user lain tidak diubah.
- Risiko: cakupan logo tidak dijamin; biaya tercatat berupa reservasi konservatif,
  belum rekonsiliasi billing provider. Grafik/wizard dan compare real belum dibuat.
- Delivery: belum commit/push; folder Hackaton dan dua file LSP tidak disentuh.

Jangan menambah scope ke checklist tanpa instruksi user.
