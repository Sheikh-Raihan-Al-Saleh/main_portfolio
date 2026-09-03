<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import {
    Briefcase,
    ExternalLink,
    FolderKanban,
    GraduationCap,
    LayoutGrid,
    Mail,
    Settings,
    Sparkles,
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
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { dashboard } from '@/routes/admin';
import educations from '@/routes/admin/educations';
import experiences from '@/routes/admin/experiences';
import messages from '@/routes/admin/messages';
import profile from '@/routes/admin/profile';
import projects from '@/routes/admin/projects';
import skills from '@/routes/admin/skills';
import type { NavItem } from '@/types';

const page = usePage();

const unreadMessages = computed(
    () => (page.props.unreadMessages as number) ?? 0,
);

const contentNavItems = computed<NavItem[]>(() => [
    { title: 'Dashboard', href: dashboard(), icon: LayoutGrid },
    { title: 'Projects', href: projects.index(), icon: FolderKanban },
    { title: 'Skills', href: skills.index(), icon: Sparkles },
    { title: 'Experience', href: experiences.index(), icon: Briefcase },
    { title: 'Education', href: educations.index(), icon: GraduationCap },
    {
        title: 'Messages',
        href: messages.index(),
        icon: Mail,
        badge: unreadMessages.value,
    },
]);

const siteNavItems: NavItem[] = [
    { title: 'Site profile', href: profile.edit(), icon: UserRound },
    { title: 'Settings', href: profile.edit(), icon: Settings },
];

const footerNavItems: NavItem[] = [
    { title: 'View live site', href: '/', icon: ExternalLink },
];
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="dashboard()">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :items="contentNavItems" label="Content" />
            <NavMain :items="siteNavItems" label="Site" class="mt-4" />
        </SidebarContent>

        <SidebarFooter>
            <NavFooter :items="footerNavItems" />
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
