import ScoreInputEvidence from '@/components/score-input-evidence';
import { Button } from '@/components/ui/button';
import type { ResearchPriority, ScoreMetric } from '@/types/research-priority';
import { Link } from '@inertiajs/react';
import { Calculator, ChevronDown, FileCheck2, LoaderCircle, RefreshCw } from 'lucide-react';
import { useEffect, useRef, useState } from 'react';
import { Bar, BarChart, CartesianGrid, Cell, ResponsiveContainer, Tooltip, XAxis, YAxis } from 'recharts';

const number = (value: number | null) =>
    value === null ? 'Belum tersedia' : new Intl.NumberFormat('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(value);
const colors = ['#0d9488', '#0284c7', '#d97706', '#e11d48', '#7c3aed'];
const date = (value: string) =>
    new Intl.DateTimeFormat('id-ID', { dateStyle: 'medium', timeStyle: 'short', timeZone: 'Asia/Jakarta' }).format(new Date(value));

export default function ResearchPriorityPanel({
    symbol,
    initial,
    onLoad,
    historical = false,
    detailed = false,
}: {
    symbol: string;
    initial?: ResearchPriority;
    onLoad?: (data: ResearchPriority) => void;
    historical?: boolean;
    detailed?: boolean;
}) {
    const [data, setData] = useState<ResearchPriority | undefined>(initial);
    const [loading, setLoading] = useState(false);
    const [error, setError] = useState<string | null>(null);
    const request = useRef<AbortController | null>(null);
    const [expired, setExpired] = useState(false);
    useEffect(() => () => request.current?.abort(), []);
    useEffect(() => {
        if (!data || historical) return;
        const timer = window.setTimeout(() => setExpired(true), Math.max(0, Date.parse(data.expiresAt) - Date.now()));
        return () => window.clearTimeout(timer);
    }, [data, historical]);

    async function load(includeMarket = false) {
        if (request.current) return;
        const controller = new AbortController();
        request.current = controller;
        setLoading(true);
        setError(null);
        try {
            const response = await fetch(`/nusalens/companies/${encodeURIComponent(symbol)}/score?include_market=${includeMarket ? 1 : 0}`, {
                headers: { Accept: 'application/json' },
                signal: controller.signal,
            });
            if ([401, 403].includes(response.status)) throw new Error('Silakan login dengan email terverifikasi.');
            const result = await response.json();
            if (!response.ok) throw new Error(result.message ?? 'Bukti nilai belum dapat dimuat.');
            if (result.symbol !== symbol || !Array.isArray(result.components)) throw new Error('Format bukti belum dapat dibaca.');
            if (!controller.signal.aborted) {
                setData(result);
                setExpired(false);
                onLoad?.(result);
            }
        } catch (e) {
            if (!controller.signal.aborted)
                setError(e instanceof Error && !(e instanceof SyntaxError) && !(e instanceof TypeError) ? e.message : 'Koneksi terputus. Coba lagi.');
        } finally {
            if (request.current === controller) request.current = null;
            if (!controller.signal.aborted) setLoading(false);
        }
    }

    return (
        <section className="min-w-0 space-y-5 border-t py-6" aria-label={`Posisi ${symbol} dibanding perusahaan sejenis`}>
            <header className="flex flex-wrap items-start justify-between gap-3">
                <div>
                    <h2 className="text-lg font-semibold">Posisi {symbol} dibanding perusahaan sejenis</h2>
                    <p className="text-muted-foreground mt-1 text-sm">Kinerja, pertumbuhan, valuasi, pasar, dan kondisi keuangan.</p>
                </div>
                {!historical && (
                    <Button onClick={() => load()} disabled={loading} variant={data ? 'outline' : 'default'}>
                        {loading ? (
                            <LoaderCircle className="size-4 animate-spin" />
                        ) : data ? (
                            <RefreshCw className="size-4" />
                        ) : (
                            <Calculator className="size-4" />
                        )}
                        {loading ? 'Memeriksa peer...' : data ? 'Perbarui analisis' : 'Analisis perusahaan sejenis'}
                    </Button>
                )}
            </header>
            {!data && !loading && (
                <p className="text-muted-foreground text-sm">
                    Pengambilan awal memakai credit sesuai jumlah halaman kelompok perusahaan. Data yang masih tersimpan dipakai kembali.
                </p>
            )}
            {loading && (
                <p role="status" className="text-muted-foreground text-sm">
                    Memeriksa kelengkapan seluruh kelompok pembanding {symbol}...
                </p>
            )}
            {error && (
                <p role="alert" className="border-l-2 border-rose-500 pl-3 text-sm">
                    {error}
                </p>
            )}
            {data && (
                <>
                    {(expired || historical) && (
                        <p role="status" className="border-l-2 border-amber-500 pl-3 text-sm">
                            {historical
                                ? 'Bukti historis pada saat disimpan.'
                                : 'Masa berlaku input telah berakhir. Hasil di bawah adalah riwayat; perbarui sebelum dipakai sebagai analisis terkini.'}
                        </p>
                    )}
                    <dl className="grid gap-5 border-y py-5 sm:grid-cols-3">
                        <div>
                            <dt className="text-muted-foreground text-xs">Nilai Prioritas Riset</dt>
                            <dd className="mt-2 text-2xl font-semibold tabular-nums">
                                {data.score === null ? 'Data belum cukup' : number(data.score)}
                                {data.score !== null && <span className="text-muted-foreground text-sm font-normal"> / 100</span>}
                            </dd>
                        </div>
                        <div>
                            <dt className="text-muted-foreground text-xs">Kelengkapan berbobot</dt>
                            <dd className="mt-2 text-2xl font-semibold tabular-nums">{number(data.completeness)}%</dd>
                        </div>
                        <div>
                            <dt className="text-muted-foreground text-xs">Perusahaan dalam populasi sumber</dt>
                            <dd className="mt-2 text-2xl font-semibold tabular-nums">{data.populationCount}</dd>
                        </div>
                    </dl>
                    {data.reason && <p className="text-sm">{data.reason}</p>}
                    {data.marketNotice && (
                        <p role="status" className="border-l-2 border-amber-500 pl-3 text-sm">
                            {data.marketNotice}
                        </p>
                    )}
                    {!historical && (
                        <div className="flex flex-wrap items-center gap-3">
                            <Button variant="outline" disabled={loading} onClick={() => load(true)}>
                                <Calculator className="size-4" />
                                Lengkapi valuasi peer
                            </Button>
                            <p className="text-muted-foreground max-w-xl text-xs">
                                Memerlukan data pasar tiap peer. Seluruh kelompok harus tercakup; pengambilan dimulai hanya bila estimasinya cukup
                                dalam sisa kuota.
                            </p>
                        </div>
                    )}
                    {data.components.some((component) => component.score !== null) && (
                        <div className="h-72 min-w-0" role="img" aria-label={`Grafik lima komponen analisis ${symbol}`}>
                            <ResponsiveContainer width="100%" height="100%">
                                <BarChart data={data.components} layout="vertical" margin={{ left: 0, right: 24, top: 8, bottom: 8 }}>
                                    <CartesianGrid strokeDasharray="3 3" horizontal={false} />
                                    <XAxis type="number" domain={[0, 100]} tick={{ fontSize: 11 }} />
                                    <YAxis type="category" dataKey="label" width={130} tick={{ fontSize: 11 }} />
                                    <Tooltip formatter={(value) => number(typeof value === 'number' ? value : null)} />
                                    <Bar dataKey="score" name="Posisi relatif" maxBarSize={24} radius={[0, 3, 3, 0]} isAnimationActive={!historical}>
                                        {data.components.map((c, i) => (
                                            <Cell key={c.key} fill={colors[i]} />
                                        ))}
                                    </Bar>
                                </BarChart>
                            </ResponsiveContainer>
                        </div>
                    )}
                    <div className="divide-y">
                        {data.components.map((component, index) => (
                            <details key={component.key} className="group py-4">
                                <summary className="flex cursor-pointer list-none items-center justify-between gap-3 text-sm focus-visible:outline-2">
                                    <span className="flex min-w-0 items-center gap-3">
                                        <span className="size-2 shrink-0 rounded-full" style={{ backgroundColor: colors[index] }} />
                                        <span className="font-medium">
                                            {component.label}
                                            <span className="text-muted-foreground ml-2 text-xs">Bobot {component.weight}%</span>
                                        </span>
                                    </span>
                                    <span className="flex shrink-0 items-center gap-3 tabular-nums">
                                        {number(component.score)}
                                        <ChevronDown className="size-4 group-open:rotate-180" />
                                    </span>
                                </summary>
                                <div className="mt-4 space-y-6">
                                    {detailed && (
                                        <p className="text-muted-foreground text-xs leading-6">
                                            Bobot efektif komponen pada total {number(component.effectiveWeight * 100)}%. Kontribusi tersimpan{' '}
                                            {number(component.contribution ?? null)} poin. Nilai komponen memakai rata-rata percentile metrik yang
                                            tersedia; total menggabungkan komponen dengan bobot efektifnya.
                                        </p>
                                    )}
                                    {component.metrics.map((metric) => (
                                        <MetricEvidence key={metric.key} metric={metric} detailed={detailed} />
                                    ))}
                                </div>
                            </details>
                        ))}
                    </div>
                    <footer className="text-muted-foreground space-y-2 border-t pt-4 text-xs leading-6">
                        <p>
                            Sumber Sectors Financial API. Diambil {date(data.fetchedAt)} WIB. Formula {data.formulaVersion}.
                        </p>
                        <p>
                            Nilai membandingkan perusahaan sejenis, bukan prediksi keuntungan atau rekomendasi transaksi. Metrik kosong tidak dihitung
                            sebagai nol.
                        </p>
                        {!historical && (
                            <Button asChild variant="outline">
                                <Link href={`/kandidat-menarik?evidence=${data.evidenceId}`}>Lihat kandidat sejenis</Link>
                            </Button>
                        )}
                        {!historical && (
                            <a
                                href={`/nusalens/evidence/${data.evidenceId}`}
                                target="_blank"
                                rel="noopener noreferrer"
                                className="inline-flex items-center gap-2 underline underline-offset-4"
                            >
                                <FileCheck2 className="size-4" />
                                Buka seluruh bukti perhitungan
                            </a>
                        )}
                    </footer>
                </>
            )}
        </section>
    );
}

function MetricEvidence({ metric, detailed }: { metric: ScoreMetric; detailed: boolean }) {
    return (
        <article className="border-border min-w-0 space-y-3 border-l-2 pl-4">
            <h3 className="text-sm font-semibold">{metric.label}</h3>
            <p className="text-muted-foreground text-xs">
                {metric.basis}
                {metric.priceDate ? ` | Tanggal pasar ${metric.priceDate}` : ''}
            </p>
            <p className="text-sm">
                {number(metric.value)} {metric.value !== null ? metric.unit : ''}
                {metric.period && ` | ${metric.period}`}
            </p>
            {metric.percentile === null ? (
                <p className="text-muted-foreground text-sm">{metric.reason}</p>
            ) : (
                <>
                    <p className="text-sm leading-6">
                        Posisi relatif {number(metric.percentile)} / 100 dari {metric.peers.length} perusahaan pembanding. Kelompok {metric.group} (
                        {metric.groupLevel}).{' '}
                        {metric.higherIsBetter
                            ? 'Nilai mentah lebih besar mendapat posisi lebih besar.'
                            : 'Nilai mentah lebih kecil mendapat posisi lebih besar.'}
                    </p>
                    <p className="text-muted-foreground text-xs">
                        Rank rata-rata {number(metric.rank)} dari {metric.populationSize}; bobot efektif dalam komponen{' '}
                        {number(metric.effectiveWeight * 100)}%.
                    </p>
                </>
            )}
            {detailed && <ScoreInputEvidence metric={metric} />}
            {metric.attempts.length > 1 && (
                <p className="text-muted-foreground text-xs">
                    Pemeriksaan kelompok: {metric.attempts.map((a) => `${a.period} ${a.group}: ${a.validPeers} peer`).join('; ')}.
                </p>
            )}
            {metric.peers.length > 0 && (
                <div className="max-h-72 overflow-auto rounded-md border">
                    <table className="w-full min-w-96 text-sm">
                        <thead className="bg-muted sticky top-0 text-xs uppercase">
                            <tr>
                                <th className="p-2 text-left">Peer</th>
                                <th className="p-2 text-right">Nilai ({metric.unit})</th>
                                <th className="p-2 text-left">Periode</th>
                            </tr>
                        </thead>
                        <tbody>
                            {metric.peers.map((peer) => (
                                <tr key={peer.symbol} className="border-t">
                                    <td className="p-2">
                                        <Link href={`/perusahaan/${peer.symbol}`} className="font-medium underline-offset-4 hover:underline">
                                            {peer.symbol}
                                        </Link>
                                        <span className="text-muted-foreground block text-xs">{peer.name}</span>
                                    </td>
                                    <td className="p-2 text-right tabular-nums">{number(peer.value)}</td>
                                    <td className="p-2">{peer.period}</td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                </div>
            )}
            {metric.excluded.length > 0 && (
                <details>
                    <summary className="cursor-pointer text-xs">{metric.excluded.length} perusahaan tidak memenuhi syarat metrik</summary>
                    <ul className="text-muted-foreground mt-2 space-y-1 text-xs">
                        {metric.excluded.map((peer) => (
                            <li key={peer.symbol}>
                                {peer.symbol}: {peer.reason}
                            </li>
                        ))}
                    </ul>
                </details>
            )}
        </article>
    );
}
