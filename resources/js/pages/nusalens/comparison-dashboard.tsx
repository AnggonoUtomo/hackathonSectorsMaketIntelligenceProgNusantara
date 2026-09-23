import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Tooltip, TooltipContent, TooltipTrigger } from '@/components/ui/tooltip';
import { Link, router } from '@inertiajs/react';
import { Building2, Eye, GitCompare, Keyboard, RotateCcw, Table2 } from 'lucide-react';
import type { ComponentProps, FormEvent } from 'react';
import { useEffect, useRef, useState } from 'react';

interface ComparisonPayload {
    symbols: string[];
    companies: Array<{
        symbol: string;
        name: string;
        sector: string;
        freshness: string;
    }>;
    metrics: Array<{
        label: string;
        values: Record<string, string>;
        notes: Record<string, string>;
    }>;
    meta: {
        source: 'backend_fake';
        state: 'ready' | 'empty';
        limit: number;
        liveProvider: boolean;
    };
}

type Props = {
    comparison: ComparisonPayload;
};

function ActionButton({
    children,
    tooltip,
    ...props
}: ComponentProps<typeof Button> & {
    tooltip: string;
}) {
    return (
        <Tooltip>
            <TooltipTrigger asChild>
                <Button size="icon" variant="ghost" className="size-8" {...props}>
                    {children}
                </Button>
            </TooltipTrigger>
            <TooltipContent>{tooltip}</TooltipContent>
        </Tooltip>
    );
}

export function ComparisonDashboard({ comparison }: Props) {
    const [symbols, setSymbols] = useState(comparison.symbols.join(','));
    const inputRef = useRef<HTMLInputElement>(null);
    const hasSymbols = comparison.companies.length > 0;
    const pendingCount = comparison.metrics[0]
        ? comparison.symbols.filter((symbol) => comparison.metrics[0].values[symbol] === '-').length
        : 0;

    function submitComparison(event: FormEvent<HTMLFormElement>) {
        event.preventDefault();
        router.get(
            '/bandingkan',
            { symbols: symbols.trim() || undefined },
            {
                preserveState: true,
                preserveScroll: true,
                replace: true,
            },
        );
    }

    function resetComparison() {
        setSymbols('');
        router.get('/bandingkan', {}, { preserveState: true, preserveScroll: true, replace: true });
    }

    useEffect(() => {
        const isTyping = (target: EventTarget | null) =>
            target instanceof HTMLElement && (['INPUT', 'TEXTAREA', 'SELECT'].includes(target.tagName) || target.isContentEditable);
        const handleShortcut = (event: KeyboardEvent) => {
            if (event.key === '/' && !isTyping(event.target)) {
                event.preventDefault();
                inputRef.current?.focus();
            }

            if (event.key === 'Escape') {
                setSymbols(comparison.symbols.join(','));
            }
        };

        window.addEventListener('keydown', handleShortcut);

        return () => window.removeEventListener('keydown', handleShortcut);
    }, [comparison.symbols]);

    return (
        <div className="flex flex-1 flex-col gap-5 p-4">
            <div className="grid gap-4 md:grid-cols-3">
                <section className="dashboard-card dashboard-card--blue rounded-2xl border p-4">
                    <div className="flex items-center gap-3">
                        <span className="dashboard-icon dashboard-accent--blue flex size-10 items-center justify-center rounded-lg">
                            <GitCompare aria-hidden="true" className="size-5" />
                        </span>
                        <div>
                            <p className="text-xs text-muted-foreground">Dipilih</p>
                            <p className="mt-1 text-2xl font-semibold tabular-nums">{comparison.symbols.length}</p>
                        </div>
                    </div>
                </section>
                <section className="dashboard-card dashboard-card--emerald rounded-2xl border p-4">
                    <div className="flex items-center gap-3">
                        <span className="dashboard-icon dashboard-accent--emerald flex size-10 items-center justify-center rounded-lg">
                            <Table2 aria-hidden="true" className="size-5" />
                        </span>
                        <div>
                            <p className="text-xs text-muted-foreground">Metrik</p>
                            <p className="mt-1 text-2xl font-semibold tabular-nums">{comparison.metrics.length}</p>
                        </div>
                    </div>
                </section>
                <section className="dashboard-card dashboard-card--violet rounded-2xl border p-4">
                    <div className="flex items-center gap-3">
                        <span className="dashboard-icon dashboard-accent--violet flex size-10 items-center justify-center rounded-lg">
                            <Building2 aria-hidden="true" className="size-5" />
                        </span>
                        <div>
                            <p className="text-xs text-muted-foreground">Pending real data</p>
                            <p className="mt-1 text-2xl font-semibold tabular-nums">{pendingCount}</p>
                        </div>
                    </div>
                </section>
            </div>

            <div className="dashboard-shortcut-bar flex flex-wrap items-center gap-x-4 gap-y-2 rounded-xl border px-3 py-2 text-xs">
                <span className="flex items-center gap-2 font-medium">
                    <Keyboard aria-hidden="true" className="size-4" />
                    Shortcut
                </span>
                <span>
                    <kbd>/</kbd> input ticker
                </span>
                <span>
                    <kbd>Enter</kbd> bandingkan
                </span>
                <span>
                    <kbd>Esc</kbd> pulihkan input
                </span>
            </div>

            <section className="dashboard-card dashboard-card--cyan overflow-hidden rounded-2xl border">
                <div className="flex flex-col gap-3 border-b p-4">
                    <form className="flex min-w-0 flex-col gap-3" onSubmit={submitComparison}>
                        <div className="flex min-w-0 flex-col gap-3 xl:flex-row">
                            <div className="relative min-w-0 flex-1">
                                <label htmlFor="comparison-symbols" className="sr-only">
                                    Ticker yang dibandingkan
                                </label>
                                <GitCompare
                                    aria-hidden="true"
                                    className="pointer-events-none absolute top-1/2 left-3 size-4 -translate-y-1/2 text-muted-foreground"
                                />
                                <Input
                                    ref={inputRef}
                                    id="comparison-symbols"
                                    value={symbols}
                                    onChange={(event) => setSymbols(event.target.value)}
                                    placeholder="BBCA,TLKM,ADES"
                                    className="pl-9"
                                />
                            </div>
                            <Button type="submit" variant="outline">
                                Bandingkan
                            </Button>
                            {symbols.trim() !== '' ? (
                                <Button type="button" variant="ghost" onClick={resetComparison}>
                                    Reset
                                </Button>
                            ) : null}
                        </div>
                        <div className="flex flex-wrap items-center gap-2 text-xs text-muted-foreground">
                            <Badge variant="outline">Maks {comparison.meta.limit} saham</Badge>
                            <span>Sumber {comparison.meta.source}</span>
                            <span>{comparison.meta.liveProvider ? 'Live provider aktif' : 'Tanpa live call baru'}</span>
                        </div>
                    </form>
                </div>

                {!hasSymbols ? (
                    <div role="status" className="p-10 text-center">
                        <GitCompare className="mx-auto mb-3 size-9 text-muted-foreground" />
                        <h2 className="font-semibold">Belum ada saham untuk dibandingkan</h2>
                        <p className="mt-2 text-sm text-muted-foreground">Masukkan maksimal tiga ticker, pisahkan dengan koma.</p>
                    </div>
                ) : (
                    <div className="overflow-x-auto">
                        <table className="w-full min-w-[840px] text-left text-sm">
                            <thead className="dashboard-table-header border-b text-xs tracking-wide text-foreground/80 uppercase">
                                <tr>
                                    <th className="px-5 py-3 font-medium">Metrik</th>
                                    {comparison.companies.map((company) => (
                                        <th key={company.symbol} className="px-5 py-3 font-medium">
                                            <div className="flex items-start justify-between gap-3">
                                                <div className="min-w-0">
                                                    <Link
                                                        href={`/perusahaan/${company.symbol}`}
                                                        prefetch
                                                        className="block truncate underline-offset-4 hover:underline"
                                                    >
                                                        {company.symbol}
                                                    </Link>
                                                    <div className="max-w-[220px] truncate text-[0.7rem] font-normal normal-case text-muted-foreground">
                                                        {company.name}
                                                    </div>
                                                </div>
                                                <ActionButton asChild tooltip={`Lihat ${company.symbol}`}>
                                                    <Link href={`/perusahaan/${company.symbol}`} prefetch aria-label={`Lihat ${company.symbol}`}>
                                                        <Eye className="size-4" />
                                                    </Link>
                                                </ActionButton>
                                            </div>
                                        </th>
                                    ))}
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-border/70">
                                <tr className="dashboard-table-row transition-colors">
                                    <td className="px-5 py-4 font-medium">Sektor</td>
                                    {comparison.companies.map((company) => (
                                        <td key={company.symbol} className="px-5 py-4">
                                            <Badge variant="outline" className="dashboard-badge">
                                                {company.sector}
                                            </Badge>
                                            <div className="mt-1 text-xs text-muted-foreground">{company.freshness}</div>
                                        </td>
                                    ))}
                                </tr>
                                {comparison.metrics.map((metric) => (
                                    <tr key={metric.label} className="dashboard-table-row transition-colors">
                                        <td className="px-5 py-4 font-medium">{metric.label}</td>
                                        {comparison.symbols.map((symbol) => (
                                            <td key={symbol} className="px-5 py-4">
                                                <div className="font-semibold tabular-nums">{metric.values[symbol]}</div>
                                                <div className="mt-1 max-w-[220px] text-xs text-muted-foreground">{metric.notes[symbol]}</div>
                                            </td>
                                        ))}
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>
                )}

                {hasSymbols ? (
                    <div className="flex flex-col gap-3 border-t p-4 text-sm sm:flex-row sm:items-center sm:justify-between">
                        <p className="text-muted-foreground">
                            Matrix ini adalah alat riset, bukan rekomendasi beli/jual. Nilai `-` berarti data real belum dimuat.
                        </p>
                        <Button type="button" variant="outline" onClick={resetComparison}>
                            <RotateCcw className="size-4" />
                            Reset
                        </Button>
                    </div>
                ) : null}
            </section>
        </div>
    );
}
