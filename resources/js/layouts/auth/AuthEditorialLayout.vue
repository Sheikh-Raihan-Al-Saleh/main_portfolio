<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { ArrowLeft } from '@lucide/vue';
import { AnimatePresence, motion } from 'motion-v';
import { computed, ref, watch } from 'vue';
import LoginScene from '@/components/auth/LoginScene.vue';
import BrandLogo from '@/components/brand/BrandLogo.vue';
import { Toaster } from '@/components/ui/sonner';
import { useBrand } from '@/composables/useSite';
import { useSiteScope } from '@/composables/useSiteScope';
import { EASE_OUT } from '@/lib/motion';
import { home } from '@/routes';
import type {
    AuthEditorialLayoutProps,
    AuthEditorialLayoutShowcase,
} from '@/types';

/**
 * The frame for every sign-in page (log in, forgot / reset password,
 * two-factor, verify email).
 *
 * Left: a three.js scene of the studio's published work floating in space,
 * with the brand and a "Content Studio" badge. Right: the form on paper,
 * headed by the company logo. On phones the scene becomes a banner.
 *
 * The brand comes from the shared `brand` prop; the covers from the
 * `showcase` prop the Fortify views pass (FortifyServiceProvider).
 */
defineProps<AuthEditorialLayoutProps>();

useSiteScope('studio');

const page = usePage();
const brand = useBrand();
const showcase = computed(
    () =>
        (page.props.showcase as AuthEditorialLayoutShowcase | undefined) ?? [],
);
const hovered = ref<string | null>(null);

/**
 * Shake the form when the server returns validation errors. Animated on the
 * element directly (Web Animations API), so the form is never re-mounted and
 * its error messages stay on screen.
 */
const formBox = ref<HTMLElement | null>(null);
watch(
    () => page.props.errors,
    (errors) => {
        const reduced = window.matchMedia(
            '(prefers-reduced-motion: reduce)',
        ).matches;

        if (errors && Object.keys(errors).length > 0 && !reduced) {
            formBox.value?.animate(
                [0, -10, 10, -6, 6, 0].map((x) => ({
                    transform: `translateX(${x}px)`,
                })),
                { duration: 450, easing: 'ease-out' },
            );
        }
    },
);

const year = new Date().getFullYear();
</script>

<template>
    <div
        data-site="studio"
        class="site grid min-h-dvh lg:grid-cols-[minmax(0,1.15fr)_minmax(0,1fr)]"
    >
        <!-- The scene -->
        <aside
            class="relative h-[38vh] overflow-hidden border-b border-line bg-paper lg:sticky lg:top-0 lg:h-dvh lg:border-r lg:border-b-0"
        >
            <div
                class="bg-grid pointer-events-none absolute inset-0 opacity-70"
                aria-hidden="true"
            />

            <LoginScene
                class="absolute inset-0"
                :covers="showcase"
                :logo-url="brand.logo_mark_url ?? brand.logo_url"
                :brand-name="brand.name"
                @hover="hovered = $event"
            />

            <div
                class="pointer-events-none absolute inset-x-0 top-0 flex items-center justify-between p-5 sm:p-8"
            >
                <Link
                    :href="home().url"
                    class="pointer-events-auto"
                    :aria-label="`${brand.name} home`"
                >
                    <BrandLogo size="lg" />
                </Link>
                <span
                    class="rounded-full border border-ink bg-paper px-4 py-1.5 font-mono text-[0.7rem] tracking-[0.16em] uppercase"
                >
                    Content Studio
                </span>
            </div>

            <div
                class="pointer-events-none absolute inset-x-0 bottom-0 hidden p-8 lg:block"
            >
                <p class="meta mb-3">
                    {{
                        hovered
                            ? 'Published project'
                            : 'Your work, managed in one place'
                    }}
                </p>
                <AnimatePresence mode="wait">
                    <motion.p
                        :key="hovered ?? 'brand'"
                        class="display text-5xl leading-none xl:text-6xl"
                        :initial="{ opacity: 0, y: 16 }"
                        :animate="{ opacity: 1, y: 0 }"
                        :exit="{ opacity: 0, y: -16 }"
                        :transition="{ duration: 0.35, ease: EASE_OUT }"
                    >
                        {{ hovered ?? brand.name }}<em v-if="!hovered">.</em>
                    </motion.p>
                </AnimatePresence>
            </div>
        </aside>

        <!-- The form -->
        <main
            class="flex flex-col bg-card px-5 py-10 sm:px-12 lg:px-16 xl:px-24"
        >
            <div
                class="mx-auto flex w-full max-w-md flex-1 flex-col justify-center"
            >
                <motion.div
                    :initial="{ opacity: 0, y: 24 }"
                    :animate="{ opacity: 1, y: 0 }"
                    :transition="{ duration: 0.8, ease: EASE_OUT }"
                >
                    <BrandLogo variant="mark" size="xl" />

                    <h1 class="display mt-10 text-5xl sm:text-6xl">
                        {{ title }}
                    </h1>
                    <p v-if="description" class="body-copy mt-4">
                        {{ description }}
                    </p>
                </motion.div>

                <motion.div
                    class="mt-10"
                    :initial="{ opacity: 0, y: 24 }"
                    :animate="{ opacity: 1, y: 0 }"
                    :transition="{ duration: 0.8, ease: EASE_OUT, delay: 0.12 }"
                >
                    <div ref="formBox">
                        <slot />
                    </div>
                </motion.div>
            </div>

            <footer
                class="mx-auto mt-12 flex w-full max-w-md items-center justify-between text-sm text-stone"
            >
                <Link
                    :href="home().url"
                    class="inline-flex items-center gap-2 hover:text-ink"
                >
                    <ArrowLeft class="size-4" aria-hidden="true" /> Back to site
                </Link>
                <span>© {{ year }} {{ brand.name }}</span>
            </footer>
        </main>

        <Toaster position="bottom-right" rich-colors />
    </div>
</template>
