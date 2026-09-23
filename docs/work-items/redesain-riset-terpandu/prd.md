# PRD: Riset Terpandu NusaLens

## Status

Rancangan 2026-09-24; user memberi instruksi lanjutan "ok, lakukan bro".
Implementasi dimulai dari increment 1; rincian increment berikut belum diimplementasi.
Ini rancangan pengalaman akhir, bukan klaim fitur sudah tersedia.

## Masalah, tujuan, dan pengguna

Pengguna awam ingin mencari perusahaan, memahami bisnisnya, dan menilai hal
yang perlu diteliti tanpa terlebih dahulu menghafal ticker atau rasio.
Pengguna yang lebih mahir tetap membutuhkan tabel, filter, dan bukti lengkap.

Hasil yang dituju: pengguna dapat menemukan perusahaan berdasarkan nama,
membaca fakta beserta periode, mengikuti analisis yang dijelaskan, kemudian
membandingkan dan menyimpan maksimal tiga saham. Tidak ada rekomendasi transaksi.

## Referensi dan pembeda

Sumber observasi adalah salinan lokal `Hackaton/`, termasuk screenshot
`Informasi lainya/view menu dan salah satu mappingnya.png` dan
`Informasi lainya/MenuIdeaDariSectors.app.png`, halaman ownership, news,
indices, dan weekly digest. Screenshot memperlihatkan susunan visual;
perilaku interaktif di situs live belum diuji pada pekerjaan ini.

| Yang terlihat pada referensi | Adaptasi NusaLens | Nilai tambah yang harus terbukti |
| --- | --- | --- |
| Kurasi bertema dan tabel perusahaan dengan logo | Eksplorasi sektor/tujuan riset dan daftar padat | Pengguna mengetahui alasan kandidat sesuai pencariannya |
| Banyak menu berdasarkan jenis data | Navigasi utama ringkas dan konteks di detail | Analisis dapat diselesaikan tanpa berpindah-pindah menu |
| Grafik dan kartu insight pada digest | Grafik interaktif di dekat penjelasan hasil | Angka, periode, dan alasan dapat ditelusuri |
| Data kepemilikan, berita, dan pergerakan pasar | Kandidat pengayaan setelah inti stabil | Konteks tambahan hanya saat sumber dan manfaatnya jelas |

Gunakan data/logo dari sumber yang tervalidasi, bukan menjadikan asset hasil
grab sebagai katalog produksi. Keberadaan fitur di dashboard Sectors tidak
menjamin akses melalui Financial API atau paket yang digunakan NusaLens.

## Alur dan navigasi

```text
Login + verifikasi
  -> Temukan Saham
       -> ketik nama/kode -> pilih perusahaan
       -> atau jelajahi sektor/tujuan -> pilih dari tabel
  -> Detail Perusahaan
       -> eksplorasi tab data secara bebas
       -> atau Mulai Analisis -> wizard -> ringkasan berbukti
  -> pilih maksimal 3 saham -> Bandingkan
  -> Simpan Perbandingan -> snapshot privat -> buka/perbarui sebagai versi baru
```

Usulan menu utama: Temukan Saham, Bandingkan, dan Perbandingan Tersimpan.
Perusahaan dibuka dari hasil pencarian; Jelaskan Nilai menjadi aksi kontekstual
pada skor; Kandidat Menarik menjadi bagian eksplorasi Temukan Saham. Pengaturan
akun tetap di menu pengguna. Entry setelah login menuju Temukan Saham.

Ini penyederhanaan penempatan menu, bukan penghapusan URL. Route lama tetap
dapat dibuka dengan konteks/pilihan yang sesuai. Target route untuk daftar
perbandingan tersimpan ditetapkan setelah audit persistence/route pada increment 5.
Jangan membuat menu yang berakhir pada placeholder selama transisi.

## Temukan Saham: susunan layar

Halaman kerja dimulai langsung dengan pencarian. Tidak ada hero pemasaran.

```text
DESKTOP
+-------------------+------------------------------------------------------+
| NusaLens          | Temukan Saham                                        |
| Temukan Saham     | [ Cari nama perusahaan atau kode saham...        ]   |
| Bandingkan        |   [logo] Nama perusahaan             KODE | sektor   |
| Tersimpan         |   [logo] Nama perusahaan             KODE | sektor   |
|                   |   Lihat semua hasil untuk "<ketikan>"                |
|                   |                                                      |
|                   | Eksplorasi: [Sektor v] [Tujuan riset v]               |
|                   | Ringkasan hasil yang tersedia                        |
|                   | Filter [Urutkan v] [Kolom] [Terapkan] [Reset]         |
|                   | PERUSAHAAN | HARGA | PERUBAHAN | SEKTOR | AKSI       |
|                   | ... hasil real ...                                   |
|                   | Pagination                                           |
|                   | Dipilih: <saham> <saham>        [Bandingkan (2)]      |
+-------------------+------------------------------------------------------+

MOBILE
NusaLens                                              [menu]
Temukan Saham
[ Cari nama perusahaan atau kode saham...                 ]
[ Hasil autocomplete selebar area konten                   ]
[Sektor v] [Tujuan riset v] [Filter]
Ringkasan hasil
Tabel dengan kolom identitas dan overflow horizontal
Pagination
Pilihan compare + aksi; tersedia ruang agar konten tidak tertutup
```

Wireframe menunjukkan hierarki; baris autocomplete hanya muncul saat relevan.
Nama dalam tanda sudut merupakan notasi dokumen, bukan data demo di aplikasi.

## Autocomplete

1. Label input "Cari perusahaan"; placeholder "Nama perusahaan atau kode saham".
   Nama parsial dan kode dapat digunakan tanpa memilih sektor lebih dahulu.
2. Usulan interaksi: minimal 2 karakter, debounce 300 ms. Ini ambang request,
   bukan pembatasan perusahaan. Huruf besar/kecil tidak membedakan pencarian.
3. Usulan dropdown maksimal 8 saran per tampilan; "Lihat semua hasil" membuka
   tabel paginated dengan ketikan yang sama. Total hanya ditampilkan jika diketahui.
4. Setiap saran memuat logo asli jika tersedia, nama lengkap, kode, dan sektor.
   Harga tidak wajib di saran agar pencarian identitas tidak memerlukan enrichment.
5. Klik/Enter pada saran aktif membuka detail; Enter tanpa saran aktif membuka
   semua hasil. Panah memilih saran, Escape menutup, Tab mengikuti urutan fokus.
   Gunakan combobox/listbox dengan status loading dan hasil yang dapat diakses.
6. Batalkan request lama atau abaikan respons yang bukan milik ketikan terbaru.
   Loading tidak mengosongkan input atau memindahkan fokus.
7. Mengosongkan pencarian menghapus saran dan keyword; filter eksplorasi lain
   tetap berlaku. Tombol Reset menghapus seluruh filter dan kembali ke halaman 1.
8. Simpan keyword/filter/sort/page tabel pada URL. Back dari detail mengembalikan
   pencarian, halaman, dan posisi scroll. Ketikan sementara dropdown tidak
   menambah satu entri history untuk setiap huruf.
9. Logo yang gagal/tidak tersedia memakai inisial netral. Jangan mengganti dengan
   logo perusahaan lain; sumber logo dan cakupannya harus diverifikasi sebelum rilis.

Autocomplete harus mencocokkan nama/kode. Jangan menyamakan kebutuhan ini dengan
natural-language investment query. Pemilihan endpoint, operator pencarian, dan
urutan kecocokan harus dibuktikan pada increment pertama.

## Eksplorasi dan tabel

- Sektor/subsektor mengikuti klasifikasi real yang tervalidasi, termasuk bank
  dan nonbank. Jangan membatasi ke tiga sektor contoh yang ada saat ini.
- Tujuan riset yang diusulkan: kesehatan bisnis, pertumbuhan, valuasi relatif,
  kekuatan pasar, dan keamanan keuangan. Kriteria/sort harus terlihat dan
  bersumber dari formula v1; bukan kategori baru untuk nilai total.
- Tujuan berbasis skor baru diaktifkan ketika perhitungan dan cakupan peer
  tersedia. Awal implementasi menyediakan pencarian dan sektor terlebih dahulu.
- Tabel mengikuti pola ContohUI/dashboard-*: ringkasan, shortcut, filter,
  loading/empty/error, reset, pagination, badge, dan aksi ikon ber-tooltip.
- Ringkasan memakai jumlah hasil atau statistik dari himpunan yang jelas.
  Angka halaman aktif tidak boleh diberi judul seolah mewakili seluruh IDX.
- Kolom awal: identitas, harga penutupan, perubahan harian, sektor, dan aksi.
  Kapitalisasi pasar dan metrik tersedia lewat pemilih kolom. Tanggal harga
  ditampilkan; harga penutupan tidak diberi label real-time.
- Klik nama membuka detail. Kontrol pemilihan compare terpisah, bernama aksesibel,
  dan tidak ikut menavigasi baris. Nilai Prioritas Riset baru menjadi kolom ketika
  dihitung; jangan menampilkan nilai demo pada emiten real.
- Pagination bukan batas total pencarian. Jangan berhenti di 10 hasil pertama
  atau menyaring hanya halaman yang kebetulan telah dimuat.
- Compare mempertahankan pilihan saat mengganti halaman/filter. Pada pilihan
  keempat, pertahankan tiga pilihan dan tampilkan alasan serta cara menghapus.
  Tombol Bandingkan aktif mulai dua perusahaan; tidak mengganti pilihan diam-diam.

## Detail dan wizard

Detail memuat identitas, sektor, profil bisnis, harga dan tanggal observasinya,
serta tab Ringkasan, Kinerja, Valuasi, dan Risiko. Aksi utama "Mulai Analisis";
aksi tambahan memasukkan perusahaan ke pilihan compare.

| Langkah wizard | Pertanyaan pengguna | Bukti/hasil yang disajikan |
| --- | --- | --- |
| Kenali bisnis | Perusahaan ini bergerak di bidang apa? | Profil, sektor, model bisnis sejauh tersedia |
| Periksa kinerja | Bagaimana perkembangan bisnisnya? | Tren pendapatan/laba dan metrik bank/nonbank yang relevan |
| Pahami valuasi | Bagaimana posisinya dibanding perusahaan sejenis? | Rasio dan distribusi peer dengan periode yang cocok |
| Periksa risiko | Apa yang perlu saya perhatikan? | Utang/risiko yang relevan, data hilang, dan keterbatasan bukti |
| Ringkasan | Apa alasan perusahaan ini layak diteliti lebih lanjut? | Lima komponen, nilai total bila layak, penjelasan dan bukti |

Stepper dapat diklik; pengguna tidak wajib menjawab form atau menuntaskan urutan.
Kembali ke langkah sebelumnya mempertahankan perusahaan dan data. Ringkasan
tidak menyebut analisis lengkap bila input belum cukup. Wizard bukan formulir
profil risiko investor dan tidak memberi rekomendasi beli/jual.

Penjelasan menempel pada angka: makna, perbandingan, periode, dan keterbatasan.
Detail rumus dibuka melalui aksi "Lihat perhitungan". Hindari halaman instruksi
panjang dan deskripsi fitur yang harus dibaca sebelum pengguna dapat bekerja.

## Grafik dan informasi

Recharts tetap pilihan visualisasi. Grafik berfungsi menjawab pertanyaan;
seri waktu tidak dibuat dari satu snapshot dan data hilang tidak diisi nol.

| Pertanyaan | Visual yang diusulkan | Kebutuhan data |
| --- | --- | --- |
| Bagaimana perubahan harga? | Line chart penutupan harian + pilihan rentang | Seri harga, tanggal sesi dan satuan |
| Apakah kinerja berkembang? | Bar chart pendapatan/laba per periode | Periode fiskal dan basis kuartalan/TTM yang jelas |
| Bagaimana valuasi relatif? | Dot plot emiten terhadap peer | Rasio dengan basis sama, identitas peer dan tanggal |
| Apa pembentuk nilai? | Bar horizontal lima komponen + rincian kontribusi | Output kalkulator, bobot efektif, kelengkapan, versi formula |

Setiap grafik memuat unit, periode, tooltip dan alternatif tabel. Grafik tidak
mengandalkan warna saja, tidak membuat garis melintasi data kosong tanpa penanda,
dan tidak menghubungkan nilai rasio yang tidak sebanding. Tampilkan tren dulu,
lalu penjelasan ringkas serta bukti yang bisa diperluas.

Gunakan permukaan netral, teks kontras, aksen teal untuk aksi dan aksen berbeda
secukupnya untuk seri data. Hijau/merah dipakai untuk perubahan naik/turun dengan
tanda angka, bukan klaim saham baik/buruk. Hindari gauge dekoratif atau label
"murah" hanya dari harga nominal yang kecil. Angka tampil dua desimal.

Dimensi grafik dan logo stabil; mobile memakai susunan satu kolom. Nama panjang
membungkus, dropdown tetap dalam viewport, tabel overflow di dalam kontainer.
UI ikon memakai Lucide dan label aksesibel; tooltip bukan satu-satunya label.

## States dan kejujuran data

| Keadaan | Perilaku |
| --- | --- |
| Belum mencari | Tampilkan pencarian dan eksplorasi yang tersedia |
| Loading | Skeleton/status pada area terkait; input tetap dapat dipakai |
| Hasil benar-benar kosong | Sebut ketikan/filter, sediakan reset |
| Provider gagal | Pesan gagal + coba lagi, jangan disamarkan sebagai hasil kosong |
| Budget/kuota habis | Nyatakan batas dan gunakan cache layak bila ada |
| Cache lama yang layak | Tampilkan waktu sumber, status data lama dan alasan |
| Data parsial | Bagian tersedia tetap terlihat; bagian hilang menyebut alasan |
| Nilai belum layak | "Data belum cukup", kelengkapan dan metrik yang kurang |

Rumus dan ambang mengikuti SCORING.md: minimal lima peer lain per metrik,
kelengkapan berbobot minimal 70% untuk nilai total, tanpa label kategori skor.
Tiga pilihan compare bukan populasi peer kalkulasi. Bank/nonbank tidak dicampur
sebagai peer; compare lintas kelompok tetap memperlihatkan fakta, tetapi tidak
menghasilkan ranking gabungan seolah sebanding.

Pengguna memperoleh seluruh informasi relevan yang tersedia melalui tab dan
tabel terperinci. "Lengkap" tidak berarti memuat seluruh endpoint sekaligus
atau mengarang field yang tidak disediakan provider.

## Data, biaya, dan batas validasi

| Kebutuhan | Kandidat sumber berdasarkan baseline docs | Gate sebelum implementasi |
| --- | --- | --- |
| Nama/kode, sektor, hasil paginated | Companies Screener atau sumber identitas resmi yang sesuai | Pencarian parsial, pagination, escaping, response dan biaya |
| Logo | Sumber resmi/berizin yang tervalidasi | URL, cakupan, pemakaian dan fallback; belum terkonfirmasi |
| Profil dan rasio | Company Report dengan section eksplisit | Field, unit, null, tanggal, ketersediaan bank/nonbank |
| Tren harga | Daily | Rentang, tanggal sesi dan basis harga |
| Tren keuangan | Quarterly Financials | Kuartal vs kumulatif, periode dan kelengkapan |
| Peer dan skor | Data peer valid + kalkulator NusaLens | Seluruh populasi relevan, kompatibilitas dan bukti input |

Angka biaya dalam docs/API.md adalah baseline yang perlu diverifikasi ulang:
structured screener 1 credit, natural query 3, report per section, quarterly
per kuartal, Daily 1. Jangan mengulang asumsi bahwa parameter `q` selalu 1 credit.
Rancangan ini belum memverifikasi kontrak/tarif live dan tidak memakai credit.

TTL mengikuti DATA-FLOW.md: profil 7 hari, fundamental 24 jam, pasar/screener
1 jam; payload campuran mengikuti TTL terpendek atau dipisahkan. Data diambil
saat dibutuhkan; hover/prefetch/keystroke tidak memicu enrichment report/peer.
Estimasi tiap alur harus meliputi halaman, section, kuartal, peer dan retry.

## Fitur lanjutan

Kepemilikan dan perubahannya, foreign flow, berita/filing kontekstual, dividen,
dan anomali volume dievaluasi setelah alur inti berjalan. Masing-masing perlu
bukti akses API, biaya, periode, makna, dan kebutuhan pengguna. Tidak dimasukkan
ke skor v1 tanpa keputusan formula baru. Paket berbayar, BYOK dan AI opsional
tetap mengikuti ROADMAP.md; tidak menjadi dependency wizard MVP.

## Acceptance pengguna

- [ ] Ketik potongan nama tanpa ticker, temukan hasil real dan buka detail real.
- [ ] Temukan hasil di luar halaman pertama dan seluruh sektor yang didukung.
- [ ] Back dari detail memulihkan hasil pencarian dan filter.
- [ ] Pahami satu tren melalui grafik beserta angka, periode dan penjelasannya.
- [ ] Selesaikan wizard atau pindah langkah tanpa kehilangan konteks.
- [ ] Telusuri nilai ke metrik/peer; data kurang tidak berubah menjadi nol.
- [ ] Pilih dua/tiga perusahaan, bandingkan dan simpan snapshot privat.
- [ ] Pada mobile dan keyboard, tidak ada kontrol tertutup atau hasil sulit dipilih.

## Hal yang belum dikunci

Rincian menu dan wireframe masih proposal. Pencarian nama, logo, cakupan seri,
dan biaya merupakan pekerjaan validasi sumber, bukan pertanyaan yang perlu
dijawab user dari ingatan. Scope fitur lanjutan diputuskan terpisah setelah MVP.

## Validasi increment 1

Dokumentasi resmi Companies mendukung `company_name like` dan `symbol like`
dengan perbandingan case-insensitive. Smoke terstruktur untuk "central"
mengembalikan empat perusahaan real; overview BBCA juga berhasil. Dua request
dicatat lewat ledger internal, estimasi total 2 credit. Respons screener ini
hanya memuat simbol dan nama; sektor/harga tidak dijanjikan pada hasil search.
Detail menampilkan overview real; tabel increment 1 memprioritaskan identitas.
Filter sektor menyusul validasi taxonomy pada increment eksplorasi.

Sumber logo publik teramati dalam asset referensi:
`https://storage.googleapis.com/sectorsapp-sea/logo/{SYMBOL}.webp`.
Tidak mengunduh katalog logo; browser memuat simbol yang ditampilkan dan
memakai inisial jika gagal. Ini sumber asset dashboard, bukan field API dengan
jaminan cakupan. Tidak ada API key yang dikirim ke host asset.

Referensi resmi yang diperiksa pada 2026-09-24:
[Companies](https://docs.sectors.app/api-references/v2/indonesia/screener/companies),
[Company Report](https://docs.sectors.app/api-references/v2/indonesia/report/company-report).
Overview membawa harga sehingga cache payload campuran ditetapkan 1 jam.
