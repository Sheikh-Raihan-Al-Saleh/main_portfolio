<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import { motion } from 'motion-v';
import AppContent from '@/components/AppContent.vue';
import AppShell from '@/components/AppShell.vue';
import AppSidebar from '@/components/AppSidebar.vue';
import AppSidebarHeader from '@/components/AppSidebarHeader.vue';
import { Toaster } from '@/components/ui/sonner';
import { useSiteScope } from '@/composables/useSiteScope';
import type { BreadcrumbItem } from '@/types';

type Props = {
    breadcrumbs?: BreadcrumbItem[];
};

withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
});

const page = usePage();

// The admin wears the public design system's tokens (resources/css/site.css).
useSiteScope('admin');
</script>

<template>
    <div data-site="admin" class="contents">
        <AppShell variant="sidebar">
            <AppSidebar />
            <AppContent
                variant="sidebar"
                class="min-w-0 overflow-x-clip bg-background"
            >
                <AppSidebarHeader :breadcrumbs="breadcrumbs" />
                <!--
                    Enter-only transition, remounted per route. No exit
                    animation: keeping the outgoing page mounted would show
                    stale content after the address bar has already changed.
                -->
                <motion.div
                    :key="page.url"
                    :initial="{ opacity: 0, y: 10 }"
                    :animate="{ opacity: 1, y: 0 }"
                    :transition="{ duration: 0.35, ease: [0.16, 1, 0.3, 1] }"
                    class="mx-auto w-full max-w-7xl"
                >
                    <slot />
                </motion.div>
            </AppContent>
            <Toaster position="bottom-right" rich-colors />
        </AppShell>
    </div>
</template>
