<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import { MotionConfig } from 'motion-v';
import { onBeforeUnmount, onMounted, watch } from 'vue';
import SiteFooter from '@/components/Site/SiteFooter.vue';
import SiteHeader from '@/components/Site/SiteHeader.vue';
import SiteLoader from '@/components/Site/SiteLoader.vue';
import { Toaster } from '@/components/ui/sonner';
import { useSite } from '@/composables/useSite';

/**
 * The public website shell: loader, header, page, footer.
 *
 * `data-site` selects the accent of the current face (studio or founder, see
 * site.css). It is mirrored onto <body> because dialogs and toasts render
 * outside this wrapper.
 */
const page = usePage();
const { face } = useSite();

onMounted(() => (document.body.dataset.site = face.value));
watch(face, (value) => (document.body.dataset.site = value));
onBeforeUnmount(() => delete document.body.dataset.site);
</script>

<template>
    <MotionConfig reduced-motion="user">
        <div
            id="top"
            :data-site="face"
            class="site min-h-screen overflow-x-clip antialiased"
        >
            <a
                href="#main"
                class="sr-only focus:not-sr-only focus:fixed focus:top-4 focus:left-4 focus:z-[110] focus:rounded-full focus:bg-ink focus:px-5 focus:py-3 focus:text-paper"
            >
                Skip to content
            </a>

            <SiteLoader />
            <SiteHeader />

            <main id="main">
                <div :key="page.url" class="page-enter">
                    <slot />
                </div>
            </main>

            <SiteFooter />

            <Toaster position="bottom-right" rich-colors />
        </div>
    </MotionConfig>
</template>
