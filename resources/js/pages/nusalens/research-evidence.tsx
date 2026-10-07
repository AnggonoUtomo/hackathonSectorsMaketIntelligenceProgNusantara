import ResearchPriorityPanel from '@/components/research-priority';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/app-layout';
import type { ResearchPriority } from '@/types/research-priority';
import { Head, Link } from '@inertiajs/react';
import { ArrowLeft, FileCheck2, FileQuestion, Users } from 'lucide-react';

type Evidence = {
    id: string;
    input: { source: string };
    result: ResearchPriority;
};

function date(value: string) {
    const parsed = new Date(value);
    return Number.isNaN(parsed.getTime())
        ? 'Tanggal tidak tersedia'
        : `${new Intl.DateTimeFormat('id-ID', { dateStyle: 'medium', timeStyle: 'short', timeZone: 'Asia/Jakarta' }).format(parsed)} WIB`;
}

export default function ResearchEvidence({ evidence }: { evidence: Evidence | null }) {
    const result = evidence?.result;
    return (
        <AppLayout
            breadcrumbs={[
                { title: 'Temukan Saham', href: '/temukan-saham' },
                { title: 'Bukti Perhitungan', href: evidence ? `/nusalens/evidence/${evidence.id}` : '#' },
            ]}
        >
            <Head title={result ? `Bukti ${result.symbol}` : 'Bukti Tidak Ditemukan'} />
            <main className="min-w-0 space-y-6 p-4 md:p-6">
                {evidence && result ? (
                    <>
                        <header className="space-y-4">
                            <div className="flex flex-wrap items-center gap-2">
                                <FileCheck2 className="size-5 text-teal-600" aria-hidden="true" />
                                <h1 className="text-xl font-semibold">Bukti Perhitungan</h1>
                                <Badge variant="secondary">{result.symbol}</Badge>
                                <Badge variant="outline">{result.formulaVersion}</Badge>
                            </div>
                            <p className="text-lg font-medium break-words">{result.name}</p>
                            <nav aria-label="Navigasi bukti" className="flex flex-wrap gap-2">
                                <Button asChild variant="outline">
                                    <Link href={`/perusahaan/${result.symbol}`}>
                                        <ArrowLeft className="size-4" />
                                        Perusahaan
                                    </Link>
                                </Button>
                                <Button asChild variant="outline">
                                    <Link href={`/kandidat-menarik?evidence=${evidence.id}`}>
                                        <Users className="size-4" />
                                        Kandidat sejenis
                                    </Link>
                                </Button>
                            </nav>
                        </header>
                        <dl className="grid gap-4 border-y py-4 text-sm sm:grid-cols-2 xl:grid-cols-4">
                            <div>
                                <dt className="text-muted-foreground text-xs">Sumber</dt>
                                <dd className="mt-1 break-words">{evidence.input.source}</dd>
                            </div>
                            <div>
                                <dt className="text-muted-foreground text-xs">Diambil pada</dt>
                                <dd className="mt-1">{date(result.fetchedAt)}</dd>
                            </div>
                            <div>
                                <dt className="text-muted-foreground text-xs">Batas berlaku input saat disimpan</dt>
                                <dd className="mt-1">{date(result.expiresAt)}</dd>
                            </div>
                            <div>
                                <dt className="text-muted-foreground text-xs">ID bukti</dt>
                                <dd className="mt-1 font-mono text-xs leading-5 break-all">{evidence.id}</dd>
                            </div>
                        </dl>
                        <ResearchPriorityPanel symbol={result.symbol} initial={result} historical detailed />
                    </>
                ) : (
                    <section className="space-y-4 py-10">
                        <FileQuestion className="text-muted-foreground size-8" aria-hidden="true" />
                        <h1 className="text-xl font-semibold">Bukti tidak ditemukan</h1>
                        <p className="text-muted-foreground max-w-lg text-sm">
                            Bukti mungkin sudah melewati masa retensi atau tautannya tidak valid. Salinan pada riset tersimpan tetap tersedia.
                        </p>
                        <Button asChild variant="outline">
                            <Link href="/bandingkan/snapshots">
                                <ArrowLeft className="size-4" />
                                Riset tersimpan
                            </Link>
                        </Button>
                    </section>
                )}
            </main>
        </AppLayout>
    );
}
