import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/react';
import { ArrowRight, BarChart3, Database, GitCompare, Search, ShieldCheck, WalletCards } from 'lucide-react';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
];

const metrics = [
    { label: 'Credit Sectors MVP', value: '1.000', note: 'Budget aplikasi sekali pakai' },
    { label: 'Kuota harian user', value: '20', note: 'Reset 00.00 WIB' },
    { label: 'Cache screener', value: '1 jam', note: 'Hit tidak memakai credit' },
    { label: 'Compare MVP', value: '3', note: 'Maksimum saham' },
];

const flows = [
    { title: 'Temukan Saham', href: '/temukan-saham', icon: Search, text: 'Filter kandidat dengan structured screener dan pagination.' },
    { title: 'Detail Perusahaan', href: '/perusahaan', icon: BarChart3, text: 'Profil, ringkasan metrik, freshness, dan bukti data.' },
    { title: 'Bandingkan', href: '/bandingkan', icon: GitCompare, text: 'Bandingkan maksimal 3 saham dengan snapshot privat.' },
];

export default function Dashboard() {
    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Dashboard" />
            <div className="flex flex-1 flex-col gap-4 p-4">
                <section className="grid gap-4 lg:grid-cols-[1.6fr_1fr]">
                    <div className="rounded-lg border bg-card p-5 text-card-foreground">
                        <div className="flex flex-wrap items-start justify-between gap-3">
                            <div className="max-w-2xl">
                                <Badge variant="outline">MVP Workspace</Badge>
                                <h1 className="mt-3 text-2xl font-semibold">NusaLens Market Intelligence</h1>
                                <p className="mt-2 text-sm text-muted-foreground">
                                    Ruang kerja untuk menyaring, membandingkan, dan memahami saham Indonesia yang layak diteliti lebih lanjut.
                                </p>
                            </div>
                            <Button asChild>
                                <Link href="/temukan-saham" prefetch>
                                    Mulai riset <ArrowRight />
                                </Link>
                            </Button>
                        </div>
                        <div className="mt-5 grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
                            {metrics.map((metric) => (
                                <div key={metric.label} className="rounded-md border bg-background p-3">
                                    <div className="text-xs text-muted-foreground">{metric.label}</div>
                                    <div className="mt-2 text-xl font-semibold">{metric.value}</div>
                                    <div className="mt-1 text-xs text-muted-foreground">{metric.note}</div>
                                </div>
                            ))}
                        </div>
                    </div>

                    <Card>
                        <CardHeader>
                            <CardTitle className="text-base">Status Fondasi</CardTitle>
                            <CardDescription>Placeholder ini belum melakukan live call Sectors.</CardDescription>
                        </CardHeader>
                        <CardContent className="space-y-3 text-sm">
                            <div className="flex items-start gap-3">
                                <ShieldCheck className="mt-0.5 size-4 text-emerald-600" />
                                <span>Fitur riset berada di balik login dan verifikasi email.</span>
                            </div>
                            <div className="flex items-start gap-3">
                                <Database className="mt-0.5 size-4 text-sky-600" />
                                <span>Ledger credit disimpan di MySQL; Redis hanya cache.</span>
                            </div>
                            <div className="flex items-start gap-3">
                                <WalletCards className="mt-0.5 size-4 text-amber-600" />
                                <span>Structured screener memakai estimasi 1 credit saat cache miss.</span>
                            </div>
                        </CardContent>
                    </Card>
                </section>

                <section className="grid gap-4 md:grid-cols-3">
                    {flows.map((flow) => (
                        <Card key={flow.title}>
                            <CardHeader>
                                <div className="flex items-center gap-2">
                                    <flow.icon className="size-4" />
                                    <CardTitle className="text-base">{flow.title}</CardTitle>
                                </div>
                                <CardDescription>{flow.text}</CardDescription>
                            </CardHeader>
                            <CardContent>
                                <Button asChild variant="outline" size="sm">
                                    <Link href={flow.href} prefetch>
                                        Buka <ArrowRight />
                                    </Link>
                                </Button>
                            </CardContent>
                        </Card>
                    ))}
                </section>
            </div>
        </AppLayout>
    );
}
