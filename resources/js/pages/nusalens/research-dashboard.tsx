import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Link, router } from '@inertiajs/react';
import { BookOpenCheck, Brain, Keyboard, Search, ShieldCheck, Sparkles } from 'lucide-react';
import type { FormEvent } from 'react';
import { useEffect, useRef, useState } from 'react';

interface ResearchPayload {
    symbol: string;
    company: {
        symbol: string;
        name: string;
        sector: string;
        subSector: string;
        summary: string;
    };
    metrics: Array<{
        label: string;
        value: string;
        note: string;
        explanation: string;
    }>;
    bullets: string[];
    meta: {
        source: string;
        state: 'ready' | 'pending';
        aiEnabled: boolean;
        disclaimer: string;
    };
}

type Props = {
    research: ResearchPayload;
};

export function ResearchDashboard({ research }: Props) {
    const [symbol, setSymbol] = useState(research.symbol);
    const inputRef = useRef<HTMLInputElement>(null);
    const isPending = research.meta.state === 'pending';

    function submitResearch(event: FormEvent<HTMLFormElement>) {
        event.preventDefault();
        router.get(
            '/jelaskan-nilai',
            { symbol: symbol.trim() || undefined },
            {
                preserveState: true,
                preserveScroll: true,
                replace: true,
            },
        );
    }

    function resetResearch() {
        setSymbol('BBCA');
        router.get('/jelaskan-nilai', {}, { preserveState: true, preserveScroll: true, replace: true });
    }

    useEffect(() => {
        const isTyping = (target: EventTarget | null) =>
            target instanceof HTMLElement && (['INPUT', 'TEXTAREA', 'SELECT'].includes(target.tagName) || target.isContentEditable);
        const handleShortcut = (event: KeyboardEvent) => {
            if (event.key === '/' && !isTyping(event.target)) {
                event.preventDefault();
                inputRef.current?.focus();
            }

            if (event.key === 'Escape') {
                setSymbol(research.symbol);
            }
        };

        window.addEventListener('keydown', handleShortcut);

        return () => window.removeEventListener('keydown', handleShortcut);
    }, [research.symbol]);

    return (
        <div className="flex flex-1 flex-col gap-5 p-4">
            <div className="grid gap-4 md:grid-cols-3">
                <section className="dashboard-card dashboard-card--blue rounded-2xl border p-4">
                    <div className="flex items-center gap-3">
                        <span className="dashboard-icon dashboard-accent--blue flex size-10 items-center justify-center rounded-lg">
                            <Sparkles aria-hidden="true" className="size-5" />
                        </span>
                        <div>
                            <p className="text-xs text-muted-foreground">Ticker</p>
                            <p className="mt-1 text-2xl font-semibold">{research.symbol}</p>
                        </div>
                    </div>
                </section>
                <section className="dashboard-card dashboard-card--emerald rounded-2xl border p-4">
                    <div className="flex items-center gap-3">
                        <span className="dashboard-icon dashboard-accent--emerald flex size-10 items-center justify-center rounded-lg">
                            <BookOpenCheck aria-hidden="true" className="size-5" />
                        </span>
                        <div>
                            <p className="text-xs text-muted-foreground">Status penjelasan</p>
                            <p className="mt-1 text-lg font-semibold">{isPending ? 'Pending data' : 'Rule-based ready'}</p>
                        </div>
                    </div>
                </section>
                <section className="dashboard-card dashboard-card--violet rounded-2xl border p-4">
                    <div className="flex items-center gap-3">
                        <span className="dashboard-icon dashboard-accent--violet flex size-10 items-center justify-center rounded-lg">
                            <Brain aria-hidden="true" className="size-5" />
                        </span>
                        <div>
                            <p className="text-xs text-muted-foreground">AI</p>
                            <p className="mt-1 text-lg font-semibold">{research.meta.aiEnabled ? 'Aktif' : 'Nonaktif'}</p>
                        </div>
                    </div>
                </section>
            </div>

            <div className="dashboard-shortcut-bar flex flex-wrap items-center gap-x-4 gap-y-2 rounded-xl border px-3 py-2 text-xs">
                <span className="flex items-center gap-2 font-medium">
                    <Keyboard aria-hidden="true" className="size-4" />
                    Shortcut
                </span>
                <span>
                    <kbd>/</kbd> input ticker
                </span>
                <span>
                    <kbd>Enter</kbd> jelaskan
                </span>
                <span>
                    <kbd>Esc</kbd> pulihkan input
                </span>
            </div>

            <section className="dashboard-card dashboard-card--cyan overflow-hidden rounded-2xl border">
                <div className="flex flex-col gap-3 border-b p-4">
                    <form className="flex min-w-0 flex-col gap-3 xl:flex-row" onSubmit={submitResearch}>
                        <div className="relative min-w-0 flex-1">
                            <label htmlFor="research-symbol" className="sr-only">
                                Ticker untuk dijelaskan
                            </label>
                            <Search
                                aria-hidden="true"
                                className="pointer-events-none absolute top-1/2 left-3 size-4 -translate-y-1/2 text-muted-foreground"
                            />
                            <Input
                                ref={inputRef}
                                id="research-symbol"
                                value={symbol}
                                onChange={(event) => setSymbol(event.target.value.toUpperCase())}
                                placeholder="BBCA"
                                className="pl-9"
                            />
                        </div>
                        <Button type="submit" variant="outline">
                            Jelaskan
                        </Button>
                        <Button type="button" variant="ghost" onClick={resetResearch}>
                            Reset
                        </Button>
                    </form>
                </div>

                <div className="grid gap-4 p-4 xl:grid-cols-[1fr_320px]">
                    <div className="space-y-4">
                        <div className="rounded-xl border bg-background/80 p-4">
                            <div className="flex flex-wrap items-start justify-between gap-3">
                                <div>
                                    <h2 className="text-lg font-semibold">{research.company.name}</h2>
                                    <p className="mt-1 text-sm text-muted-foreground">
                                        {research.company.sector} / {research.company.subSector}
                                    </p>
                                </div>
                                <Badge variant="outline">{research.meta.source}</Badge>
                            </div>
                            <p className="mt-3 text-sm text-muted-foreground">{research.company.summary}</p>
                        </div>

                        <div className="overflow-x-auto rounded-xl border">
                            <table className="w-full min-w-[760px] text-left text-sm">
                                <thead className="dashboard-table-header border-b text-xs tracking-wide text-foreground/80 uppercase">
                                    <tr>
                                        <th className="px-5 py-3 font-medium">Metrik</th>
                                        <th className="px-5 py-3 text-right font-medium">Nilai</th>
                                        <th className="px-5 py-3 font-medium">Catatan</th>
                                        <th className="px-5 py-3 font-medium">Penjelasan</th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y divide-border/70">
                                    {research.metrics.map((metric) => (
                                        <tr key={metric.label} className="dashboard-table-row transition-colors">
                                            <td className="px-5 py-4 font-medium">{metric.label}</td>
                                            <td className="px-5 py-4 text-right font-semibold tabular-nums">{metric.value}</td>
                                            <td className="px-5 py-4 text-muted-foreground">{metric.note}</td>
                                            <td className="px-5 py-4 text-muted-foreground">{metric.explanation}</td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <aside className="space-y-4">
                        <div className="rounded-xl border bg-background/80 p-4">
                            <div className="flex items-center gap-2 font-medium">
                                <ShieldCheck aria-hidden="true" className="size-4" />
                                Batas Penjelasan
                            </div>
                            <p className="mt-2 text-sm text-muted-foreground">{research.meta.disclaimer}</p>
                        </div>
                        <div className="rounded-xl border bg-background/80 p-4">
                            <h3 className="font-medium">Ringkasan Rule-based</h3>
                            <ul className="mt-3 space-y-2 text-sm text-muted-foreground">
                                {research.bullets.map((item) => (
                                    <li key={item} className="flex gap-2">
                                        <span aria-hidden="true">-</span>
                                        <span>{item}</span>
                                    </li>
                                ))}
                            </ul>
                        </div>
                        <div className="grid gap-2">
                            <Button asChild variant="outline">
                                <Link href={`/perusahaan/${research.symbol}`} prefetch>
                                    Buka Detail
                                </Link>
                            </Button>
                            <Button asChild>
                                <Link href={`/bandingkan?symbols=${research.symbol}`} prefetch>
                                    Bandingkan
                                </Link>
                            </Button>
                        </div>
                    </aside>
                </div>
            </section>
        </div>
    );
}
