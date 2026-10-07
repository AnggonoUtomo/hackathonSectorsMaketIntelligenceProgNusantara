import type { ScoreMetric } from '@/types/research-priority';

const definitions: Record<string, { formula: string; fields: Array<[string, string]> }> = {
    roe: {
        formula: 'Rasio ROE sumber dikonversi ke persen (x 100); ekuitas harus positif.',
        fields: [
            ['roe', 'ROE sumber (dalam persen)'],
            ['equity', 'Ekuitas (Rp)'],
        ],
    },
    roa: {
        formula: 'Rasio ROA sumber dikonversi ke persen (x 100); aset harus positif.',
        fields: [
            ['roa', 'ROA sumber (dalam persen)'],
            ['assets', 'Aset (Rp)'],
        ],
    },
    revenueGrowth: {
        formula: '(Pendapatan sekarang / pendapatan tahun lalu - 1) x 100',
        fields: [
            ['current', 'Pendapatan sekarang (Rp)'],
            ['previous', 'Pendapatan tahun lalu (Rp)'],
            ['previousPeriod', 'Periode pembanding'],
        ],
    },
    earningsGrowth: {
        formula: '(Laba sekarang / laba tahun lalu - 1) x 100',
        fields: [
            ['current', 'Laba sekarang (Rp)'],
            ['previous', 'Laba tahun lalu (Rp)'],
            ['previousPeriod', 'Periode pembanding'],
        ],
    },
    pe: {
        formula: 'Kapitalisasi pasar / laba empat kuartal (TTM)',
        fields: [
            ['marketCap', 'Kapitalisasi pasar (Rp)'],
            ['earningsTTM', 'Laba empat kuartal (Rp)'],
            ['priceDate', 'Tanggal harga'],
        ],
    },
    pb: {
        formula: 'Kapitalisasi pasar / ekuitas kuartal terbaru (MRQ)',
        fields: [
            ['marketCap', 'Kapitalisasi pasar (Rp)'],
            ['equity', 'Ekuitas kuartalan (Rp)'],
            ['priceDate', 'Tanggal harga'],
        ],
    },
    der: {
        formula: 'Utang berbunga / ekuitas',
        fields: [
            ['debt', 'Utang berbunga (Rp)'],
            ['equity', 'Ekuitas (Rp)'],
        ],
    },
    currentRatio: {
        formula: 'Aset lancar / liabilitas lancar',
        fields: [
            ['currentAssets', 'Aset lancar (Rp)'],
            ['currentLiabilities', 'Liabilitas lancar (Rp)'],
        ],
    },
    car: {
        formula: 'Modal / aset tertimbang menurut risiko x 100',
        fields: [
            ['capital', 'Modal (Rp)'],
            ['riskWeightedAssets', 'Aset tertimbang menurut risiko (Rp)'],
        ],
    },
    npl: {
        formula: 'Kredit bermasalah / kredit bruto x 100',
        fields: [
            ['nonPerformingLoan', 'Kredit bermasalah (Rp)'],
            ['grossLoan', 'Kredit bruto (Rp)'],
        ],
    },
};

function value(input: unknown, percent = false) {
    if (typeof input === 'number' && Number.isFinite(input)) {
        return new Intl.NumberFormat('id-ID', { style: percent ? 'percent' : 'decimal', minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(
            input,
        );
    }
    return typeof input === 'string' && input !== '' ? input : 'Belum tersedia';
}

export default function ScoreInputEvidence({ metric }: { metric: ScoreMetric }) {
    const definition = definitions[metric.key];
    const prices = Array.isArray(metric.inputs) ? (metric.inputs as Array<{ date?: string; close?: number }>) : [];
    const rows = definition
        ? definition.fields.map(([key, label]) => ({ key, label, input: metric.inputs[key] }))
        : prices.map((price, index) => ({ key: String(index), label: price.date ?? `Sesi ${index + 1}`, input: price.close }));
    return (
        <div className="space-y-3">
            <h4 className="text-xs font-semibold uppercase">Input dan rumus</h4>
            <p className="text-muted-foreground text-sm">
                {definition?.formula ?? '(Penutupan akhir / penutupan 20 sesi sebelumnya - 1) x 100; basis harga harus terverifikasi.'}
            </p>
            {rows.length > 0 ? (
                <div className="overflow-x-auto rounded-md border">
                    <table className="w-full text-sm">
                        <caption className="sr-only">Input tersimpan untuk {metric.label}</caption>
                        <thead className="bg-muted text-xs uppercase">
                            <tr>
                                <th scope="col" className="p-2 text-left">
                                    Input
                                </th>
                                <th scope="col" className="p-2 text-right">
                                    Nilai sumber
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            {rows.map((row) => (
                                <tr key={row.key} className="border-t">
                                    <th scope="row" className="p-2 text-left font-normal">
                                        {row.label}
                                    </th>
                                    <td
                                        className="p-2 text-right whitespace-nowrap tabular-nums"
                                        title={typeof row.input === 'number' ? `Input asli: ${row.input}` : undefined}
                                    >
                                        {value(row.input, row.key === 'roe' || row.key === 'roa')}
                                    </td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                </div>
            ) : (
                <p className="text-muted-foreground text-sm">Input metrik belum tersedia pada bukti ini.</p>
            )}
            <p className="text-muted-foreground text-xs leading-6">
                Percentile = 100 x (rank rata-rata - 1) / (jumlah populasi valid - 1).
                {!metric.higherIsBetter && ' Posisi akhir = 100 - percentile karena nilai mentah yang lebih kecil mendapat posisi lebih besar.'} Bobot
                awal metrik dalam komponen {value(metric.originalWeight * 100)}%; bobot efektif {value(metric.effectiveWeight * 100)}%.
            </p>
        </div>
    );
}
