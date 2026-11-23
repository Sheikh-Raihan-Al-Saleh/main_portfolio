<script setup lang="ts">
import { Link, router, usePage } from '@inertiajs/vue3';
import { ArrowUpRight, Menu, X } from '@lucide/vue';
import { AnimatePresence, motion } from 'motion-v';
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue';
import BrandLogo from '@/components/brand/BrandLogo.vue';
import { useCurrentUrl } from '@/composables/useCurrentUrl';
import { useSite } from '@/composables/useSite';
import { about, founder as founderRoute, home } from '@/routes';
import projects from '@/routes/projects';
import type { SiteHeaderNavLink } from '@/types';

/**
 * The public header: the company logo (large, always visible), the page
 * navigation for the current face of the site, and one call to action.
 */
const page = usePage();
const { studio, founder, face } = useSite();

/** Founder sections are in-page anchors on /founder, full links elsewhere. */
const founderBase = computed(() =>
    page.component === 'public/founder/Index' ? '' : founderRoute().url,
);

const links = computed<SiteHeaderNavLink[]>(() =>
    face.value === 'founder'
        ? [
              { label: 'About', href: `${founderBase.value}#about` },
              { label: 'Skills', href: `${founderBase.value}#skills` },
              { label: 'Work', href: `${founderBase.value}#projects` },
              { label: 'Experience', href: `${founderBase.value}#experience` },
              { label: 'Contact', href: `${founderBase.value}#contact` },
          ]
        : [
              { label: 'Work', href: projects.index().url },
              { label: 'Services', href: `${home().url}#capabilities` },
              { label: 'About', href: about().url },
              { label: 'Founder', href: founderRoute().url },
              { label: 'Contact', href: `${home().url}#contact` },
          ],
);

const cta = computed(() =>
    face.value === 'founder'
        ? founder.value.resume_url
            ? {
                  label: 'Resume',
                  href: founder.value.resume_url,
                  external: true,
              }
            : {
                  label: 'Get in touch',
                  href: `${founderBase.value}#contact`,
                  external: false,
              }
        : {
              label: studio.value.hero.primary_cta.label || 'Start a project',
              href: `${home().url}#contact`,
              external: false,
          },
);

const open = ref(false);
const scrolled = ref(false);

function onScroll(): void {
    scrolled.value = window.scrollY > 12;
}

onMounted(() => {
    onScroll();
    window.addEventListener('scroll', onScroll, { passive: true });
});

onBeforeUnmount(() => window.removeEventListener('scroll', onScroll));

// Close the mobile menu on navigation, and lock page scroll while it is open.
const removeListener = router.on('navigate', () => (open.value = false));
onBeforeUnmount(removeListener);

watch(open, (value) => {
    document.documentElement.style.overflow = value ? 'hidden' : '';
});

// Never leave the page locked if the header unmounts with the menu open.
onBeforeUnmount(() => {
    document.documentElement.style.overflow = '';
});

/** In-page anchors scroll; everything else is an Inertia visit. */
const isAnchor = (href: string) => href.startsWith('#');

const { isCurrentOrParentUrl } = useCurrentUrl();

/**
 * Marks the link to the page being shown. Links to a section of a page
 * (with a #fragment) are never marked, since the whole page is not theirs.
 */
function currentPageAttribute(href: string): 'page' | undefined {
    return !href.includes('#') && isCurrentOrParentUrl(href)
        ? 'page'
        : undefined;
}
</script>

<template>
    <header class="site-header" :data-scrolled="scrolled || open">
        <div
            class="container-site flex h-20 items-center justify-between gap-6 sm:h-24"
        >
            <Link
                :href="home().url"
                class="relative z-50 shrink-0"
                :aria-label="`${studio.name} home`"
            >
                <BrandLogo size="lg" />
            </Link>

            <nav aria-label="Main" class="hidden lg:block">
                <ul class="nav-pill">
                    <li v-for="link in links" :key="link.label">
                        <a
                            v-if="isAnchor(link.href)"
                            :href="link.href"
                            class="nav-pill-link"
                        >
                            {{ link.label }}
                        </a>
                        <Link
                            v-else
                            :href="link.href"
                            class="nav-pill-link"
                            :aria-current="currentPageAttribute(link.href)"
                        >
                            {{ link.label }}
                        </Link>
                    </li>
                </ul>
            </nav>

            <div class="flex items-center gap-2">
                <a
                    :href="cta.href"
                    :target="cta.external ? '_blank' : undefined"
                    :rel="cta.external ? 'noopener' : undefined"
                    class="btn btn-ink btn-sm hidden sm:inline-flex"
                >
                    {{ cta.label }}
                    <ArrowUpRight class="btn-arrow size-4" aria-hidden="true" />
                </a>

                <button
                    type="button"
                    class="relative z-50 grid size-11 place-items-center rounded-full border border-line bg-card shadow-[var(--shadow-soft)] lg:hidden"
                    :aria-expanded="open"
                    aria-controls="mobile-menu"
                    :aria-label="open ? 'Close menu' : 'Open menu'"
                    @click="open = !open"
                >
                    <X v-if="open" class="size-5" aria-hidden="true" />
                    <Menu v-else class="size-5" aria-hidden="true" />
                </button>
            </div>
        </div>

        <AnimatePresence>
            <motion.div
                v-if="open"
                id="mobile-menu"
                class="fixed inset-0 z-40 flex flex-col bg-paper px-4 pt-28 pb-10 sm:px-6 lg:hidden"
                :initial="{ opacity: 0 }"
                :animate="{ opacity: 1 }"
                :exit="{ opacity: 0 }"
                :transition="{ duration: 0.25 }"
            >
                <nav aria-label="Mobile">
                    <ul class="grid">
                        <motion.li
                            v-for="(link, linkIndex) in links"
                            :key="link.label"
                            class="rule"
                            :initial="{ opacity: 0, y: 16 }"
                            :animate="{ opacity: 1, y: 0 }"
                            :transition="{
                                delay: 0.05 + linkIndex * 0.05,
                                duration: 0.5,
                            }"
                        >
                            <component
                                :is="isAnchor(link.href) ? 'a' : Link"
                                :href="link.href"
                                class="flex items-baseline justify-between py-5"
                                @click="open = false"
                            >
                                <span class="display text-4xl">{{
                                    link.label
                                }}</span>
                                <ArrowUpRight
                                    class="size-5 text-stone"
                                    aria-hidden="true"
                                />
                            </component>
                        </motion.li>
                    </ul>
                </nav>

                <a
                    :href="cta.href"
                    class="btn btn-ink mt-auto w-full"
                    @click="open = false"
                >
                    {{ cta.label }}
                </a>
            </motion.div>
        </AnimatePresence>
    </header>
</template>
