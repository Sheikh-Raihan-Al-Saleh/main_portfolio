<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ArrowUpRight, Download, Menu } from '@lucide/vue';
import { useWindowScroll } from '@vueuse/core';
import { motion, useScroll, useSpring } from 'motion-v';
import { computed, ref } from 'vue';
import ThemeToggle from '@/components/portfolio/ThemeToggle.vue';
import BrandMark from '@/components/site/BrandMark.vue';
import { Sheet, SheetContent, SheetTitle } from '@/components/ui/sheet';
import type { PublicNav } from '@/composables/usePublicNav';
import { cn } from '@/lib/utils';

/**
 * The public header, shared by both faces: a floating glass bar that tightens
 * once the page scrolls. The call to action follows the face — the studio
 * asks for a project, the founder offers the résumé (or the contact form).
 */
type Props = {
    nav: PublicNav;
};

const props = defineProps<Props>();

const { y: scrollY } = useWindowScroll();

// Reading progress along the top edge of the viewport.
const { scrollYProgress } = useScroll();
const progress = useSpring(scrollYProgress, { stiffness: 140, damping: 30 });
const scrolled = computed(() => scrollY.value > 16);

const menuOpen = ref(false);

const links = computed(() => [
    ...props.nav.sections.value.map((section) => ({
        key: `s-${section.id}`,
        label: section.label,
        href: props.nav.sectionHref(section.id),
        page: false,
        active: props.nav.activeSection.value === section.id,
    })),
    ...props.nav.pageLinks.value.map((link) => ({
        key: `p-${link.id}`,
        label: link.label,
        href: props.nav.pageLinkHref(link.id) ?? '/',
        page: true,
        active: props.nav.isCurrentPageLink(link.id),
    })),
]);

const cta = computed(() => {
    if (props.nav.isCompanySite.value) {
        return { label: 'Start a project', href: '/#contact', resume: false };
    }

    return props.nav.profile.value?.resume_url
        ? { label: 'Résumé', href: '/resume', resume: true }
        : { label: 'Hire me', href: '/founder#contact', resume: false };
});
</script>

<template>
    <!--
        A full-width bar: transparent over the hero, then frosted with a
        hairline underneath once the page scrolls.
    -->
    <header
        :class="
            cn(
                'fixed inset-x-0 top-0 z-50 border-b transition-colors duration-300',
                scrolled
                    ? 'border-border bg-background/80 backdrop-blur-md backdrop-saturate-150'
                    : 'border-transparent bg-transparent',
            )
        "
    >
        <motion.div
            class="pointer-events-none absolute inset-x-0 bottom-[-1px] h-0.5 origin-left bg-[var(--accent-fill)]"
            :style="{ scaleX: progress }"
            aria-hidden="true"
            data-testid="scroll-progress"
        />
        <div
            :class="
                cn(
                    'site-container flex items-center gap-4 transition-all duration-500',
                    // Tall at the top so the logo can lead; compact once scrolling.
                    scrolled ? 'h-16 sm:h-[4.5rem]' : 'h-20 sm:h-24',
                )
            "
        >
            <Link
                :href="nav.homeHref.value"
                class="shrink-0"
                :aria-label="nav.owner.value?.name ?? 'Home'"
            >
                <BrandMark
                    :logo-url="nav.brandLogoUrl.value"
                    :name="nav.owner.value?.name"
                    :initials="nav.initials.value"
                    :initials-class="
                        cn(
                            'rounded-xl bg-foreground text-background transition-all duration-500',
                            scrolled
                                ? 'size-10 text-sm'
                                : 'size-12 text-base sm:size-14 sm:text-lg',
                        )
                    "
                    :image-class="
                        cn(
                            'transition-all duration-500',
                            scrolled ? 'h-11 sm:h-14' : 'h-14 sm:h-[4.5rem]',
                        )
                    "
                />
            </Link>

            <nav
                class="mx-auto hidden items-center gap-1 lg:flex"
                aria-label="Primary"
            >
                <component
                    :is="link.page ? Link : 'a'"
                    v-for="link in links"
                    :key="link.key"
                    :href="link.href"
                    :aria-current="
                        link.active ? (link.page ? 'page' : 'true') : undefined
                    "
                    :class="
                        cn(
                            // Mono labels with an accent underline that slides in.
                            'ui-label relative px-3 py-2 transition-colors after:absolute after:inset-x-3 after:bottom-1 after:h-0.5 after:origin-left after:bg-[var(--accent-fill)] after:transition-transform after:duration-300',
                            link.active
                                ? 'text-foreground after:scale-x-100'
                                : 'text-muted-foreground after:scale-x-0 hover:text-foreground hover:after:scale-x-100',
                        )
                    "
                >
                    {{ link.label }}
                </component>
            </nav>

            <div class="ml-auto flex items-center gap-2 lg:ml-0">
                <span class="hidden sm:inline-flex"><ThemeToggle /></span>
                <a
                    :href="cta.href"
                    :target="cta.resume ? '_blank' : undefined"
                    :rel="cta.resume ? 'noopener' : undefined"
                    class="ui-btn ui-btn-primary ui-btn-sm hidden sm:inline-flex"
                >
                    <Download v-if="cta.resume" class="size-4" />
                    {{ cta.label }}
                    <ArrowUpRight v-if="!cta.resume" class="size-4" />
                </a>
                <button
                    type="button"
                    class="grid size-10 place-items-center rounded-full border border-border bg-card lg:hidden"
                    aria-label="Open menu"
                    @click="menuOpen = true"
                >
                    <Menu class="size-4" />
                </button>
            </div>
        </div>
    </header>

    <Sheet v-model:open="menuOpen">
        <SheetContent side="right" class="w-80 border-border bg-background">
            <SheetTitle class="px-5 pt-5 text-sm font-semibold">
                {{ nav.owner.value?.name ?? 'Menu' }}
            </SheetTitle>
            <nav class="mt-2 flex flex-col gap-1 px-3" aria-label="Mobile">
                <component
                    :is="link.page ? Link : 'a'"
                    v-for="link in links"
                    :key="link.key"
                    :href="link.href"
                    :aria-current="link.active ? 'true' : undefined"
                    :class="
                        cn(
                            'rounded-xl px-4 py-3 text-base font-medium',
                            link.active
                                ? 'bg-brand-subtle text-brand'
                                : 'hover:bg-muted',
                        )
                    "
                    @click="menuOpen = false"
                >
                    {{ link.label }}
                </component>
            </nav>
            <div class="mt-auto flex flex-col gap-4 border-t border-border p-5">
                <ThemeToggle />
                <a
                    :href="cta.href"
                    :target="cta.resume ? '_blank' : undefined"
                    :rel="cta.resume ? 'noopener' : undefined"
                    class="ui-btn ui-btn-primary"
                    @click="menuOpen = false"
                >
                    {{ cta.label }}
                </a>
            </div>
        </SheetContent>
    </Sheet>
</template>
