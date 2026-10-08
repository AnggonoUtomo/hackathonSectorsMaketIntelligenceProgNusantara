# Akses dan Identitas NusaLens

Otorisasi user: login tetap wajib, tanpa verifikasi email; logo NusaLens baru,
default dark, serta aksen warna tipis pada badge, ikon dan tombol.
Status: selesai dan terverifikasi lokal pada 8 Oktober 2026. Perubahan ini
menggantikan kewajiban verifikasi email.

Scope: auth starter dan presentasi frontend. Akun, ULID, ownership snapshot,
formula, cache dan ledger tetap. Tidak memakai endpoint/credit Sectors baru,
tidak menambah dependency dan tidak melakukan commit/push tanpa instruksi baru.

Acceptance: akun belum terverifikasi bisa masuk riset, tamu tetap harus login,
registrasi tidak mengirim verifikasi, logo tampil di navigasi/login/favicon,
tema awal dark tanpa menimpa preferensi tersimpan, dan layout responsif.

Verifikasi terbatas: tes auth yang terdampak, typecheck/build, satu smoke visual
desktop/mobile tanpa memanggil provider. Tidak mengulang seluruh suite/audit.

## Hasil

- Middleware riset tetap auth; kontrak MustVerifyEmail dilepas, sehingga
  registrasi tidak mengirim email verifikasi. Tautan lama hanya redirect ke
  dashboard, tanpa mengubah timestamp verifikasi. Reset password tetap tersedia.
- Logo NusaLens berbentuk N/lensa dibuat khusus dan disimpan sebagai
  `public/nusalens-logo.png`, dipakai pada navigasi, halaman auth dan favicon.
- Default dark memakai charcoal bertingkat dengan aksen teal, sky, indigo dan
  amber. Badge bertint, tombol utama teal, outline tipis, ikon navigasi berwarna.
  Pilihan light/system yang sudah disimpan tetap dihormati, termasuk saat reload.
- 22 test terdampak / 108 assertion lulus (14 auth/evidence dan 8 regresi
  navigasi/profile/akses). HTTP fake, tanpa pengulangan seluruh suite.
- Typecheck, build client, Pint dirty, ESLint file frontend yang diubah,
  dan whitespace check lulus.
- Chrome 1440x1000 dan 390x844: default dark meski OS light, logo termuat,
  login akun belum terverifikasi menuju dashboard, tidak ada overflow maupun
  page/console error. Preferensi light bertahan setelah reload. Tidak ada request
  data perusahaan saat smoke. Akun/sesi QA dan credential/helper sementara dibersihkan.
- Pemakaian credit real: 0. Akun pengguna, snapshot, ledger dan cache tidak diubah.
  File Hackaton/LSP tidak disentuh. Tidak commit/push pada pekerjaan ini.

## Batas

Kepemilikan alamat email tidak lagi diverifikasi; pengguna tetap perlu email
yang dapat diakses untuk reset password. Rate limit, kuota per akun
dan budget global tidak dilonggarkan. Deployment/SMTP produksi tidak diuji ulang.
