import CompanyAutocomplete from '@/components/company-autocomplete';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/app-layout';
import { Head, Link, router } from '@inertiajs/react';
import { ArrowRight, Bookmark, GitCompare, Search } from 'lucide-react';
import { useState } from 'react';

export default function Dashboard() {
    const [query, setQuery] = useState('');
    return (
        <AppLayout breadcrumbs={[{ title: 'Ruang Riset', href: '/dashboard' }]}>
            <Head title="Ruang Riset" />
            <main className="flex min-w-0 flex-1 flex-col gap-8 p-4 md:p-6">
                <header className="border-b pb-5">
                    <p className="text-sm font-medium text-teal-700 dark:text-teal-300">NusaLens</p>
                    <h1 className="mt-2 text-2xl font-semibold">Ruang Riset Saham Indonesia</h1>
                </header>
                <section className="max-w-3xl space-y-4" aria-label="Mulai riset perusahaan">
                    <h2 className="text-base font-semibold">Perusahaan mana yang ingin kamu pelajari?</h2>
                    <CompanyAutocomplete
                        value={query}
                        onChange={setQuery}
                        onSelect={(company) => router.visit(`/perusahaan/${company.symbol}`)}
                        onSearch={() => router.get('/temukan-saham', { keyword: query })}
                    />
                    <Button asChild variant="outline">
                        <Link href="/temukan-saham">
                            <Search className="size-4" />
                            Semua perusahaan
                            <ArrowRight className="size-4" />
                        </Link>
                    </Button>
                </section>
                <section className="grid gap-6 border-y py-6 sm:grid-cols-2">
                    <div className="space-y-3">
                        <GitCompare className="size-6 text-sky-600" />
                        <h2 className="font-semibold">Bandingkan perusahaan</h2>
                        <p className="text-muted-foreground max-w-md text-sm leading-6">
                            Harga, kinerja keuangan, dan bukti analisis untuk maksimal tiga perusahaan.
                        </p>
                        <Button asChild variant="outline">
                            <Link href="/bandingkan">
                                Buka perbandingan
                                <ArrowRight className="size-4" />
                            </Link>
                        </Button>
                    </div>
                    <div className="space-y-3">
                        <Bookmark className="size-6 text-amber-600" />
                        <h2 className="font-semibold">Riset tersimpan</h2>
                        <p className="text-muted-foreground max-w-md text-sm leading-6">
                            Kembali ke bukti yang sudah disimpan atau mulai versi perbandingan terbaru.
                        </p>
                        <Button asChild variant="outline">
                            <Link href="/bandingkan/snapshots">
                                Buka riset tersimpan
                                <ArrowRight className="size-4" />
                            </Link>
                        </Button>
                    </div>
                </section>
                <footer className="text-muted-foreground max-w-3xl text-xs leading-6">
                    Sumber data: Sectors Financial API. NusaLens membantu riset dengan data dan perhitungan yang dapat diperiksa. Bukan penasihat
                    investasi atau rekomendasi membeli dan menjual saham.
                </footer>
            </main>
        </AppLayout>
    );
}
