<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ArrowLeft, ArrowRight } from '@lucide/vue';
import { motion, useTransform } from 'motion-v';
import { computed, ref } from 'vue';
import { resolveSection } from '@/components/landing/sectionRegistry';
import HeroDevicesCarousel from '@/components/landing/HeroDevicesCarousel.vue';
import TechBadge from '@/components/portfolio/TechBadge.vue';
import { Button } from '@/components/ui/button';
import { useScrollProgress } from '@/composables/useScrollProgress';
import { useScrollSpy } from '@/composables/useScrollSpy';
import { fadeUp, stagger } from '@/lib/motion';
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

const title = computed(
    () =>
        props.landingPage.seo_title ??
        `${props.project.title} — ${props.profile.name}`,
);

const headline = computed(
    () => props.landingPage.headline ?? props.project.title,
);

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

function anchorFor(section: LandingSection): string {
    return `section-${section.id}`;
}

const heroRef = ref<HTMLElement | null>(null);
const { progress } = useScrollProgress({
    target: heroRef,
    offset: ['start start', 'end start'],
});
const heroY = useTransform(progress, [0, 1], [0, 100]);
const heroOpacity = useTransform(progress, [0, 0.85], [1, 0]);
const heroScale = useTransform(progress, [0, 1], [1, 0.97]);
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
        <!-- Hero — Laravel-style clean hero with 3D scroll -->
        <section ref="heroRef" class="bg-noise relative overflow-hidden">
            <div class="corner-dot corner-dot-tl" aria-hidden="true" />
            <div class="corner-dot corner-dot-tr" aria-hidden="true" />

            <div
                class="pointer-events-none absolute inset-0"
                aria-hidden="true"
            >
                <div class="bg-red-glow absolute inset-0" />
                <div
                    class="absolute inset-0 bg-[linear-gradient(to_right,var(--border)_1px,transparent_1px),linear-gradient(to_bottom,var(--border)_1px,transparent_1px)] opacity-[0.03] dark:opacity-[0.05]"
                    style="background-size: 80px 80px"
                />
            </div>

            <motion.div
                :style="{ y: heroY, opacity: heroOpacity, scale: heroScale }"
                class="container-laravel relative py-24 sm:py-32"
            >
                <Link
                    href="/projects"
                    class="mb-12 inline-flex items-center gap-1.5 font-mono text-sm text-muted-foreground transition-colors duration-200 hover:text-foreground"
                >
                    <ArrowLeft class="size-4" />
                    All projects
                </Link>

                <motion.div
                    :variants="stagger(0.1)"
                    initial="hidden"
                    animate="visible"
                    class="grid items-center gap-14 lg:grid-cols-2"
                >
                    <div>
                        <motion.p
                            v-if="landingPage.eyebrow"
                            :variants="fadeUp"
                            class="mb-5 font-mono text-xs text-brand uppercase"
                        >
                            {{ landingPage.eyebrow }}
                        </motion.p>

                        <motion.h1
                            :variants="fadeUp"
                            class="font-display text-4xl font-bold tracking-tight text-balance sm:text-5xl lg:text-6xl"
                        >
                            {{ headline }}
                        </motion.h1>

                        <motion.p
                            v-if="landingPage.subheadline"
                            :variants="fadeUp"
                            class="mt-6 max-w-xl text-lg text-pretty text-muted-foreground"
                        >
                            {{ landingPage.subheadline }}
                        </motion.p>

                        <motion.div
                            v-if="project.tech_stack?.length"
                            :variants="fadeUp"
                            class="mt-6 flex flex-wrap gap-1.5"
                        >
                            <TechBadge
                                v-for="tech in project.tech_stack.slice(0, 8)"
                                :key="tech"
                                :label="tech"
                            />
                        </motion.div>

                        <motion.div
                            :variants="fadeUp"
                            class="mt-8 flex flex-wrap items-center gap-3"
                        >
                            <Button
                                v-if="landingPage.primary_cta_url"
                                as-child
                                size="lg"
                                class="btn-laravel-primary"
                            >
                                <a
                                    :href="landingPage.primary_cta_url"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                >
                                    {{
                                        landingPage.primary_cta_label ??
                                        'View live'
                                    }}
                                    <ArrowRight class="size-4" />
                                </a>
                            </Button>

                            <Button
                                v-if="landingPage.secondary_cta_url"
                                as-child
                                size="lg"
                                variant="outline"
                                class="btn-laravel"
                            >
                                <a :href="landingPage.secondary_cta_url">
                                    {{
                                        landingPage.secondary_cta_label ??
                                        'Get in touch'
                                    }}
                                </a>
                            </Button>
                        </motion.div>
                    </div>

                    <motion.div
                        v-if="landingPage.hero_media_urls && landingPage.hero_media_urls.length > 0"
                        :variants="fadeUp"
                    >
                        <HeroDevicesCarousel
                            :images="landingPage.hero_media_urls"
                        />
                    </motion.div>
                </motion.div>
            </motion.div>
        </section>

        <!-- In-page navigation -->
        <nav
            v-if="navSections.length > 1"
            class="sticky top-16 z-40 border-y border-border bg-background/95 backdrop-blur-sm"
            aria-label="Sections on this page"
        >
            <div
                class="mask-fade-x mx-auto max-w-6xl overflow-x-auto px-4 sm:px-6"
            >
                <div class="flex gap-1 py-2">
                    <a
                        v-for="entry in navSections"
                        :key="entry.id"
                        :href="`#${entry.id}`"
                        :class="
                            cn(
                                'rounded-lg px-3 py-1.5 text-sm whitespace-nowrap transition-colors duration-150',
                                activeSection === entry.id
                                    ? 'bg-foreground text-background'
                                    : 'text-muted-foreground hover:text-foreground',
                            )
                        "
                    >
                        {{ entry.label }}
                    </a>
                </div>
            </div>
        </nav>

        <!-- Composed sections -->
        <template v-for="section in sections" :key="section.id">
            <component
                :is="resolveSection(section.type)"
                v-if="resolveSection(section.type)"
                :section="section"
                :anchor="anchorFor(section)"
            />
        </template>
    </div>
</template>
