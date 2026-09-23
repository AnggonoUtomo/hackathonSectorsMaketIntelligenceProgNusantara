import CompanyAutocomplete from '@/components/company-autocomplete';
import CompanyLogo from '@/components/company-logo';
import { Button } from '@/components/ui/button';
import { Tooltip, TooltipContent, TooltipTrigger } from '@/components/ui/tooltip';
import AppLayout from '@/layouts/app-layout';
import type { CompanyIdentity, CompanySearchResult, MarketDataError } from '@/types/company-directory';
import { Head, Link, router, usePage, useRemember } from '@inertiajs/react';
import { ArrowLeft, ArrowRight, Building2, CheckCircle2, Eye, ListFilter, RotateCcw, Search } from 'lucide-react';
import { useEffect, useRef, useState } from 'react';

interface Props {
    filters: { keyword: string; page: number; limit: number };
    result: CompanySearchResult | null;
    error: MarketDataError | null;
}

export default function Discover({ filters, result, error }: Props) {
    const { errors } = usePage().props;
    const [keyword, setKeyword] = useRemember(filters.keyword, 'discover-keyword');
    const previousKeyword = useRef(filters.keyword);
    const [busy, setBusy] = useState(false);
    useEffect(() => {
        if (previousKeyword.current !== filters.keyword) {
            setKeyword(filters.keyword);
            previousKeyword.current = filters.keyword;
        }
    }, [filters.keyword, setKeyword]);
    const lastPage = result ? Math.max(1, Math.ceil(result.total / result.perPage)) : filters.page;

    function search(page = 1, limit = filters.limit, term = keyword) {
        router.get(
            '/temukan-saham',
            { ...(term.trim() ? { keyword: term.trim() } : {}), page, limit },
            {
                preserveState: true,
                preserveScroll: true,
                onStart: () => setBusy(true),
                onFinish: () => setBusy(false),
            },
        );
    }
    function detailUrl(company: CompanyIdentity, term = filters.keyword) {
        const from = `/temukan-saham?${new URLSearchParams({ keyword: term.trim(), page: String(term === filters.keyword ? filters.page : 1), limit: String(filters.limit) })}`;
        return `/perusahaan/${company.symbol}?${new URLSearchParams({ from })}`;
    }

    return (
        <AppLayout breadcrumbs={[{ title: 'Temukan Saham', href: '/temukan-saham' }]}>
            <Head title="Temukan Saham" />
            <div className="flex min-w-0 flex-1 flex-col gap-6 p-4 md:p-6">
                <header className="flex flex-wrap items-center justify-between gap-3">
                    <h1 className="text-2xl font-semibold">Temukan Saham</h1>
                    <span className="text-muted-foreground flex items-center gap-1.5 text-xs">
                        <CheckCircle2 className="size-4 text-teal-600" />
                        Sectors Financial Data
                    </span>
                </header>
                <section aria-label="Pencarian perusahaan" className="border-b pb-6">
                    <form
                        onSubmit={(event) => {
                            event.preventDefault();
                            search();
                        }}
                        className="flex flex-col gap-3 sm:flex-row"
                    >
                        <CompanyAutocomplete
                            value={keyword}
                            onChange={setKeyword}
                            onSelect={(company) => router.visit(detailUrl(company, keyword))}
                            onSearch={() => search()}
                        />
                        <Button
                            type="submit"
                            disabled={busy || (keyword.trim().length > 0 && keyword.trim().length < 2)}
                            className="h-12 bg-teal-700 px-6 text-white hover:bg-teal-800"
                        >
                            <Search className="size-4" />
                            Cari perusahaan
                        </Button>
                    </form>
                    {errors.keyword && (
                        <p role="alert" className="text-destructive mt-2 text-sm">
                            {errors.keyword}
                        </p>
                    )}
                </section>
                <dl className="grid grid-cols-1 gap-3 sm:grid-cols-3">
                    {[
                        {
                            label: 'Perusahaan ditemukan',
                            value: result?.total.toLocaleString('id-ID') ?? '-',
                            Icon: Building2,
                            accent: 'dashboard-accent--blue',
                        },
                        {
                            label: 'Ditampilkan',
                            value: result?.items.length.toLocaleString('id-ID') ?? '-',
                            Icon: ListFilter,
                            accent: 'dashboard-accent--emerald',
                        },
                        { label: 'Halaman', value: result ? `${filters.page} / ${lastPage}` : '-', Icon: Eye, accent: 'dashboard-accent--rose' },
                    ].map(({ label, value, Icon, accent }) => (
                        <div key={label} className="flex items-center gap-3 rounded-lg border px-4 py-3">
                            <span className={`dashboard-icon flex size-10 items-center justify-center rounded-md ${accent}`}>
                                <Icon className="size-5" />
                            </span>
                            <div>
                                <dt className="text-muted-foreground text-xs">{label}</dt>
                                <dd className="mt-1 text-lg font-semibold tabular-nums">{value}</dd>
                            </div>
                        </div>
                    ))}
                </dl>
                <section aria-label="Daftar perusahaan" className="min-w-0" aria-busy={busy}>
                    <div className="dashboard-shortcut-bar flex flex-wrap items-center justify-between gap-3 border-y px-3 py-3">
                        <h2 className="text-foreground text-sm font-semibold">
                            {filters.keyword ? `Hasil untuk "${filters.keyword}"` : 'Daftar perusahaan'}
                        </h2>
                        <div className="flex items-center gap-3">
                            {filters.keyword && (
                                <Button
                                    size="sm"
                                    variant="ghost"
                                    disabled={busy}
                                    onClick={() => {
                                        setKeyword('');
                                        search(1, filters.limit, '');
                                    }}
                                >
                                    <RotateCcw className="size-4" />
                                    Reset
                                </Button>
                            )}
                            <label className="flex items-center gap-2 text-xs">
                                Baris
                                <select
                                    aria-label="Jumlah baris"
                                    value={filters.limit}
                                    disabled={busy}
                                    className="bg-background text-foreground h-9 rounded-md border px-2"
                                    onChange={(event) => search(1, Number(event.target.value), filters.keyword)}
                                >
                                    {[10, 20, 25].map((limit) => (
                                        <option key={limit} value={limit}>
                                            {limit}
                                        </option>
                                    ))}
                                </select>
                            </label>
                        </div>
                    </div>
                    {busy ? (
                        <div role="status" aria-label="Memuat perusahaan" className="space-y-3 py-4">
                            {Array.from({ length: 5 }, (_, i) => (
                                <div key={i} className="bg-muted h-14 animate-pulse rounded-md" />
                            ))}
                        </div>
                    ) : error ? (
                        <div role="alert" className="space-y-3 border-b py-10 text-center">
                            <p className="text-sm">{error.message}</p>
                            <Button variant="outline" onClick={() => search(filters.page, filters.limit, filters.keyword)}>
                                Coba lagi
                            </Button>
                        </div>
                    ) : result?.items.length === 0 ? (
                        <div role="status" className="space-y-3 border-b py-12 text-center">
                            <Search className="text-muted-foreground mx-auto size-7" />
                            <h3 className="font-medium">Tidak ada perusahaan pada halaman ini</h3>
                            <Button
                                variant="outline"
                                onClick={() => {
                                    setKeyword('');
                                    search(1, filters.limit, '');
                                }}
                            >
                                Lihat semua perusahaan
                            </Button>
                        </div>
                    ) : (
                        <div className="overflow-x-auto">
                            <table className="w-full min-w-[420px] text-left text-sm">
                                <thead className="dashboard-table-header border-b text-xs uppercase">
                                    <tr>
                                        <th className="px-3 py-3 font-medium">Perusahaan</th>
                                        <th className="w-24 px-3 py-3 font-medium">Kode</th>
                                        <th className="w-16 px-3 py-3 text-right font-medium">Detail</th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y">
                                    {result?.items.map((company) => (
                                        <tr key={company.symbol} className="dashboard-table-row">
                                            <td className="px-3 py-3">
                                                <Link
                                                    href={detailUrl(company)}
                                                    className="focus-visible:ring-ring flex items-center gap-3 rounded-sm focus-visible:ring-2"
                                                >
                                                    <CompanyLogo company={company} />
                                                    <span className="max-w-lg font-medium break-words">{company.name}</span>
                                                </Link>
                                            </td>
                                            <td className="px-3 py-3">
                                                <span className="rounded border px-2 py-1 text-xs font-medium">{company.symbol}</span>
                                            </td>
                                            <td className="px-3 py-3 text-right">
                                                <Tooltip>
                                                    <TooltipTrigger asChild>
                                                        <Button asChild size="icon" variant="ghost">
                                                            <Link aria-label={`Buka ${company.name}`} href={detailUrl(company)}>
                                                                <ArrowRight className="size-4" />
                                                            </Link>
                                                        </Button>
                                                    </TooltipTrigger>
                                                    <TooltipContent>Buka perusahaan</TooltipContent>
                                                </Tooltip>
                                            </td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        </div>
                    )}
                    <div className="text-muted-foreground flex flex-wrap items-center justify-between gap-3 border-t py-4 text-xs">
                        <p>
                            {result ? `Halaman ${filters.page} dari ${lastPage}` : 'Data belum tersedia'}
                            {result && (
                                <span className="mt-1 block">
                                    Diambil{' '}
                                    {new Intl.DateTimeFormat('id-ID', { dateStyle: 'medium', timeStyle: 'short', timeZone: 'Asia/Jakarta' }).format(
                                        new Date(result.fetchedAt),
                                    )}{' '}
                                    WIB
                                </span>
                            )}
                        </p>
                        <div className="flex gap-2">
                            <Button
                                size="sm"
                                variant="outline"
                                disabled={busy || filters.page <= 1}
                                onClick={() => search(filters.page - 1, filters.limit, filters.keyword)}
                            >
                                <ArrowLeft className="size-4" />
                                Sebelumnya
                            </Button>
                            <Button
                                size="sm"
                                variant="outline"
                                disabled={busy || !result || filters.page >= lastPage}
                                onClick={() => search(filters.page + 1, filters.limit, filters.keyword)}
                            >
                                Berikutnya
                                <ArrowRight className="size-4" />
                            </Button>
                        </div>
                    </div>
                </section>
            </div>
        </AppLayout>
    );
}
