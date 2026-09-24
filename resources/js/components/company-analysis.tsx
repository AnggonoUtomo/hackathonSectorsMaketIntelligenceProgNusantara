import CompanyAnalysisChart, {
    AnalysisTable,
    formatPeriod,
    formatValue,
    type AnalysisMetric,
    type AnalysisRow,
} from '@/components/company-analysis-chart';
import { Button } from '@/components/ui/button';
import { ChartNoAxesCombined, RefreshCw, Table2 } from 'lucide-react';
import { useEffect, useRef, useState, type ReactNode } from 'react';

type Section = 'prices' | 'financials' | 'valuation';
type Result = { symbol: string; section: Section; rows: AnalysisRow[]; fetchedAt: string; range?: { start: string; end: string } };
type State = { status: 'loading' } | { status: 'error'; message: string } | { status: 'ready'; data: Result };
const tabs = [
    { key: 'overview', label: 'Profil' },
    { key: 'prices', label: 'Harga' },
    { key: 'financials', label: 'Keuangan' },
    { key: 'valuation', label: 'Valuasi' },
] as const;
const metric = (key: string, label: string, color = '#0d9488'): AnalysisMetric => ({ key, label, color });
const groups = {
    performance: { label: 'Pendapatan dan laba', metrics: [metric('revenue', 'Pendapatan'), metric('earnings', 'Laba bersih', '#0284c7')] },
    cash: { label: 'Arus kas', metrics: [metric('operating_cash_flow', 'Kas operasi'), metric('free_cash_flow', 'Arus kas bebas', '#0284c7')] },
    balance: {
        label: 'Posisi keuangan',
        metrics: [
            metric('total_assets', 'Aset'),
            metric('total_equity', 'Ekuitas', '#0284c7'),
            metric('total_liabilities', 'Liabilitas', '#d97706'),
            metric('total_debt', 'Utang berbunga', '#e11d48'),
        ],
    },
    bank: { label: 'Kredit dan simpanan', metrics: [metric('gross_loan', 'Kredit bruto'), metric('total_deposit', 'Simpanan nasabah', '#0284c7')] },
    interest: {
        label: 'Pendapatan bunga',
        metrics: [metric('interest_income', 'Pendapatan bunga'), metric('net_interest_income', 'Bunga bersih', '#0284c7')],
    },
};

export default function CompanyAnalysis({ symbol, children }: { symbol: string; children: ReactNode }) {
    const [tab, setTab] = useState<Section | 'overview'>('overview');
    const cache = useRef<Partial<Record<Section, Result>>>({});
    return (
        <section className="min-w-0">
            <div role="tablist" aria-label="Data perusahaan" className="mb-6 flex overflow-x-auto border-b">
                {tabs.map((item, index) => (
                    <button
                        key={item.key}
                        id={`tab-${item.key}`}
                        type="button"
                        role="tab"
                        aria-selected={tab === item.key}
                        aria-controls="company-analysis-panel"
                        tabIndex={tab === item.key ? 0 : -1}
                        className={`shrink-0 border-b-2 px-3 py-3 text-sm font-medium focus-visible:outline-2 focus-visible:outline-offset-[-2px] sm:px-4 ${tab === item.key ? 'border-teal-600 text-teal-700 dark:text-teal-300' : 'text-muted-foreground hover:text-foreground border-transparent'}`}
                        onClick={() => setTab(item.key)}
                        onKeyDown={(event) => {
                            const next =
                                event.key === 'ArrowRight'
                                    ? (index + 1) % tabs.length
                                    : event.key === 'ArrowLeft'
                                      ? (index + tabs.length - 1) % tabs.length
                                      : event.key === 'Home'
                                        ? 0
                                        : event.key === 'End'
                                          ? tabs.length - 1
                                          : null;
                            if (next !== null) {
                                event.preventDefault();
                                document.getElementById(`tab-${tabs[next].key}`)?.focus();
                                setTab(tabs[next].key);
                            }
                        }}
                    >
                        {item.label}
                    </button>
                ))}
            </div>
            <div id="company-analysis-panel" role="tabpanel" aria-labelledby={`tab-${tab}`} tabIndex={0} className="min-w-0 space-y-6">
                {tab === 'overview' ? (
                    children
                ) : (
                    <AnalysisPanel
                        key={`${symbol}-${tab}`}
                        symbol={symbol}
                        section={tab}
                        cached={cache.current[tab]}
                        remember={(data) => {
                            cache.current[data.section] = data;
                        }}
                    />
                )}
            </div>
        </section>
    );
}

function AnalysisPanel({
    symbol,
    section,
    cached,
    remember,
}: {
    symbol: string;
    section: Section;
    cached?: Result;
    remember: (data: Result) => void;
}) {
    const [state, setState] = useState<State>({ status: 'loading' });
    const [attempt, setAttempt] = useState(0);
    const [days, setDays] = useState(90);
    const [view, setView] = useState<'chart' | 'table'>('chart');
    const [group, setGroup] = useState<keyof typeof groups>('performance');
    const rememberRef = useRef(remember);
    rememberRef.current = remember;
    useEffect(() => {
        if (attempt === 0 && cached && Date.now() - Date.parse(cached.fetchedAt) < (section === 'financials' ? 86400 : 3600) * 1000) {
            setState({ status: 'ready', data: cached });
            return;
        }
        const controller = new AbortController();
        setState({ status: 'loading' });
        fetch(`/nusalens/companies/${encodeURIComponent(symbol)}/analysis?section=${section}`, {
            signal: controller.signal,
            headers: { Accept: 'application/json' },
        })
            .then(async (response) => {
                const data = await response.json();
                if (!response.ok)
                    throw new Error(
                        response.status === 401 || response.status === 403
                            ? 'Sesi berakhir atau email belum diverifikasi. Silakan login kembali.'
                            : (data.message ?? 'Data belum dapat dimuat.'),
                    );
                if (data.symbol !== symbol || data.section !== section || !Array.isArray(data.rows))
                    throw new Error('Format data belum dapat dibaca.');
                if (!controller.signal.aborted) {
                    rememberRef.current(data);
                    setState({ status: 'ready', data });
                }
            })
            .catch((error) => {
                if (!controller.signal.aborted)
                    setState({
                        status: 'error',
                        message:
                            error instanceof TypeError
                                ? 'Koneksi terputus. Silakan coba lagi.'
                                : error instanceof Error && !(error instanceof SyntaxError)
                                  ? error.message
                                  : 'Data belum dapat dimuat. Silakan coba lagi.',
                    });
            });
        return () => controller.abort();
    }, [symbol, section, cached, attempt]);

    if (state.status === 'loading')
        return (
            <div role="status" className="space-y-4 py-6">
                <p className="text-muted-foreground text-sm">Memuat data {symbol}...</p>
                <div className="bg-muted h-80 animate-pulse rounded-md" />
            </div>
        );
    if (state.status === 'error')
        return (
            <div role="alert" className="space-y-4 border-y py-8">
                <p className="text-sm">{state.message}</p>
                <Button variant="outline" onClick={() => setAttempt((n) => n + 1)}>
                    <RefreshCw className="size-4" />
                    Coba lagi
                </Button>
            </div>
        );
    const { data } = state;
    const end =
        data.range?.end ??
        new Intl.DateTimeFormat('en-CA', { year: 'numeric', month: '2-digit', day: '2-digit', timeZone: 'Asia/Jakarta' }).format(
            new Date(data.fetchedAt),
        );
    const cutoff = new Date(`${end}T00:00:00Z`);
    cutoff.setUTCDate(cutoff.getUTCDate() - days + 1);
    const rows = section === 'prices' ? data.rows.filter((row) => row.date >= cutoff.toISOString().slice(0, 10)) : data.rows;
    const metrics =
        section === 'prices'
            ? [metric('close', 'Penutupan (Rp)')]
            : section === 'valuation'
              ? [metric('pe', 'P/E'), metric('pb', 'P/B', '#0284c7'), metric('ps', 'P/S', '#d97706')]
              : groups[group].metrics;
    const tableMetrics =
        section === 'prices'
            ? [
                  ...metrics,
                  metric('open', 'Pembukaan (Rp)'),
                  metric('high', 'Tertinggi (Rp)'),
                  metric('low', 'Terendah (Rp)'),
                  metric('volume', 'Volume (lembar)'),
              ]
            : section === 'valuation'
              ? [...metrics, metric('pcf', 'P/CF'), metric('enterprise_to_ebitda', 'EV/EBITDA')]
              : metrics;
    const hasValues = rows.some((row) => metrics.some((m) => typeof row[m.key] === 'number'));
    const latest = rows.at(-1);
    return (
        <>
            <div className="flex flex-wrap items-start justify-between gap-4">
                <div>
                    <h2 className="text-base font-semibold">
                        {section === 'prices'
                            ? 'Pergerakan harga'
                            : section === 'financials'
                              ? 'Kinerja dan posisi keuangan'
                              : 'Rasio valuasi historis'}
                    </h2>
                    <p className="text-muted-foreground mt-1 text-xs">
                        {section === 'prices'
                            ? 'Harga penutupan harian dalam rupiah; bukan harga real-time.'
                            : section === 'financials'
                              ? 'Empat laporan kuartalan terbaru. Grafik dalam miliar rupiah.'
                              : 'Rasio dalam kali (x), per tahun sumber. Tahun berjalan belum final.'}
                    </p>
                </div>
                <div className="flex flex-wrap items-center gap-2">
                    {section === 'prices' && (
                        <div role="group" aria-label="Rentang harga" className="flex rounded-md border p-1">
                            {[30, 90].map((n) => (
                                <button
                                    type="button"
                                    key={n}
                                    aria-pressed={days === n}
                                    onClick={() => setDays(n)}
                                    className={`rounded px-3 py-1.5 text-xs ${days === n ? 'bg-muted font-semibold' : ''}`}
                                >
                                    {n} hari
                                </button>
                            ))}
                        </div>
                    )}
                    {section === 'financials' && (
                        <select
                            aria-label="Kelompok metrik keuangan"
                            value={group}
                            onChange={(event) => setGroup(event.target.value as keyof typeof groups)}
                            className="bg-background max-w-full rounded-md border px-3 py-2 text-sm"
                        >
                            {Object.entries(groups)
                                .filter(
                                    ([key, g]) =>
                                        !['bank', 'interest'].includes(key) ||
                                        data.rows.some((row) => g.metrics.some((m) => typeof row[m.key] === 'number')),
                                )
                                .map(([key, g]) => (
                                    <option key={key} value={key}>
                                        {g.label}
                                    </option>
                                ))}
                        </select>
                    )}
                    <div role="group" aria-label="Tampilan data" className="flex gap-1">
                        {(
                            [
                                { key: 'chart', label: 'Grafik', Icon: ChartNoAxesCombined },
                                { key: 'table', label: 'Tabel', Icon: Table2 },
                            ] as const
                        ).map(({ key, label, Icon }) => (
                            <Button
                                key={key}
                                size="icon"
                                variant={view === key ? 'secondary' : 'ghost'}
                                title={label}
                                aria-label={label}
                                aria-pressed={view === key}
                                onClick={() => setView(key)}
                            >
                                <Icon className="size-4" />
                            </Button>
                        ))}
                    </div>
                </div>
            </div>
            {latest && (
                <dl className="grid gap-4 border-y py-4 sm:grid-cols-2 xl:grid-cols-4">
                    {metrics.map((m) => (
                        <div key={m.key} className="min-w-0">
                            <dt className="text-muted-foreground text-xs">
                                {m.label} <span className="block">{formatPeriod(latest.date)}</span>
                            </dt>
                            <dd className="mt-2 text-lg font-semibold break-words tabular-nums">
                                {formatValue(typeof latest[m.key] === 'number' ? Number(latest[m.key]) / (section === 'financials' ? 1e9 : 1) : null)}
                                {typeof latest[m.key] === 'number' && (section === 'financials' ? ' M' : section === 'valuation' ? 'x' : '')}
                            </dd>
                        </div>
                    ))}
                </dl>
            )}
            {rows.length === 0 ? (
                <p role="status" className="text-muted-foreground py-12 text-center text-sm">
                    Belum ada data untuk periode ini.
                </p>
            ) : view === 'table' ? (
                <AnalysisTable rows={rows} metrics={tableMetrics} financial={section === 'financials'} />
            ) : hasValues ? (
                <CompanyAnalysisChart rows={rows} metrics={metrics} financial={section === 'financials'} />
            ) : (
                <p role="status" className="text-muted-foreground py-12 text-center text-sm">
                    Metrik ini belum tersedia pada laporan sumber.
                </p>
            )}
            <div className="text-muted-foreground space-y-1 border-t pt-4 text-xs leading-6">
                {rows.length > 0 && (
                    <p>
                        Periode sumber: {formatPeriod(rows[0].date)} sampai {formatPeriod(rows[rows.length - 1].date)}.
                    </p>
                )}
                <p>
                    Sectors Financial API. Diambil{' '}
                    {new Intl.DateTimeFormat('id-ID', { dateStyle: 'medium', timeStyle: 'short', timeZone: 'Asia/Jakarta' }).format(
                        new Date(data.fetchedAt),
                    )}{' '}
                    WIB.
                </p>
                {section === 'financials' && (
                    <p>
                        Pendapatan dan laba adalah nilai kuartalan dari sumber. Aset, ekuitas dan liabilitas adalah posisi pada tanggal laporan. Angka
                        yang tidak tersedia bukan nol.
                    </p>
                )}
                {section === 'valuation' && (
                    <p>
                        P/E membandingkan harga dengan laba, P/B dengan nilai buku, P/S dengan pendapatan. Rasio rendah tidak otomatis berarti murah;
                        laba negatif, perbedaan sektor dan periode perlu diperhatikan.
                    </p>
                )}
                {section === 'prices' && (
                    <p>Perubahan harga tidak mencakup dividen. Penyesuaian aksi korporasi mengikuti data sumber; bukan perhitungan total return.</p>
                )}
            </div>
        </>
    );
}
