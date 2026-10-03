import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import AppLayout from '@/layouts/app-layout';
import type { BreadcrumbItem } from '@/types';
import { Head, Link, router } from '@inertiajs/react';
import { Bookmark, CalendarDays, Check, ChevronLeft, ChevronRight, Eye, GitCompare, Pencil, Plus, Search, Trash2, X } from 'lucide-react';
import { useState } from 'react';

type SnapshotListItem = {
    id: string;
    title: string;
    symbols: string[];
    version: number;
    createdAt: string | null;
};

type Props = {
    snapshots: SnapshotListItem[];
    pagination: { total: number; page: number; lastPage: number };
    filters: { q: string };
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

export default function ComparisonSnapshots({ snapshots, pagination, filters }: Props) {
    const [query, setQuery] = useState(filters.q);
    const [editing, setEditing] = useState<string | null>(null);
    const [title, setTitle] = useState('');
    const [deleting, setDeleting] = useState<string | null>(null);
    const [busy, setBusy] = useState(false);
    const [error, setError] = useState('');
    function rename(id: string) {
        setBusy(true);
        setError('');
        router.patch(
            `/bandingkan/snapshots/${id}`,
            { title },
            {
                preserveScroll: true,
                onSuccess: () => setEditing(null),
                onError: (errors) => setError(errors.title ?? 'Nama belum tersimpan.'),
                onFinish: () => setBusy(false),
            },
        );
    }
    function remove(id: string) {
        setBusy(true);
        router.delete(`/bandingkan/snapshots/${id}`, { preserveScroll: true, onSuccess: () => setDeleting(null), onFinish: () => setBusy(false) });
    }
    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Snapshot Perbandingan" />
            <div className="flex flex-1 flex-col gap-5 p-4">
                {error && (
                    <p role="alert" className="text-sm text-rose-700">
                        {error}
                    </p>
                )}
                <form
                    className="flex flex-wrap gap-2"
                    onSubmit={(event) => {
                        event.preventDefault();
                        router.get('/bandingkan/snapshots', { q: query }, { preserveState: true });
                    }}
                >
                    <Input
                        aria-label="Cari riset tersimpan"
                        className="max-w-sm"
                        placeholder="Nama riset..."
                        value={query}
                        onChange={(event) => setQuery(event.target.value)}
                    />
                    <Button variant="outline" type="submit">
                        <Search className="size-4" />
                        Terapkan
                    </Button>
                    {filters.q && (
                        <Button
                            variant="ghost"
                            type="button"
                            onClick={() => {
                                setQuery('');
                                router.get('/bandingkan/snapshots');
                            }}
                        >
                            Reset
                        </Button>
                    )}
                    <span className="text-muted-foreground self-center text-sm">{pagination.total} riset tersimpan</span>
                </form>
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
                        <h2 className="font-semibold">{filters.q ? 'Tidak ada riset yang cocok' : 'Belum ada snapshot'}</h2>
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
                                                {editing === snapshot.id ? (
                                                    <form
                                                        className="flex gap-2"
                                                        onSubmit={(event) => {
                                                            event.preventDefault();
                                                            rename(snapshot.id);
                                                        }}
                                                    >
                                                        <Input
                                                            aria-label="Nama riset"
                                                            value={title}
                                                            onChange={(event) => setTitle(event.target.value)}
                                                            maxLength={120}
                                                            autoFocus
                                                        />
                                                        <Button
                                                            size="icon"
                                                            type="submit"
                                                            title="Simpan nama"
                                                            aria-label="Simpan nama"
                                                            disabled={busy || !title.trim()}
                                                        >
                                                            <Check className="size-4" />
                                                        </Button>
                                                        <Button
                                                            type="button"
                                                            size="icon"
                                                            variant="ghost"
                                                            onClick={() => setEditing(null)}
                                                            title="Batal"
                                                            aria-label="Batal"
                                                        >
                                                            <X className="size-4" />
                                                        </Button>
                                                    </form>
                                                ) : (
                                                    <div className="font-medium">{snapshot.title}</div>
                                                )}
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
                                                {deleting === snapshot.id ? (
                                                    <div className="flex flex-wrap justify-end gap-2">
                                                        <span className="w-full text-xs">Hapus versi ini?</span>
                                                        <Button variant="destructive" size="sm" disabled={busy} onClick={() => remove(snapshot.id)}>
                                                            Hapus
                                                        </Button>
                                                        <Button variant="ghost" size="sm" onClick={() => setDeleting(null)}>
                                                            Batal
                                                        </Button>
                                                    </div>
                                                ) : (
                                                    <div className="flex justify-end gap-1">
                                                        <Button asChild variant="ghost" size="icon" title="Buka riset">
                                                            <Link href={`/bandingkan/snapshots/${snapshot.id}`} aria-label={`Buka ${snapshot.title}`}>
                                                                <Eye className="size-4" />
                                                            </Link>
                                                        </Button>
                                                        <Button
                                                            variant="ghost"
                                                            size="icon"
                                                            title="Ubah nama"
                                                            aria-label={`Ubah nama ${snapshot.title}`}
                                                            onClick={() => {
                                                                setEditing(snapshot.id);
                                                                setTitle(snapshot.title);
                                                            }}
                                                        >
                                                            <Pencil className="size-4" />
                                                        </Button>
                                                        <Button
                                                            variant="ghost"
                                                            size="icon"
                                                            title="Hapus versi"
                                                            aria-label={`Hapus ${snapshot.title}`}
                                                            onClick={() => setDeleting(snapshot.id)}
                                                        >
                                                            <Trash2 className="size-4" />
                                                        </Button>
                                                    </div>
                                                )}
                                            </td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        </div>
                    </section>
                )}
                {pagination.lastPage > 1 && (
                    <nav aria-label="Halaman riset" className="flex items-center justify-between gap-3">
                        <span className="text-sm">
                            Halaman {pagination.page} dari {pagination.lastPage}
                        </span>
                        <div className="flex gap-2">
                            <Button
                                variant="outline"
                                size="icon"
                                aria-label="Halaman sebelumnya"
                                title="Halaman sebelumnya"
                                disabled={pagination.page <= 1}
                                onClick={() => router.get('/bandingkan/snapshots', { q: filters.q, page: pagination.page - 1 })}
                            >
                                <ChevronLeft className="size-4" />
                            </Button>
                            <Button
                                variant="outline"
                                size="icon"
                                aria-label="Halaman berikutnya"
                                title="Halaman berikutnya"
                                disabled={pagination.page >= pagination.lastPage}
                                onClick={() => router.get('/bandingkan/snapshots', { q: filters.q, page: pagination.page + 1 })}
                            >
                                <ChevronRight className="size-4" />
                            </Button>
                        </div>
                    </nav>
                )}
            </div>
        </AppLayout>
    );
}
