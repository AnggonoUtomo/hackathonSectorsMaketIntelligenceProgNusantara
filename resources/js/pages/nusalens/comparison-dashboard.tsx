import CompanyAutocomplete from '@/components/company-autocomplete';
import CompanyLogo from '@/components/company-logo';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import type { CompanyIdentity } from '@/types/company-directory';
import { Link, router } from '@inertiajs/react';
import { AlertTriangle, Building2, ExternalLink, GitCompare, Info, RotateCcw, Search, X } from 'lucide-react';
import { useEffect, useState } from 'react';

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

export function ComparisonDashboard({ comparison }: Props) {
    const [query, setQuery] = useState('');
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
                    </div>
                )}
            </section>
        </div>
    );
}
