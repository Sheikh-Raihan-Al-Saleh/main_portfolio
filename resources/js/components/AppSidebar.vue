<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import {
    Briefcase,
    Building2,
    Cpu,
    ExternalLink,
    FolderKanban,
    GraduationCap,
    LayoutGrid,
    LayoutTemplate,
    Mail,
    Sparkles,
    Users,
    UserRound,
} from '@lucide/vue';
import { computed } from 'vue';
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
} from '@/components/ui/sidebar';
import { route } from '@/composables/useRoute';
import { useBrand } from '@/composables/useSite';
import type { NavItem } from '@/types';

const page = usePage();
const brand = useBrand();

const unreadMessages = computed(
    () => (page.props.unreadMessages as number) ?? 0,
);

/**
 * The admin edits two sites, not one. Content is grouped by which site it
 * appears on so it is obvious where a change will show up: the studio's own
 * pages first, then the founder's portfolio. Groups read like folders in a
 * code editor's file tree.
 */
const companyNavItems = computed<NavItem[]>(() => [
    { title: 'Studio & brand', href: route('studioEdit'), icon: Building2 },
    {
        title: 'Home page copy',
        href: route('studioContentEdit'),
        icon: LayoutTemplate,
    },
    { title: 'Clients', href: route('clientIndex'), icon: Users },
    { title: 'Projects', href: route('projectIndex'), icon: FolderKanban },
    { title: 'Technologies', href: route('technologyIndex'), icon: Cpu },
]);

const founderNavItems: NavItem[] = [
    { title: 'Founder', href: route('founderEdit'), icon: UserRound },
    { title: 'Skills', href: route('skillIndex'), icon: Sparkles },
    { title: 'Experience', href: route('experienceIndex'), icon: Briefcase },
    { title: 'Education', href: route('educationIndex'), icon: GraduationCap },
];

const overviewNavItems = computed<NavItem[]>(() => [
    { title: 'Dashboard', href: route('adminDashboard'), icon: LayoutGrid },
    {
        title: 'Messages',
        href: route('messageIndex'),
        icon: Mail,
        badge: unreadMessages.value,
    },
]);

const footerNavItems: NavItem[] = [
    { title: 'View live site', href: '/', icon: ExternalLink },
];
</script>

<template>
    <Sidebar variant="sidebar" collapsible="icon">
        <SidebarHeader class="border-b border-sidebar-border">
            <Link
                :href="route('adminDashboard')"
                class="flex h-12 items-center gap-2.5 rounded-md px-2 font-mono group-data-[collapsible=icon]:justify-center group-data-[collapsible=icon]:px-0"
            >
                <span
                    aria-hidden="true"
                    class="grid size-8 shrink-0 place-items-center rounded-md border border-primary text-sm font-bold text-primary"
                    >&gt;_</span
                >
                <span
                    class="grid min-w-0 leading-tight group-data-[collapsible=icon]:hidden"
                >
                    <span class="truncate text-sm font-bold text-foreground"
                        >{{ brand.name
                        }}<span class="text-primary">.admin</span></span
                    >
                    <span class="truncate text-[11px] text-muted-foreground"
                        >content studio</span
                    >
                </span>
            </Link>
        </SidebarHeader>

        <SidebarContent class="gap-3 py-3">
            <p
                class="flex justify-between px-4 font-mono text-[11px] tracking-[0.08em] text-muted-foreground uppercase group-data-[collapsible=icon]:hidden"
            >
                <span>Explorer</span><span class="normal-case">~/studio</span>
            </p>
            <NavMain :items="overviewNavItems" label="overview/" />
            <NavMain :items="companyNavItems" label="studio-site/" />
            <NavMain :items="founderNavItems" label="founder-portfolio/" />
        </SidebarContent>

        <SidebarFooter class="gap-2 border-t border-sidebar-border">
            <a
                href="/"
                target="_blank"
                rel="noopener noreferrer"
                class="flex flex-col gap-1 rounded-md border border-sidebar-border px-3 py-2.5 font-mono text-xs transition-colors group-data-[collapsible=icon]:hidden hover:bg-sidebar-accent"
            >
                <span class="flex items-center gap-2 text-foreground">
                    <span
                        aria-hidden="true"
                        class="size-2 rounded-full bg-primary"
                    />
                    sites are live
                </span>
                <span class="text-muted-foreground"
                    >open studio + portfolio ↗</span
                >
            </a>
            <NavFooter
                :items="footerNavItems"
                class="hidden group-data-[collapsible=icon]:block"
            />
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
