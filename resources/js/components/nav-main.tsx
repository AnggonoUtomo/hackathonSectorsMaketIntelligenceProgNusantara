import { SidebarGroup, SidebarGroupLabel, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { type NavItem } from '@/types';
import { Link, usePage } from '@inertiajs/react';

const iconColors: Record<string, string> = {
    '/dashboard': 'text-teal-700 dark:text-teal-300',
    '/temukan-saham': 'text-sky-700 dark:text-sky-300',
    '/bandingkan': 'text-indigo-600 dark:text-indigo-300',
    '/bandingkan/snapshots': 'text-amber-700 dark:text-amber-300',
};

export function NavMain({ items = [] }: { items: NavItem[] }) {
    const page = usePage();
    const pathname = new URL(page.url, 'http://localhost').pathname;
    return (
        <SidebarGroup className="px-2 py-0">
            <SidebarGroupLabel>Platform</SidebarGroupLabel>
            <SidebarMenu>
                {items.map((item) => (
                    <SidebarMenuItem key={item.title}>
                        <SidebarMenuButton asChild isActive={item.isActive ?? item.url === pathname} tooltip={item.title}>
                            <Link
                                href={item.url}
                                prefetch={item.prefetch ?? true}
                                aria-current={(item.isActive ?? item.url === pathname) ? 'page' : undefined}
                            >
                                {item.icon && <item.icon className={iconColors[item.url]} />}
                                <span>{item.title}</span>
                            </Link>
                        </SidebarMenuButton>
                    </SidebarMenuItem>
                ))}
            </SidebarMenu>
        </SidebarGroup>
    );
}
