<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ArrowLeft, ArrowUpRight } from '@lucide/vue';
import { computed } from 'vue';
import HeroDevicesCarousel from '@/components/landing/HeroDevicesCarousel.vue';
import SectionList from '@/components/landing/SectionList.vue';
import CircuitBackground from '@/components/site/CircuitBackground.vue';
import { useScrollSpy } from '@/composables/useScrollSpy';
import { cn } from '@/lib/utils';
import type {
    LandingSection,
    Profile,
    Project,
    ProjectLandingPage,
} from '@/types';

type Props = {
    profile: Profile;
    project: Project;
    landingPage: ProjectLandingPage;
    sections: LandingSection[];
};

const props = defineProps<Props>();

/**
 * A project's marketing page. It belongs to whichever face owns the project
 * (the layout resolves that from `project.context`), so the hero is set in
 * that face's type: the studio's display face or the founder's monospace.
 */
const isPersonal = computed(() => props.project.context === 'personal');

const title = computed(
    () =>
        props.landingPage.seo_title ??
        `${props.project.title} — ${props.profile.name}`,
);

const headline = computed(
    () => props.landingPage.headline ?? props.project.title,
);

const subheadline = computed(
    () => props.landingPage.subheadline ?? props.project.summary ?? null,
);

/** The admin may give a page its own accent; it replaces the face's text accent. */
const accentStyle = computed(() => {
    const style: Record<string, string> = {};

    if (props.landingPage.accent_from) {
        style['--brand'] = props.landingPage.accent_from;
    }

    if (props.landingPage.accent_to) {
        style['--brand-accent'] = props.landingPage.accent_to;
    }

    return style;
});

const navSections = computed(() =>
    props.sections
        .filter((section) => Boolean(section.heading))
        .map((section) => ({
            id: `section-${section.id}`,
            label: section.heading as string,
        })),
);

const activeSection = useScrollSpy(navSections.value.map((entry) => entry.id));

function anchorFor(section: LandingSection) {
    return `section-${section.id}`;
}

const backHref = computed(() =>
    isPersonal.value ? '/founder#projects' : '/projects',
);
</script>

<template>
    <Head :title="title">
        <meta
            v-if="landingPage.seo_description"
            name="description"
            :content="landingPage.seo_description"
        />
        <meta property="og:title" :content="title" />
        <meta
            v-if="landingPage.seo_description"
            property="og:description"
            :content="landingPage.seo_description"
        />
        <meta
            v-if="landingPage.og_image_url"
            property="og:image"
            :content="landingPage.og_image_url"
        />
        <meta name="twitter:card" content="summary_large_image" />
    </Head>

    <div :style="accentStyle">
        <!-- Hero -->
        <section class="relative overflow-hidden pt-32 lg:pt-40">
            <div
                class="pointer-events-none absolute inset-x-0 top-0 h-[40rem]"
                aria-hidden="true"
            >
                <CircuitBackground />
                <div class="ui-blob ui-blob-a -top-32 left-1/4 size-[30rem]" />
                <div class="ui-blob ui-blob-b -top-10 right-1/4 size-[24rem]" />
            </div>

            <div
                class="site-container relative flex flex-col items-center text-center"
            >
                <Link
                    :href="backHref"
                    class="inline-flex items-center gap-2 text-sm font-medium text-muted-foreground hover:text-foreground"
                >
                    <ArrowLeft class="size-4" />
                    {{ isPersonal ? 'Back to portfolio' : 'All projects' }}
                </Link>

                <span v-if="landingPage.eyebrow" class="ui-eyebrow mt-8">{{
                    landingPage.eyebrow
                }}</span>

                <h1 class="ui-display ui-h1 mt-5 max-w-4xl">{{ headline }}</h1>

                <p
                    v-if="subheadline"
                    class="mt-6 max-w-2xl text-lg leading-relaxed text-pretty text-muted-foreground sm:text-xl"
                >
                    {{ subheadline }}
                </p>

                <ul
                    v-if="project.tech_stack?.length"
                    class="mt-6 flex flex-wrap justify-center gap-1.5"
                >
                    <li
                        v-for="tech in project.tech_stack.slice(0, 8)"
                        :key="tech"
                        class="ui-pill"
                    >
                        {{ tech }}
                    </li>
                </ul>

                <div class="mt-8 flex flex-wrap justify-center gap-3">
                    <a
                        v-if="landingPage.primary_cta_url"
                        :href="landingPage.primary_cta_url"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="ui-btn ui-btn-primary"
                    >
                        {{ landingPage.primary_cta_label ?? 'View live' }}
                        <ArrowUpRight class="size-4" />
                    </a>
                    <a
                        v-if="landingPage.secondary_cta_url"
                        :href="landingPage.secondary_cta_url"
                        class="ui-btn ui-btn-secondary"
                    >
                        {{ landingPage.secondary_cta_label ?? 'Get in touch' }}
                    </a>
                    <a
                        v-if="
                            !landingPage.primary_cta_url &&
                            !landingPage.secondary_cta_url
                        "
                        :href="isPersonal ? '/founder#contact' : '/#contact'"
                        class="ui-btn ui-btn-primary"
                    >
                        Start a project
                        <ArrowUpRight class="size-4" />
                    </a>
                </div>

                <div class="mt-14 w-full sm:mt-20">
                    <HeroDevicesCarousel
                        :images="landingPage.hero_media_urls ?? []"
                    />
                </div>
            </div>
        </section>

        <!-- In-page navigation -->
        <nav
            v-if="navSections.length > 1"
            class="sticky top-20 z-30 mt-16 lg:top-24"
            aria-label="Sections on this page"
        >
            <div class="site-container">
                <div
                    class="ui-glass mask-fade-x flex gap-1.5 overflow-x-auto rounded-full p-1.5 shadow-[var(--shadow-soft)]"
                >
                    <a
                        v-for="entry in navSections"
                        :key="entry.id"
                        :href="`#${entry.id}`"
                        :class="
                            cn(
                                'shrink-0 rounded-full px-4 py-2 text-sm font-medium whitespace-nowrap transition-colors',
                                activeSection === entry.id
                                    ? 'bg-signal text-signal-foreground'
                                    : 'text-muted-foreground hover:text-foreground',
                            )
                        "
                    >
                        {{ entry.label }}
                    </a>
                </div>
            </div>
        </nav>

        <SectionList :sections="sections" :anchor-for="anchorFor" />
    </div>
</template>
