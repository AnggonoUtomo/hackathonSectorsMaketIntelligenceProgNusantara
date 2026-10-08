# Keamanan dan Batas Produk

## Secret dan endpoint

- Sectors API key berada di environment backend; jangan commit `.env`.
- Jangan kirim key ke frontend, mencatat header `Authorization`, atau
  menampilkan credential di error page, test output, maupun dokumentasi.
- Backend menjadi security authority; permission frontend hanya membantu UX.
- Endpoint aplikasi memvalidasi semua input dan memakai rate limit, termasuk
  endpoint riset terautentikasi dan alur registrasi/verifikasi.
- Query screener hanya menggunakan field/operator allowlist yang didukung.
  Jangan meneruskan expression mentah user atau menjadikan aplikasi proxy
  bebas ke Sectors.

Registrasi terbuka untuk umum. Semua fitur riset wajib login tanpa verifikasi
email, sesuai revisi user 8 Oktober 2026. Gunakan auth session starter; jangan
mengganti dengan token/JWT tanpa kebutuhan baru. Auth/reset/verifikasi tetap
dapat diakses sesuai state pengguna agar tidak terjadi redirect loop.

User tidak lagi mengimplementasikan MustVerifyEmail. Route riset memakai
middleware auth; akun belum terverifikasi dapat masuk. Registrasi tidak mengirim
email verifikasi; endpoint verifikasi lama hanya redirect ke dashboard tanpa
mengubah email_verified_at. Snapshot tetap privat per pemilik. Registrasi
dibatasi 5 request/jam/IP. Login/reset berbagi
batas 10 request/menit/IP, ditambah limiter kegagalan login starter. Penulisan
snapshot dibatasi 30 request/menit/akun; baca compare/snapshot 60/menit.

Kepemilikan alamat email tidak dibuktikan pada registrasi. Pengguna harus mengisi
alamat yang dapat diakses untuk reset password. Rate limit dan budget global
tetap berlaku; kuota per akun bukan pengaman tunggal terhadap pembuatan banyak akun.

Header nosniff, SAMEORIGIN, referrer/permissions policy dan CSP dasar diterapkan
di middleware. CSP membatasi base/object/frame, belum berupa allowlist script
dan style menyeluruh. HSTS hanya aktif pada HTTPS production. Debug wajib mati,
document root harus public, dan secret/runtime berada di luar public root.
Pemeriksaan konfigurasi tersedia melalui `php artisan nusalens:preflight`;
detail dan batas pengujian ada di [Deployment](DEPLOYMENT.md).

Perbandingan tersimpan hanya dapat dibaca, diperbarui, dinamai, atau dihapus
pemiliknya. ULID bukan permission; uji akses lintas akun pada setiap endpoint.
Data pasar/skor bersama tidak boleh membawa metadata perbandingan privat.
Budget dan kuota ditegakkan di backend sebelum upstream request, termasuk retry;
lihat [DATA-FLOW.md](DATA-FLOW.md). Cache hit tetap tunduk rate limit aplikasi.

## Integritas data

Vendor JSON harus dipetakan ke model internal. Snapshot mencatat `fetched_at`
dan sumber/provider jika diperlukan. Jangan mencampur data baru dan lama tanpa
tanda yang jelas. UI penting menampilkan waktu pengambilan dan kelengkapan
data; error provider tidak boleh disamarkan sebagai hasil kosong.

## Posisi produk

NusaLens adalah alat informasi dan riset untuk membantu pengambilan keputusan.
Produk tidak menjadi penasihat investasi, pemberi rekomendasi pribadi, broker,
atau alat eksekusi trading. Hasilnya bukan BUY/HOLD/SELL.

Draft disclaimer dari rancangan produk:

> NusaLens menyediakan informasi dan analisis untuk tujuan riset. Informasi
> yang ditampilkan bukan nasihat investasi atau rekomendasi untuk membeli,
> menjual, atau menahan instrumen keuangan. Keputusan investasi tetap merupakan
> tanggung jawab pengguna.

## Penjelasan AI

Input AI hanya bukti dan `ScoreBreakdown` yang telah dihitung sistem. AI tidak
mengubah nilai, formula, atau membuat klaim di luar data. Prompt harus melarang
rekomendasi investasi; tampilkan bukti/penjelasan sumber bersama ringkasan AI
agar hasil dapat diperiksa. AI opsional dan tidak menjadi prasyarat alur inti.

Penjelasan berbasis aturan wajib tersedia saat AI gagal/kuota habis. AI hanya
dijalankan atas permintaan, setelah inti stabil. Provider/model/budget belum
dipilih; budget AI bukan bagian 1.000 credit Sectors. Secret AI tetap backend;
jangan mengirim data pribadi yang tidak dibutuhkan. Billing dan BYOK di luar MVP.

Prinsip: **Sistem menghitung. AI menjelaskan.**
