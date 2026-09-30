<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import {
    Briefcase,
    Building2,
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
import AppLogo from '@/components/AppLogo.vue';
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
} from '@/components/ui/sidebar';
import { useAppearance } from '@/composables/useAppearance';
import { dashboard } from '@/routes/admin';
import clients from '@/routes/admin/clients';
import company from '@/routes/admin/company';
import educations from '@/routes/admin/educations';
import experiences from '@/routes/admin/experiences';
import messages from '@/routes/admin/messages';
import profile from '@/routes/admin/profile';
import projects from '@/routes/admin/projects';
import skills from '@/routes/admin/skills';
import studioSections from '@/routes/admin/studio-sections';
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
    { title: 'Company', href: company.edit(), icon: Building2 },
    {
        title: 'Home sections',
        href: studioSections.edit(),
        icon: LayoutTemplate,
    },
    { title: 'Clients', href: clients.index(), icon: Users },
    { title: 'Projects', href: projects.index(), icon: FolderKanban },
]);

const founderNavItems: NavItem[] = [
    { title: 'Founder', href: profile.edit(), icon: UserRound },
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

const { appearance } = useAppearance();

const sidebarVariant = computed(() => {
    if (appearance.value === 'colorful') {
        return 'sidebar' as const;
    }

    if (appearance.value === 'aesthetic') {
        return 'floating' as const;
    }

    return 'inset' as const;
});
</script>

<template>
    <Sidebar :variant="sidebarVariant" collapsible="icon">
        <SidebarHeader>
            <Link
                :href="dashboard()"
                class="flex h-14 items-center gap-2 rounded-lg px-2 transition-colors group-data-[collapsible=icon]:justify-center group-data-[collapsible=icon]:px-0 hover:bg-sidebar-accent"
            >
                <AppLogo />
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
            <NavFooter :items="footerNavItems" />
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
