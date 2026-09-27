import CompanyAutocomplete from '@/components/company-autocomplete';
import CompanyLogo from '@/components/company-logo';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import type { CompanyIdentity } from '@/types/company-directory';
import type { FormDataConvertible } from '@inertiajs/core';
import { Link, router } from '@inertiajs/react';
import {
    AlertTriangle,
    BookmarkPlus,
    Building2,
    ChartNoAxesCombined,
    ExternalLink,
    GitCompare,
    Info,
    RefreshCw,
    RotateCcw,
    Scale,
    Search,
    Table2,
    X,
} from 'lucide-react';
import { useEffect, useMemo, useState } from 'react';
import { Bar, BarChart, CartesianGrid, Legend, Line, LineChart, ResponsiveContainer, Tooltip, XAxis, YAxis } from 'recharts';

interface ComparisonCompany extends CompanyIdentity {
    sector: string | null;
    subSector: string | null;
    industry: string | null;
    price: number | null;
    priceDate: string | null;
    fetchedAt: string | null;
    freshness: string;
    status: 'ready' | 'error';
    error: {
        reason: string;
        message: string;
    } | null;
}

interface ComparisonPayload {
    symbols: string[];
    companies: ComparisonCompany[];
    metrics: Array<{
        label: string;
        values: Record<string, string>;
        notes: Record<string, string>;
    }>;
    meta: {
        source: 'profile';
        state: 'ready' | 'partial' | 'empty';
        limit: number;
        liveProvider: boolean;
        estimatedCredits: number;
    };
}

type Props = {
    comparison: ComparisonPayload;
};

type PriceRow = {
    date: string;
    close: number | null;
    open: number | null;
    high: number | null;
    low: number | null;
    volume: number | null;
};

type PriceResult = {
    symbol: string;
    section: 'prices';
    rows: PriceRow[];
    fetchedAt: string;
    range?: {
        start: string;
        end: string;
    };
};

type PriceState = { status: 'idle' } | { status: 'loading' } | { status: 'ready'; data: PriceResult } | { status: 'error'; message: string };

type FinancialRow = {
    date: string;
    revenue: number | null;
    earnings: number | null;
    gross_profit: number | null;
    operating_cash_flow: number | null;
    free_cash_flow: number | null;
    total_assets: number | null;
    total_equity: number | null;
    total_liabilities: number | null;
    total_debt: number | null;
    interest_income: number | null;
    net_interest_income: number | null;
    gross_loan: number | null;
    total_deposit: number | null;
};

type FinancialResult = {
    symbol: string;
    section: 'financials';
    rows: FinancialRow[];
    fetchedAt: string;
};

type FinancialState = { status: 'idle' } | { status: 'loading' } | { status: 'ready'; data: FinancialResult } | { status: 'error'; message: string };

type FinancialMetricKey = keyof Omit<FinancialRow, 'date'>;

type ValuationRow = {
    date: string;
    pe: number | null;
    pb: number | null;
    ps: number | null;
    pcf: number | null;
    enterprise_to_ebitda: number | null;
};

type ValuationResult = {
    symbol: string;
    section: 'valuation';
    rows: ValuationRow[];
    fetchedAt: string;
};

type ValuationState = { status: 'idle' } | { status: 'loading' } | { status: 'ready'; data: ValuationResult } | { status: 'error'; message: string };

type ValuationMetricKey = keyof Omit<ValuationRow, 'date'>;

type SnapshotSections = {
    prices?: Record<string, PriceResult>;
    financials?: Record<string, FinancialResult>;
    valuation?: Record<string, ValuationResult>;
};

const fallbackCompany = (symbol: string): CompanyIdentity => ({
    symbol,
    name: symbol,
    logoUrl: null,
});

const formatCurrency = (value: number | null) =>
    value === null
        ? 'Belum tersedia'
        : new Intl.NumberFormat('id-ID', {
              style: 'currency',
              currency: 'IDR',
              maximumFractionDigits: 2,
          }).format(value);

const formatFreshness = (value: string | null) => {
    if (value === null) {
        return 'Belum tersedia';
    }

    const date = new Date(value);

    if (Number.isNaN(date.getTime())) {
        return value;
    }

    return new Intl.DateTimeFormat('id-ID', {
        dateStyle: 'medium',
        timeStyle: 'short',
    }).format(date);
};

const formatNumber = (value: number | null) =>
    value === null ? 'Belum tersedia' : new Intl.NumberFormat('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(value);

const priceColors = ['#0d9488', '#2563eb', '#d97706'];
const financialMetrics: Array<{ key: FinancialMetricKey; label: string }> = [
    { key: 'revenue', label: 'Pendapatan' },
    { key: 'earnings', label: 'Laba bersih' },
    { key: 'operating_cash_flow', label: 'Kas operasi' },
    { key: 'free_cash_flow', label: 'Arus kas bebas' },
    { key: 'total_assets', label: 'Aset' },
    { key: 'total_equity', label: 'Ekuitas' },
    { key: 'total_debt', label: 'Utang berbunga' },
    { key: 'net_interest_income', label: 'Bunga bersih' },
    { key: 'gross_loan', label: 'Kredit bruto' },
    { key: 'total_deposit', label: 'Simpanan nasabah' },
];
const valuationMetrics: Array<{ key: ValuationMetricKey; label: string }> = [
    { key: 'pe', label: 'P/E' },
    { key: 'pb', label: 'P/B' },
    { key: 'ps', label: 'P/S' },
    { key: 'pcf', label: 'P/CF' },
    { key: 'enterprise_to_ebitda', label: 'EV/EBITDA' },
];

export function ComparisonDashboard({ comparison }: Props) {
    const [query, setQuery] = useState('');
    const [saving, setSaving] = useState(false);
    const [snapshotSections, setSnapshotSections] = useState<SnapshotSections>({});
    const [selected, setSelected] = useState<CompanyIdentity[]>(() =>
        comparison.symbols.map((symbol) => comparison.companies.find((company) => company.symbol === symbol) ?? fallbackCompany(symbol)),
    );
    const atLimit = selected.length >= comparison.meta.limit;

    useEffect(() => {
        setSelected((current) =>
            comparison.symbols.map(
                (symbol) =>
                    comparison.companies.find((company) => company.symbol === symbol) ??
                    current.find((company) => company.symbol === symbol) ??
                    fallbackCompany(symbol),
            ),
        );
    }, [comparison.companies, comparison.symbols]);

    function navigate(next: CompanyIdentity[]) {
        const symbols = next.map((company) => company.symbol).join(',');

        router.get(
            '/bandingkan',
            { symbols: symbols || undefined },
            {
                preserveScroll: true,
                preserveState: true,
                replace: true,
            },
        );
    }

    function addCompany(company: CompanyIdentity) {
        const symbol = company.symbol.toUpperCase();
        if (selected.some((item) => item.symbol === symbol) || atLimit) {
            setQuery('');
            return;
        }

        setQuery('');
        navigate([...selected, { ...company, symbol }]);
    }

    function removeCompany(symbol: string) {
        navigate(selected.filter((company) => company.symbol !== symbol));
    }

    function resetComparison() {
        setQuery('');
        navigate([]);
    }

    function searchFallback() {
        const keyword = query.trim();

        if (keyword !== '') {
            router.get('/temukan-saham', { keyword }, { preserveScroll: true });
        }
    }

    function rememberSection(section: keyof SnapshotSections, symbol: string, data: PriceResult | FinancialResult | ValuationResult) {
        setSnapshotSections((current) => ({
            ...current,
            [section]: {
                ...(current[section] ?? {}),
                [symbol]: data,
            },
        }));
    }

    function saveSnapshot() {
        setSaving(true);
        const payload = {
            companies: comparison.companies,
            sections: snapshotSections,
        } as unknown as FormDataConvertible;

        router.post(
            '/bandingkan/snapshots',
            {
                title: `Perbandingan ${comparison.symbols.join(', ')}`,
                symbols: comparison.symbols,
                payload,
            },
            {
                preserveScroll: true,
                onFinish: () => setSaving(false),
            },
        );
    }

    return (
        <div className="flex flex-1 flex-col gap-5 p-4">
            <div className="grid gap-4 md:grid-cols-3">
                <section className="dashboard-card dashboard-card--blue rounded-lg border p-4">
                    <div className="flex items-center gap-3">
                        <span className="dashboard-icon dashboard-accent--blue flex size-10 items-center justify-center rounded-lg">
                            <GitCompare aria-hidden="true" className="size-5" />
                        </span>
                        <div>
                            <p className="text-muted-foreground text-xs">Dipilih</p>
                            <p className="mt-1 text-2xl font-semibold tabular-nums">{selected.length}</p>
                        </div>
                    </div>
                </section>
                <section className="dashboard-card dashboard-card--emerald rounded-lg border p-4">
                    <div className="flex items-center gap-3">
                        <span className="dashboard-icon dashboard-accent--emerald flex size-10 items-center justify-center rounded-lg">
                            <Info aria-hidden="true" className="size-5" />
                        </span>
                        <div>
                            <p className="text-muted-foreground text-xs">Batas</p>
                            <p className="mt-1 text-2xl font-semibold tabular-nums">{comparison.meta.limit}</p>
                        </div>
                    </div>
                </section>
                <section className="dashboard-card dashboard-card--cyan rounded-lg border p-4">
                    <div className="flex items-center gap-3">
                        <span className="dashboard-icon dashboard-accent--cyan flex size-10 items-center justify-center rounded-lg">
                            <Search aria-hidden="true" className="size-5" />
                        </span>
                        <div>
                            <p className="text-muted-foreground text-xs">Status</p>
                            <p className="mt-1 text-sm font-semibold">
                                {comparison.meta.state === 'empty'
                                    ? 'Belum memilih saham'
                                    : comparison.meta.state === 'partial'
                                      ? 'Sebagian data bermasalah'
                                      : 'Profil real tersedia'}
                            </p>
                        </div>
                    </div>
                </section>
            </div>

            <section className="dashboard-card dashboard-card--cyan overflow-hidden rounded-lg border">
                <div className="space-y-4 border-b p-4">
                    <div className="flex flex-col gap-3 xl:flex-row xl:items-start">
                        <CompanyAutocomplete value={query} onChange={setQuery} onSelect={addCompany} onSearch={searchFallback} />
                        {selected.length > 0 ? (
                            <Button type="button" variant="ghost" onClick={resetComparison}>
                                <RotateCcw className="size-4" />
                                Reset
                            </Button>
                        ) : null}
                    </div>
                    <div className="text-muted-foreground flex flex-wrap items-center gap-2 text-xs">
                        <Badge variant="outline">Maks {comparison.meta.limit} saham</Badge>
                        <span>{comparison.meta.liveProvider ? 'Profil real/cache aktif' : 'Belum memuat provider'}</span>
                        <span>Cold cache profil: sampai {comparison.meta.estimatedCredits} credit.</span>
                        <span>Harga, keuangan, valuasi, dan skor tetap on-demand.</span>
                        <Link className="text-foreground underline-offset-4 hover:underline" href="/bandingkan/snapshots">
                            Snapshot tersimpan
                        </Link>
                    </div>
                </div>

                {selected.length === 0 ? (
                    <div role="status" className="p-10 text-center">
                        <GitCompare className="text-muted-foreground mx-auto mb-3 size-9" />
                        <h2 className="font-semibold">Pilih saham untuk dibandingkan</h2>
                        <p className="text-muted-foreground mx-auto mt-2 max-w-xl text-sm">
                            Cari nama perusahaan atau kode saham, lalu pilih maksimal tiga saham. NusaLens tidak lagi mengisi contoh default agar
                            perbandingan dimulai dari pilihan risetmu sendiri.
                        </p>
                    </div>
                ) : (
                    <div className="space-y-4 p-4">
                        <div className="flex flex-wrap gap-3">
                            {selected.map((company) => (
                                <div key={company.symbol} className="flex max-w-full min-w-0 items-center gap-3 rounded-lg border px-3 py-2">
                                    <CompanyLogo company={company} />
                                    <div className="min-w-0">
                                        <div className="text-sm font-semibold">{company.symbol}</div>
                                        <div className="text-muted-foreground max-w-56 truncate text-xs">{company.name}</div>
                                    </div>
                                    <Button
                                        type="button"
                                        variant="ghost"
                                        size="icon"
                                        className="size-8"
                                        aria-label={`Hapus ${company.symbol}`}
                                        onClick={() => removeCompany(company.symbol)}
                                    >
                                        <X className="size-4" />
                                    </Button>
                                </div>
                            ))}
                        </div>

                        <div className="flex flex-col gap-3 rounded-lg border p-4 md:flex-row md:items-center md:justify-between">
                            <div>
                                <h2 className="font-semibold">Simpan riset ini</h2>
                                <p className="text-muted-foreground mt-1 text-sm">
                                    Snapshot menyimpan profil dan section yang sudah dimuat. Menyimpan snapshot tidak melakukan refresh provider.
                                </p>
                            </div>
                            <Button type="button" onClick={saveSnapshot} disabled={saving || comparison.symbols.length === 0}>
                                <BookmarkPlus className="size-4" />
                                {saving ? 'Menyimpan...' : 'Simpan Snapshot'}
                            </Button>
                        </div>

                        <div className="grid gap-3 lg:grid-cols-3">
                            {comparison.companies.map((company) => (
                                <article key={company.symbol} className="rounded-lg border p-4">
                                    <div className="flex items-start justify-between gap-3">
                                        <div className="flex min-w-0 items-center gap-3">
                                            <CompanyLogo company={company} />
                                            <div className="min-w-0">
                                                <div className="flex items-center gap-2">
                                                    <h2 className="font-semibold">{company.symbol}</h2>
                                                    <Badge variant={company.status === 'ready' ? 'secondary' : 'destructive'}>
                                                        {company.status === 'ready' ? 'Profil' : 'Error'}
                                                    </Badge>
                                                </div>
                                                <p className="text-muted-foreground mt-1 truncate text-sm">{company.name}</p>
                                            </div>
                                        </div>
                                        <Button asChild type="button" variant="ghost" size="icon" className="size-8">
                                            <Link href={`/perusahaan/${company.symbol}`} aria-label={`Buka ${company.symbol}`}>
                                                <ExternalLink className="size-4" />
                                            </Link>
                                        </Button>
                                    </div>

                                    {company.status === 'error' ? (
                                        <div className="border-destructive/30 bg-destructive/5 mt-4 rounded-md border p-3 text-sm">
                                            <div className="flex items-start gap-2">
                                                <AlertTriangle className="text-destructive mt-0.5 size-4" />
                                                <div>
                                                    <p className="font-medium">Profil belum dapat dimuat</p>
                                                    <p className="text-muted-foreground mt-1">
                                                        {company.error?.message ?? 'Data provider belum tersedia.'}
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    ) : (
                                        <dl className="mt-4 grid gap-3 text-sm">
                                            <div className="bg-muted/40 rounded-md p-3">
                                                <dt className="text-muted-foreground text-xs">Sektor</dt>
                                                <dd className="mt-1 font-medium">{company.sector ?? 'Belum tersedia'}</dd>
                                                <dd className="text-muted-foreground mt-1 text-xs">
                                                    {company.subSector ?? company.industry ?? 'Subsektor belum tersedia'}
                                                </dd>
                                            </div>
                                            <div className="grid grid-cols-2 gap-3">
                                                <div className="rounded-md border p-3">
                                                    <dt className="text-muted-foreground text-xs">Harga terakhir</dt>
                                                    <dd className="mt-1 font-semibold tabular-nums">{formatCurrency(company.price)}</dd>
                                                    <dd className="text-muted-foreground mt-1 text-xs">
                                                        {company.priceDate ?? 'Tanggal belum tersedia'}
                                                    </dd>
                                                </div>
                                                <div className="rounded-md border p-3">
                                                    <dt className="text-muted-foreground text-xs">Freshness</dt>
                                                    <dd className="mt-1 font-semibold">{formatFreshness(company.fetchedAt)}</dd>
                                                    <dd className="text-muted-foreground mt-1 text-xs">Overview cache</dd>
                                                </div>
                                            </div>
                                        </dl>
                                    )}
                                </article>
                            ))}
                        </div>

                        <div className="rounded-lg border border-dashed p-5">
                            <div className="flex items-start gap-3">
                                <Building2 className="text-muted-foreground mt-0.5 size-5" />
                                <div>
                                    <h2 className="font-semibold">Profil ringkas sudah real</h2>
                                    <p className="text-muted-foreground mt-2 text-sm leading-6">
                                        Increment ini memuat overview real/cache untuk saham yang dipilih. Section harga, keuangan, valuasi, grafik,
                                        dan skor tetap ditahan sampai user meminta agar credit API tidak habis di halaman awal.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <ComparePricesPanel
                            companies={comparison.companies.filter((company) => company.status === 'ready')}
                            onReady={(symbol, data) => rememberSection('prices', symbol, data)}
                        />
                        <CompareFinancialsPanel
                            companies={comparison.companies.filter((company) => company.status === 'ready')}
                            onReady={(symbol, data) => rememberSection('financials', symbol, data)}
                        />
                        <CompareValuationPanel
                            companies={comparison.companies.filter((company) => company.status === 'ready')}
                            onReady={(symbol, data) => rememberSection('valuation', symbol, data)}
                        />
                    </div>
                )}
            </section>
        </div>
    );
}

function ComparePricesPanel({ companies, onReady }: { companies: ComparisonCompany[]; onReady: (symbol: string, data: PriceResult) => void }) {
    const [states, setStates] = useState<Record<string, PriceState>>({});
    const [view, setView] = useState<'chart' | 'table'>('chart');
    const symbols = useMemo(() => companies.map((company) => company.symbol), [companies]);
    const loaded = symbols.filter((symbol) => states[symbol]?.status === 'ready');
    const loading = symbols.some((symbol) => states[symbol]?.status === 'loading');
    const estimatedCredits = symbols.filter((symbol) => states[symbol]?.status !== 'ready').length;
    const chartRows = buildPriceRows(symbols, states);

    useEffect(() => {
        setStates((current) => Object.fromEntries(symbols.map((symbol) => [symbol, current[symbol] ?? { status: 'idle' }])));
    }, [symbols]);

    async function loadPrices() {
        const nextSymbols = symbols.filter((symbol) => states[symbol]?.status !== 'ready');

        setStates((current) => ({
            ...current,
            ...Object.fromEntries(nextSymbols.map((symbol) => [symbol, { status: 'loading' as const }])),
        }));

        await Promise.all(
            nextSymbols.map(async (symbol) => {
                try {
                    const response = await fetch(`/nusalens/companies/${encodeURIComponent(symbol)}/analysis?section=prices`, {
                        headers: { Accept: 'application/json' },
                    });
                    const data = await response.json();

                    if (!response.ok) {
                        throw new Error(data.message ?? 'Harga belum dapat dimuat.');
                    }

                    if (data.symbol !== symbol || data.section !== 'prices' || !Array.isArray(data.rows)) {
                        throw new Error('Format harga belum dapat dibaca.');
                    }

                    onReady(symbol, data);
                    setStates((current) => ({ ...current, [symbol]: { status: 'ready', data } }));
                } catch (error) {
                    setStates((current) => ({
                        ...current,
                        [symbol]: {
                            status: 'error',
                            message:
                                error instanceof TypeError
                                    ? 'Koneksi terputus. Silakan coba lagi.'
                                    : error instanceof Error
                                      ? error.message
                                      : 'Harga belum dapat dimuat.',
                        },
                    }));
                }
            }),
        );
    }

    if (companies.length === 0) {
        return null;
    }

    return (
        <section className="rounded-lg border">
            <div className="flex flex-col gap-3 border-b p-4 xl:flex-row xl:items-start xl:justify-between">
                <div>
                    <div className="flex items-center gap-2">
                        <ChartNoAxesCombined className="size-5 text-teal-700" />
                        <h2 className="font-semibold">Harga on-demand</h2>
                    </div>
                    <p className="text-muted-foreground mt-1 text-sm leading-6">
                        Muat harga penutupan 90 hari hanya saat diperlukan. Cold cache sampai {estimatedCredits} credit untuk saham yang belum dimuat.
                    </p>
                </div>
                <div className="flex flex-wrap gap-2">
                    {loaded.length > 0 ? (
                        <div role="group" aria-label="Tampilan harga" className="flex gap-1">
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
                    ) : null}
                    <Button
                        type="button"
                        variant={loaded.length > 0 ? 'outline' : 'default'}
                        onClick={loadPrices}
                        disabled={loading || estimatedCredits === 0}
                    >
                        <RefreshCw className={`size-4 ${loading ? 'animate-spin' : ''}`} />
                        {loaded.length > 0 ? 'Muat ulang error' : 'Muat harga'}
                    </Button>
                </div>
            </div>

            <div className="space-y-4 p-4">
                <div className="flex flex-wrap gap-2">
                    {symbols.map((symbol, index) => {
                        const state = states[symbol] ?? { status: 'idle' };

                        return (
                            <Badge
                                key={symbol}
                                variant={state.status === 'error' ? 'destructive' : state.status === 'ready' ? 'secondary' : 'outline'}
                            >
                                <span
                                    className="mr-1 inline-block size-2 rounded-full"
                                    style={{ backgroundColor: priceColors[index % priceColors.length] }}
                                />
                                {symbol}:{' '}
                                {state.status === 'idle'
                                    ? 'belum dimuat'
                                    : state.status === 'loading'
                                      ? 'memuat'
                                      : state.status === 'ready'
                                        ? 'siap'
                                        : 'error'}
                            </Badge>
                        );
                    })}
                </div>

                {symbols.some((symbol) => states[symbol]?.status === 'error') ? (
                    <div className="grid gap-2 md:grid-cols-3">
                        {symbols
                            .filter((symbol) => states[symbol]?.status === 'error')
                            .map((symbol) => (
                                <div key={symbol} role="alert" className="border-destructive/30 bg-destructive/5 rounded-md border p-3 text-sm">
                                    <p className="font-medium">{symbol}</p>
                                    <p className="text-muted-foreground mt-1">{states[symbol].status === 'error' ? states[symbol].message : ''}</p>
                                </div>
                            ))}
                    </div>
                ) : null}

                {loaded.length === 0 ? (
                    <div role="status" className="rounded-lg border border-dashed p-8 text-center">
                        <ChartNoAxesCombined className="text-muted-foreground mx-auto mb-3 size-8" />
                        <p className="font-medium">Harga belum dimuat</p>
                        <p className="text-muted-foreground mx-auto mt-2 max-w-xl text-sm">
                            Klik Muat harga untuk mengambil seri harga real/cache. Data ini bukan real-time dan belum termasuk dividen.
                        </p>
                    </div>
                ) : view === 'table' ? (
                    <ComparePriceTable symbols={symbols} rows={chartRows} />
                ) : (
                    <div className="h-80 w-full min-w-0">
                        <ResponsiveContainer width="100%" height="100%" minWidth={0}>
                            <LineChart data={chartRows} margin={{ top: 16, right: 12, bottom: 12, left: 0 }} accessibilityLayer>
                                <CartesianGrid strokeDasharray="3 3" vertical={false} stroke="var(--border)" />
                                <XAxis
                                    dataKey="date"
                                    tickFormatter={(value) => String(value).slice(5)}
                                    minTickGap={28}
                                    tick={{ fontSize: 11, fill: 'var(--muted-foreground)' }}
                                />
                                <YAxis
                                    width={72}
                                    tickFormatter={(value) =>
                                        new Intl.NumberFormat('id-ID', { notation: 'compact', maximumFractionDigits: 1 }).format(value)
                                    }
                                    tick={{ fontSize: 11, fill: 'var(--muted-foreground)' }}
                                />
                                <Tooltip
                                    labelFormatter={(value) =>
                                        new Intl.DateTimeFormat('id-ID', {
                                            day: 'numeric',
                                            month: 'short',
                                            year: 'numeric',
                                            timeZone: 'Asia/Jakarta',
                                        }).format(new Date(String(value)))
                                    }
                                    formatter={(value, name) => [`Rp ${formatNumber(typeof value === 'number' ? value : null)}`, name]}
                                    contentStyle={{
                                        background: 'var(--background)',
                                        color: 'var(--foreground)',
                                        borderColor: 'var(--border)',
                                        borderRadius: 6,
                                    }}
                                />
                                <Legend wrapperStyle={{ fontSize: 12, paddingTop: 12 }} />
                                {symbols.map((symbol, index) => (
                                    <Line
                                        key={symbol}
                                        dataKey={symbol}
                                        name={symbol}
                                        stroke={priceColors[index % priceColors.length]}
                                        strokeWidth={2}
                                        dot={false}
                                        activeDot={{ r: 5 }}
                                        connectNulls={false}
                                        isAnimationActive={false}
                                    />
                                ))}
                            </LineChart>
                        </ResponsiveContainer>
                    </div>
                )}

                {loaded.length > 0 ? (
                    <p className="text-muted-foreground border-t pt-3 text-xs leading-6">
                        Sectors Financial API. Harga adalah penutupan harian 90 hari, mengikuti cache endpoint analitik. Angka kosong berarti data
                        sumber tidak tersedia, bukan nol.
                    </p>
                ) : null}
            </div>
        </section>
    );
}

function buildPriceRows(symbols: string[], states: Record<string, PriceState>) {
    const rows = new Map<string, Record<string, string | number | null>>();

    for (const symbol of symbols) {
        const state = states[symbol];
        if (state?.status !== 'ready') {
            continue;
        }

        for (const row of state.data.rows) {
            const current = rows.get(row.date) ?? { date: row.date };
            current[symbol] = row.close;
            rows.set(row.date, current);
        }
    }

    return Array.from(rows.values()).sort((a, b) => String(a.date).localeCompare(String(b.date)));
}

function ComparePriceTable({ symbols, rows }: { symbols: string[]; rows: Record<string, string | number | null>[] }) {
    return (
        <div className="max-h-96 overflow-auto rounded-md border">
            <table className="w-full min-w-[640px] text-sm">
                <caption className="text-muted-foreground p-3 text-left text-xs">Harga penutupan harian dalam rupiah (IDR)</caption>
                <thead className="bg-muted sticky top-0 text-xs uppercase">
                    <tr>
                        <th scope="col" className="p-3 text-left">
                            Tanggal
                        </th>
                        {symbols.map((symbol) => (
                            <th scope="col" key={symbol} className="p-3 text-right">
                                {symbol}
                            </th>
                        ))}
                    </tr>
                </thead>
                <tbody>
                    {[...rows].reverse().map((row) => (
                        <tr key={String(row.date)} className="hover:bg-muted/50 border-t">
                            <th scope="row" className="p-3 text-left font-normal whitespace-nowrap">
                                {new Intl.DateTimeFormat('id-ID', {
                                    day: 'numeric',
                                    month: 'short',
                                    year: 'numeric',
                                    timeZone: 'Asia/Jakarta',
                                }).format(new Date(String(row.date)))}
                            </th>
                            {symbols.map((symbol) => (
                                <td key={symbol} className="p-3 text-right whitespace-nowrap tabular-nums">
                                    {formatNumber(typeof row[symbol] === 'number' ? row[symbol] : null)}
                                </td>
                            ))}
                        </tr>
                    ))}
                </tbody>
            </table>
        </div>
    );
}

function CompareFinancialsPanel({
    companies,
    onReady,
}: {
    companies: ComparisonCompany[];
    onReady: (symbol: string, data: FinancialResult) => void;
}) {
    const [states, setStates] = useState<Record<string, FinancialState>>({});
    const [view, setView] = useState<'chart' | 'table'>('chart');
    const [metric, setMetric] = useState<FinancialMetricKey>('revenue');
    const symbols = useMemo(() => companies.map((company) => company.symbol), [companies]);
    const loaded = symbols.filter((symbol) => states[symbol]?.status === 'ready');
    const loading = symbols.some((symbol) => states[symbol]?.status === 'loading');
    const estimatedCredits = symbols.filter((symbol) => states[symbol]?.status !== 'ready').length * 4;
    const chartRows = buildFinancialRows(symbols, states, metric);
    const availableMetrics = financialMetrics.filter((item) => hasFinancialMetric(states, item.key));

    useEffect(() => {
        setStates((current) => Object.fromEntries(symbols.map((symbol) => [symbol, current[symbol] ?? { status: 'idle' }])));
    }, [symbols]);

    useEffect(() => {
        if (availableMetrics.length > 0 && !availableMetrics.some((item) => item.key === metric)) {
            setMetric(availableMetrics[0].key);
        }
    }, [availableMetrics, metric]);

    async function loadFinancials() {
        const nextSymbols = symbols.filter((symbol) => states[symbol]?.status !== 'ready');

        setStates((current) => ({
            ...current,
            ...Object.fromEntries(nextSymbols.map((symbol) => [symbol, { status: 'loading' as const }])),
        }));

        await Promise.all(
            nextSymbols.map(async (symbol) => {
                try {
                    const response = await fetch(`/nusalens/companies/${encodeURIComponent(symbol)}/analysis?section=financials`, {
                        headers: { Accept: 'application/json' },
                    });
                    const data = await response.json();

                    if (!response.ok) {
                        throw new Error(data.message ?? 'Keuangan belum dapat dimuat.');
                    }

                    if (data.symbol !== symbol || data.section !== 'financials' || !Array.isArray(data.rows)) {
                        throw new Error('Format keuangan belum dapat dibaca.');
                    }

                    onReady(symbol, data);
                    setStates((current) => ({ ...current, [symbol]: { status: 'ready', data } }));
                } catch (error) {
                    setStates((current) => ({
                        ...current,
                        [symbol]: {
                            status: 'error',
                            message:
                                error instanceof TypeError
                                    ? 'Koneksi terputus. Silakan coba lagi.'
                                    : error instanceof Error
                                      ? error.message
                                      : 'Keuangan belum dapat dimuat.',
                        },
                    }));
                }
            }),
        );
    }

    if (companies.length === 0) {
        return null;
    }

    return (
        <section className="rounded-lg border">
            <div className="flex flex-col gap-3 border-b p-4 xl:flex-row xl:items-start xl:justify-between">
                <div>
                    <div className="flex items-center gap-2">
                        <ChartNoAxesCombined className="size-5 text-blue-700" />
                        <h2 className="font-semibold">Keuangan on-demand</h2>
                    </div>
                    <p className="text-muted-foreground mt-1 text-sm leading-6">
                        Muat empat kuartal laporan keuangan hanya saat diperlukan. Cold cache sampai {estimatedCredits} credit untuk saham yang belum
                        dimuat.
                    </p>
                </div>
                <div className="flex flex-wrap gap-2">
                    {loaded.length > 0 ? (
                        <>
                            <select
                                aria-label="Metrik keuangan"
                                value={metric}
                                onChange={(event) => setMetric(event.target.value as FinancialMetricKey)}
                                className="bg-background max-w-full rounded-md border px-3 py-2 text-sm"
                            >
                                {(availableMetrics.length > 0 ? availableMetrics : financialMetrics).map((item) => (
                                    <option key={item.key} value={item.key}>
                                        {item.label}
                                    </option>
                                ))}
                            </select>
                            <div role="group" aria-label="Tampilan keuangan" className="flex gap-1">
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
                        </>
                    ) : null}
                    <Button
                        type="button"
                        variant={loaded.length > 0 ? 'outline' : 'default'}
                        onClick={loadFinancials}
                        disabled={loading || estimatedCredits === 0}
                    >
                        <RefreshCw className={`size-4 ${loading ? 'animate-spin' : ''}`} />
                        {loaded.length > 0 ? 'Muat ulang error' : 'Muat keuangan'}
                    </Button>
                </div>
            </div>

            <div className="space-y-4 p-4">
                <div className="flex flex-wrap gap-2">
                    {symbols.map((symbol, index) => {
                        const state = states[symbol] ?? { status: 'idle' };

                        return (
                            <Badge
                                key={symbol}
                                variant={state.status === 'error' ? 'destructive' : state.status === 'ready' ? 'secondary' : 'outline'}
                            >
                                <span
                                    className="mr-1 inline-block size-2 rounded-full"
                                    style={{ backgroundColor: priceColors[index % priceColors.length] }}
                                />
                                {symbol}:{' '}
                                {state.status === 'idle'
                                    ? 'belum dimuat'
                                    : state.status === 'loading'
                                      ? 'memuat'
                                      : state.status === 'ready'
                                        ? 'siap'
                                        : 'error'}
                            </Badge>
                        );
                    })}
                </div>

                {symbols.some((symbol) => states[symbol]?.status === 'error') ? (
                    <div className="grid gap-2 md:grid-cols-3">
                        {symbols
                            .filter((symbol) => states[symbol]?.status === 'error')
                            .map((symbol) => (
                                <div key={symbol} role="alert" className="border-destructive/30 bg-destructive/5 rounded-md border p-3 text-sm">
                                    <p className="font-medium">{symbol}</p>
                                    <p className="text-muted-foreground mt-1">{states[symbol].status === 'error' ? states[symbol].message : ''}</p>
                                </div>
                            ))}
                    </div>
                ) : null}

                {loaded.length === 0 ? (
                    <div role="status" className="rounded-lg border border-dashed p-8 text-center">
                        <ChartNoAxesCombined className="text-muted-foreground mx-auto mb-3 size-8" />
                        <p className="font-medium">Keuangan belum dimuat</p>
                        <p className="text-muted-foreground mx-auto mt-2 max-w-xl text-sm">
                            Klik Muat keuangan untuk mengambil empat kuartal data real/cache. Angka kosong berarti data sumber belum tersedia, bukan
                            nol.
                        </p>
                    </div>
                ) : view === 'table' ? (
                    <CompareFinancialTable symbols={symbols} states={states} metric={metric} />
                ) : chartRows.length === 0 ? (
                    <p role="status" className="text-muted-foreground py-12 text-center text-sm">
                        Metrik ini belum tersedia pada saham yang dimuat.
                    </p>
                ) : (
                    <div className="h-80 w-full min-w-0">
                        <ResponsiveContainer width="100%" height="100%" minWidth={0}>
                            <BarChart data={chartRows} margin={{ top: 16, right: 12, bottom: 12, left: 0 }} accessibilityLayer>
                                <CartesianGrid strokeDasharray="3 3" vertical={false} stroke="var(--border)" />
                                <XAxis dataKey="date" minTickGap={24} tick={{ fontSize: 11, fill: 'var(--muted-foreground)' }} />
                                <YAxis
                                    width={72}
                                    tickFormatter={(value) =>
                                        new Intl.NumberFormat('id-ID', { notation: 'compact', maximumFractionDigits: 1 }).format(value)
                                    }
                                    tick={{ fontSize: 11, fill: 'var(--muted-foreground)' }}
                                />
                                <Tooltip
                                    formatter={(value, name) => [`${formatNumber(typeof value === 'number' ? value : null)} M`, name]}
                                    contentStyle={{
                                        background: 'var(--background)',
                                        color: 'var(--foreground)',
                                        borderColor: 'var(--border)',
                                        borderRadius: 6,
                                    }}
                                />
                                <Legend wrapperStyle={{ fontSize: 12, paddingTop: 12 }} />
                                {symbols.map((symbol, index) => (
                                    <Bar
                                        key={symbol}
                                        dataKey={symbol}
                                        name={symbol}
                                        fill={priceColors[index % priceColors.length]}
                                        maxBarSize={48}
                                        radius={[3, 3, 0, 0]}
                                        isAnimationActive={false}
                                    />
                                ))}
                            </BarChart>
                        </ResponsiveContainer>
                    </div>
                )}

                {loaded.length > 0 ? (
                    <p className="text-muted-foreground border-t pt-3 text-xs leading-6">
                        Sectors Financial API. Grafik keuangan memakai miliar rupiah agar mudah dibandingkan. Pendapatan/laba adalah nilai kuartalan;
                        aset, ekuitas dan utang adalah posisi pada tanggal laporan.
                    </p>
                ) : null}
            </div>
        </section>
    );
}

function hasFinancialMetric(states: Record<string, FinancialState>, metric: FinancialMetricKey) {
    return Object.values(states).some((state) => state.status === 'ready' && state.data.rows.some((row) => typeof row[metric] === 'number'));
}

function buildFinancialRows(symbols: string[], states: Record<string, FinancialState>, metric: FinancialMetricKey) {
    const rows = new Map<string, Record<string, string | number | null>>();

    for (const symbol of symbols) {
        const state = states[symbol];
        if (state?.status !== 'ready') {
            continue;
        }

        for (const row of state.data.rows) {
            const current = rows.get(row.date) ?? { date: formatQuarter(row.date) };
            current[symbol] = typeof row[metric] === 'number' ? row[metric] / 1e9 : null;
            rows.set(row.date, current);
        }
    }

    return Array.from(rows.values());
}

function CompareFinancialTable({
    symbols,
    states,
    metric,
}: {
    symbols: string[];
    states: Record<string, FinancialState>;
    metric: FinancialMetricKey;
}) {
    const rows = buildFinancialRows(symbols, states, metric);

    return (
        <div className="max-h-96 overflow-auto rounded-md border">
            <table className="w-full min-w-[640px] text-sm">
                <caption className="text-muted-foreground p-3 text-left text-xs">Data keuangan dalam miliar rupiah</caption>
                <thead className="bg-muted sticky top-0 text-xs uppercase">
                    <tr>
                        <th scope="col" className="p-3 text-left">
                            Periode
                        </th>
                        {symbols.map((symbol) => (
                            <th scope="col" key={symbol} className="p-3 text-right">
                                {symbol}
                            </th>
                        ))}
                    </tr>
                </thead>
                <tbody>
                    {[...rows].reverse().map((row) => (
                        <tr key={String(row.date)} className="hover:bg-muted/50 border-t">
                            <th scope="row" className="p-3 text-left font-normal whitespace-nowrap">
                                {row.date}
                            </th>
                            {symbols.map((symbol) => (
                                <td key={symbol} className="p-3 text-right whitespace-nowrap tabular-nums">
                                    {formatNumber(typeof row[symbol] === 'number' ? row[symbol] : null)}
                                </td>
                            ))}
                        </tr>
                    ))}
                </tbody>
            </table>
        </div>
    );
}

function formatQuarter(date: string) {
    if (!/^\d{4}-\d{2}-\d{2}$/.test(date)) {
        return date;
    }

    return `Q${Math.ceil(Number(date.slice(5, 7)) / 3)} ${date.slice(0, 4)}`;
}

function CompareValuationPanel({ companies, onReady }: { companies: ComparisonCompany[]; onReady: (symbol: string, data: ValuationResult) => void }) {
    const [states, setStates] = useState<Record<string, ValuationState>>({});
    const [view, setView] = useState<'chart' | 'table'>('chart');
    const [metric, setMetric] = useState<ValuationMetricKey>('pe');
    const symbols = useMemo(() => companies.map((company) => company.symbol), [companies]);
    const loaded = symbols.filter((symbol) => states[symbol]?.status === 'ready');
    const loading = symbols.some((symbol) => states[symbol]?.status === 'loading');
    const estimatedCredits = symbols.filter((symbol) => states[symbol]?.status !== 'ready').length;
    const chartRows = buildValuationRows(symbols, states, metric);
    const availableMetrics = useMemo(() => valuationMetrics.filter((item) => hasValuationMetric(states, item.key)), [states]);

    useEffect(() => {
        setStates((current) => Object.fromEntries(symbols.map((symbol) => [symbol, current[symbol] ?? { status: 'idle' }])));
    }, [symbols]);

    useEffect(() => {
        if (availableMetrics.length > 0 && !availableMetrics.some((item) => item.key === metric)) {
            setMetric(availableMetrics[0].key);
        }
    }, [availableMetrics, metric]);

    async function loadValuation() {
        const nextSymbols = symbols.filter((symbol) => states[symbol]?.status !== 'ready');

        setStates((current) => ({
            ...current,
            ...Object.fromEntries(nextSymbols.map((symbol) => [symbol, { status: 'loading' as const }])),
        }));

        await Promise.all(
            nextSymbols.map(async (symbol) => {
                try {
                    const response = await fetch(`/nusalens/companies/${encodeURIComponent(symbol)}/analysis?section=valuation`, {
                        headers: { Accept: 'application/json' },
                    });
                    const data = await response.json();

                    if (!response.ok) {
                        throw new Error(data.message ?? 'Valuasi belum dapat dimuat.');
                    }

                    if (data.symbol !== symbol || data.section !== 'valuation' || !Array.isArray(data.rows)) {
                        throw new Error('Format valuasi belum dapat dibaca.');
                    }

                    onReady(symbol, data);
                    setStates((current) => ({ ...current, [symbol]: { status: 'ready', data } }));
                } catch (error) {
                    setStates((current) => ({
                        ...current,
                        [symbol]: {
                            status: 'error',
                            message:
                                error instanceof TypeError
                                    ? 'Koneksi terputus. Silakan coba lagi.'
                                    : error instanceof Error
                                      ? error.message
                                      : 'Valuasi belum dapat dimuat.',
                        },
                    }));
                }
            }),
        );
    }

    if (companies.length === 0) {
        return null;
    }

    return (
        <section className="rounded-lg border">
            <div className="flex flex-col gap-3 border-b p-4 xl:flex-row xl:items-start xl:justify-between">
                <div>
                    <div className="flex items-center gap-2">
                        <Scale className="size-5 text-amber-700" />
                        <h2 className="font-semibold">Valuasi on-demand</h2>
                    </div>
                    <p className="text-muted-foreground mt-1 text-sm leading-6">
                        Muat rasio valuasi historis hanya saat diperlukan. Cold cache sampai {estimatedCredits} credit untuk saham yang belum dimuat.
                    </p>
                </div>
                <div className="flex flex-wrap gap-2">
                    {loaded.length > 0 ? (
                        <>
                            <select
                                aria-label="Metrik valuasi"
                                value={metric}
                                onChange={(event) => setMetric(event.target.value as ValuationMetricKey)}
                                className="bg-background max-w-full rounded-md border px-3 py-2 text-sm"
                            >
                                {(availableMetrics.length > 0 ? availableMetrics : valuationMetrics).map((item) => (
                                    <option key={item.key} value={item.key}>
                                        {item.label}
                                    </option>
                                ))}
                            </select>
                            <div role="group" aria-label="Tampilan valuasi" className="flex gap-1">
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
                        </>
                    ) : null}
                    <Button
                        type="button"
                        variant={loaded.length > 0 ? 'outline' : 'default'}
                        onClick={loadValuation}
                        disabled={loading || estimatedCredits === 0}
                    >
                        <RefreshCw className={`size-4 ${loading ? 'animate-spin' : ''}`} />
                        {loaded.length > 0 ? 'Muat ulang error' : 'Muat valuasi'}
                    </Button>
                </div>
            </div>

            <div className="space-y-4 p-4">
                <div className="flex flex-wrap gap-2">
                    {symbols.map((symbol, index) => {
                        const state = states[symbol] ?? { status: 'idle' };

                        return (
                            <Badge
                                key={symbol}
                                variant={state.status === 'error' ? 'destructive' : state.status === 'ready' ? 'secondary' : 'outline'}
                            >
                                <span
                                    className="mr-1 inline-block size-2 rounded-full"
                                    style={{ backgroundColor: priceColors[index % priceColors.length] }}
                                />
                                {symbol}:{' '}
                                {state.status === 'idle'
                                    ? 'belum dimuat'
                                    : state.status === 'loading'
                                      ? 'memuat'
                                      : state.status === 'ready'
                                        ? 'siap'
                                        : 'error'}
                            </Badge>
                        );
                    })}
                </div>

                {symbols.some((symbol) => states[symbol]?.status === 'error') ? (
                    <div className="grid gap-2 md:grid-cols-3">
                        {symbols
                            .filter((symbol) => states[symbol]?.status === 'error')
                            .map((symbol) => (
                                <div key={symbol} role="alert" className="border-destructive/30 bg-destructive/5 rounded-md border p-3 text-sm">
                                    <p className="font-medium">{symbol}</p>
                                    <p className="text-muted-foreground mt-1">{states[symbol].status === 'error' ? states[symbol].message : ''}</p>
                                </div>
                            ))}
                    </div>
                ) : null}

                {loaded.length === 0 ? (
                    <div role="status" className="rounded-lg border border-dashed p-8 text-center">
                        <Scale className="text-muted-foreground mx-auto mb-3 size-8" />
                        <p className="font-medium">Valuasi belum dimuat</p>
                        <p className="text-muted-foreground mx-auto mt-2 max-w-xl text-sm">
                            Klik Muat valuasi untuk mengambil rasio historis real/cache. Rasio rendah tidak otomatis berarti murah dan perlu
                            dibandingkan dengan sektor serta kondisi laba.
                        </p>
                    </div>
                ) : view === 'table' ? (
                    <CompareValuationTable symbols={symbols} states={states} metric={metric} />
                ) : chartRows.length === 0 ? (
                    <p role="status" className="text-muted-foreground py-12 text-center text-sm">
                        Metrik ini belum tersedia pada saham yang dimuat.
                    </p>
                ) : (
                    <div className="h-80 w-full min-w-0">
                        <ResponsiveContainer width="100%" height="100%" minWidth={0}>
                            <LineChart data={chartRows} margin={{ top: 16, right: 12, bottom: 12, left: 0 }} accessibilityLayer>
                                <CartesianGrid strokeDasharray="3 3" vertical={false} stroke="var(--border)" />
                                <XAxis dataKey="date" minTickGap={28} tick={{ fontSize: 11, fill: 'var(--muted-foreground)' }} />
                                <YAxis
                                    width={72}
                                    tickFormatter={(value) => new Intl.NumberFormat('id-ID', { maximumFractionDigits: 1 }).format(value)}
                                    tick={{ fontSize: 11, fill: 'var(--muted-foreground)' }}
                                />
                                <Tooltip
                                    formatter={(value, name) => [`${formatNumber(typeof value === 'number' ? value : null)}x`, name]}
                                    contentStyle={{
                                        background: 'var(--background)',
                                        color: 'var(--foreground)',
                                        borderColor: 'var(--border)',
                                        borderRadius: 6,
                                    }}
                                />
                                <Legend wrapperStyle={{ fontSize: 12, paddingTop: 12 }} />
                                {symbols.map((symbol, index) => (
                                    <Line
                                        key={symbol}
                                        dataKey={symbol}
                                        name={symbol}
                                        stroke={priceColors[index % priceColors.length]}
                                        strokeWidth={2}
                                        dot
                                        activeDot={{ r: 5 }}
                                        connectNulls={false}
                                        isAnimationActive={false}
                                    />
                                ))}
                            </LineChart>
                        </ResponsiveContainer>
                    </div>
                )}

                {loaded.length > 0 ? (
                    <p className="text-muted-foreground border-t pt-3 text-xs leading-6">
                        Sectors Financial API. Rasio valuasi ditampilkan dalam kali (x). P/E, P/B, P/S, P/CF dan EV/EBITDA perlu dibaca bersama
                        kualitas laba, sektor, dan periode data.
                    </p>
                ) : null}
            </div>
        </section>
    );
}

function hasValuationMetric(states: Record<string, ValuationState>, metric: ValuationMetricKey) {
    return Object.values(states).some((state) => state.status === 'ready' && state.data.rows.some((row) => typeof row[metric] === 'number'));
}

function buildValuationRows(symbols: string[], states: Record<string, ValuationState>, metric: ValuationMetricKey) {
    const rows = new Map<string, Record<string, string | number | null>>();

    for (const symbol of symbols) {
        const state = states[symbol];
        if (state?.status !== 'ready') {
            continue;
        }

        for (const row of state.data.rows) {
            const current = rows.get(row.date) ?? { date: row.date };
            current[symbol] = row[metric];
            rows.set(row.date, current);
        }
    }

    return Array.from(rows.values()).sort((a, b) => String(a.date).localeCompare(String(b.date)));
}

function CompareValuationTable({
    symbols,
    states,
    metric,
}: {
    symbols: string[];
    states: Record<string, ValuationState>;
    metric: ValuationMetricKey;
}) {
    const rows = buildValuationRows(symbols, states, metric);

    return (
        <div className="max-h-96 overflow-auto rounded-md border">
            <table className="w-full min-w-[640px] text-sm">
                <caption className="text-muted-foreground p-3 text-left text-xs">Rasio valuasi historis dalam kali (x)</caption>
                <thead className="bg-muted sticky top-0 text-xs uppercase">
                    <tr>
                        <th scope="col" className="p-3 text-left">
                            Tahun
                        </th>
                        {symbols.map((symbol) => (
                            <th scope="col" key={symbol} className="p-3 text-right">
                                {symbol}
                            </th>
                        ))}
                    </tr>
                </thead>
                <tbody>
                    {[...rows].reverse().map((row) => (
                        <tr key={String(row.date)} className="hover:bg-muted/50 border-t">
                            <th scope="row" className="p-3 text-left font-normal whitespace-nowrap">
                                {row.date}
                            </th>
                            {symbols.map((symbol) => (
                                <td key={symbol} className="p-3 text-right whitespace-nowrap tabular-nums">
                                    {typeof row[symbol] === 'number' ? `${formatNumber(row[symbol])}x` : 'Belum tersedia'}
                                </td>
                            ))}
                        </tr>
                    ))}
                </tbody>
            </table>
        </div>
    );
}
