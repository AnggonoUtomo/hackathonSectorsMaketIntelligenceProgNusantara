<?php

namespace App\Modules\Research\Domain;

use DateTimeImmutable;
use DateTimeZone;
use Exception;

class ResearchSummary
{
    public function build(array $company, array $financials, DateTimeImmutable $now): array
    {
        $profileStatus = $this->freshness($company['fetchedAt'] ?? null, $now, 3600);
        $financialStatus = $this->freshness($financials['fetchedAt'] ?? null, $now, 86400);
        $kind = $profileStatus === 'fresh' ? $this->kind($company) : 'unknown';
        $rows = $financials['rows'];
        usort($rows, fn (array $a, array $b) => strcmp($a['date'], $b['date']));
        $latest = $rows === [] ? null : $rows[array_key_last($rows)];
        $period = $latest['date'] ?? null;
        $result = [
            'symbol' => $company['symbol'], 'ruleVersion' => 'research-facts-v1',
            'classification' => ['sector' => $company['sector'] ?? null, 'subSector' => $company['subSector'] ?? null,
                'industry' => $company['industry'] ?? null],
            'companyKind' => $kind, 'period' => $period, 'state' => 'unavailable', 'score' => null,
            'sources' => [
                'profile' => ['endpoint' => 'company/report/'.$company['symbol'], 'section' => 'overview',
                    'fetchedAt' => $company['fetchedAt'] ?? null, 'status' => $profileStatus],
                'financials' => ['endpoint' => 'financials/quarterly/'.$company['symbol'], 'section' => null,
                    'fetchedAt' => $financials['fetchedAt'] ?? null, 'status' => $financialStatus],
            ],
            'findings' => [], 'checks' => [], 'chartRows' => [],
        ];
        $checks = [];
        if ($financialStatus !== 'fresh') {
            $checks[] = $this->check('freshness', 'Usia data perlu diperbarui',
                'Data keuangan sudah melewati 24 jam atau waktu sumber tidak valid.', 'Muat ulang sumber sebelum membaca temuan.');
        } elseif ($latest === null) {
            $result['state'] = 'empty';
            $checks[] = $this->check('empty', 'Laporan belum tersedia',
                'Sumber tidak mengembalikan laporan kuartalan.', 'Periksa ketersediaan laporan perusahaan di sumber.');
        } elseif (! $this->validPeriod($period, $now)) {
            $checks[] = $this->check('period', 'Periode belum dapat digunakan',
                'Tanggal laporan bukan akhir kuartal yang valid atau berada di masa depan.', 'Periksa periode laporan sebelum menarik kesimpulan.');
        } else {
            $earnings = $this->value($latest, $financials, 'earnings', 'quarterly');
            $revenue = $this->value($latest, $financials, 'revenue', 'quarterly');
            $equity = $this->value($latest, $financials, 'total_equity', 'instant');
            if ($earnings !== null) {
                $result['findings'][] = $this->finding('earnings',
                    $earnings > 0 ? 'Perusahaan mencatat laba kuartalan' : ($earnings < 0 ? 'Perusahaan mencatat rugi kuartalan' : 'Laba bersih tercatat nol'),
                    'Laba bersih pada laporan kuartal terakhir yang dikembalikan sumber.', $earnings, 'IDR',
                    'earnings > 0: laba; earnings < 0: rugi; earnings = 0: nol',
                    [$this->evidence('earnings', 'Laba bersih', $earnings, $period, 'quarterly')],
                    'Satu kuartal tidak membuktikan pertumbuhan, keberlanjutan laba, atau kelayakan investasi.',
                    'Periksa kuartal yang sama tahun sebelumnya dan komponen laba yang tidak berulang.');
            } else {
                $checks[] = $this->check('earnings', 'Laba belum dapat dijelaskan',
                    'Laba terbaru hilang atau basis/satuan belum terverifikasi; angka lama tidak menggantikannya.', 'Periksa laba pada laporan periode ini.');
            }
            if ($kind === 'non_financial') {
                $margin = $earnings !== null && $revenue !== null && $revenue > 0 ? $earnings / $revenue * 100 : null;
                if ($margin !== null && is_finite($margin)) {
                    $result['findings'][] = $this->finding('net_margin', 'Porsi laba terhadap pendapatan',
                        'Bagian pendapatan yang menjadi laba bersih pada kuartal yang sama.', $margin, '%',
                        'earnings / revenue * 100',
                        [$this->evidence('earnings', 'Laba bersih', $earnings, $period, 'quarterly'),
                            $this->evidence('revenue', 'Pendapatan', $revenue, $period, 'quarterly')],
                        'Belum dibandingkan dengan perusahaan sejenis; margin bukan ukuran murah atau mahalnya saham.',
                        'Periksa beban usaha dan kejadian tidak berulang pada periode ini.');
                } else {
                    $checks[] = $this->check('net_margin', 'Margin belum dapat dihitung',
                        'Diperlukan laba dan pendapatan kuartalan IDR yang sebanding, dengan pendapatan lebih dari nol.',
                        'Periksa nilai dan basis kedua metrik pada laporan yang sama.');
                }
            }
            if ($equity !== null) {
                $result['findings'][] = $this->finding('equity',
                    $equity > 0 ? 'Ekuitas tercatat positif' : ($equity < 0 ? 'Ekuitas tercatat negatif' : 'Ekuitas tercatat nol'),
                    'Posisi ekuitas pada tanggal laporan, bukan arus pendapatan selama kuartal.', $equity, 'IDR',
                    'total_equity > 0: positif; total_equity < 0: negatif; total_equity = 0: nol',
                    [$this->evidence('total_equity', 'Ekuitas', $equity, $period, 'instant')],
                    'Tanda ekuitas saja tidak mengukur likuiditas, kecukupan modal, atau kemampuan membayar kewajiban.',
                    $kind === 'bank' ? 'Periksa CAR dan NPL pada laporan bank.' : 'Periksa komposisi modal dan kewajiban perusahaan.');
            } else {
                $checks[] = $this->check('equity', 'Posisi ekuitas belum tersedia',
                    'Ekuitas pada tanggal laporan hilang atau satuannya belum terverifikasi.', 'Periksa laporan posisi keuangan periode ini.');
            }
            foreach ($rows as $row) {
                if ($this->validPeriod($row['date'], $now)) {
                    $result['chartRows'][] = ['date' => $row['date'],
                        'revenue' => $this->value($row, $financials, 'revenue', 'quarterly'),
                        'earnings' => $this->value($row, $financials, 'earnings', 'quarterly')];
                }
            }
            $result['state'] = $result['findings'] === [] ? 'unavailable' : 'ready';
        }
        $checks[] = $this->check('cash_basis', 'Hubungan laba dan kas belum disimpulkan',
            'Basis kuartalan atau kumulatif arus kas belum terverifikasi.', 'Cocokkan cakupan periode arus kas dengan laba sebelum membandingkannya.');
        $checks[] = $this->check('yoy', 'Pertumbuhan tahunan belum dihitung',
            'Permintaan empat kuartal belum menjamin tersedianya kuartal pembanding tahun sebelumnya.', 'Periksa kuartal yang sama tahun sebelumnya, bukan hanya kuartal sebelumnya.');
        if ($kind === 'bank' || $kind === 'financial_nonbank') {
            $checks[] = $this->check('sector_risk', 'Risiko sektor belum dinilai',
                $kind === 'bank' ? 'Data CAR dan NPL belum dimuat.' : 'Paket risiko perusahaan keuangan nonbank belum ditetapkan.',
                'Gunakan ukuran risiko yang sesuai jenis perusahaan; jangan memakai rumus perusahaan industri.');
        } elseif ($kind === 'unknown') {
            $checks[] = $this->check('classification', 'Klasifikasi perlu dipastikan',
                'Sektor tidak dikenal, belum tersedia, atau sumber profil kedaluwarsa.', 'Periksa sektor perusahaan sebelum menerapkan interpretasi khusus.');
        }
        $checks[] = $this->check('score', 'Nilai Prioritas Riset belum tersedia',
            'Metrik scoring dan bukti peer lengkap belum dihitung.', 'Lengkapi data yang sebanding sebelum menilai prioritas riset.');
        $result['checks'] = $checks;

        return $result;
    }

    private function finding(string $id, string $title, string $description, float $value, string $unit, string $formula, array $evidence, string $limitation, string $nextCheck): array
    {
        return compact('id', 'title', 'description', 'value', 'unit', 'formula', 'evidence', 'limitation', 'nextCheck');
    }

    private function evidence(string $field, string $label, float $value, string $period, string $basis): array
    {
        return compact('field', 'label', 'value', 'period', 'basis') + ['unit' => 'IDR', 'sourceId' => 'financials'];
    }

    private function check(string $id, string $title, string $reason, string $nextCheck): array
    {
        return compact('id', 'title', 'reason', 'nextCheck');
    }

    private function value(array $row, array $financials, string $field, string $basis): ?float
    {
        $value = $row[$field] ?? null;

        return ($financials['currency'] ?? null) === 'IDR' && ($financials['basis'][$field] ?? null) === $basis
            && (is_int($value) || is_float($value)) && is_finite((float) $value) ? (float) $value : null;
    }

    private function kind(array $company): string
    {
        $sector = strtolower(trim($company['sector'] ?? ''));
        $subsector = strtolower(trim($company['subSector'] ?? ''));
        $industry = strtolower(trim($company['industry'] ?? ''));
        if ($sector === 'financials') {
            return $subsector === 'banks' && ($industry === 'banks' || $industry === '') ? 'bank' : 'financial_nonbank';
        }
        $nonFinancial = ['energy', 'basic materials', 'industrials', 'consumer non-cyclicals', 'consumer cyclicals',
            'healthcare', 'properties & real estate', 'technology', 'infrastructures', 'transportation & logistic', 'transportation & logistics'];

        return in_array($sector, $nonFinancial, true) ? 'non_financial' : 'unknown';
    }

    private function freshness(?string $value, DateTimeImmutable $now, int $ttl): string
    {
        if ($value === null || ! preg_match('/\A\d{4}-\d{2}-\d{2}T/', $value)) {
            return 'unknown';
        }
        try {
            $age = $now->getTimestamp() - (new DateTimeImmutable($value))->getTimestamp();

            return $age < 0 ? 'unknown' : ($age >= $ttl ? 'stale' : 'fresh');
        } catch (Exception) {
            return 'unknown';
        }
    }

    private function validPeriod(string $period, DateTimeImmutable $now): bool
    {
        $date = DateTimeImmutable::createFromFormat('!Y-m-d', $period);

        return $date && $date->format('Y-m-d') === $period
            && in_array($date->format('m-d'), ['03-31', '06-30', '09-30', '12-31'], true)
            && $period <= $now->setTimezone(new DateTimeZone('Asia/Jakarta'))->format('Y-m-d');
    }
}
