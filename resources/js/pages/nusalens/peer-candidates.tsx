import CompanyLogo from '@/components/company-logo';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import AppLayout from '@/layouts/app-layout';
import { Head, Link, router } from '@inertiajs/react';
import { ChevronLeft, ChevronRight, Eye, GitCompare, Search } from 'lucide-react';
import { useState } from 'react';

type Props = {
    result: {
        items: Array<{ symbol: string; name: string; group: string | null; completeness: number; components: Record<string, number | null> }>;
        total: number;
        target: string;
        evidenceId: string;
        fetchedAt: string;
        expiresAt: string;
    };
    filters: { q: string; component: string; page: number };
};
const number = (n: number | null) =>
    n === null ? 'Belum tersedia' : new Intl.NumberFormat('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(n);
const components = { quality: 'Kesehatan Bisnis', growth: 'Pertumbuhan', risk: 'Keamanan Keuangan' };
export default function PeerCandidates({ result, filters }: Props) {
    const [query, setQuery] = useState(filters.q);
    const [component, setComponent] = useState(filters.component);
    function visit(page = 1) {
        router.get('/kandidat-menarik', { evidence: result.evidenceId, q: query, component, page }, { preserveState: true });
    }
    return (
        <AppLayout
            breadcrumbs={[
                { title: result.target, href: `/perusahaan/${result.target}` },
                { title: 'Kandidat sejenis', href: '/kandidat-menarik' },
            ]}
        >
            <Head title="Kandidat Sejenis" />
            <main className="min-w-0 space-y-6 p-4 md:p-6">
                <header className="border-b pb-5">
                    <h1 className="text-xl font-semibold">Kandidat sejenis {result.target}</h1>
                    <p className="text-muted-foreground mt-2 text-sm">{result.total} perusahaan dengan bukti fundamental dari populasi yang sama.</p>
                    <p className="text-muted-foreground mt-2 text-xs">
                        Bukti{' '}
                        {new Intl.DateTimeFormat('id-ID', { dateStyle: 'medium', timeStyle: 'short', timeZone: 'Asia/Jakarta' }).format(
                            new Date(result.fetchedAt),
                        )}{' '}
                        WIB
                        {Date.parse(result.expiresAt) <= Date.now() ? ' (historis; perbarui analisis sebelum dipakai sebagai kondisi terkini)' : ''}.
                    </p>
                </header>
                <form
                    className="flex flex-wrap gap-2"
                    onSubmit={(event) => {
                        event.preventDefault();
                        visit();
                    }}
                >
                    <Input
                        className="max-w-sm"
                        aria-label="Cari kandidat"
                        placeholder="Nama atau kode perusahaan..."
                        value={query}
                        onChange={(event) => setQuery(event.target.value)}
                    />
                    <select
                        aria-label="Urutkan menurut"
                        value={component}
                        onChange={(event) => setComponent(event.target.value)}
                        className="bg-background max-w-full rounded-md border px-3 py-2 text-sm"
                    >
                        {Object.entries(components).map(([key, label]) => (
                            <option key={key} value={key}>
                                {label}
                            </option>
                        ))}
                    </select>
                    <Button variant="outline" type="submit">
                        <Search className="size-4" />
                        Terapkan
                    </Button>
                    <Button
                        variant="ghost"
                        type="button"
                        onClick={() => {
                            setQuery('');
                            setComponent('quality');
                            router.get('/kandidat-menarik', { evidence: result.evidenceId });
                        }}
                    >
                        Reset
                    </Button>
                </form>
                <div className="overflow-x-auto rounded-md border">
                    <table className="w-full min-w-[700px] text-sm">
                        <thead className="bg-muted text-xs uppercase">
                            <tr>
                                <th className="p-3 text-left">Perusahaan</th>
                                {Object.values(components).map((label) => (
                                    <th key={label} className="p-3 text-right">
                                        {label}
                                    </th>
                                ))}
                                <th className="p-3 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            {result.items.map((row) => (
                                <tr key={row.symbol} className="hover:bg-muted/40 border-t">
                                    <td className="p-3">
                                        <div className="flex items-center gap-3">
                                            <CompanyLogo
                                                company={{ ...row, logoUrl: `https://storage.googleapis.com/sectorsapp-sea/logo/${row.symbol}.webp` }}
                                            />
                                            <div>
                                                <Link href={`/perusahaan/${row.symbol}`} className="font-medium">
                                                    {row.symbol}
                                                </Link>
                                                <p className="text-muted-foreground text-xs">{row.name}</p>
                                                <p className="text-muted-foreground mt-1 text-xs">{row.group ?? 'Subindustri belum tersedia'}</p>
                                            </div>
                                        </div>
                                    </td>
                                    {Object.keys(components).map((key) => (
                                        <td key={key} className="p-3 text-right tabular-nums">
                                            {number(row.components[key])}
                                        </td>
                                    ))}
                                    <td className="p-3">
                                        <div className="flex justify-end gap-1">
                                            <Button asChild size="icon" variant="ghost" title="Buka perusahaan">
                                                <Link href={`/perusahaan/${row.symbol}`} aria-label={`Buka ${row.symbol}`}>
                                                    <Eye className="size-4" />
                                                </Link>
                                            </Button>
                                            <Button asChild size="icon" variant="ghost" title="Bandingkan">
                                                <Link
                                                    href={`/bandingkan?symbols=${[...new Set([result.target, row.symbol])].join(',')}`}
                                                    aria-label={`Bandingkan ${row.symbol}`}
                                                >
                                                    <GitCompare className="size-4" />
                                                </Link>
                                            </Button>
                                        </div>
                                    </td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                    {result.items.length === 0 && (
                        <p role="status" className="p-8 text-center text-sm">
                            Tidak ada kandidat yang cocok. Ubah pencarian atau reset filter.
                        </p>
                    )}
                </div>
                <nav aria-label="Halaman kandidat" className="flex items-center justify-between">
                    <span className="text-sm">
                        Halaman {filters.page} dari {Math.max(1, Math.ceil(result.total / 15))}
                    </span>
                    <div className="flex gap-2">
                        <Button
                            variant="outline"
                            size="icon"
                            title="Sebelumnya"
                            aria-label="Sebelumnya"
                            disabled={filters.page <= 1}
                            onClick={() => visit(filters.page - 1)}
                        >
                            <ChevronLeft className="size-4" />
                        </Button>
                        <Button
                            variant="outline"
                            size="icon"
                            title="Berikutnya"
                            aria-label="Berikutnya"
                            disabled={filters.page * 15 >= result.total}
                            onClick={() => visit(filters.page + 1)}
                        >
                            <ChevronRight className="size-4" />
                        </Button>
                    </div>
                </nav>
                <p className="text-muted-foreground text-xs leading-6">
                    Urutan memakai angka sebelum pembulatan. Tiap komponen tetap memerlukan lima peer valid; nilai kosong bukan nol. Posisi relatif
                    tidak berarti rekomendasi membeli atau menjual. Kelompok dan periode pembanding dapat berbeda per metrik; periksa bukti pada
                    detail perusahaan.
                </p>
            </main>
        </AppLayout>
    );
}
