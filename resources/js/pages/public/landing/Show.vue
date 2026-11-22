<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ArrowLeft, ArrowUpRight } from '@lucide/vue';
import { computed } from 'vue';
import HeroDevicesCarousel from '@/components/landing/HeroDevicesCarousel.vue';
import SectionList from '@/components/landing/SectionList.vue';
import HeroHeadline from '@/components/site/HeroHeadline.vue';
import LiquidBlob from '@/components/site/LiquidBlob.vue';
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

/** The facts the spec sheet beside the headline can state, blanks dropped. */
const specs = computed(() => {
    const start = props.project.started_at?.slice(0, 4) ?? null;
    const end = props.project.completed_at?.slice(0, 4) ?? null;
    const timeline =
        start && end && start !== end ? `${start} — ${end}` : (end ?? start);

    return [
        { label: 'Role', value: props.project.role },
        { label: 'Timeline', value: timeline },
        { label: 'Type', value: isPersonal.value ? 'Personal' : 'Client work' },
    ].filter((spec): spec is { label: string; value: string } =>
        Boolean(spec.value),
    );
});

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
        <section class="relative overflow-hidden pt-36 pb-10 lg:pt-44">
            <!-- The project's liquid shape, in the face's accent -->
            <LiquidBlob
                class="absolute top-24 right-[-15%] h-[34rem] w-[80%] sm:right-[-6%] lg:w-[50%]"
            />

            <div class="site-container relative">
                <Link
                    :href="backHref"
                    class="inline-flex items-center gap-2 font-mono text-xs tracking-wider text-muted-foreground uppercase hover:text-foreground"
                >
                    <ArrowLeft class="size-4" />
                    {{ isPersonal ? 'Back to portfolio' : 'All projects' }}
                </Link>

                <div
                    class="mt-10 grid items-end gap-12 lg:grid-cols-12 lg:gap-10"
                >
                    <div class="flex flex-col items-start gap-7 lg:col-span-8">
                        <span v-if="landingPage.eyebrow" class="ui-eyebrow">{{
                            landingPage.eyebrow
                        }}</span>

                        <HeroHeadline
                            :text="headline"
                            :highlight="2"
                            :delay="80"
                            class="ui-h1"
                        />

                        <p
                            v-if="subheadline"
                            class="max-w-2xl text-lg leading-relaxed text-pretty text-muted-foreground sm:text-xl"
                        >
                            {{ subheadline }}
                        </p>

                        <div class="flex flex-wrap gap-3">
                            <a
                                v-if="landingPage.primary_cta_url"
                                :href="landingPage.primary_cta_url"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="ui-btn ui-btn-primary"
                            >
                                {{
                                    landingPage.primary_cta_label ?? 'View live'
                                }}
                                <ArrowUpRight class="size-4" />
                            </a>
                            <a
                                v-if="landingPage.secondary_cta_url"
                                :href="landingPage.secondary_cta_url"
                                class="ui-btn ui-btn-secondary"
                            >
                                {{
                                    landingPage.secondary_cta_label ??
                                    'Get in touch'
                                }}
                            </a>
                            <a
                                v-if="
                                    !landingPage.primary_cta_url &&
                                    !landingPage.secondary_cta_url
                                "
                                :href="
                                    isPersonal
                                        ? '/founder#contact'
                                        : '/#contact'
                                "
                                class="ui-btn ui-btn-primary"
                            >
                                Start a project
                                <ArrowUpRight class="size-4" />
                            </a>
                        </div>
                    </div>

                    <!-- Project spec sheet -->
                    <dl
                        v-if="specs.length || project.tech_stack?.length"
                        class="ui-card grid gap-5 p-6 font-mono text-sm lg:col-span-4"
                    >
                        <div
                            v-for="spec in specs"
                            :key="spec.label"
                            class="flex items-baseline justify-between gap-4 border-b border-border pb-4"
                        >
                            <dt
                                class="text-[11px] tracking-wider text-muted-foreground uppercase"
                            >
                                {{ spec.label }}
                            </dt>
                            <dd class="text-right font-semibold">
                                {{ spec.value }}
                            </dd>
                        </div>
                        <div v-if="project.tech_stack?.length">
                            <dt
                                class="text-[11px] tracking-wider text-muted-foreground uppercase"
                            >
                                Stack
                            </dt>
                            <dd class="mt-3 flex flex-wrap gap-1.5">
                                <span
                                    v-for="tech in project.tech_stack.slice(
                                        0,
                                        10,
                                    )"
                                    :key="tech"
                                    class="ui-pill"
                                    >{{ tech }}</span
                                >
                            </dd>
                        </div>
                    </dl>
                </div>

                <!-- The device stage -->
                <div class="relative mt-16 sm:mt-20">
                    <div
                        class="absolute inset-x-[10%] top-1/4 bottom-0 rounded-full bg-gradient-to-r from-[var(--scene-1)] via-[var(--scene-3)] to-[var(--scene-2)] opacity-20 blur-3xl"
                        aria-hidden="true"
                    />
                    <div class="relative">
                        <HeroDevicesCarousel
                            :images="landingPage.hero_media_urls ?? []"
                        />
                    </div>
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
