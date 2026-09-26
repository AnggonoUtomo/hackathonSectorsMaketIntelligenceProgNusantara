import CompanyAnalysisChart, { AnalysisTable, formatPeriod, formatValue } from '@/components/company-analysis-chart';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import type { ResearchSummary } from '@/types/research-summary';
import { ArrowRight, ChartNoAxesCombined, ChevronDown, FileSearch, LoaderCircle, RefreshCw, Table2 } from 'lucide-react';
import { useEffect, useRef, useState } from 'react';

type State = { status: 'idle' | 'loading' } | { status: 'error'; message: string } | { status: 'ready'; data: ResearchSummary };
const metrics = [
    { key: 'revenue', label: 'Pendapatan', color: '#0d9488' },
    { key: 'earnings', label: 'Laba bersih', color: '#0284c7' },
];
const timestamp = (value: string | null) =>
    value && !Number.isNaN(Date.parse(value))
        ? `${new Intl.DateTimeFormat('id-ID', { dateStyle: 'medium', timeStyle: 'short', timeZone: 'Asia/Jakarta' }).format(new Date(value))} WIB`
        : 'Waktu belum tersedia';

export default function ResearchSummaryPanel({ symbol, active }: { symbol: string; active: boolean }) {
    const [state, setState] = useState<State>({ status: 'idle' });
    const [expired, setExpired] = useState(false);
    const [view, setView] = useState<'chart' | 'table'>('chart');
    const request = useRef<AbortController | null>(null);
    useEffect(() => () => request.current?.abort(), []);
    useEffect(() => {
        if (state.status !== 'ready') return;
        const expiry = Math.min(
            Date.parse(state.data.sources.profile.fetchedAt ?? '') + 3600_000,
            Date.parse(state.data.sources.financials.fetchedAt ?? '') + 86400_000,
        );
        const timer = window.setTimeout(() => setExpired(true), Math.max(0, expiry - Date.now()) || 0);
        return () => window.clearTimeout(timer);
    }, [state]);

    async function load() {
        if (request.current) return;
        const controller = new AbortController();
        request.current = controller;
        setExpired(false);
        setState({ status: 'loading' });
        try {
            const response = await fetch(`/nusalens/companies/${encodeURIComponent(symbol)}/research`, {
                headers: { Accept: 'application/json' },
                signal: controller.signal,
            });
            if (response.status === 401 || response.status === 403)
                throw new Error('Sesi berakhir atau email belum diverifikasi. Silakan login kembali.');
            const data = await response.json();
            if (!response.ok) throw new Error(data.message ?? 'Ringkasan belum dapat dimuat.');
            if (data.symbol !== symbol || !Array.isArray(data.findings) || !Array.isArray(data.checks) || !data.sources?.financials)
                throw new Error('Format ringkasan belum dapat dibaca.');
            if (!controller.signal.aborted) setState({ status: 'ready', data });
        } catch (error) {
            if (!controller.signal.aborted)
                setState({
                    status: 'error',
                    message:
                        error instanceof Error && !(error instanceof SyntaxError) && !(error instanceof TypeError)
                            ? error.message
                            : 'Koneksi atau data belum dapat dibaca. Silakan coba lagi.',
                });
        } finally {
            if (request.current === controller) request.current = null;
        }
    }

    if (!active) return null;
    const ready = state.status === 'ready' && !expired;
    return (
        <section className="min-w-0 space-y-6" aria-label="Ringkasan riset">
            <header className="flex flex-wrap items-start justify-between gap-4">
                <div>
                    <h2 className="text-lg font-semibold">Ringkasan Riset</h2>
                    <p className="text-muted-foreground mt-1 text-sm">
                        {ready && state.data.period
                            ? `Laporan ${formatPeriod(state.data.period)}`
                            : 'Kinerja, bukti, dan hal yang masih perlu diperiksa.'}
                    </p>
                </div>
                {ready && <Badge variant="outline">Tanpa rekomendasi transaksi</Badge>}
            </header>
            {!ready && (
                <div className="space-y-4 border-y py-6">
                    {state.status === 'loading' ? (
                        <div role="status" className="flex items-center gap-3 text-sm">
                            <LoaderCircle className="size-4 animate-spin" />
                            Memuat bukti {symbol}...
                        </div>
                    ) : (
                        <>
                            {state.status === 'error' && (
                                <p role="alert" className="text-sm">
                                    {state.message}
                                </p>
                            )}
                            {expired && (
                                <p role="status" className="text-sm">
                                    Usia data melewati batas pembaruan. Muat ulang sebelum membaca temuan.
                                </p>
                            )}
                            <p className="text-muted-foreground max-w-2xl text-sm leading-6">
                                Empat laporan kuartalan dan profil perusahaan. Estimasi hingga 5 credit bila sumber belum tersimpan; data tersimpan
                                yang masih berlaku tidak memakai credit tambahan.
                            </p>
                            <Button onClick={load}>
                                {state.status === 'idle' ? <FileSearch className="size-4" /> : <RefreshCw className="size-4" />}
                                {state.status === 'idle' ? 'Buka ringkasan riset' : 'Muat ulang ringkasan'}
                            </Button>
                        </>
                    )}
                </div>
            )}
            {ready && (
                <>
                    <div className="space-y-1 divide-y">
                        {state.data.findings.length === 0 && (
                            <p role="status" className="py-4 text-sm">
                                {state.data.state === 'empty'
                                    ? 'Belum ada laporan kuartalan dari sumber.'
                                    : 'Bukti belum cukup untuk menyusun temuan.'}
                            </p>
                        )}
                        {state.data.findings.map((finding) => (
                            <article key={finding.id} className="min-w-0 py-5 first:pt-0">
                                <div className="flex flex-wrap items-start justify-between gap-3">
                                    <div className="min-w-0 flex-1 basis-64">
                                        <h3 className="font-medium">{finding.title}</h3>
                                        <p className="text-muted-foreground mt-1 text-sm leading-6">{finding.description}</p>
                                    </div>
                                    <p className="max-w-full text-lg font-semibold break-words tabular-nums">
                                        {finding.unit === 'IDR' ? 'Rp ' : ''}
                                        {formatValue(finding.value)}
                                        {finding.unit === '%' ? '%' : ''}
                                    </p>
                                </div>
                                <p className="mt-3 border-l-2 border-amber-500 pl-3 text-sm leading-6">{finding.limitation}</p>
                                <p className="text-muted-foreground mt-3 flex items-start gap-2 text-sm leading-6">
                                    <ArrowRight className="mt-1 size-4 shrink-0" />
                                    {finding.nextCheck}
                                </p>
                                <details className="group mt-4">
                                    <summary className="flex w-fit cursor-pointer list-none items-center gap-2 text-sm font-medium text-teal-700 focus-visible:outline-2 dark:text-teal-300">
                                        <ChevronDown className="size-4 transition-transform group-open:rotate-180" />
                                        Bukti dan perhitungan
                                    </summary>
                                    <div className="mt-3 overflow-x-auto rounded-md border">
                                        <table className="w-full min-w-128 text-sm">
                                            <caption className="text-muted-foreground p-3 text-left text-xs">
                                                Bukti laporan kuartalan dalam rupiah (IDR).
                                            </caption>
                                            <thead className="bg-muted text-xs uppercase">
                                                <tr>
                                                    <th className="p-3 text-left" scope="col">
                                                        Metrik
                                                    </th>
                                                    <th className="p-3 text-right" scope="col">
                                                        Nilai (IDR)
                                                    </th>
                                                    <th className="p-3 text-left" scope="col">
                                                        Periode / basis
                                                    </th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                {finding.evidence.map((evidence) => (
                                                    <tr key={evidence.field} className="border-t">
                                                        <th className="p-3 text-left font-normal" scope="row">
                                                            {evidence.label}
                                                            <span className="text-muted-foreground block text-xs">{evidence.field}</span>
                                                        </th>
                                                        <td className="p-3 text-right whitespace-nowrap tabular-nums">
                                                            {formatValue(evidence.value)}
                                                        </td>
                                                        <td className="p-3 whitespace-nowrap">
                                                            {formatPeriod(evidence.period)}
                                                            <span className="text-muted-foreground block text-xs">
                                                                {evidence.basis === 'quarterly' ? 'Selama kuartal' : 'Posisi pada tanggal laporan'}
                                                            </span>
                                                        </td>
                                                    </tr>
                                                ))}
                                            </tbody>
                                        </table>
                                    </div>
                                    <p className="mt-3 text-sm">
                                        <span className="font-medium">Aturan: </span>
                                        <code className="break-all">{finding.formula}</code>
                                    </p>
                                    <p className="text-muted-foreground mt-2 text-xs break-words">
                                        Sectors Financial API: {state.data.sources.financials.endpoint}. Diambil{' '}
                                        {timestamp(state.data.sources.financials.fetchedAt)}.
                                    </p>
                                    <p className="text-muted-foreground mt-1 text-xs">
                                        {state.data.ruleVersion} / {finding.id}
                                    </p>
                                </details>
                            </article>
                        ))}
                    </div>
                    {state.data.chartRows.some((row) => typeof row.revenue === 'number' || typeof row.earnings === 'number') && (
                        <section className="min-w-0 space-y-4 border-t pt-6">
                            <div className="flex flex-wrap items-center justify-between gap-3">
                                <div>
                                    <h3 className="font-medium">Pendapatan dan laba per kuartal</h3>
                                    <p className="text-muted-foreground mt-1 text-xs">Miliar rupiah. Bukan perhitungan pertumbuhan tahunan.</p>
                                </div>
                                <div role="group" aria-label="Tampilan bukti keuangan" className="flex gap-1">
                                    {(
                                        [
                                            { key: 'chart', label: 'Grafik bukti', Icon: ChartNoAxesCombined },
                                            { key: 'table', label: 'Tabel bukti', Icon: Table2 },
                                        ] as const
                                    ).map(({ key, label, Icon }) => (
                                        <Button
                                            key={key}
                                            variant={view === key ? 'secondary' : 'ghost'}
                                            size="icon"
                                            aria-label={label}
                                            title={label}
                                            aria-pressed={view === key}
                                            onClick={() => setView(key)}
                                        >
                                            <Icon className="size-4" />
                                        </Button>
                                    ))}
                                </div>
                            </div>
                            {view === 'chart' ? (
                                <CompanyAnalysisChart rows={state.data.chartRows} metrics={metrics} financial />
                            ) : (
                                <AnalysisTable rows={state.data.chartRows} metrics={metrics} financial />
                            )}
                        </section>
                    )}
                    <section className="border-t pt-6">
                        <h3 className="font-medium">Yang masih perlu diperiksa</h3>
                        <div className="mt-3 divide-y">
                            {state.data.checks.map((check) => (
                                <details key={check.id} className="py-3">
                                    <summary className="cursor-pointer text-sm font-medium">{check.title}</summary>
                                    <p className="text-muted-foreground mt-2 text-sm leading-6">{check.reason}</p>
                                    <p className="mt-2 text-sm leading-6">{check.nextCheck}</p>
                                </details>
                            ))}
                        </div>
                    </section>
                    <footer className="text-muted-foreground space-y-2 border-t pt-4 text-xs leading-6">
                        <p>
                            Klasifikasi sumber: {state.data.classification.sector ?? 'Belum tersedia'} /{' '}
                            {state.data.classification.subSector ?? 'Belum tersedia'} / {state.data.classification.industry ?? 'Belum tersedia'}.
                            Sumber: {state.data.sources.profile.endpoint}, section overview.
                        </p>
                        <p>
                            Profil diambil {timestamp(state.data.sources.profile.fetchedAt)}. Laporan diambil{' '}
                            {timestamp(state.data.sources.financials.fetchedAt)}.
                        </p>
                        <p>Waktu pengambilan berbeda dari periode laporan. Data yang baru diambil belum tentu mencakup laporan terbaru perusahaan.</p>
                        <p>Ringkasan berbasis data tersedia, bukan penilaian lengkap atau rekomendasi membeli/menjual saham.</p>
                    </footer>
                </>
            )}
        </section>
    );
}
