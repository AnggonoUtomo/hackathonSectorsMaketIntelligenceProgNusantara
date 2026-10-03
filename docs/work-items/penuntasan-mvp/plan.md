# Rencana Penuntasan

1. Buktikan projection Companies Screener dengan smoke terbatas melalui adapter
   dan ledger; maksimal 6 credit untuk pemeriksaan awal. Jangan menaikkan kuota.
2. Intelligence Domain pure PHP: normalisasi, eligibility, semua peer pada kelompok
   kompatibel, fallback hierarki/periode, percentile, bobot dan kelengkapan.
3. MarketData menyediakan input melalui public contract. Projection terstruktur
   memakai periode eksplisit, pagination lengkap dan cache 24 jam untuk fundamental.
   Data pasar campuran memakai 1 jam. Growth memakai kuartal eksplisit; momentum
   tanpa bukti adjusted close tidak dihitung. Annual tidak menggantikan TTM/MRQ.
   Valuasi tambahan on-demand memakai kapitalisasi Daily / jumlah laba empat
   kuartal dan ekuitas kuartal yang eksplisit, tanggal harga sama antar-peer.
   Preflight kuota untuk seluruh kelompok sebelum fan-out. Tanggal Daily tanpa
   timestamp diuji konservatif sejak awal hari; pengecualian libur belum dibuktikan.
4. Bukti immutable Intelligence disimpan MySQL dengan ULID, hash input/config,
   payload JSON tanpa pembulatan antara, waktu sumber dan formula version.
   IEEE-754 binary64 untuk kalkulasi nonmoneter; tampilan 2 desimal saja.
5. Cockpit dan Comparison mengonsumsi hasil yang sama. Snapshot compare mengambil
   data terpercaya dari server; payload browser bukan otoritas angka.
6. Rapikan navigasi, dashboard dan panduan untuk alur riset yang selesai.

Verifikasi: unit domain, integration fake HTTP, auth/ownership/tamper/replay,
seluruh PHPUnit, typecheck, lint, build, pemeriksaan browser desktop/mobile.
Migration additive saja. Tidak reset data. Commit/push tetap menunggu instruksi.
