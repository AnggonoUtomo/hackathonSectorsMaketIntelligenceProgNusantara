import CompanyAutocomplete from '@/components/company-autocomplete';
import CompanyLogo from '@/components/company-logo';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import type { CompanyIdentity } from '@/types/company-directory';
import { router } from '@inertiajs/react';
import { GitCompare, Info, RotateCcw, Search, X } from 'lucide-react';
import { useEffect, useState } from 'react';

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
        source: 'selection';
        state: 'selected' | 'empty';
        limit: number;
        liveProvider: boolean;
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

export function ComparisonDashboard({ comparison }: Props) {
    const [query, setQuery] = useState('');
    const [selected, setSelected] = useState<CompanyIdentity[]>(() => comparison.symbols.map(fallbackCompany));
    const atLimit = selected.length >= comparison.meta.limit;

    useEffect(() => {
        setSelected((current) => comparison.symbols.map((symbol) => current.find((company) => company.symbol === symbol) ?? fallbackCompany(symbol)));
    }, [comparison.symbols]);

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
                            <p className="mt-1 text-sm font-semibold">{selected.length === 0 ? 'Belum memilih saham' : 'Siap memuat profil'}</p>
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
                        <span>{comparison.meta.liveProvider ? 'Live provider aktif' : 'Belum memuat data provider tambahan'}</span>
                        <span>Profil dan metrik real dimuat pada increment berikutnya.</span>
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

                        <div className="rounded-lg border border-dashed p-5">
                            <h2 className="font-semibold">Data compare belum dimuat</h2>
                            <p className="text-muted-foreground mt-2 text-sm leading-6">
                                Increment ini baru memastikan pilihan saham real dan URL state. Profil, harga, keuangan, valuasi, dan skor akan dimuat
                                bertahap supaya credit API tetap terkendali dan tidak ada angka palsu.
                            </p>
                        </div>
                    </div>
                )}
            </section>
        </div>
    );
}
