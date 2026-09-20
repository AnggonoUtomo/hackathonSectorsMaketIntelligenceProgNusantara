import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/react';
import { BarChart3, Building2, Database, GitCompare, Info, Radar, Search, Sparkles } from 'lucide-react';

type SectionKey = 'discover' | 'companies' | 'compare' | 'research' | 'candidates';

interface PlaceholderProps {
    section: SectionKey;
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

const companies = [
    { symbol: 'BBCA', name: 'Bank Central Asia Tbk', sector: 'Financials', score: '82,45', completeness: '91,00', freshness: 'Cache 42 menit' },
    { symbol: 'TLKM', name: 'Telkom Indonesia Tbk', sector: 'Infrastructure', score: '78,20', completeness: '86,50', freshness: 'Cache 18 menit' },
    { symbol: 'ICBP', name: 'Indofood CBP Sukses Makmur Tbk', sector: 'Consumer Non-Cyclicals', score: '74,85', completeness: '88,00', freshness: 'Cache 51 menit' },
];

const breadcrumbs = (title: string): BreadcrumbItem[] => [
    { title: 'Dashboard', href: '/dashboard' },
    { title, href: '#' },
];

export default function NusaLensPlaceholder({ section }: PlaceholderProps) {
    const current = sections[section] ?? sections.discover;
    const Icon = current.icon;

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
                            <span className="text-muted-foreground">Estimasi aksi:</span> 1 credit saat cache miss
                        </div>
                    </div>
                </section>

                <section className="grid gap-4 xl:grid-cols-[320px_1fr]">
                    <Card>
                        <CardHeader>
                            <CardTitle className="text-base">Filter Placeholder</CardTitle>
                            <CardDescription>Belum mengirim request ke Sectors API.</CardDescription>
                        </CardHeader>
                        <CardContent className="space-y-3">
                            <div className="space-y-1.5">
                                <label className="text-sm font-medium" htmlFor="keyword">
                                    Ticker atau nama
                                </label>
                                <Input id="keyword" placeholder="BBCA, TLKM, ICBP" />
                            </div>
                            <div className="space-y-1.5">
                                <label className="text-sm font-medium">Sektor</label>
                                <Select defaultValue="all">
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
                                    <Input id="min-score" placeholder="70,00" />
                                </div>
                                <div className="space-y-1.5">
                                    <label className="text-sm font-medium" htmlFor="max-items">
                                        Limit
                                    </label>
                                    <Input id="max-items" placeholder="10" />
                                </div>
                            </div>
                            <Button className="w-full" disabled>
                                Jalankan setelah backend siap
                            </Button>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader>
                            <div className="flex flex-wrap items-start justify-between gap-3">
                                <div>
                                    <CardTitle className="text-base">Shortlist Contoh</CardTitle>
                                    <CardDescription>Angka berikut hanya data contoh untuk mempelajari layout.</CardDescription>
                                </div>
                                <Badge variant="secondary">2 desimal</Badge>
                            </div>
                        </CardHeader>
                        <CardContent>
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
