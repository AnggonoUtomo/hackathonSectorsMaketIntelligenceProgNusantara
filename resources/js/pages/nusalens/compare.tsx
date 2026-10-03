import AppLayout from '@/layouts/app-layout';
import { Head } from '@inertiajs/react';
import type { ComponentProps } from 'react';
import { ComparisonDashboard } from './comparison-dashboard';

export default function Compare(props: ComponentProps<typeof ComparisonDashboard>) {
    return (
        <AppLayout breadcrumbs={[{ title: 'Bandingkan', href: '/bandingkan' }]}>
            <Head title="Bandingkan Perusahaan" />
            <ComparisonDashboard {...props} />
        </AppLayout>
    );
}
