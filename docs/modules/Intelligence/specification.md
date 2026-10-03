# Spesifikasi Intelligence MVP

- Formula `nusalens-v1.0.0`; minimal5 peer lain, semua anggota valid kelompok.
- Kompatibilitas kind dan subsektor; keuangan nonbank memakai industri sama.
- Periode terbaru dicoba di seluruh hierarki sebelum mundur maksimal satu periode.
- Ties average-rank, lower-is-better dibalik, tanpa pembulatan tahap menengah.
- Kelengkapan bobot awal minimal70%; total ditahan bila kurang, bukan nol.
- Input PE: market cap/laba empat kuartal; PB: market cap/ekuitas kuartal.
  Laporan, tanggal pasar dan denominator harus cocok, denominator positif.
- Momentum memerlukan21 close dan bukti penyesuaian aksi korporasi. Adapter real
  tidak mengklaim bukti ini tersedia.
- Kegagalan fundamental/populasi lengkap tidak menghasilkan evidence sukses.
  Kegagalan pasar optional tidak menghilangkan fundamental.
- Bukti berisi input, peer/excluded, unit/basis/periode, rank, bobot/kontribusi,
  formula, waktu sumber dan masa berlaku. Bukti historis tidak menjadi data fresh.
- Estimasi fundamental cold: satu target + ceil(populasi sektor/200), masing-masing
  structured call1 credit. Pasar: jumlah anggota eligible yang cache miss.
  Maksimum satu retry berbayar tetap melalui ledger; preflight bukan reservasi
  seluruh kelompok dan tidak menjamin provider tidak gagal di tengah.

Kontrak HTTP dan authorization: [API](../../API.md). Matriks formula lengkap:
[SCORING](../../SCORING.md). Fake HTTP default, smoke real terarah terpisah.
