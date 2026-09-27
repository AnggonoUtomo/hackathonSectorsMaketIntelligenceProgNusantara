import CompanyLogo from '@/components/company-logo';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/app-layout';
import type { BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/react';
import { Bookmark, CalendarDays, ExternalLink, GitCompare, History, Table2 } from 'lucide-react';

type SnapshotCompany = {
    symbol: string;
    name: string;
    logoUrl: string | null;
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
};

type SnapshotSectionEntry = {
    symbol?: string;
    section?: string;
    fetchedAt?: string;
    rows?: unknown[];
};

type SnapshotPayload = {
    companies?: SnapshotCompany[];
    sections?: Record<string, Record<string, SnapshotSectionEntry>>;
};

type SnapshotDetail = {
    id: string;
    title: string;
    symbols: string[];
    payload: SnapshotPayload;
    version: number;
    createdFromSnapshotId: string | null;
    createdAt: string | null;
    updatedAt: string | null;
};

type Props = {
    snapshot: SnapshotDetail;
};

function breadcrumbs(snapshot: SnapshotDetail): BreadcrumbItem[] {
    return [
        { title: 'Dashboard', href: '/dashboard' },
        { title: 'Snapshot Perbandingan', href: '/bandingkan/snapshots' },
        { title: snapshot.title, href: `/bandingkan/snapshots/${snapshot.id}` },
    ];
}

function formatDate(value: string | null) {
    if (value === null) {
        return 'Tanggal belum tersedia';
    }

    const date = new Date(value);

    if (Number.isNaN(date.getTime())) {
        return value;
    }

    return new Intl.DateTimeFormat('id-ID', {
        dateStyle: 'medium',
        timeStyle: 'short',
    }).format(date);
}

function formatCurrency(value: number | null) {
    return value === null
        ? 'Belum tersedia'
        : new Intl.NumberFormat('id-ID', {
              style: 'currency',
              currency: 'IDR',
              maximumFractionDigits: 2,
          }).format(value);
}

const sectionLabels: Record<string, string> = {
    prices: 'Harga',
    financials: 'Keuangan',
    valuation: 'Valuasi',
};

export default function ComparisonSnapshotDetail({ snapshot }: Props) {
    const companies = snapshot.payload.companies ?? [];
    const sections = snapshot.payload.sections ?? {};
    const compareUrl = `/bandingkan?symbols=${snapshot.symbols.join(',')}`;

    return (
        <AppLayout breadcrumbs={breadcrumbs(snapshot)}>
            <Head title={snapshot.title} />
            <div className="flex flex-1 flex-col gap-5 p-4">
                <section className="dashboard-card dashboard-card--blue rounded-lg border p-5">
                    <div className="flex flex-col gap-4 xl:flex-row xl:items-start xl:justify-between">
                        <div className="min-w-0">
                            <div className="flex flex-wrap items-center gap-2">
                                <Bookmark className="size-5 text-blue-700" />
                                <h1 className="text-xl font-semibold">{snapshot.title}</h1>
                                <Badge variant="secondary">v{snapshot.version}</Badge>
                            </div>
                            <p className="text-muted-foreground mt-2 max-w-3xl text-sm leading-6">
                                Ini adalah snapshot privat dan read-only. Data di bawah berasal dari halaman Bandingkan saat snapshot disimpan,
                                sehingga tidak melakukan refresh provider atau memakai credit API.
                            </p>
                        </div>
                        <div className="flex flex-wrap gap-2">
                            <Button asChild variant="outline">
                                <Link href="/bandingkan/snapshots">Daftar Snapshot</Link>
                            </Button>
                            <Button asChild>
                                <Link href={compareUrl}>
                                    <GitCompare className="size-4" />
                                    Buka Compare Live
                                </Link>
                            </Button>
                        </div>
                    </div>
                </section>

                <section className="grid gap-3 md:grid-cols-3">
                    <div className="rounded-lg border p-4">
                        <div className="text-muted-foreground text-xs">Saham</div>
                        <div className="mt-2 flex flex-wrap gap-1.5">
                            {snapshot.symbols.map((symbol) => (
                                <Badge key={symbol} variant="outline">
                                    {symbol}
                                </Badge>
                            ))}
                        </div>
                    </div>
                    <div className="rounded-lg border p-4">
                        <div className="text-muted-foreground text-xs">Dibuat</div>
                        <div className="mt-2 flex items-center gap-2 text-sm font-medium">
                            <CalendarDays className="size-4" />
                            {formatDate(snapshot.createdAt)}
                        </div>
                    </div>
                    <div className="rounded-lg border p-4">
                        <div className="text-muted-foreground text-xs">Versi sebelumnya</div>
                        <div className="mt-2 flex items-center gap-2 text-sm font-medium">
                            <History className="size-4" />
                            {snapshot.createdFromSnapshotId ?? 'Snapshot awal'}
                        </div>
                    </div>
                </section>

                <section className="grid gap-3 lg:grid-cols-3">
                    {companies.map((company) => (
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
                                        <dd className="text-muted-foreground mt-1 text-xs">{company.priceDate ?? 'Tanggal belum tersedia'}</dd>
                                    </div>
                                    <div className="rounded-md border p-3">
                                        <dt className="text-muted-foreground text-xs">Freshness</dt>
                                        <dd className="mt-1 font-semibold">{formatDate(company.fetchedAt)}</dd>
                                        <dd className="text-muted-foreground mt-1 text-xs">{company.freshness}</dd>
                                    </div>
                                </div>
                            </dl>
                        </article>
                    ))}
                </section>

                <section className="rounded-lg border">
                    <div className="border-b p-4">
                        <div className="flex items-center gap-2">
                            <Table2 className="size-5 text-teal-700" />
                            <h2 className="font-semibold">Section yang tersimpan</h2>
                        </div>
                        <p className="text-muted-foreground mt-1 text-sm">
                            Hanya section yang sudah dimuat sebelum snapshot disimpan yang muncul di sini.
                        </p>
                    </div>
                    {Object.keys(sections).length === 0 ? (
                        <div role="status" className="p-8 text-center">
                            <p className="font-medium">Belum ada section on-demand tersimpan</p>
                            <p className="text-muted-foreground mx-auto mt-2 max-w-xl text-sm">
                                Snapshot ini hanya berisi profil ringkas. Buka compare live untuk memuat harga, keuangan, atau valuasi.
                            </p>
                        </div>
                    ) : (
                        <div className="grid gap-3 p-4 md:grid-cols-3">
                            {Object.entries(sections).map(([section, entries]) => (
                                <div key={section} className="rounded-lg border p-4">
                                    <h3 className="font-semibold">{sectionLabels[section] ?? section}</h3>
                                    <div className="mt-3 space-y-2">
                                        {Object.entries(entries).map(([symbol, entry]) => (
                                            <div key={symbol} className="bg-muted/40 rounded-md p-3 text-sm">
                                                <div className="flex items-center justify-between gap-3">
                                                    <span className="font-medium">{symbol}</span>
                                                    <Badge variant="outline">{entry.rows?.length ?? 0} baris</Badge>
                                                </div>
                                                <div className="text-muted-foreground mt-1 text-xs">
                                                    Diambil: {formatDate(entry.fetchedAt ?? null)}
                                                </div>
                                            </div>
                                        ))}
                                    </div>
                                </div>
                            ))}
                        </div>
                    )}
                </section>
            </div>
        </AppLayout>
    );
}
