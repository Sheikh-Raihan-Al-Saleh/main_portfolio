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
import AppLogo from '@/components/app/AppLogo.vue';
import NavFooter from '@/components/app/NavFooter.vue';
import NavMain from '@/components/app/NavMain.vue';
import NavUser from '@/components/app/NavUser.vue';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
} from '@/components/ui/sidebar';
import { dashboard } from '@/routes/admin';
import clients from '@/routes/admin/clients';
import educations from '@/routes/admin/educations';
import experiences from '@/routes/admin/experiences';
import founder from '@/routes/admin/founder';
import messages from '@/routes/admin/messages';
import projects from '@/routes/admin/projects';
import skills from '@/routes/admin/skills';
import studio from '@/routes/admin/studio';
import technologies from '@/routes/admin/technologies';
import type { NavItem } from '@/types';

const page = usePage();

const unreadMessages = computed(
    () => (page.props.unreadMessages as number) ?? 0,
);

/**
 * The admin edits two sites, not one. Content is grouped by which site it
 * appears on so it is obvious where a change will show up: the studio's own
 * pages first, then the founder's portfolio.
 */
const companyNavItems = computed<NavItem[]>(() => [
    { title: 'Studio & brand', href: studio.edit(), icon: Building2 },
    {
        title: 'Home page copy',
        href: studio.content.edit(),
        icon: LayoutTemplate,
    },
    { title: 'Clients', href: clients.index(), icon: Users },
    { title: 'Projects', href: projects.index(), icon: FolderKanban },
    { title: 'Technologies', href: technologies.index(), icon: Cpu },
]);

const founderNavItems: NavItem[] = [
    { title: 'Founder', href: founder.edit(), icon: UserRound },
    { title: 'Skills', href: skills.index(), icon: Sparkles },
    { title: 'Experience', href: experiences.index(), icon: Briefcase },
    { title: 'Education', href: educations.index(), icon: GraduationCap },
];

const dashboardNavItems: NavItem[] = [
    { title: 'Dashboard', href: dashboard(), icon: LayoutGrid },
];

const messageNavItems = computed<NavItem[]>(() => [
    {
        title: 'Messages',
        href: messages.index(),
        icon: Mail,
        badge: unreadMessages.value,
    },
]);

const footerNavItems: NavItem[] = [
    { title: 'View live site', href: '/', icon: ExternalLink },
];
</script>

<template>
    <Sidebar variant="floating" collapsible="icon">
        <SidebarHeader>
            <Link
                :href="dashboard()"
                class="flex h-14 items-center gap-2 rounded-xl px-2 transition-colors group-data-[collapsible=icon]:justify-center group-data-[collapsible=icon]:px-0"
            >
                <AppLogo />
                <span
                    class="ml-auto rounded-full bg-brand-subtle px-2 py-0.5 text-[0.65rem] font-semibold text-brand-text group-data-[collapsible=icon]:hidden"
                    >Admin</span
                >
            </Link>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :items="dashboardNavItems" label="Overview" />
            <NavMain
                :items="companyNavItems"
                label="Studio site"
                class="mt-4"
            />
            <NavMain
                :items="founderNavItems"
                label="Founder portfolio"
                class="mt-4"
            />
            <NavMain :items="messageNavItems" label="Inbox" class="mt-4" />
        </SidebarContent>

        <SidebarFooter>
            <!-- Live site card -->
            <a
                href="/"
                target="_blank"
                rel="noopener noreferrer"
                class="group relative mx-1 mb-1 overflow-hidden rounded-2xl bg-ink p-4 text-paper group-data-[collapsible=icon]:hidden"
            >
                <p class="text-sm font-semibold">Your sites are live</p>
                <p class="mt-0.5 text-xs text-paper/70">
                    Preview the studio and the founder portfolio.
                </p>
                <span
                    class="mt-3 inline-flex items-center gap-1.5 rounded-full bg-brand px-2.5 py-1 text-xs font-medium text-brand-foreground transition-colors group-hover:bg-paper group-hover:text-ink"
                >
                    <ExternalLink class="size-3.5" />
                    View live site
                </span>
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
