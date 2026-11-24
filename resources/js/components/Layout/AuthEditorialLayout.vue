<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { ArrowLeft, ShieldCheck } from '@lucide/vue';
import { motion } from 'motion-v';
import { computed, ref, watch } from 'vue';
import BrandLogo from '@/components/BrandLogo.vue';
import SmartImage from '@/components/SmartImage.vue';
import { Toaster } from '@/components/ui/sonner';
import { EASE_OUT } from '@/composables/motion';
import { route } from '@/composables/useRoute';
import { useSite } from '@/composables/useSite';
import { useSiteScope } from '@/composables/useSiteScope';
import type {
    AuthEditorialLayoutProps,
    AuthEditorialLayoutShowcase,
} from '@/types';

/**
 * The frame for every sign-in page (log in, forgot / reset password,
 * two-factor, verify email), in the "Founder portrait" design.
 *
 * Left: the founder's photo, full height, with the brand on top, a featured
 * published project as a small card, and the studio mission set over the
 * bottom of the photo. Right: the form on a white card on the sage ground.
 * On phones the photo becomes a banner above the form.
 *
 * Everything shown is admin-editable: the photo and name come from the
 * shared `founder` prop, the mission from `studio`, the logo from `brand`,
 * and the project card from the `showcase` prop the Fortify views pass
 * (FortifyServiceProvider).
 */
defineProps<AuthEditorialLayoutProps>();

useSiteScope('studio');

const page = usePage();
const { brand, studio, founder } = useSite();

/** The first published project (in display order) with a cover. */
const latestProject = computed(
    () =>
        ((page.props.showcase as AuthEditorialLayoutShowcase | undefined) ??
            [])[0] ?? null,
);

/** The founder's role line under the quote: headline, else first role. */
const founderRole = computed(
    () => founder.value.headline || founder.value.roles[0] || 'Founder',
);

/** The quote over the photo: the studio mission, else its tagline. */
const quote = computed(() => studio.value.mission || studio.value.tagline);

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
                [0, -10, 10, -6, 6, 0].map((offset) => ({
                    transform: `translateX(${offset}px)`,
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
        <!-- The founder portrait -->
        <aside
            class="auth-portrait relative h-[44vh] min-h-80 overflow-hidden bg-ink text-white lg:sticky lg:top-0 lg:h-dvh"
        >
            <img
                v-if="founder.avatar_url"
                :src="founder.avatar_url"
                :alt="founder.name"
                class="absolute inset-0 size-full object-cover object-[50%_30%]"
                fetchpriority="high"
            />
            <!-- Until a founder photo is uploaded: a quiet dotted forest
                 ground with the founder's initial, so the panel never
                 looks broken. -->
            <div v-else class="auth-portrait-empty" aria-hidden="true">
                <span class="display">{{ founder.name.charAt(0) }}</span>
            </div>
            <div class="auth-portrait-shade" aria-hidden="true" />

            <!-- Brand -->
            <Link
                :href="route('home')"
                class="absolute top-5 left-5 z-10 inline-flex items-center gap-3 sm:top-8 sm:left-10"
                :aria-label="`${brand.name} home`"
            >
                <BrandLogo variant="mark" size="md" />
                <span class="display text-lg">{{ brand.name }}</span>
            </Link>

            <!-- Featured published project -->
            <motion.div
                v-if="latestProject"
                class="absolute top-24 right-5 z-10 hidden w-56 rotate-3 rounded-2xl bg-white p-2 text-ink shadow-[var(--shadow-lift)] sm:right-9 lg:block"
                :initial="{ opacity: 0, y: -12, rotate: 0 }"
                :animate="{ opacity: 1, y: 0, rotate: 3 }"
                :transition="{ duration: 0.9, ease: EASE_OUT, delay: 0.35 }"
            >
                <SmartImage
                    :src="latestProject.cover"
                    :alt="latestProject.title"
                    ratio="16/10"
                    class="rounded-xl"
                />
                <p class="display mx-1.5 mt-2 text-sm">
                    {{ latestProject.title }}
                </p>
                <p class="mx-1.5 mb-1 text-xs text-stone">Featured work</p>
            </motion.div>

            <!-- Mission over the photo -->
            <div
                class="absolute inset-x-5 bottom-6 z-10 sm:inset-x-10 sm:bottom-10"
            >
                <motion.blockquote
                    v-if="quote"
                    class="display max-w-[24ch] text-2xl leading-tight font-semibold sm:text-3xl xl:text-[2.1rem]"
                    :initial="{ opacity: 0, y: 16 }"
                    :animate="{ opacity: 1, y: 0 }"
                    :transition="{ duration: 0.9, ease: EASE_OUT, delay: 0.2 }"
                >
                    {{ quote }}
                </motion.blockquote>
                <p class="mt-4 flex items-center gap-3 text-sm text-white/85">
                    <span class="h-px w-7 bg-white/50" aria-hidden="true" />
                    <span class="font-bold text-white">{{ founder.name }}</span>
                    {{ founderRole }}
                </p>
            </div>
        </aside>

        <!-- The form -->
        <main
            class="flex flex-col bg-paper py-10"
            :style="{
                paddingLeft: 'max(1.25rem, env(safe-area-inset-left, 0px))',
                paddingRight: 'max(1.25rem, env(safe-area-inset-right, 0px))',
            }"
        >
            <div
                class="mx-auto flex w-full max-w-[27rem] flex-1 flex-col justify-center"
            >
                <motion.div
                    class="auth-card rounded-[1.75rem] border border-line bg-card p-6 shadow-[var(--shadow-lift)] sm:p-10"
                    :initial="{ opacity: 0, y: 24 }"
                    :animate="{ opacity: 1, y: 0 }"
                    :transition="{ duration: 0.8, ease: EASE_OUT }"
                >
                    <BrandLogo size="lg" />

                    <h1 class="display mt-7 text-4xl">
                        {{ title }}
                    </h1>
                    <p v-if="description" class="body-copy mt-2">
                        {{ description }}
                    </p>

                    <div ref="formBox" class="mt-7">
                        <slot />
                    </div>

                    <p
                        class="mt-6 flex items-center gap-2 border-t border-line pt-5 text-sm text-stone"
                    >
                        <ShieldCheck class="size-4" aria-hidden="true" />
                        Admin sign in for {{ brand.name }}
                    </p>
                </motion.div>
            </div>

            <footer
                class="mx-auto mt-10 flex w-full max-w-[27rem] items-center justify-between text-sm text-stone"
            >
                <Link
                    :href="route('home')"
                    class="inline-flex items-center gap-2 font-medium hover:text-ink"
                >
                    <ArrowLeft class="size-4" aria-hidden="true" /> Back to site
                </Link>
                <span>© {{ year }} {{ brand.name }}</span>
            </footer>
        </main>

        <Toaster position="bottom-right" rich-colors />
    </div>
</template>
