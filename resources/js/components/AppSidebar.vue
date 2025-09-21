<script setup lang="ts">
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import { Sidebar, SidebarContent, SidebarFooter, SidebarHeader, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { type NavItem } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';
import { CalendarDays, FileClock, House, KeyRound, LayoutGrid, NotebookPen, ScrollTextIcon, UserCheck, Users } from 'lucide-vue-next';
import AppLogo from './AppLogo.vue';
import { computed } from 'vue';

const page = usePage()
const roles = computed(() => page.props.auth?.user?.roles ?? [])
const permissions = computed(() => page.props.auth?.user?.permissions ?? [])

const isSuperAdmin = computed(() => roles.value.includes('super-admin'))

const mainNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
        icon: LayoutGrid,
        permissions: null,
    },
    {
        title: 'Properties',
        href: '/properties',
        icon: House,
        permissions: ['view-properties'],
    },
    {
        title: 'Units',
        href: '/units',
        icon: KeyRound,
        permissions: ['view-units'],
    },
    {
        title: 'Reservations',
        href: '/reservations',
        icon: NotebookPen,
        permissions: ['view-reservations'],
    },
    {
        title: 'Calendar',
        href: '/calendar',
        icon: CalendarDays,
        permissions: ['view-calendar'],
    },
    {
        title: 'Users',
        href: '/users',
        icon: Users,
        permissions: ['view-users'],
    },
    {
        title: 'Roles & Permissions',
        href: '/roles',
        icon: UserCheck,
        permissions: ['view-roles'],
    },
    {
        title: 'Activity Logs',
        href: '/activity-logs',
        icon: FileClock,
        permissions: ['view-activity-logs'],
    },
    {
        title: 'System Logs',
        href: '/system-logs',
        icon: ScrollTextIcon,
        permissions: ['view-system-logs'],
    },
];

const footerNavItems: NavItem[] = [
    // {
    //     title: 'Github Repo',
    //     href: 'https://github.com/laravel/vue-starter-kit',
    //     icon: Folder,
    //     permissions: ['view-github-repo'],
    // },
];

const visibleNavItems = computed(() =>
    mainNavItems.filter((item) => {
        if (!item.permissions) return true
        if (isSuperAdmin.value) return true
        return item.permissions.every((p) => permissions.value.includes(p))
    })
)
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="route('dashboard')">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :items="visibleNavItems" />
        </SidebarContent>

        <SidebarFooter>
            <NavFooter :items="footerNavItems" />
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
