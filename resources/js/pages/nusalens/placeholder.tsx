import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, router, useForm } from '@inertiajs/react';
import { BarChart3, Building2, Database, GitCompare, Info, Radar, Search, Sparkles } from 'lucide-react';
import { FormEvent } from 'react';

type SectionKey = 'discover' | 'companies' | 'compare' | 'research' | 'candidates';

interface PlaceholderProps {
    section: SectionKey;
    discover?: DiscoverPayload;
}

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
        source: 'backend_fake';
        state: 'ready' | 'empty';
        estimatedCredits: number;
        cachePolicy: string;
        liveProvider: boolean;
    };
}

const sections = {
    discover: {
        title: 'Temukan Saham',
        eyebrow: 'Structured Screener',
        description: 'Filter kandidat berdasarkan sektor, ukuran, dan kualitas bisnis tanpa query bebas mentah.',
        icon: Search,
    },
    companies: {
        title: 'Detail Perusahaan',
        eyebrow: 'Company Snapshot',
        description: 'Ringkasan profil dan metrik utama sebelum user membuka laporan lebih lengkap.',
        icon: Building2,
    },
    compare: {
        title: 'Bandingkan',
        eyebrow: 'Comparison',
        description: 'Perbandingan privat maksimal 3 saham, berbasis snapshot dan versi tersimpan.',
        icon: GitCompare,
    },
    research: {
        title: 'Jelaskan Nilai',
        eyebrow: 'Research Explanation',
        description: 'Penjelasan berbasis aturan atas angka yang dihitung sistem, bukan rekomendasi beli atau jual.',
        icon: Sparkles,
    },
    candidates: {
        title: 'Kandidat Menarik',
        eyebrow: 'Research Priority',
        description: 'Daftar kandidat yang layak dipelajari setelah scoring dan bukti input tersedia.',
        icon: Radar,
    },
} satisfies Record<SectionKey, { title: string; eyebrow: string; description: string; icon: typeof Search }>;

const fallbackCompanies = [
    { symbol: 'BBCA', name: 'Bank Central Asia Tbk', sector: 'Financials', score: '82,45', completeness: '91,00', freshness: 'Cache 42 menit' },
    { symbol: 'TLKM', name: 'Telkom Indonesia Tbk', sector: 'Infrastructure', score: '78,20', completeness: '86,50', freshness: 'Cache 18 menit' },
    { symbol: 'ICBP', name: 'Indofood CBP Sukses Makmur Tbk', sector: 'Consumer Non-Cyclicals', score: '74,85', completeness: '88,00', freshness: 'Cache 51 menit' },
];

const breadcrumbs = (title: string): BreadcrumbItem[] => [
    { title: 'Dashboard', href: '/dashboard' },
    { title, href: '#' },
];

export default function NusaLensPlaceholder({ section, discover }: PlaceholderProps) {
    const current = sections[section] ?? sections.discover;
    const Icon = current.icon;
    const isDiscover = section === 'discover';
    const companies = discover?.results ?? fallbackCompanies;
    const meta = discover?.meta ?? {
        source: 'backend_fake',
        state: companies.length === 0 ? 'empty' : 'ready',
        estimatedCredits: 1,
        cachePolicy: '1 jam',
        liveProvider: false,
    };
    const form = useForm({
        keyword: discover?.filters.keyword ?? '',
        sector: discover?.filters.sector ?? 'all',
        min_score: discover?.filters.minScore ?? '',
        limit: discover?.filters.limit ?? '10',
    });

    function submitDiscover(event: FormEvent<HTMLFormElement>) {
        event.preventDefault();

        form.get('/temukan-saham', {
            preserveScroll: true,
            preserveState: true,
        });
    }

    function resetDiscover() {
        router.get('/temukan-saham', {}, { preserveScroll: true });
    }

    return (
        <AppLayout breadcrumbs={breadcrumbs(current.title)}>
            <Head title={current.title} />
            <div className="flex flex-1 flex-col gap-4 p-4">
                <section className="rounded-lg border bg-card p-5 text-card-foreground">
                    <div className="flex flex-wrap items-start justify-between gap-4">
                        <div className="max-w-3xl">
                            <Badge variant="outline">{current.eyebrow}</Badge>
                            <div className="mt-3 flex items-center gap-3">
                                <Icon className="size-6" />
                                <h1 className="text-2xl font-semibold">{current.title}</h1>
                            </div>
                            <p className="mt-2 text-sm text-muted-foreground">{current.description}</p>
                        </div>
                        <div className="rounded-md border bg-background px-3 py-2 text-sm">
                            <span className="text-muted-foreground">Estimasi aksi:</span> {meta.estimatedCredits} credit saat cache miss
                        </div>
                    </div>
                </section>

                <section className="grid gap-4 xl:grid-cols-[320px_1fr]">
                    <Card>
                        <CardHeader>
                            <CardTitle className="text-base">{isDiscover ? 'Filter Backend Fake' : 'Filter Placeholder'}</CardTitle>
                            <CardDescription>
                                {isDiscover ? 'Submit filter ke Laravel, hasil masih dari provider fake internal.' : 'Belum mengirim request ke Sectors API.'}
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <form className="space-y-3" onSubmit={submitDiscover}>
                            <div className="space-y-1.5">
                                <label className="text-sm font-medium" htmlFor="keyword">
                                    Ticker atau nama
                                </label>
                                <Input
                                    id="keyword"
                                    placeholder="BBCA, TLKM, ICBP"
                                    value={form.data.keyword}
                                    onChange={(event) => form.setData('keyword', event.target.value)}
                                    disabled={!isDiscover}
                                />
                            </div>
                            <div className="space-y-1.5">
                                <label className="text-sm font-medium">Sektor</label>
                                <Select value={form.data.sector} onValueChange={(value) => form.setData('sector', value)} disabled={!isDiscover}>
                                    <SelectTrigger>
                                        <SelectValue placeholder="Pilih sektor" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="all">Semua sektor</SelectItem>
                                        <SelectItem value="financials">Financials</SelectItem>
                                        <SelectItem value="consumer">Consumer Non-Cyclicals</SelectItem>
                                        <SelectItem value="infrastructure">Infrastructure</SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>
                            <div className="grid grid-cols-2 gap-3">
                                <div className="space-y-1.5">
                                    <label className="text-sm font-medium" htmlFor="min-score">
                                        Nilai min
                                    </label>
                                    <Input
                                        id="min-score"
                                        placeholder="70,00"
                                        value={form.data.min_score}
                                        onChange={(event) => form.setData('min_score', event.target.value)}
                                        disabled={!isDiscover}
                                    />
                                </div>
                                <div className="space-y-1.5">
                                    <label className="text-sm font-medium" htmlFor="max-items">
                                        Limit
                                    </label>
                                    <Input
                                        id="max-items"
                                        placeholder="10"
                                        value={form.data.limit}
                                        onChange={(event) => form.setData('limit', event.target.value)}
                                        disabled={!isDiscover}
                                    />
                                </div>
                            </div>
                            <div className="grid grid-cols-2 gap-2">
                                <Button type="submit" disabled={!isDiscover || form.processing}>
                                    Terapkan
                                </Button>
                                <Button type="button" variant="outline" disabled={!isDiscover || form.processing} onClick={resetDiscover}>
                                    Reset
                                </Button>
                            </div>
                            </form>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader>
                            <div className="flex flex-wrap items-start justify-between gap-3">
                                <div>
                                    <CardTitle className="text-base">Shortlist Contoh</CardTitle>
                                    <CardDescription>
                                        {isDiscover
                                            ? `Sumber ${meta.source}; live provider ${meta.liveProvider ? 'aktif' : 'nonaktif'}; cache ${meta.cachePolicy}.`
                                            : 'Angka berikut hanya data contoh untuk mempelajari layout.'}
                                    </CardDescription>
                                </div>
                                <Badge variant="secondary">2 desimal</Badge>
                            </div>
                        </CardHeader>
                        <CardContent>
                            {companies.length === 0 ? (
                                <div role="status" className="rounded-md border border-dashed p-8 text-center">
                                    <div className="text-sm font-medium">Belum ada kandidat cocok</div>
                                    <p className="mt-1 text-sm text-muted-foreground">Coba longgarkan filter ticker, sektor, atau nilai minimum.</p>
                                </div>
                            ) : (
                                <div className="overflow-x-auto rounded-md border">
                                <table className="w-full min-w-[720px] text-sm">
                                    <thead className="bg-muted/50 text-left">
                                        <tr>
                                            <th className="px-3 py-2 font-medium">Ticker</th>
                                            <th className="px-3 py-2 font-medium">Perusahaan</th>
                                            <th className="px-3 py-2 font-medium">Sektor</th>
                                            <th className="px-3 py-2 text-right font-medium">Nilai</th>
                                            <th className="px-3 py-2 text-right font-medium">Kelengkapan</th>
                                            <th className="px-3 py-2 font-medium">Freshness</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {companies.map((company) => (
                                            <tr key={company.symbol} className="border-t">
                                                <td className="px-3 py-3 font-semibold">{company.symbol}</td>
                                                <td className="px-3 py-3">{company.name}</td>
                                                <td className="px-3 py-3 text-muted-foreground">{company.sector}</td>
                                                <td className="px-3 py-3 text-right tabular-nums">{company.score}</td>
                                                <td className="px-3 py-3 text-right tabular-nums">{company.completeness}%</td>
                                                <td className="px-3 py-3 text-muted-foreground">{company.freshness}</td>
                                            </tr>
                                        ))}
                                    </tbody>
                                </table>
                            </div>
                            )}
                        </CardContent>
                    </Card>
                </section>

                <section className="grid gap-4 md:grid-cols-3">
                    <Card>
                        <CardHeader>
                            <div className="flex items-center gap-2">
                                <Database className="size-4" />
                                <CardTitle className="text-base">Data Policy</CardTitle>
                            </div>
                        </CardHeader>
                        <CardContent className="text-sm text-muted-foreground">
                            Cache hit tidak menambah ledger credit. Refresh live nanti tetap on-demand dan bertahap.
                        </CardContent>
                    </Card>
                    <Card>
                        <CardHeader>
                            <div className="flex items-center gap-2">
                                <BarChart3 className="size-4" />
                                <CardTitle className="text-base">Scoring</CardTitle>
                            </div>
                        </CardHeader>
                        <CardContent className="text-sm text-muted-foreground">
                            Nilai ditampilkan dua angka di belakang koma dan tidak memakai label BUY/HOLD/SELL.
                        </CardContent>
                    </Card>
                    <Card>
                        <CardHeader>
                            <div className="flex items-center gap-2">
                                <Info className="size-4" />
                                <CardTitle className="text-base">Batas Riset</CardTitle>
                            </div>
                        </CardHeader>
                        <CardContent className="text-sm text-muted-foreground">
                            NusaLens adalah alat informasi dan riset, bukan penasihat investasi atau broker.
                        </CardContent>
                    </Card>
                </section>
            </div>
        </AppLayout>
    );
}
