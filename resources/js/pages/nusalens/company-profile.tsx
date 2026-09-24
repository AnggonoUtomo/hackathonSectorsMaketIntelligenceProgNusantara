import CompanyAnalysis from '@/components/company-analysis';
import CompanyLogo from '@/components/company-logo';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/app-layout';
import type { CompanyProfile, MarketDataError } from '@/types/company-directory';
import { Head, Link, router } from '@inertiajs/react';
import { ArrowLeft, ArrowUpRight, Building2, CalendarDays, Globe, MapPin, Phone, Users } from 'lucide-react';

const number = (value: number | null) =>
    value === null ? 'Belum tersedia' : new Intl.NumberFormat('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(value);
const date = (value: string | null) =>
    value && !Number.isNaN(Date.parse(value))
        ? new Intl.DateTimeFormat('id-ID', { dateStyle: 'long', timeZone: 'Asia/Jakarta' }).format(new Date(value))
        : 'Belum tersedia';

export default function CompanyProfilePage({
    company,
    error,
    returnTo,
}: {
    company: CompanyProfile | null;
    error: MarketDataError | null;
    returnTo: string;
}) {
    return (
        <AppLayout
            breadcrumbs={[
                { title: 'Temukan Saham', href: returnTo },
                { title: company?.symbol ?? 'Perusahaan', href: company ? `/perusahaan/${company.symbol}` : '/perusahaan' },
            ]}
        >
            <Head title={company?.name ?? 'Perusahaan'} />
            <div className="flex min-w-0 flex-1 flex-col gap-6 p-4 md:p-6">
                <div>
                    <Button asChild variant="ghost" size="sm">
                        <Link href={returnTo}>
                            <ArrowLeft className="size-4" />
                            Kembali ke hasil
                        </Link>
                    </Button>
                </div>
                {error ? (
                    <section role="alert" className="space-y-4 border-y py-10">
                        <h1 className="text-xl font-semibold">Data perusahaan belum tersedia</h1>
                        <p className="text-muted-foreground text-sm">{error.message}</p>
                        <Button variant="outline" onClick={() => router.reload()}>
                            Coba lagi
                        </Button>
                    </section>
                ) : (
                    company && (
                        <>
                            <header className="flex flex-col items-start justify-between gap-4 border-b pb-6 sm:flex-row">
                                <div className="flex w-full min-w-0 items-start gap-4 sm:flex-1">
                                    <CompanyLogo company={company} />
                                    <div className="min-w-0">
                                        <div className="mb-2 flex flex-wrap gap-2">
                                            <Badge variant="outline">{company.symbol}</Badge>
                                            {company.sector && <Badge variant="secondary">{company.sector}</Badge>}
                                        </div>
                                        <h1 className="text-2xl font-semibold break-words">{company.name}</h1>
                                        <p className="text-muted-foreground mt-2 text-sm">
                                            {company.subSector ?? company.industry ?? 'Klasifikasi belum tersedia'}
                                        </p>
                                    </div>
                                </div>
                                {company.website && (
                                    <Button asChild variant="outline" size="sm">
                                        <a href={company.website} target="_blank" rel="noopener noreferrer">
                                            <Globe className="size-4" />
                                            Website perusahaan
                                            <ArrowUpRight className="size-4" />
                                        </a>
                                    </Button>
                                )}
                            </header>
                            <section aria-label="Ringkasan pasar" className="grid gap-4 sm:grid-cols-3">
                                <div className="border-l-2 border-teal-600 py-1 pl-4">
                                    <p className="text-muted-foreground text-xs">Harga penutupan</p>
                                    <p className="mt-2 text-2xl font-semibold tabular-nums">
                                        {company.price === null ? 'Belum tersedia' : `Rp ${number(company.price)}`}
                                    </p>
                                    <p className="text-muted-foreground mt-2 text-xs">{date(company.priceDate)}</p>
                                </div>
                                <div className="border-l-2 border-sky-500 py-1 pl-4">
                                    <p className="text-muted-foreground text-xs">Perubahan harian</p>
                                    <p
                                        className={`mt-2 text-2xl font-semibold tabular-nums ${company.changePercent !== null && company.changePercent < 0 ? 'text-rose-700 dark:text-rose-300' : 'text-teal-700 dark:text-teal-300'}`}
                                    >
                                        {company.changePercent === null
                                            ? 'Belum tersedia'
                                            : `${company.changePercent > 0 ? '+' : ''}${number(company.changePercent)}%`}
                                    </p>
                                    <p className="text-muted-foreground mt-2 text-xs">Terhadap penutupan sebelumnya</p>
                                </div>
                                <div className="border-l-2 border-amber-500 py-1 pl-4">
                                    <p className="text-muted-foreground text-xs">Kapitalisasi pasar</p>
                                    <p className="mt-2 text-lg font-semibold break-words tabular-nums">
                                        {company.marketCap === null ? 'Belum tersedia' : `Rp ${number(company.marketCap)}`}
                                    </p>
                                </div>
                            </section>
                            <CompanyAnalysis key={company.symbol} symbol={company.symbol}>
                                <section>
                                    <h2 className="text-base font-semibold">Profil perusahaan</h2>
                                    <dl className="mt-4 grid gap-x-8 sm:grid-cols-2">
                                        {[
                                            { label: 'Sektor', value: company.sector, Icon: Building2 },
                                            { label: 'Industri', value: company.industry, Icon: Building2 },
                                            { label: 'Tanggal pencatatan', value: date(company.listingDate), Icon: CalendarDays },
                                            { label: 'Papan pencatatan', value: company.board, Icon: Building2 },
                                            { label: 'Karyawan', value: number(company.employees), Icon: Users },
                                            { label: 'Telepon', value: company.phone, Icon: Phone },
                                            { label: 'Alamat', value: company.address?.replaceAll('\\r\\n', '\n'), Icon: MapPin },
                                        ].map(({ label, value, Icon }) => (
                                            <div key={label} className="flex gap-3 border-b py-4">
                                                <Icon className="text-muted-foreground mt-0.5 size-4 shrink-0" />
                                                <div className="min-w-0">
                                                    <dt className="text-muted-foreground text-xs">{label}</dt>
                                                    <dd className="mt-1 text-sm break-words whitespace-pre-line">{value ?? 'Belum tersedia'}</dd>
                                                </div>
                                            </div>
                                        ))}
                                    </dl>
                                </section>
                                {company.indices.length > 0 && (
                                    <section>
                                        <h2 className="mb-3 text-base font-semibold">Keanggotaan indeks</h2>
                                        <div className="flex flex-wrap gap-2">
                                            {company.indices.map((index) => (
                                                <Badge key={index} variant="outline">
                                                    {index}
                                                </Badge>
                                            ))}
                                        </div>
                                    </section>
                                )}
                                <footer className="text-muted-foreground border-t pt-4 text-xs leading-6">
                                    Sumber: Sectors Financial API. Data diambil{' '}
                                    {new Intl.DateTimeFormat('id-ID', { dateStyle: 'long', timeStyle: 'short', timeZone: 'Asia/Jakarta' }).format(
                                        new Date(company.fetchedAt),
                                    )}{' '}
                                    WIB.
                                    {company.priceDate && (
                                        <span className="block">Harga adalah penutupan pada {date(company.priceDate)}, bukan harga real-time.</span>
                                    )}
                                </footer>
                            </CompanyAnalysis>
                        </>
                    )
                )}
            </div>
        </AppLayout>
    );
}
