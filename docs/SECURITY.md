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

Registrasi terbuka untuk umum. Semua fitur riset wajib login dan email
terverifikasi, ditegakkan di backend. Gunakan auth session starter; jangan
mengganti dengan token/JWT tanpa kebutuhan baru. Auth/reset/verifikasi tetap
dapat diakses sesuai state pengguna agar tidak terjadi redirect loop.

Starter belum menerapkan keputusan ini: User belum mengimplementasikan
MustVerifyEmail dan dashboard baru memakai middleware auth. Route verifikasi
yang sudah ada bukan bukti akses riset sudah terlindungi.

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
