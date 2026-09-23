import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Tooltip, TooltipContent, TooltipTrigger } from '@/components/ui/tooltip';
import { Link } from '@inertiajs/react';
import { Building2, Eye, GitCompare, Keyboard, Search, Server, Table2 } from 'lucide-react';
import type { ComponentProps } from 'react';
import { useEffect, useMemo, useRef, useState } from 'react';

interface CompanyListItem {
    symbol: string;
    name: string;
    sector: string;
    subSector: string;
    freshness: string;
    source: string;
}

type Props = {
    companies: CompanyListItem[];
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

export function CompanyDashboard({ companies }: Props) {
    const [search, setSearch] = useState('');
    const [sector, setSector] = useState('all');
    const searchInputRef = useRef<HTMLInputElement>(null);
    const sectors = useMemo(() => ['all', ...Array.from(new Set(companies.map((company) => company.sector)))], [companies]);
    const filteredCompanies = companies.filter((company) => {
        const query = search.trim().toLowerCase();
        const matchesSearch =
            query === '' || company.symbol.toLowerCase().includes(query) || company.name.toLowerCase().includes(query);
        const matchesSector = sector === 'all' || company.sector === sector;

        return matchesSearch && matchesSector;
    });
    const hasActiveFilters = search.trim() !== '' || sector !== 'all';

    function resetFilters() {
        setSearch('');
        setSector('all');
        searchInputRef.current?.focus();
    }

    useEffect(() => {
        const isTyping = (target: EventTarget | null) =>
            target instanceof HTMLElement && (['INPUT', 'TEXTAREA', 'SELECT'].includes(target.tagName) || target.isContentEditable);
        const handleShortcut = (event: KeyboardEvent) => {
            if (event.key === '/' && !isTyping(event.target)) {
                event.preventDefault();
                searchInputRef.current?.focus();
            }

            if (event.key === 'Escape') {
                resetFilters();
            }
        };

        window.addEventListener('keydown', handleShortcut);

        return () => window.removeEventListener('keydown', handleShortcut);
    }, []);

    return (
        <div className="flex flex-1 flex-col gap-5 p-4">
            <div className="grid gap-4 md:grid-cols-3">
                <section className="dashboard-card dashboard-card--blue rounded-2xl border p-4">
                    <div className="flex items-center gap-3">
                        <span className="dashboard-icon dashboard-accent--blue flex size-10 items-center justify-center rounded-lg">
                            <Building2 aria-hidden="true" className="size-5" />
                        </span>
                        <div>
                            <p className="text-xs text-muted-foreground">Perusahaan tersedia</p>
                            <p className="mt-1 text-2xl font-semibold tabular-nums">{companies.length}</p>
                        </div>
                    </div>
                </section>
                <section className="dashboard-card dashboard-card--emerald rounded-2xl border p-4">
                    <div className="flex items-center gap-3">
                        <span className="dashboard-icon dashboard-accent--emerald flex size-10 items-center justify-center rounded-lg">
                            <Table2 aria-hidden="true" className="size-5" />
                        </span>
                        <div>
                            <p className="text-xs text-muted-foreground">Hasil filter</p>
                            <p className="mt-1 text-2xl font-semibold tabular-nums">{filteredCompanies.length}</p>
                        </div>
                    </div>
                </section>
                <section className="dashboard-card dashboard-card--violet rounded-2xl border p-4">
                    <div className="flex items-center gap-3">
                        <span className="dashboard-icon dashboard-accent--violet flex size-10 items-center justify-center rounded-lg">
                            <Server aria-hidden="true" className="size-5" />
                        </span>
                        <div>
                            <p className="text-xs text-muted-foreground">Status detail real</p>
                            <p className="mt-1 text-lg font-semibold">On-demand</p>
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
                    <kbd>/</kbd> cari perusahaan
                </span>
                <span>
                    <kbd>Esc</kbd> reset filter
                </span>
            </div>

            <section className="dashboard-card dashboard-card--cyan overflow-hidden rounded-2xl border">
                <div className="flex flex-col gap-3 border-b p-4">
                    <div className="flex min-w-0 flex-col gap-3 xl:flex-row">
                        <div className="relative min-w-0 flex-1">
                            <label htmlFor="company-search" className="sr-only">
                                Cari perusahaan
                            </label>
                            <Search
                                aria-hidden="true"
                                className="pointer-events-none absolute top-1/2 left-3 size-4 -translate-y-1/2 text-muted-foreground"
                            />
                            <Input
                                ref={searchInputRef}
                                id="company-search"
                                value={search}
                                onChange={(event) => setSearch(event.target.value)}
                                onKeyDown={(event) => {
                                    if (event.key === 'Escape') {
                                        resetFilters();
                                    }
                                }}
                                placeholder="Cari ticker atau nama perusahaan..."
                                className="pl-9"
                            />
                        </div>
                        <Select name="sector" value={sector} onValueChange={setSector}>
                            <SelectTrigger id="company-filter-sector" aria-label="Filter sektor perusahaan" className="w-full xl:w-56">
                                <SelectValue placeholder="Sektor" />
                            </SelectTrigger>
                            <SelectContent>
                                {sectors.map((item) => (
                                    <SelectItem key={item} value={item}>
                                        {item === 'all' ? 'Semua sektor' : item}
                                    </SelectItem>
                                ))}
                            </SelectContent>
                        </Select>
                        {hasActiveFilters ? (
                            <Button type="button" variant="ghost" onClick={resetFilters}>
                                Reset filter
                            </Button>
                        ) : null}
                    </div>
                </div>

                {filteredCompanies.length === 0 ? (
                    <div role="status" className="p-10 text-center">
                        <Building2 className="mx-auto mb-3 size-9 text-muted-foreground" />
                        <h2 className="font-semibold">Tidak ada perusahaan yang cocok</h2>
                        <p className="mt-2 text-sm text-muted-foreground">Ubah pencarian atau reset filter sektor.</p>
                        <Button type="button" variant="outline" className="mt-4" onClick={resetFilters}>
                            Reset filter
                        </Button>
                    </div>
                ) : (
                    <div className="overflow-x-auto">
                        <table className="w-full min-w-[760px] text-left text-sm">
                            <thead className="dashboard-table-header border-b text-xs tracking-wide text-foreground/80 uppercase">
                                <tr>
                                    <th className="px-5 py-3 font-medium">Perusahaan</th>
                                    <th className="px-5 py-3 font-medium">Sektor</th>
                                    <th className="hidden px-5 py-3 font-medium xl:table-cell">Subsektor</th>
                                    <th className="px-5 py-3 font-medium">Freshness</th>
                                    <th className="px-5 py-3 text-right font-medium">Aksi</th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-border/70">
                                {filteredCompanies.map((company) => (
                                    <tr key={company.symbol} className="dashboard-table-row transition-colors">
                                        <td className="px-5 py-4">
                                            <Link
                                                href={`/perusahaan/${company.symbol}`}
                                                prefetch
                                                className="block rounded-md outline-none focus-visible:ring-2 focus-visible:ring-ring"
                                            >
                                                <span className="block font-medium">{company.symbol}</span>
                                                <span className="block max-w-[360px] truncate text-xs text-muted-foreground">{company.name}</span>
                                            </Link>
                                        </td>
                                        <td className="px-5 py-4">
                                            <Badge variant="outline" className="dashboard-badge">
                                                {company.sector}
                                            </Badge>
                                        </td>
                                        <td className="hidden px-5 py-4 text-muted-foreground xl:table-cell">{company.subSector}</td>
                                        <td className="px-5 py-4 text-xs text-muted-foreground">{company.freshness}</td>
                                        <td className="px-5 py-4">
                                            <div className="flex justify-end gap-1">
                                                <ActionButton asChild tooltip={`Lihat ${company.symbol}`}>
                                                    <Link href={`/perusahaan/${company.symbol}`} prefetch aria-label={`Lihat ${company.symbol}`}>
                                                        <Eye className="size-4" />
                                                    </Link>
                                                </ActionButton>
                                                <ActionButton asChild tooltip={`Bandingkan ${company.symbol}`}>
                                                    <Link href={`/bandingkan?symbols=${company.symbol}`} prefetch aria-label={`Bandingkan ${company.symbol}`}>
                                                        <GitCompare className="size-4" />
                                                    </Link>
                                                </ActionButton>
                                            </div>
                                        </td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>
                )}
            </section>
        </div>
    );
}
