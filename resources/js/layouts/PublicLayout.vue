<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import { MotionConfig } from 'motion-v';
import { computed, onBeforeUnmount, onMounted, watch } from 'vue';
import SiteFooter from '@/components/site/SiteFooter.vue';
import SiteHeader from '@/components/site/SiteHeader.vue';
import { Toaster } from '@/components/ui/sonner';
import { usePublicNav } from '@/composables/usePublicNav';

/**
 * The public site's shell.
 *
 * The site has two faces — the studio and the founder — that share one design
 * system and differ by accent. `data-site` on the wrapper selects the face's
 * tokens (see resources/css/site.css). It is mirrored onto <body> on the
 * client as well, because dialogs and toasts portal out of the wrapper and
 * would otherwise fall back to the admin palette.
 */
const page = usePage();
const nav = usePublicNav();

const site = computed(() => (nav.isCompanySite.value ? 'studio' : 'founder'));

onMounted(() => {
    document.body.dataset.site = site.value;
});

watch(site, (value) => {
    document.body.dataset.site = value;
});

onBeforeUnmount(() => {
    delete document.body.dataset.site;
});
</script>

<template>
    <MotionConfig reduced-motion="user">
        <div
            id="top"
            :data-site="site"
            class="min-h-screen bg-background text-foreground antialiased"
        >
            <a
                href="#main"
                class="sr-only focus:not-sr-only focus:fixed focus:top-4 focus:left-4 focus:z-100 focus:rounded-md focus:bg-background focus:px-4 focus:py-2 focus:text-foreground focus:ring-2 focus:ring-ring"
            >
                Skip to content
            </a>

            <SiteHeader :nav="nav" />

            <main id="main" class="relative">
                <!--
                    Plain page wrapper with a CSS entrance that replays as the
                    key changes on navigation. Motion-based page transitions
                    left stale content behind during Inertia visits.
                -->
                <div :key="page.url" class="page-enter">
                    <slot />
                </div>
            </main>

            <SiteFooter :nav="nav" />

            <Toaster position="bottom-right" rich-colors />
        </div>
    </MotionConfig>
</template>
