import { NavFooter } from '@/components/nav-footer';
import { NavMain } from '@/components/nav-main';
import { NavUser } from '@/components/nav-user';
import { Sidebar, SidebarContent, SidebarFooter, SidebarHeader, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { type NavItem } from '@/types';
import { Link, usePage } from '@inertiajs/react';
import { BookOpen, Folder, GitCompare, LayoutGrid, Radar, Search, Sparkles } from 'lucide-react';
import AppLogo from './app-logo';

const mainNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        url: '/dashboard',
        icon: LayoutGrid,
    },
    {
        title: 'Temukan Saham',
        url: '/temukan-saham',
        icon: Search,
        prefetch: false,
    },
    {
        title: 'Bandingkan',
        url: '/bandingkan',
        icon: GitCompare,
    },
    {
        title: 'Jelaskan Nilai',
        url: '/jelaskan-nilai',
        icon: Sparkles,
    },
    {
        title: 'Kandidat Menarik',
        url: '/kandidat-menarik',
        icon: Radar,
    },
];

const footerNavItems: NavItem[] = [
    {
        title: 'Repository',
        url: 'https://github.com/laravel/react-starter-kit',
        icon: Folder,
    },
    {
        title: 'Documentation',
        url: 'https://laravel.com/docs/starter-kits',
        icon: BookOpen,
    },
];

export function AppSidebar() {
    const { url } = usePage();
    const pathname = new URL(url, 'http://localhost').pathname;
    const items = mainNavItems.map((item) => ({
        ...item,
        isActive:
            item.url === '/temukan-saham'
                ? pathname === '/temukan-saham' || pathname === '/perusahaan' || /^\/perusahaan\/[a-z0-9]{4}$/i.test(pathname)
                : undefined,
    }));

    return (
        <Sidebar collapsible="icon" variant="inset">
            <SidebarHeader>
                <SidebarMenu>
                    <SidebarMenuItem>
                        <SidebarMenuButton size="lg" asChild>
                            <Link href="/dashboard" prefetch>
                                <AppLogo />
                            </Link>
                        </SidebarMenuButton>
                    </SidebarMenuItem>
                </SidebarMenu>
            </SidebarHeader>

            <SidebarContent>
                <NavMain items={items} />
            </SidebarContent>

            <SidebarFooter>
                <NavFooter items={footerNavItems} className="mt-auto" />
                <NavUser />
            </SidebarFooter>
        </Sidebar>
    );
}
