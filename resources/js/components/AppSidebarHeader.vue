<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { Bell, ExternalLink } from '@lucide/vue';
import { computed } from 'vue';
import Breadcrumbs from '@/components/Breadcrumbs.vue';
import ThemeToggle from '@/components/portfolio/ThemeToggle.vue';
import { SidebarTrigger } from '@/components/ui/sidebar';
import messages from '@/routes/admin/messages';
import type { BreadcrumbItem } from '@/types';

withDefaults(
    defineProps<{
        breadcrumbs?: BreadcrumbItem[];
    }>(),
    {
        breadcrumbs: () => [],
    },
);

const page = usePage();

const unread = computed(() => (page.props.unreadMessages as number) ?? 0);
</script>

<template>
    <header
        class="ui-glass sticky top-0 z-30 flex h-16 shrink-0 items-center gap-3 border-x-0 border-t-0 border-b border-border px-4 sm:px-6"
    >
        <SidebarTrigger class="-ml-1 rounded-lg" />
        <span class="h-5 w-px bg-border" aria-hidden="true" />
        <Breadcrumbs
            v-if="breadcrumbs && breadcrumbs.length > 0"
            :breadcrumbs="breadcrumbs"
        />

        <div class="ml-auto flex items-center gap-2">
            <span class="hidden sm:inline-flex"><ThemeToggle /></span>
            <Link
                :href="messages.index()"
                class="relative grid size-9 place-items-center rounded-full border border-border bg-card text-muted-foreground transition-colors hover:text-foreground"
                :aria-label="unread ? `Inbox, ${unread} unread` : 'Inbox'"
            >
                <Bell class="size-4" />
                <span
                    v-if="unread"
                    class="absolute -top-1 -right-1 grid min-w-4 place-items-center rounded-full bg-gradient-to-r from-[var(--signal)] to-[var(--grad-to)] px-1 text-[0.6rem] leading-4 font-semibold text-white"
                    >{{ unread }}</span
                >
            </Link>
            <a
                href="/"
                target="_blank"
                rel="noopener"
                class="ui-btn ui-btn-primary ui-btn-sm hidden sm:inline-flex"
            >
                <ExternalLink class="size-4" />
                View site
            </a>
        </div>
    </header>
</template>
