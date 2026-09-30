<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { Menu, X } from '@lucide/vue';
import { useWindowScroll } from '@vueuse/core';
import { MotionConfig } from 'motion-v';
import { computed, onMounted, ref } from 'vue';
import ScrollProgressBar from '@/components/motion/ScrollProgressBar.vue';
import WaterIntro from '@/components/motion/WaterIntro.vue';
import FooterSection from '@/components/portfolio/FooterSection.vue';
import ThemeToggle from '@/components/portfolio/ThemeToggle.vue';
import { Button } from '@/components/ui/button';
import { Sheet, SheetContent, SheetTitle } from '@/components/ui/sheet';
import { Toaster } from '@/components/ui/sonner';
import { getInitials } from '@/composables/useInitials';
import { useScrollSpy } from '@/composables/useScrollSpy';
import { useSiteOwner } from '@/composables/useSiteOwner';
import { cn } from '@/lib/utils';

const page = usePage();

const { scope, owner, profile, company } = useSiteOwner();

const isCompanySite = computed(() => scope.value === 'company');
const isHome = computed(() => page.url === '/' || page.url.startsWith('/?'));
const isProjectsRoute = computed(() => page.url.startsWith('/projects'));
const isAboutRoute = computed(() => page.url.startsWith('/about'));
const isFounderRoute = computed(() => page.url.startsWith('/founder'));

/**
 * Water intro.
 *
 * Shown once per browser session, on the home page only, and never for
 * reduced-motion users — it is a greeting, not a gate. Because it is an
 * overlay rather than its own route, the home page (its HTML, images and
 * Inertia payload) is already loading underneath it, so the intro costs no
 * round trip. The key below is what makes "once per session" true; clear it
 * to see the intro again.
 */
const INTRO_SESSION_KEY = 'nabil:intro-played';

/**
 * The overlay is mounted.
 *
 * The page beneath is deliberately left visible rather than faded out: the
 * overlay is opaque, so it already hides everything, and the scene's exit
 * wipe is what reveals the page. Gating content visibility as well would add
 * a second reveal to keep in step — and a way for the page to stay hidden if
 * anything went wrong with it.
 */
const introActive = ref(false);

/**
 * The drain has begun. The page entrance is keyed off this rather than off
 * mount time, so the content rises into place *as* the water clears instead
 * of having finished its entrance behind an opaque overlay.
 */
const introDraining = ref(false);

onMounted(() => {
    if (!isHome.value) {
        return;
    }

    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        return;
    }

    try {
        if (window.sessionStorage.getItem(INTRO_SESSION_KEY) === '1') {
            return;
        }

        window.sessionStorage.setItem(INTRO_SESSION_KEY, '1');
    } catch {
        // Storage unavailable (private mode, blocked cookies). Skip the intro
        // rather than replaying it on every single navigation.
        return;
    }

    introActive.value = true;
});

function onIntroExit() {
    introDraining.value = true;
}

function onIntroDone() {
    introActive.value = false;
    introDraining.value = true;
}

/**
 * Navigation follows the current face of the site, and only lists sections that
 * actually exist on the page being rendered — a link to a missing anchor is
 * worse than no link at all. The client strip is only offered when there are
 * clients to show, because it renders nothing otherwise.
 */
const hasClients = computed(
    () => ((page.props as { clients?: unknown[] }).clients?.length ?? 0) > 0,
);

const sections = computed(() => {
    if (!isCompanySite.value) {
        return [
            { id: 'about', label: 'About' },
            { id: 'skills', label: 'Skills' },
            { id: 'projects', label: 'Work' },
            { id: 'experience', label: 'Experience' },
            { id: 'contact', label: 'Contact' },
        ];
    }

    const clients = hasClients.value ? [{ id: 'clients', label: 'Clients' }] : [];

    // The About page has its own chapters; the home page has its own.
    if (isAboutRoute.value) {
        return [...clients, { id: 'founder', label: 'Founder' }, { id: 'contact', label: 'Contact' }];
    }

    if (isHome.value) {
        return [
            { id: 'work', label: 'Work' },
            ...clients,
            { id: 'contact', label: 'Contact' },
        ];
    }

    // The archive and project pages have no chapters of their own, so these
    // point back at the home page sections they name.
    return [
        { id: 'work', label: 'Work' },
        { id: 'contact', label: 'Contact' },
    ];
});

/**
 * Whole-page links, kept separate from the in-page section links so the same
 * label is never shown twice: any destination the section links already cover
 * is dropped, and a link to the page you are already on is dropped.
 */
const pageLinks = computed(() => {
    const links = isCompanySite.value
        ? [
              { id: 'about', label: 'About' },
              { id: 'archive', label: 'Archive' },
              { id: 'founder', label: 'Founder' },
          ]
        : [{ id: 'studio', label: 'Studio' }];

    const sectionLabels = sections.value.map((section) => section.label);

    return links.filter(
        (link) =>
            !sectionLabels.includes(link.label) && pageLinkHref(link.id) !== null,
    );
});

const navSectionIds = computed(() => sections.value.map((section) => section.id));

/**
 * Chapters that have no navbar link of their own. They are observed anyway so
 * the highlight is switched off while the reader is inside them, rather than
 * leaving the previous section lit.
 *
 * The company home page has grown a number of these, so the list is kept here
 * rather than derived: adding a section to the page should not silently change
 * what the navbar highlights.
 */
const unlinkedRegions = computed(() =>
    isHome.value
        ? ['capabilities', 'engineering', 'why', 'about', 'founder-work', 'start']
        : [],
);

const observedId = useScrollSpy(() => [...navSectionIds.value, ...unlinkedRegions.value]);

/** Only a section that owns a navbar link may be highlighted. */
const activeSection = computed(() =>
    navSectionIds.value.includes(observedId.value ?? '') ? observedId.value : null,
);

const mobileOpen = ref(false);

const { y: scrollY } = useWindowScroll();
const scrolled = computed(() => scrollY.value > 12);

const initials = computed(() => getInitials(owner.value?.name));

/**
 * The website logo, managed in Admin → Founder → Website logo. The studio
 * logo stays as a fallback for older uploads. The white chip keeps
 * dark-on-light artwork legible in dark mode. Null means no logo was
 * uploaded; the initials mark is shown.
 */
const brandLogoUrl = computed(
    () => profile.value?.logo_url ?? company.value?.logo_url ?? null,
);

/** The route that owns a given section, so anchors stay on the right page. */
const sectionBase = computed(() => (isCompanySite.value ? '/' : '/founder'));

/**
 * Sections scroll on the page that owns them and jump back to that page from
 * anywhere else. Both the home page and the About page own their own ids, so
 * each anchors to itself.
 */
function sectionHref(id: string): string {
    if (isHome.value || isAboutRoute.value) {
        return `#${id}`;
    }

    return `${sectionBase.value}#${id}`;
}

/** The destination of a whole-page link, or null when it is the current page. */
function pageLinkHref(id: string): string | null {
    switch (id) {
        case 'about':
            return isAboutRoute.value ? null : '/about';
        case 'archive':
            return isProjectsRoute.value ? null : '/projects';
        case 'founder':
            return isFounderRoute.value ? null : '/founder';
        case 'studio':
            return isCompanySite.value ? null : '/';
        default:
            return null;
    }
}

/** Whether a whole-page link points at the page the visitor is already on. */
function isCurrentPageLink(id: string): boolean {
    switch (id) {
        case 'about':
            return isAboutRoute.value;
        case 'archive':
            return isProjectsRoute.value;
        case 'founder':
            return isFounderRoute.value;
        default:
            return false;
    }
}

function closeMobile() {
    mobileOpen.value = false;
}
</script>

<template>
    <MotionConfig reduced-motion="user">
        <div class="min-h-screen bg-background text-foreground">
            <!-- Water intro: the logo floating on water before the home page -->
            <WaterIntro
                v-if="introActive"
                :logo-url="brandLogoUrl"
                :name="owner?.name ?? company?.name ?? null"
                @exit="onIntroExit"
                @done="onIntroDone"
            />

            <a
                href="#main"
                class="sr-only focus:not-sr-only focus:fixed focus:top-4 focus:left-4 focus:z-100 focus:rounded-md focus:bg-background focus:px-4 focus:py-2 focus:text-foreground focus:ring-2 focus:ring-ring"
            >
                Skip to content
            </a>

            <!-- Laravel-style clean nav -->
            <header
                :class="
                    cn(
                        'sticky top-0 z-50 transition-all duration-200',
                        scrolled
                            ? 'border-b border-border bg-background/95 backdrop-blur-sm'
                            : 'border-transparent bg-transparent',
                    )
                "
            >
                <div
                    class="mx-auto flex h-16 max-w-6xl items-center gap-4 px-4 sm:px-6"
                >
                    <!-- Logo -->
                    <Link
                        :href="sectionBase"
                        class="group flex items-center gap-2.5"
                        :aria-label="
                            (isCompanySite ? owner?.name : 'Personal portfolio') ??
                            'Home'
                        "
                    >
                        <span
                            v-if="brandLogoUrl"
                            class="flex h-12 items-center rounded-lg bg-white px-2.5 py-1 shadow-sm ring-1 ring-border/60"
                        >
                            <img
                                :src="brandLogoUrl"
                                :alt="owner?.name ?? 'Home'"
                                class="h-10 w-auto max-w-44 object-contain sm:max-w-64"
                            />
                        </span>
                        <template v-else>
                            <span
                                class="grid size-9 place-items-center rounded-lg bg-foreground font-display text-sm font-bold text-background transition-opacity duration-150 group-hover:opacity-90"
                            >
                                {{ initials || '{ }' }}
                            </span>
                            <span
                                class="hidden text-sm font-semibold tracking-tight sm:inline"
                            >
                                {{ owner?.name ?? 'portfolio' }}
                            </span>
                        </template>
                    </Link>

                    <!-- Section links, then a divider, then whole-page links -->
                    <nav
                        class="ml-auto hidden items-center gap-1 md:flex"
                        aria-label="Primary"
                    >
                        <a
                            v-for="section in sections"
                            :key="section.id"
                            :href="sectionHref(section.id)"
                            :data-active="
                                activeSection === section.id ? 'true' : undefined
                            "
                            :aria-current="
                                activeSection === section.id ? 'true' : undefined
                            "
                            class="rounded-lg px-3 py-1.5 text-sm font-medium text-muted-foreground transition-colors duration-150 hover:text-foreground data-[active=true]:text-foreground"
                        >
                            {{ section.label }}
                        </a>
                        <span
                            v-if="pageLinks.length"
                            aria-hidden="true"
                            class="mx-1 h-4 w-px shrink-0 rounded-full bg-border"
                        />
                        <Link
                            v-for="link in pageLinks"
                            :key="link.id"
                            :href="pageLinkHref(link.id) ?? '/'"
                            :data-active="
                                isCurrentPageLink(link.id) ? 'true' : undefined
                            "
                            :aria-current="
                                isCurrentPageLink(link.id) ? 'page' : undefined
                            "
                            class="rounded-lg px-3 py-1.5 text-sm font-medium text-muted-foreground transition-colors duration-150 hover:text-foreground data-[active=true]:text-foreground"
                        >
                            {{ link.label }}
                        </Link>
                    </nav>

                    <!-- Actions -->
                    <div class="ml-auto flex items-center gap-2 md:ml-0">
                        <ThemeToggle class="hidden sm:inline-flex" />
                        <Button
                            v-if="profile?.resume_url"
                            as-child
                            size="sm"
                            class="btn-laravel-primary hidden sm:inline-flex"
                        >
                            <a href="/resume" target="_blank" rel="noopener"
                                >Resume</a
                            >
                        </Button>
                        <Button
                            variant="ghost"
                            size="icon"
                            class="md:hidden"
                            aria-label="Open menu"
                            @click="mobileOpen = true"
                        >
                            <Menu class="size-5" />
                        </Button>
                    </div>
                </div>

                <ScrollProgressBar />
            </header>

            <main id="main" class="relative">
                <!--
                    Plain page wrapper. Motion-based page transitions unmounted
                    the outgoing route unsafely during Inertia visits and left
                    stale content behind, so page changes use a CSS entrance
                    only (replaying as the key changes on navigation).
                    Descendant components may still animate themselves.
                -->
                <!--
                    The entrance is held back only while the water is still
                    covering the page. It is never used to hide content, so a
                    failure here costs an animation, not the page.
                -->
                <div
                    :key="page.url"
                    :class="introActive && !introDraining ? '' : 'page-enter'"
                >
                    <slot />
                </div>

                <FooterSection />
            </main>

            <!-- Mobile sheet -->
            <Sheet v-model:open="mobileOpen">
                <SheetContent
                    side="right"
                    class="w-72 border-border bg-background"
                >
                    <SheetTitle
                        class="flex items-center justify-between px-4 pt-4 text-sm font-semibold"
                    >
                        <span>{{
                            owner?.name?.split(' ')[0] ?? 'Menu'
                        }}</span>
                        <button
                            type="button"
                            class="rounded-lg p-1 text-muted-foreground transition-colors hover:bg-muted hover:text-foreground"
                            aria-label="Close menu"
                            @click="closeMobile"
                        >
                            <X class="size-4" />
                        </button>
                    </SheetTitle>
                    <nav
                        class="mt-4 flex flex-col gap-1 px-2"
                        aria-label="Mobile"
                    >
                        <a
                            v-for="section in sections"
                            :key="section.id"
                            :href="sectionHref(section.id)"
                            :data-active="
                                activeSection === section.id ? 'true' : undefined
                            "
                            :aria-current="
                                activeSection === section.id ? 'true' : undefined
                            "
                            class="rounded-lg px-4 py-2.5 text-sm text-muted-foreground transition-colors hover:bg-muted hover:text-foreground data-[active=true]:font-medium data-[active=true]:text-foreground"
                            @click="closeMobile"
                        >
                            {{ section.label }}
                        </a>
                        <span
                            v-if="pageLinks.length"
                            aria-hidden="true"
                            class="my-2 h-px w-full rounded-full bg-border"
                        />
                        <Link
                            v-for="link in pageLinks"
                            :key="link.id"
                            :href="pageLinkHref(link.id) ?? '/'"
                            :data-active="
                                isCurrentPageLink(link.id) ? 'true' : undefined
                            "
                            :aria-current="
                                isCurrentPageLink(link.id) ? 'page' : undefined
                            "
                            class="rounded-lg px-4 py-2.5 text-sm text-muted-foreground transition-colors hover:bg-muted hover:text-foreground data-[active=true]:font-medium data-[active=true]:text-foreground"
                            @click="closeMobile"
                        >
                            {{ link.label }}
                        </Link>
                    </nav>
                    <div
                        class="mt-6 flex items-center justify-between gap-2 border-t border-border px-4 pt-4"
                    >
                        <ThemeToggle />
                        <a
                            v-if="profile?.resume_url"
                            href="/resume"
                            target="_blank"
                            rel="noopener"
                            class="text-sm font-medium text-brand hover:underline"
                        >
                            resume.pdf
                        </a>
                    </div>
                </SheetContent>
            </Sheet>

            <Toaster position="bottom-right" rich-colors />
        </div>
    </MotionConfig>
</template>
