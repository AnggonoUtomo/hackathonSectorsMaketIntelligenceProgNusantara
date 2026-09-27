# User Guide Sementara NusaLens

Panduan ini menjelaskan alur penggunaan NusaLens berdasarkan kondisi aplikasi
saat ini. Tujuannya membantu pengguna memahami apa yang sudah bisa dicoba, apa
yang masih berupa keterbatasan, dan ke mana alur produk seharusnya bergerak.

NusaLens adalah alat informasi dan riset. Aplikasi ini bukan penasihat investasi,
broker, trading bot, atau pemberi rekomendasi beli/jual/tahan.

## Status saat ini

Yang sudah tersedia untuk diuji:

- Login dan email verified untuk fitur riset.
- Pencarian perusahaan lewat **Temukan Saham** dengan data real.
- Detail perusahaan atau **Company Cockpit**.
- Tab **Ringkasan Riset**, **Profil**, **Harga**, **Keuangan**, dan **Valuasi**.
- Grafik Recharts dan tabel angka pada data harga, keuangan, dan valuasi.
- Ringkasan Riset berbasis aturan dengan bukti angka dan perhitungan sederhana.

Yang belum selesai:

- Wizard analisis terpandu.
- Nilai Prioritas Riset final berbasis peer dan formula scoring v1.
- Compare real maksimal tiga saham.
- Snapshot perbandingan privat dan berversi.
- AI explainer.

## Alur utama yang bisa dicoba sekarang

### 1. Login

1. Buka aplikasi.
2. Login memakai akun yang sudah terverifikasi email.
3. Masuk ke dashboard.
4. Pilih menu **Temukan Saham**.

Jika akun belum verified, fitur riset akan diarahkan ke halaman verifikasi.

### 2. Cari saham

1. Pada halaman **Temukan Saham**, ketik nama perusahaan atau kode saham.
   Contoh: `adaro`, `AADI`, `bank`, atau `central`.
2. Sistem menampilkan daftar perusahaan yang cocok.
3. Perhatikan kolom nama perusahaan dan simbol saham.
4. Pilih aksi **Riset** pada perusahaan yang ingin diperiksa.

Contoh alur:

```text
Temukan Saham -> ketik "AADI" -> pilih PT Adaro Andalan Indonesia Tbk -> Riset
```

Setelah klik **Riset**, user masuk ke halaman detail:

```text
/perusahaan/AADI
```

## Company Cockpit

Halaman detail perusahaan berfungsi sebagai ruang kerja riset satu saham. Di
sini user tidak langsung diberi kesimpulan beli/jual, tetapi diberi data dan
bukti untuk memutuskan apakah perusahaan tersebut layak diteliti lebih lanjut.

Tab yang tersedia:

| Tab             | Fungsi                                            |
| --------------- | ------------------------------------------------- |
| Ringkasan Riset | Temuan berbasis aturan dan bukti angka sumber.    |
| Profil          | Identitas, klasifikasi, dan ringkasan perusahaan. |
| Harga           | Grafik dan tabel harga historis.                  |
| Keuangan        | Grafik dan tabel laporan kuartalan.               |
| Valuasi         | Grafik dan tabel rasio valuasi historis.          |

## Ringkasan Riset

### Cara membuka

1. Pada halaman perusahaan, buka tab **Ringkasan Riset**.
2. Klik tombol **Buka ringkasan riset**.
3. Sistem mengambil profil perusahaan dan laporan keuangan kuartalan.
4. Jika data tersedia dan masih fresh, sistem menampilkan temuan.

Estimasi saat cold cache dapat mencapai beberapa credit karena sistem perlu
mengambil profil dan laporan kuartalan. Cache hit tidak memakai credit tambahan.

### Apa yang dihitung saat ini?

Ringkasan Riset saat ini memakai aturan:

```text
research-facts-v1
```

Ini belum formula scoring final. Aturan saat ini hanya membaca fakta dasar yang
dapat dibuktikan dari data perusahaan, misalnya:

- perusahaan mencatat laba/rugi kuartalan;
- porsi laba bersih terhadap pendapatan untuk perusahaan nonkeuangan;
- ekuitas tercatat positif, negatif, atau nol;
- tren pendapatan dan laba per kuartal dalam grafik/tabel.

Contoh perhitungan yang sudah tersedia:

| Temuan              | Perhitungan                                          |
| ------------------- | ---------------------------------------------------- |
| Laba/rugi kuartalan | `earnings > 0`, `earnings < 0`, atau `earnings = 0`. |
| Margin laba bersih  | `earnings / revenue * 100`.                          |
| Status ekuitas      | `total_equity > 0`, `< 0`, atau `= 0`.               |

Angka pada Ringkasan Riset berasal dari data real yang dimuat melalui backend,
bukan dari AI. AI juga belum dipakai untuk membuat kesimpulan.

### Bukti dan perhitungan

Setiap temuan memiliki bagian **Bukti dan perhitungan**. Saat dibuka, user dapat
melihat:

- metrik sumber;
- nilai angka;
- periode laporan;
- basis angka, misalnya selama kuartal atau posisi pada tanggal laporan;
- formula aturan;
- endpoint sumber;
- waktu pengambilan data;
- versi aturan.

Bagian ini dibuat agar user bisa menelusuri alasan sebuah temuan muncul, bukan
hanya menerima kalimat ringkasan.

## Yang masih perlu diperiksa

Bagian **Yang masih perlu diperiksa** adalah daftar keterbatasan analisis saat
ini. Ini bukan nilai dari API dan bukan skor. Daftar ini sengaja muncul agar
NusaLens tidak menarik kesimpulan yang belum didukung bukti.

### Hubungan laba dan kas belum disimpulkan

Alasan:

- sistem belum memverifikasi basis arus kas;
- laba positif belum tentu berarti kas operasional sehat;
- laporan arus kas belum dibandingkan dengan laba pada basis periode yang sama.

Yang perlu dilakukan ke depan:

- ambil dan validasi data arus kas;
- cocokkan basis kuartalan atau kumulatif;
- bandingkan laba dengan kas operasi.

### Pertumbuhan tahunan belum dihitung

Alasan:

- ringkasan saat ini belum menghitung YoY;
- empat kuartal yang dimuat belum otomatis menjamin kuartal pembanding tahun
  sebelumnya tersedia dan sebanding;
- pertumbuhan harus membandingkan kuartal yang sama, misalnya Q2 2026 dengan Q2
  2025, bukan sekadar kuartal sebelumnya.

Yang perlu dilakukan ke depan:

- pastikan data kuartal pembanding tahun sebelumnya tersedia;
- hitung pertumbuhan pendapatan dan laba YoY;
- tampilkan periode dan basis pembanding.

### Nilai Prioritas Riset belum tersedia

Alasan:

- kalkulator scoring v1 belum dihubungkan;
- sistem belum mengambil semua peer valid;
- percentile dan kelengkapan data belum dihitung;
- bukti peer belum disimpan sebagai snapshot immutable.

Nilai Prioritas Riset final nantinya mengikuti formula di `docs/SCORING.md`,
termasuk:

- Kesehatan Bisnis 30%;
- Pertumbuhan 25%;
- Harga Saham 20%;
- Kekuatan Pasar 15%;
- Keamanan Keuangan 10%;
- kelengkapan minimal 70%;
- peer minimal 5 perusahaan lain per metrik;
- angka tampil dua desimal;
- tanpa label beli/jual/tahan.

Jadi, bila dropdown ini muncul, artinya sistem sedang jujur bahwa skor prioritas
belum layak ditampilkan.

## Tab Profil

Tab **Profil** digunakan untuk membaca identitas perusahaan. Gunakan tab ini
untuk memastikan:

- nama perusahaan benar;
- sektor/subsektor sesuai;
- deskripsi bisnis tersedia;
- data profil masih fresh.

Profil membantu memahami konteks sebelum membaca grafik atau angka keuangan.

## Tab Harga

Tab **Harga** digunakan untuk membaca pergerakan harga historis.

Langkah:

1. Buka tab **Harga**.
2. Pilih rentang, misalnya 30 hari atau 90 hari.
3. Baca grafik harga.
4. Bila butuh angka lengkap, buka tabel.

Catatan:

- perubahan harga bukan total return;
- grafik tidak memberi rekomendasi beli/jual;
- harga historis perlu dibaca bersama data keuangan dan valuasi.

## Tab Keuangan

Tab **Keuangan** digunakan untuk membaca laporan kuartalan.

Langkah:

1. Buka tab **Keuangan**.
2. Pilih metrik yang tersedia.
3. Baca grafik dan tabel.
4. Perhatikan periode laporan.

Catatan:

- angka tabel memakai rupiah penuh;
- grafik dapat memakai satuan ringkas seperti miliar rupiah;
- nol valid tetap ditampilkan sebagai nol;
- data tidak tersedia tidak diganti nol.

## Tab Valuasi

Tab **Valuasi** digunakan untuk membaca rasio valuasi historis.

Langkah:

1. Buka tab **Valuasi**.
2. Baca rasio yang tersedia, misalnya P/E atau P/B bila provider menyediakan.
3. Gunakan tabel untuk melihat angka lengkap per periode.

Catatan:

- rasio rendah tidak otomatis berarti murah;
- rasio tinggi tidak otomatis berarti mahal;
- konteks peer dan kualitas bisnis tetap diperlukan.

## Setelah menemukan satu saham, user ke mana?

Kondisi saat ini: setelah user membuka satu perusahaan, alur lanjut belum cukup
jelas. Secara produk, user seharusnya diarahkan ke urutan berikut:

```text
Temukan Saham
-> Riset satu perusahaan
-> Baca Ringkasan Riset
-> Buka Bukti dan Perhitungan
-> Periksa Harga, Keuangan, dan Valuasi
-> Tambahkan pembanding
-> Bandingkan maksimal tiga saham
-> Hitung Nilai Prioritas Riset bila data peer sudah siap
-> Simpan snapshot riset bila ingin ditinjau ulang
```

Alur lanjut tersebut belum seluruhnya tersedia di UI. Karena itu, untuk sementara
penggunaan yang paling masuk akal adalah:

1. mulai dari **Temukan Saham**;
2. buka satu perusahaan;
3. baca **Ringkasan Riset**;
4. buka **Bukti dan perhitungan** pada setiap temuan;
5. cek **Harga**, **Keuangan**, dan **Valuasi**;
6. catat saham yang menarik secara manual;
7. ulangi pencarian untuk perusahaan lain.

## Perbaikan UX yang perlu dibuat berikutnya

Agar pengguna awam tidak berhenti setelah satu saham, halaman perusahaan perlu
menampilkan aksi lanjut yang eksplisit:

- **Bandingkan dengan saham lain**;
- **Tambah ke perbandingan**;
- **Muat data harga**;
- **Muat data keuangan**;
- **Muat valuasi**;
- **Hitung Nilai Prioritas Riset** bila scoring sudah siap;
- **Simpan snapshot riset** bila persistence compare sudah siap.

Untuk tahap berikutnya, prioritas paling masuk akal adalah merapikan halaman
Company Cockpit agar setelah Ringkasan Riset user mendapat panduan langkah
berikutnya, bukan berhenti di halaman detail.

## Ringkasan status fitur

| Alur                        | Status                                         |
| --------------------------- | ---------------------------------------------- |
| Temukan Saham real          | Sudah bisa dicoba.                             |
| Detail perusahaan real      | Sudah bisa dicoba.                             |
| Ringkasan Riset berbukti    | Sudah bisa dicoba, tetapi belum scoring final. |
| Harga/Keuangan/Valuasi real | Sudah bisa dicoba.                             |
| Compare real                | Belum selesai. Proposal bertahap sudah dibuat. |
| Nilai Prioritas Riset final | Belum selesai. Menunggu scoring dan peer.      |
| Wizard pengguna awam        | Belum selesai.                                 |
| Snapshot tersimpan          | Belum selesai.                                 |
