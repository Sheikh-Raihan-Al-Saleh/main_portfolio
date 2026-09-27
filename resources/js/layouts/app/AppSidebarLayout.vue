<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import { motion } from 'motion-v';
import AppContent from '@/components/AppContent.vue';
import AppShell from '@/components/AppShell.vue';
import AppSidebar from '@/components/AppSidebar.vue';
import AppSidebarHeader from '@/components/AppSidebarHeader.vue';
import { Toaster } from '@/components/ui/sonner';
import type { BreadcrumbItem } from '@/types';

type Props = {
    breadcrumbs?: BreadcrumbItem[];
};

withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
});

const page = usePage();
</script>

<template>
    <AppShell variant="sidebar">
        <AppSidebar />
        <AppContent variant="sidebar" class="min-w-0 overflow-x-clip">
            <AppSidebarHeader :breadcrumbs="breadcrumbs" />
            <!--
                Enter-only transition, remounted per route. No exit animation:
                keeping the outgoing page mounted would show stale content
                after the address bar has already changed.
            -->
            <motion.div
                :key="page.url"
                :initial="{ opacity: 0, y: 8 }"
                :animate="{ opacity: 1, y: 0 }"
                :transition="{ duration: 0.25, ease: 'easeOut' }"
            >
                <slot />
            </motion.div>
        </AppContent>
        <Toaster />
    </AppShell>
</template>
