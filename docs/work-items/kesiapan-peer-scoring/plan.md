# Plan: Kesiapan Peer dan Scoring

## Urutan dan batas persetujuan

Dokumentasi ini merinci increment 3B, bukan otorisasi seluruh pekerjaan.
Usulan urutan: selesaikan UX-1 setelah persetujuan, lalu audit 3B.0. Setiap
increment berhenti pada handoff dan review sebelum berlanjut.

## Increment 3B.0: Audit kelayakan data dan biaya

- Mulai dari dokumentasi resmi Sectors, kode adapter, dan metadata cache yang
  sudah ada. Tidak membuka key atau memasukkan payload sensitif ke dokumen.
- Buat matriks untuk setiap metrik: endpoint/section/projection, field, unit,
  basis, periode, tanggal pasar, definisi denominator, nullability, entitlement,
  biaya, pagination, serta bukti sumber dan tanggal pemeriksaan.
- Kandidat sumber: helper klasifikasi, structured screener, quarterly financials,
  report section terpilih, dan daily. Endpoint/projection final belum dikunci;
  audit apakah screener dapat memasok input peer tanpa fan-out per perusahaan.
- Buktikan kelompok peer lengkap dan stabil saat pagination, deduplikasi symbol,
  serta alasan setiap eksklusi. Search/filter pengguna tidak membatasi peer.
- Verifikasi konsistensi aksi korporasi sebelum momentum; bila belum terbukti,
  metrik unavailable, bukan diasumsikan adjusted.
- Pisahkan hasil bank, nonkeuangan, dan keuangan nonbank; jangan menarik
  kesimpulan kelengkapan seluruh IDX dari satu response contoh.

### Rencana credit dan cache

Estimasi adalah jumlah biaya union request yang benar-benar cache miss, memakai
endpoint, parameter, halaman, section, dan jumlah kuartal yang ditagihkan.
Jangan menyamakan satu HTTP request dengan satu credit untuk semua endpoint.
Catat kebutuhan retry sesuai baseline, maksimal satu retry berbiaya; keberadaan
kebijakan retry tidak berarti adapter existing sudah mengimplementasikannya.

| Skenario yang harus dihitung | Yang dicatat |
| --- | --- |
| Cold bank/nonkeuangan/keuangan nonbank | Penemuan kelompok, seluruh halaman peer, input target/peer, dan batas biaya. |
| Warm dengan input valid | Cache hit bernilai nol credit; hitung hanya kekurangan yang nyata. |
| Kelompok/periode fallback | Tambahan union request, bukan mengulang seluruh request awal. |
| Quota/budget tidak cukup | Berhenti dengan alasan jelas; tidak mengambil subset lalu menampilkan skor seolah lengkap. |

Budget 1.000 sekali pakai; tambahan 600 perlu persetujuan. Quota 20 per akun
per hari reset 00.00 WIB dan ledger MySQL tetap berlaku. Sisa budget aktual belum
diaudit; tidak boleh memakai pergantian akun atau penghapusan ledger sebagai jalan keluar.

Contoh risiko biaya, bukan estimasi final: jika target dan lima peer masing-masing
memerlukan lima kuartal cold dengan biaya satu credit per kuartal, kebutuhan
financials saja 30 credit. Ini melampaui quota harian, belum termasuk klasifikasi
dan sumber lain. Jangan menjalankan pola ini sebelum alternatif projection/cache
dan tarif aktual dibuktikan. Jumlah peer sah bisa lebih dari lima.

TTL mengikuti DATA-FLOW: klasifikasi/profil murni tujuh hari, overview bercampur
harga satu jam, financial 24 jam, valuasi/harga/screener satu jam. Cache hit atau
kalkulasi ulang tidak memperbarui fetched_at. Audit implementasi fallback terhadap
baseline; jangan menganggap fallback stale dan kalender bursa sudah tersedia.

Smoke live hanya setelah daftar request, estimasi maksimum, quota tersisa, dan
batas berhenti disetujui. Jalankan melalui adapter internal, bukan HTTP mentah
yang melewati ledger. Simpan bukti sanitasi, tanpa credential.

**Acceptance:** matriks punya bukti, biaya dapat dihitung, dan populasi lengkap
dapat ditentukan. Jika gagal, laporkan metrik unavailable beserta penyebabnya;
jangan mengubah formula atau membeli quota diam-diam.

## Proposal ownership dan persistence

Review sebelum pembuatan contract/module/schema, sesuai arsitektur aktif:

| Owner | Tanggung jawab |
| --- | --- |
| MarketData | Provider, mapping transport, cache/freshness, ledger/reservasi; tidak menghitung skor. |
| Company | Identitas/klasifikasi dan fakta perusahaan melalui public contract; snapshot fakta. |
| Intelligence | Eligibility, pemilihan peer, kalkulator pure PHP, versi formula dan bukti perhitungan immutable. |
| Research | Menjelaskan hasil final beserta keterbatasan; tidak mengubah nilai atau mengambil adapter konkret lintas module. |
| Comparison | Tidak diubah pada 3B; tetap tahap migrasi real tersendiri. |

Contract harus memiliki consumer nyata. Proposal teknis setelah audit mencakup
DTO input/hasil, alasan unavailable, precision, clock, serta lokasi binding.
Application hanya bergantung pada port/contract, tidak pada adapter konkret.

Persistence bukti Intelligence wajib pada 3B, bukan ditunda ke increment 5.
Increment 5 adalah penyimpanan perbandingan privat, bukan pertama kali bukti skor
disimpan. Rancangan minimal MySQL perlu memuat ULID, identitas snapshot fakta,
input mentah/unit/periode/tanggal pasar/fetched_at, keanggotaan peer dan eksklusi,
rank/percentile, bobot, kelengkapan, serta versi formula/config.

Identitas reuse harus memasukkan input, peer, periode/basis, dan config, bukan
hanya symbol. Input berubah menghasilkan snapshot baru; perhitungan historis
dapat direproduksi tanpa API. Reuse terkini tetap melewati freshness sumber.
Snapshot bersama tidak membocorkan perbandingan privat. Retensi mengikuti
DATA-MODEL: referensi bukti tetap utuh, ledger tidak ikut dibersihkan.

DDL, transaksi/concurrency, indeks identitas input, dan retention disajikan untuk
review sebelum migration. Tidak reset atau menulis ulang database user.

## Increment 3B.1: Satu komponen dengan bukti end-to-end

- Prasyarat: gate 3B.0 lulus untuk komponen terpilih; contract, schema, dan UX
  bukti disetujui. Pilih komponen berdasarkan data paling siap, bukan skor demo.
- Implementasi: pengambilan input melalui contract, eligibility/peer lengkap,
  kalkulasi, simpan bukti, lalu tampilkan pada Ringkasan Riset existing.
- Tampilkan hasil komponen dan alasan metrik hilang. Total tetap ditahan jika
  kelengkapan belum 70%; tidak membuat total dari satu komponen saja.
- UI: angka mentah, posisi dibanding perusahaan sejenis, periode, jumlah/anggota
  peer, alasan fallback dan kelengkapan. Grafik Recharts memiliki tabel bukti;
  tidak menambah halaman peer explorer atau menu baru.
- Acceptance: hasil sama dari snapshot yang sama; semua angka dapat ditelusuri;
  unavailable/error/budget tidak berubah menjadi nol atau hasil fake.
- Verifikasi: unit formula/eligibility, feature auth/verified/cache/ledger/
  persistence, typecheck/build, dan browser desktop/mobile/keyboard.
- Biaya: mengikuti hasil audit komponen, bukan angka perkiraan umum dalam plan.

## Increment 3B.2: Paket v1 dan penjelasan total

- Prasyarat: evaluasi 3B.1; audit data komponen berikutnya lulus atau alasan
  unavailable terdokumentasi. Tidak memaksakan semua metrik tersedia.
- Lengkapi perhitungan dan status lima komponen, bobot efektif, kelengkapan,
  ambang total, dan penjelasan aturan. Formula tetap merujuk SCORING v1.
- Pertahankan angka mentah dengan presisi sumber; dua desimal hanya tampilan.
- Acceptance: bank/nonkeuangan/keuangan nonbank diperlakukan tepat; data kurang
  tidak menampilkan total; tidak ada klaim aman/murah atau rekomendasi transaksi.
- Verifikasi: seluruh matriks di tasks, reproduksi snapshot tanpa API, browser
  untuk bukti lengkap/parsial/unavailable, dan uji pemahaman pengguna.
- Berhenti setelah handoff. Compare real dan versi privat tetap increment 4/5.

## Pemulihan dan verifikasi dokumen

Jika audit terhambat, Ringkasan Riset 3A tetap berjalan; tidak menonaktifkan
fondasi real atau mengembalikan placeholder. Laporkan gap dan minta keputusan
bila formula, schema, atau anggaran harus berubah. Dokumentasi diverifikasi
melalui tautan lokal, diff whitespace, dan status scope docs-only.
