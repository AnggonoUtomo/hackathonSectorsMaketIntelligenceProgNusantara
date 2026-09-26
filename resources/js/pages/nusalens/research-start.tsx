import CompanyAutocomplete from '@/components/company-autocomplete';
import AppLayout from '@/layouts/app-layout';
import { Head, router } from '@inertiajs/react';
import { useState } from 'react';

export default function ResearchStart() {
    const [query, setQuery] = useState('');
    return (
        <AppLayout breadcrumbs={[{ title: 'Ringkasan Riset', href: '/jelaskan-nilai' }]}>
            <Head title="Ringkasan Riset" />
            <main className="flex min-w-0 flex-1 flex-col gap-6 p-4 md:p-6">
                <header className="border-b pb-5">
                    <h1 className="text-2xl font-semibold">Ringkasan Riset</h1>
                    <p className="text-muted-foreground mt-2 text-sm">Perusahaan mana yang ingin diteliti?</p>
                </header>
                <div className="max-w-2xl">
                    <CompanyAutocomplete
                        value={query}
                        onChange={setQuery}
                        onSelect={(company) => router.visit(`/perusahaan/${company.symbol}`)}
                        onSearch={() => router.visit('/temukan-saham', { data: { keyword: query } })}
                    />
                </div>
            </main>
        </AppLayout>
    );
}
