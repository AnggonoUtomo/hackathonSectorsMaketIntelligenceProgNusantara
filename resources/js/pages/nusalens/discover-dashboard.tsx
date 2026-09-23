import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Tooltip, TooltipContent, TooltipTrigger } from '@/components/ui/tooltip';
import { Link, router } from '@inertiajs/react';
import { ArrowDownUp, Database, Eye, GitCompare, Keyboard, Search, Server, Sparkles, Target } from 'lucide-react';
import type { ComponentProps } from 'react';
import { FormEvent, useEffect, useMemo, useRef, useState } from 'react';

interface DiscoverCompany {
    symbol: string;
    name: string;
    sector: string;
    score: string;
    completeness: string;
    freshness: string;
}

interface DiscoverPayload {
    filters: {
        keyword: string;
        sector: string;
        minScore: string;
        limit: string;
    };
    results: DiscoverCompany[];
    meta: {
        source: 'backend_fake' | 'sectors_real';
        state: 'ready' | 'empty';
        estimatedCredits: number;
        cachePolicy: string;
        liveProvider: boolean;
    };
}

type Props = {
    discover: DiscoverPayload;
};

const sectorLabels: Record<string, string> = {
    all: 'Semua sektor',
    financials: 'Financials',
    consumer: 'Consumer Non-Cyclicals',
    infrastructure: 'Infrastructure',
};

const perPageOptions = [5, 10, 15];

function internalSymbol(symbol: string) {
    return symbol.replace(/\.JK$/i, '');
}

function formatSource(source: DiscoverPayload['meta']['source']) {
    return source === 'sectors_real' ? 'Sectors real' : 'Backend fake';
}

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

export function DiscoverDashboard({ discover }: Props) {
    const [keyword, setKeyword] = useState(discover.filters.keyword);
    const [sector, setSector] = useState(discover.filters.sector || 'all');
    const [minScore, setMinScore] = useState(discover.filters.minScore);
    const [limit, setLimit] = useState(discover.filters.limit || '10');
    const [page, setPage] = useState(1);
    const [perPage, setPerPage] = useState(Number(discover.filters.limit || 10));
    const [isSearching, setIsSearching] = useState(false);
    const debounceTimer = useRef<number | null>(null);
    const searchInputRef = useRef<HTMLInputElement>(null);
    const hasActiveFilters = keyword.trim() !== '' || sector !== 'all' || minScore.trim() !== '' || limit !== '10';

    const visibleCompanies = discover.results;
    const lastPage = Math.max(1, Math.ceil(visibleCompanies.length / perPage));
    const currentPage = Math.min(page, lastPage);
    const paginatedCompanies = useMemo(() => {
        const start = (currentPage - 1) * perPage;

        return visibleCompanies.slice(start, start + perPage);
    }, [currentPage, perPage, visibleCompanies]);
    const uniqueSectors = new Set(visibleCompanies.map((company) => company.sector).filter(Boolean)).size;

    function requestFilters(nextKeyword = keyword, nextPage = 1, nextLimit = limit) {
        setIsSearching(true);
        router.get(
            '/temukan-saham',
            {
                keyword: nextKeyword.trim() || undefined,
                sector: sector === 'all' ? undefined : sector,
                min_score: minScore.trim() || undefined,
                limit: nextLimit === '10' ? undefined : nextLimit,
            },
            {
                preserveState: true,
                preserveScroll: true,
                replace: true,
                onFinish: () => {
                    setIsSearching(false);
                    setPage(nextPage);
                },
            },
        );
    }

    function clearLiveSearchTimer() {
        if (debounceTimer.current !== null) {
            window.clearTimeout(debounceTimer.current);
            debounceTimer.current = null;
        }
    }

    function submitFilters(event: FormEvent<HTMLFormElement>) {
        event.preventDefault();
        clearLiveSearchTimer();
        requestFilters(keyword, 1);
    }

    function handleSearchChange(value: string) {
        setKeyword(value);
        clearLiveSearchTimer();

        const query = value.trim();

        if (query.length > 0 && query.length < 3) {
            return;
        }

        debounceTimer.current = window.setTimeout(() => {
            requestFilters(value, 1);
            debounceTimer.current = null;
        }, 450);
    }

    function resetFilters() {
        clearLiveSearchTimer();
        setKeyword('');
        setSector('all');
        setMinScore('');
        setLimit('10');
        setPerPage(10);
        setIsSearching(true);
        router.get('/temukan-saham', {}, { preserveState: true, preserveScroll: true, replace: true, onFinish: () => setIsSearching(false) });
    }

    function changePerPage(value: string) {
        const next = Number(value);
        setPerPage(next);
        setLimit(value);
        requestFilters(keyword, 1, value);
    }

    useEffect(() => {
        return () => clearLiveSearchTimer();
    }, []);

    useEffect(() => {
        const isTyping = (target: EventTarget | null) =>
            target instanceof HTMLElement && (['INPUT', 'TEXTAREA', 'SELECT'].includes(target.tagName) || target.isContentEditable);
        const handleShortcut = (event: KeyboardEvent) => {
            if (event.key === '/' && !isTyping(event.target)) {
                event.preventDefault();
                searchInputRef.current?.focus();
            }

            if (event.key === 'Escape') {
                clearLiveSearchTimer();
                setKeyword(discover.filters.keyword);
                setSector(discover.filters.sector || 'all');
                setMinScore(discover.filters.minScore);
                setLimit(discover.filters.limit || '10');
            }
        };

        window.addEventListener('keydown', handleShortcut);

        return () => window.removeEventListener('keydown', handleShortcut);
    }, [discover.filters.keyword, discover.filters.limit, discover.filters.minScore, discover.filters.sector]);

    return (
        <div className="flex flex-1 flex-col gap-5 p-4">
            <div className="grid gap-4 md:grid-cols-3">
                <section className="dashboard-card dashboard-card--blue rounded-2xl border p-4">
                    <div className="flex items-center gap-3">
                        <span className="dashboard-icon dashboard-accent--blue flex size-10 items-center justify-center rounded-lg">
                            <Target aria-hidden="true" className="size-5" />
                        </span>
                        <div>
                            <p className="text-xs text-muted-foreground">Kandidat tampil</p>
                            <p className="mt-1 text-2xl font-semibold tabular-nums">{visibleCompanies.length}</p>
                        </div>
                    </div>
                </section>
                <section className="dashboard-card dashboard-card--emerald rounded-2xl border p-4">
                    <div className="flex items-center gap-3">
                        <span className="dashboard-icon dashboard-accent--emerald flex size-10 items-center justify-center rounded-lg">
                            <Database aria-hidden="true" className="size-5" />
                        </span>
                        <div>
                            <p className="text-xs text-muted-foreground">Sumber data</p>
                            <p className="mt-1 text-lg font-semibold">{formatSource(discover.meta.source)}</p>
                        </div>
                    </div>
                </section>
                <section className="dashboard-card dashboard-card--violet rounded-2xl border p-4">
                    <div className="flex items-center gap-3">
                        <span className="dashboard-icon dashboard-accent--violet flex size-10 items-center justify-center rounded-lg">
                            <Server aria-hidden="true" className="size-5" />
                        </span>
                        <div>
                            <p className="text-xs text-muted-foreground">Estimasi cache miss</p>
                            <p className="mt-1 text-lg font-semibold tabular-nums">{discover.meta.estimatedCredits} credit</p>
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
                    <kbd>/</kbd> cari saham
                </span>
                <span>
                    <kbd>Enter</kbd> terapkan filter
                </span>
                <span>
                    <kbd>Esc</kbd> pulihkan input
                </span>
            </div>

            <section className="dashboard-card dashboard-card--cyan overflow-hidden rounded-2xl border">
                <div className="flex flex-col gap-3 border-b p-4">
                    <form onSubmit={submitFilters} className="flex min-w-0 flex-col gap-3" role="search">
                        <div className="flex min-w-0 flex-col gap-3 xl:flex-row">
                            <div className="relative min-w-0 flex-1">
                                <label htmlFor="discover-search" className="sr-only">
                                    Cari ticker saham
                                </label>
                                <Search
                                    aria-hidden="true"
                                    className="pointer-events-none absolute top-1/2 left-3 size-4 -translate-y-1/2 text-muted-foreground"
                                />
                                <Input
                                    ref={searchInputRef}
                                    id="discover-search"
                                    value={keyword}
                                    onChange={(event) => handleSearchChange(event.target.value)}
                                    placeholder="Cari ticker, contoh BBCA..."
                                    className="pl-9"
                                />
                                {keyword.trim().length > 0 && keyword.trim().length < 3 ? (
                                    <p className="mt-1 text-xs text-muted-foreground" role="status">
                                        Live search aktif mulai 3 karakter.
                                    </p>
                                ) : null}
                            </div>
                            <Select name="sector" value={sector} onValueChange={setSector}>
                                <SelectTrigger id="discover-filter-sector" aria-label="Filter sektor" className="w-full xl:w-56">
                                    <SelectValue placeholder="Sektor" />
                                </SelectTrigger>
                                <SelectContent>
                                    {Object.entries(sectorLabels).map(([value, label]) => (
                                        <SelectItem key={value} value={value}>
                                            {label}
                                        </SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>
                            <Input
                                id="discover-filter-score"
                                value={minScore}
                                onChange={(event) => setMinScore(event.target.value)}
                                placeholder="Nilai min"
                                inputMode="decimal"
                                className="w-full xl:w-32"
                            />
                            <Select name="limit" value={limit} onValueChange={changePerPage}>
                                <SelectTrigger id="discover-filter-limit" aria-label="Jumlah hasil" className="w-full xl:w-36">
                                    <SelectValue />
                                </SelectTrigger>
                                <SelectContent>
                                    {perPageOptions.map((value) => (
                                        <SelectItem key={value} value={String(value)}>
                                            {value} baris
                                        </SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>
                        </div>
                        <div className="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                            <div className="flex flex-wrap gap-2">
                                <Button type="submit" variant="outline" disabled={isSearching}>
                                    {isSearching ? 'Memuat...' : 'Terapkan filter'}
                                </Button>
                                {hasActiveFilters ? (
                                    <Button type="button" variant="ghost" disabled={isSearching} onClick={resetFilters}>
                                        Reset filter
                                    </Button>
                                ) : null}
                            </div>
                            <div className="flex flex-wrap items-center gap-2 text-xs text-muted-foreground">
                                <Badge variant="outline">{discover.meta.liveProvider ? 'Live provider aktif' : 'Provider fake'}</Badge>
                                <span>Cache {discover.meta.cachePolicy}</span>
                                <span>{uniqueSectors} sektor tampil</span>
                            </div>
                        </div>
                    </form>
                </div>

                {visibleCompanies.length === 0 ? (
                    <div role="status" className="p-10 text-center">
                        <Sparkles className="mx-auto mb-3 size-9 text-muted-foreground" />
                        <h2 className="font-semibold">Tidak ada saham yang cocok</h2>
                        <p className="mt-2 text-sm text-muted-foreground">Ubah atau reset filter untuk melihat kandidat lain.</p>
                        {hasActiveFilters ? (
                            <Button type="button" variant="outline" className="mt-4" onClick={resetFilters}>
                                Reset filter
                            </Button>
                        ) : null}
                    </div>
                ) : (
                    <div className="overflow-x-auto">
                        <table className="w-full min-w-[760px] text-left text-sm">
                            <thead className="dashboard-table-header border-b text-xs tracking-wide text-foreground/80 uppercase">
                                <tr>
                                    <th className="px-5 py-3 font-medium">
                                        <span className="inline-flex items-center gap-1">
                                            Saham <ArrowDownUp className="size-3" />
                                        </span>
                                    </th>
                                    <th className="px-5 py-3 font-medium">Sektor</th>
                                    <th className="px-5 py-3 text-right font-medium">Nilai</th>
                                    <th className="px-5 py-3 text-right font-medium">Kelengkapan</th>
                                    <th className="hidden px-5 py-3 font-medium xl:table-cell">Freshness</th>
                                    <th className="px-5 py-3 text-right font-medium">Aksi</th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-border/70">
                                {paginatedCompanies.map((company, index) => {
                                    const detailSymbol = internalSymbol(company.symbol);

                                    return (
                                        <tr key={`${company.symbol}-${index}`} className="dashboard-table-row transition-colors">
                                            <td className="px-5 py-4">
                                                <Link
                                                    href={`/perusahaan/${detailSymbol}`}
                                                    prefetch
                                                    className="block rounded-md outline-none focus-visible:ring-2 focus-visible:ring-ring"
                                                >
                                                    <span className="block font-medium">{company.symbol}</span>
                                                    <span className="block max-w-[320px] truncate text-xs text-muted-foreground">{company.name}</span>
                                                </Link>
                                            </td>
                                            <td className="px-5 py-4">
                                                <Badge variant="outline" className="dashboard-badge">
                                                    {company.sector}
                                                </Badge>
                                            </td>
                                            <td className="px-5 py-4 text-right font-semibold tabular-nums">{company.score}</td>
                                            <td className="px-5 py-4 text-right tabular-nums text-muted-foreground">{company.completeness}%</td>
                                            <td className="hidden px-5 py-4 text-xs text-muted-foreground xl:table-cell">{company.freshness}</td>
                                            <td className="px-5 py-4">
                                                <div className="flex justify-end gap-1">
                                                    <ActionButton asChild tooltip={`Lihat ${company.symbol}`}>
                                                        <Link href={`/perusahaan/${detailSymbol}`} prefetch aria-label={`Lihat ${company.symbol}`}>
                                                            <Eye className="size-4" />
                                                        </Link>
                                                    </ActionButton>
                                                    <ActionButton asChild tooltip={`Bandingkan ${company.symbol}`}>
                                                        <Link href={`/bandingkan?symbols=${detailSymbol}`} prefetch aria-label={`Bandingkan ${company.symbol}`}>
                                                            <GitCompare className="size-4" />
                                                        </Link>
                                                    </ActionButton>
                                                </div>
                                            </td>
                                        </tr>
                                    );
                                })}
                            </tbody>
                        </table>
                    </div>
                )}

                <div className="flex flex-col gap-3 border-t p-4 text-sm sm:flex-row sm:items-center sm:justify-between">
                    <p className="text-muted-foreground">
                        Menampilkan halaman {currentPage} dari {lastPage} · {visibleCompanies.length} kandidat
                    </p>
                    <div className="flex flex-wrap items-center gap-2">
                        <Button type="button" variant="outline" disabled={isSearching || currentPage === 1} onClick={() => setPage(currentPage - 1)}>
                            Sebelumnya
                        </Button>
                        <Button
                            type="button"
                            variant="outline"
                            disabled={isSearching || currentPage === lastPage}
                            onClick={() => setPage(currentPage + 1)}
                        >
                            Berikutnya
                        </Button>
                    </div>
                </div>
            </section>
        </div>
    );
}
