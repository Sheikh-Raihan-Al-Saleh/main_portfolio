<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { Bell, Download } from '@lucide/vue';
import { computed } from 'vue';
import Breadcrumbs from '@/components/Breadcrumbs.vue';
import ThemeToggle from '@/components/ThemeToggle.vue';
import { SidebarTrigger } from '@/components/ui/sidebar';
import { usePwa } from '@/composables/usePwa';
import { route } from '@/composables/useRoute';
import type { AppSidebarHeaderProps } from '@/types';

withDefaults(defineProps<AppSidebarHeaderProps>(), {
    breadcrumbs: () => [],
});

const page = usePage();

const unread = computed(() => (page.props.unreadMessages as number) ?? 0);

const { canInstall, install } = usePwa();
</script>

<template>
    <header
        class="sticky top-0 z-30 flex h-14 shrink-0 items-center gap-3 border-b border-border bg-sidebar/90 px-4 backdrop-blur-md sm:px-6"
    >
        <SidebarTrigger class="-ml-1 rounded-md" />
        <span class="h-5 w-px bg-border" aria-hidden="true" />
        <Breadcrumbs
            v-if="breadcrumbs && breadcrumbs.length > 0"
            :breadcrumbs="breadcrumbs"
        />

        <div class="ml-auto flex items-center gap-2">
            <button
                v-if="canInstall"
                type="button"
                class="inline-flex h-9 items-center gap-2 rounded-md border border-border bg-card px-2.5 font-mono text-xs font-bold text-foreground transition-colors hover:border-primary hover:text-primary sm:px-3"
                aria-label="Install the admin as an app"
                @click="install"
            >
                <Download class="size-4" />
                <span class="hidden sm:inline">install app</span>
            </button>
            <span class="hidden sm:inline-flex"><ThemeToggle /></span>
            <Link
                :href="route('messageIndex')"
                class="relative grid size-9 place-items-center rounded-md border border-border bg-card text-muted-foreground transition-colors hover:text-foreground"
                :aria-label="unread ? `Inbox, ${unread} unread` : 'Inbox'"
            >
                <Bell class="size-4" />
                <span
                    v-if="unread"
                    class="absolute -top-1.5 -right-1.5 grid min-w-4 place-items-center rounded bg-primary px-1 font-mono text-[10px] leading-4 font-bold text-primary-foreground"
                    >{{ unread }}</span
                >
            </Link>
            <a
                href="/"
                target="_blank"
                rel="noopener"
                class="ui-btn ui-btn-primary ui-btn-sm hidden sm:inline-flex"
            >
                ↗ open site
            </a>
        </div>
    </header>
</template>
