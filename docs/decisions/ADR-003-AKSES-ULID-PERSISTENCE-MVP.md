# ADR-003: Akses, ULID, dan Persistence MVP

## Status

Accepted berdasarkan wawancara dan konfirmasi akhir user. Melengkapi ADR-001
dan ADR-002; bukan izin implementasi otomatis.

## Date

2026-09-20

## Context

Registrasi ditujukan untuk umum, tetapi pemakaian API terbatas dan perbandingan
tersimpan bersifat pribadi. Skor harus dapat direproduksi walau data provider
berubah. Starter Laravel/Inertia sudah tersedia dengan users integer dan belum
mewajibkan verifikasi email pada akses riset.

## Decision

- Registrasi publik; semua riset memerlukan login dan email verified.
- ULID untuk entitas internal, termasuk users dan FK terkait. Key teknis
  framework tidak otomatis dikonversi. Migrasi memerlukan inventarisasi data.
- MarketData memiliki cache/freshness serta ledger/reservasi credit di MySQL;
  Redis hilang tidak mereset budget atau kuota.
- Company memiliki fakta/snapshot keuangan dan pasar; Intelligence memiliki
  hasil, versi formula, dan bukti input/peer immutable untuk reproduksi.
- Hasil identik boleh digunakan lintas akun, tanpa berbagi data pribadi.
- Snapshot historis dipertahankan 30 hari; referensi hasil aktif/perbandingan
  tersimpan mempertahankan seluruh bukti yang dibutuhkan lebih lama.
- Comparison memiliki perbandingan privat maksimal 3 saham, simpan manual,
  penamaan/hapus, dan versi baru saat pembaruan. Tidak menimpa hasil lama.
- Screening/Research memiliki data fiturnya, tetapi tidak membuat riwayat
  screener otomatis atau laporan tersimpan terpisah pada MVP.

## Alternatives Considered

- Akses riset anonim: tidak dipilih; user meminta login wajib.
- Registrasi tertutup: tidak dipilih; user menargetkan umum.
- Mempertahankan integer users: user menyetujui ULID termasuk users.
- Menghitung tanpa bukti persisten atau menimpa snapshot: tidak memenuhi kebutuhan
  reproduksi dan perbandingan historis.
- Budget hanya di Redis: kehilangan cache dapat menghapus batas konsumsi.
- Menyimpan semua aktivitas: memperluas MVP tanpa kebutuhan; simpan manual cukup.

## Consequences

Perlu migrasi users/FK yang aman, test email verification dan akses lintas akun,
reservasi atomik untuk request/retry, serta cleanup yang menjaga referensi bukti.
Tidak menambahkan module billing atau seluruh tabel sekaligus. ULID bukan akses
kontrol. Retensi bukan freshness; skor historis tidak otomatis layak untuk saat ini.

## Verification

Keputusan didokumentasikan; runtime belum diubah pada pekerjaan ini. Work item
implementasi harus menguji migration pada database disposable, akses guest dan
unverified, kepemilikan perbandingan, concurrency credit, reproduksi skor, dan
retensi dengan shared references. Test Sectors memakai fake HTTP.

## References

- [Keputusan aktif](../DECISIONS.md).
- [Model Data](../DATA-MODEL.md).
- [Keamanan](../SECURITY.md).
- [Alur Data](../DATA-FLOW.md).
- [Scoring](../SCORING.md).
