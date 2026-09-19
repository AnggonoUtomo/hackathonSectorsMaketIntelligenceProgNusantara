# Keamanan dan Batas Produk

## Secret dan endpoint

- Sectors API key berada di environment backend; jangan commit `.env`.
- Jangan kirim key ke frontend, mencatat header `Authorization`, atau
  menampilkan credential di error page, test output, maupun dokumentasi.
- Backend menjadi security authority; permission frontend hanya membantu UX.
- Endpoint aplikasi memvalidasi semua input dan memakai rate limit jika publik.
- Query screener hanya menggunakan field/operator allowlist yang didukung.
  Jangan meneruskan expression mentah user atau menjadikan aplikasi proxy
  bebas ke Sectors.

Authentication MVP belum ditetapkan. Detail access control mengikuti keputusan
work item terkait, tanpa mengurangi validasi input dan perlindungan API key.

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

Prinsip: **Sistem menghitung. AI menjelaskan.**
