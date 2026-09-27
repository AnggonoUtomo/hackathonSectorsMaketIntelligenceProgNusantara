import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/app-layout';
import type { BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/react';
import { Bookmark, CalendarDays, GitCompare, Plus } from 'lucide-react';

type SnapshotListItem = {
    id: string;
    title: string;
    symbols: string[];
    version: number;
    createdAt: string | null;
};

type Props = {
    snapshots: SnapshotListItem[];
};

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Snapshot Perbandingan', href: '/bandingkan/snapshots' },
];

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

export default function ComparisonSnapshots({ snapshots }: Props) {
    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Snapshot Perbandingan" />
            <div className="flex flex-1 flex-col gap-5 p-4">
                <section className="dashboard-card dashboard-card--blue rounded-lg border p-5">
                    <div className="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
                        <div>
                            <div className="flex items-center gap-2">
                                <Bookmark className="size-5 text-blue-700" />
                                <h1 className="text-xl font-semibold">Snapshot Perbandingan</h1>
                            </div>
                            <p className="text-muted-foreground mt-2 max-w-2xl text-sm leading-6">
                                Snapshot menyimpan kondisi riset saat tombol simpan ditekan. Membuka halaman ini tidak memuat ulang provider dan tidak
                                memakai credit API.
                            </p>
                        </div>
                        <Button asChild>
                            <Link href="/bandingkan">
                                <Plus className="size-4" />
                                Buat Snapshot
                            </Link>
                        </Button>
                    </div>
                </section>

                {snapshots.length === 0 ? (
                    <section role="status" className="rounded-lg border border-dashed p-10 text-center">
                        <GitCompare className="text-muted-foreground mx-auto mb-3 size-9" />
                        <h2 className="font-semibold">Belum ada snapshot</h2>
                        <p className="text-muted-foreground mx-auto mt-2 max-w-xl text-sm">
                            Pilih maksimal tiga saham di halaman Bandingkan, muat section yang dibutuhkan, lalu simpan sebagai snapshot privat.
                        </p>
                        <Button asChild className="mt-4">
                            <Link href="/bandingkan">Mulai Bandingkan</Link>
                        </Button>
                    </section>
                ) : (
                    <section className="overflow-hidden rounded-lg border">
                        <div className="overflow-x-auto">
                            <table className="w-full min-w-[760px] text-sm">
                                <caption className="text-muted-foreground p-4 text-left text-xs">Daftar snapshot privat milik akun saat ini.</caption>
                                <thead className="bg-muted/60 text-xs uppercase">
                                    <tr>
                                        <th scope="col" className="p-3 text-left">
                                            Snapshot
                                        </th>
                                        <th scope="col" className="p-3 text-left">
                                            Saham
                                        </th>
                                        <th scope="col" className="p-3 text-left">
                                            Versi
                                        </th>
                                        <th scope="col" className="p-3 text-left">
                                            Dibuat
                                        </th>
                                        <th scope="col" className="p-3 text-right">
                                            Aksi
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {snapshots.map((snapshot) => (
                                        <tr key={snapshot.id} className="hover:bg-muted/40 border-t">
                                            <td className="p-3">
                                                <div className="font-medium">{snapshot.title}</div>
                                                <div className="text-muted-foreground mt-1 font-mono text-xs">{snapshot.id}</div>
                                            </td>
                                            <td className="p-3">
                                                <div className="flex flex-wrap gap-1.5">
                                                    {snapshot.symbols.map((symbol) => (
                                                        <Badge key={symbol} variant="outline">
                                                            {symbol}
                                                        </Badge>
                                                    ))}
                                                </div>
                                            </td>
                                            <td className="p-3">
                                                <Badge variant="secondary">v{snapshot.version}</Badge>
                                            </td>
                                            <td className="text-muted-foreground p-3">
                                                <span className="inline-flex items-center gap-2">
                                                    <CalendarDays className="size-4" />
                                                    {formatDate(snapshot.createdAt)}
                                                </span>
                                            </td>
                                            <td className="p-3 text-right">
                                                <Button asChild variant="outline" size="sm">
                                                    <Link href={`/bandingkan/snapshots/${snapshot.id}`}>Buka</Link>
                                                </Button>
                                            </td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        </div>
                    </section>
                )}
            </div>
        </AppLayout>
    );
}
