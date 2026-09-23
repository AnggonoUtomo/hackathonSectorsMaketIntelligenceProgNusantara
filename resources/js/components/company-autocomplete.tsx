import CompanyLogo from '@/components/company-logo';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import type { CompanyIdentity, CompanySearchResult } from '@/types/company-directory';
import { ArrowRight, LoaderCircle, Search, X } from 'lucide-react';
import { useEffect, useId, useRef, useState } from 'react';

interface Props {
    value: string;
    onChange: (value: string) => void;
    onSelect: (company: CompanyIdentity) => void;
    onSearch: () => void;
}

export default function CompanyAutocomplete({ value, onChange, onSelect, onSearch }: Props) {
    const id = useId();
    const input = useRef<HTMLInputElement>(null);
    const [open, setOpen] = useState(false);
    const [active, setActive] = useState(-1);
    const [result, setResult] = useState<CompanySearchResult | null>(null);
    const [loading, setLoading] = useState(false);
    const [error, setError] = useState('');
    const [retry, setRetry] = useState(0);
    const query = value.trim();
    const visible = open && query.length >= 2;

    useEffect(() => {
        const controller = new AbortController();
        setResult(null);
        setError('');
        setActive(-1);
        setLoading(false);
        if (!visible) return () => controller.abort();
        if (!/^[\p{L}\p{N} .&-]{2,100}$/u.test(query)) {
            setError('Gunakan nama perusahaan atau kode saham.');
            return () => controller.abort();
        }
        setLoading(true);
        const timer = window.setTimeout(async () => {
            try {
                const response = await fetch(`/nusalens/companies/search?${new URLSearchParams({ q: query, limit: '8' })}`, {
                    signal: controller.signal,
                    headers: { Accept: 'application/json' },
                });
                const body = await response.json();
                if (!response.ok)
                    throw new Error(
                        response.status === 401 || response.status === 419
                            ? 'Sesi berakhir. Silakan login kembali.'
                            : (body.message ?? 'Pencarian belum dapat dimuat.'),
                    );
                if (!controller.signal.aborted) setResult(body);
            } catch (failure) {
                if (!controller.signal.aborted) setError(failure instanceof Error ? failure.message : 'Pencarian belum dapat dimuat.');
            } finally {
                if (!controller.signal.aborted) setLoading(false);
            }
        }, 300);
        return () => {
            window.clearTimeout(timer);
            controller.abort();
        };
    }, [query, visible, retry]);

    function choose(company: CompanyIdentity) {
        setOpen(false);
        onSelect(company);
    }

    return (
        <div
            className="relative min-w-0 flex-1"
            onBlur={(event) => {
                if (!event.currentTarget.contains(event.relatedTarget)) setOpen(false);
            }}
        >
            <label htmlFor={id} className="sr-only">
                Cari perusahaan
            </label>
            <Search aria-hidden className="text-muted-foreground pointer-events-none absolute top-3.5 left-3.5 size-5" />
            <Input
                ref={input}
                id={id}
                role="combobox"
                aria-autocomplete="list"
                aria-expanded={visible}
                aria-controls={visible ? `${id}-list` : undefined}
                aria-activedescendant={visible && active >= 0 ? `${id}-${active}` : undefined}
                autoComplete="off"
                maxLength={100}
                value={value}
                onFocus={() => setOpen(true)}
                onChange={(event) => {
                    onChange(event.target.value);
                    setOpen(true);
                }}
                onKeyDown={(event) => {
                    const items = result?.items ?? [];
                    if (event.key === 'Escape') {
                        event.preventDefault();
                        setOpen(false);
                        setActive(-1);
                    }
                    if (event.key === 'ArrowDown' || event.key === 'ArrowUp') {
                        event.preventDefault();
                        setOpen(true);
                        setActive((previous) =>
                            items.length
                                ? previous < 0
                                    ? event.key === 'ArrowDown'
                                        ? 0
                                        : items.length - 1
                                    : (previous + (event.key === 'ArrowDown' ? 1 : items.length - 1)) % items.length
                                : -1,
                        );
                    }
                    if (event.key === 'Enter') {
                        event.preventDefault();
                        if (visible && active >= 0 && items[active]) choose(items[active]);
                        else {
                            setOpen(false);
                            onSearch();
                        }
                    }
                }}
                placeholder="Nama perusahaan atau kode saham"
                className="h-12 pr-11 pl-11 text-base"
            />
            {value && (
                <button
                    type="button"
                    aria-label="Hapus pencarian"
                    title="Hapus pencarian"
                    className="hover:bg-muted absolute top-2 right-2 flex size-8 items-center justify-center rounded-md"
                    onClick={() => {
                        onChange('');
                        input.current?.focus();
                    }}
                >
                    <X className="size-4" />
                </button>
            )}
            {visible && (
                <div className="bg-popover absolute top-full right-0 left-0 z-30 mt-2 max-h-96 overflow-y-auto rounded-lg border shadow-lg">
                    <div role="status" aria-live="polite" className="text-muted-foreground px-4 py-3 text-xs">
                        {loading ? (
                            <span className="flex items-center gap-2">
                                <LoaderCircle className="size-4 animate-spin" />
                                Mencari perusahaan...
                            </span>
                        ) : (
                            error || (result ? `${result.total} perusahaan ditemukan` : '')
                        )}
                    </div>
                    <div id={`${id}-list`} role="listbox" aria-label="Hasil pencarian perusahaan" aria-busy={loading}>
                        {result?.items.map((company, index) => (
                            <button
                                type="button"
                                tabIndex={-1}
                                role="option"
                                aria-selected={active === index}
                                id={`${id}-${index}`}
                                key={company.symbol}
                                onMouseDown={(event) => event.preventDefault()}
                                onClick={() => choose(company)}
                                className={`hover:bg-muted flex w-full items-center gap-3 px-4 py-3 text-left ${active === index ? 'bg-muted' : ''}`}
                            >
                                <CompanyLogo company={company} />
                                <span className="min-w-0 flex-1">
                                    <span className="block text-sm font-medium break-words">{company.name}</span>
                                    <span className="text-muted-foreground text-xs">{company.symbol}</span>
                                </span>
                                <ArrowRight aria-hidden className="text-muted-foreground size-4 shrink-0" />
                            </button>
                        ))}
                    </div>
                    {error && (
                        <Button type="button" variant="ghost" className="m-2" onClick={() => setRetry((n) => n + 1)}>
                            Coba lagi
                        </Button>
                    )}
                    {result && (
                        <button
                            type="button"
                            className="hover:bg-muted w-full border-t px-4 py-3 text-left text-sm font-medium text-teal-700 dark:text-teal-300"
                            onClick={() => {
                                setOpen(false);
                                onSearch();
                            }}
                        >
                            Lihat semua hasil <ArrowRight aria-hidden className="ml-2 inline size-4" />
                        </button>
                    )}
                </div>
            )}
        </div>
    );
}
