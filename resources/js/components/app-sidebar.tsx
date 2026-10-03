import { NavFooter } from '@/components/nav-footer';
import { NavMain } from '@/components/nav-main';
import { NavUser } from '@/components/nav-user';
import { Sidebar, SidebarContent, SidebarFooter, SidebarHeader, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { type NavItem } from '@/types';
import { Link, usePage } from '@inertiajs/react';
import { Bookmark, Folder, GitCompare, LayoutGrid, Search } from 'lucide-react';
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
    { title: 'Bandingkan', url: '/bandingkan', icon: GitCompare, prefetch: false },
    { title: 'Riset Tersimpan', url: '/bandingkan/snapshots', icon: Bookmark },
];

const footerNavItems: NavItem[] = [
    {
        title: 'Repository',
        url: 'https://github.com/AnggonoUtomo/hackathonSectorsMaketIntelligenceProgNusantara',
        icon: Folder,
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
