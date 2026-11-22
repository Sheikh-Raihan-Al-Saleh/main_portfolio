<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ArrowLeft, ArrowUpRight } from '@lucide/vue';
import { motion } from 'motion-v';
import { computed } from 'vue';
import HeroDevicesCarousel from '@/components/landing/HeroDevicesCarousel.vue';
import SectionList from '@/components/landing/SectionList.vue';
import SmartImage from '@/components/media/SmartImage.vue';
import LiquidBlob from '@/components/site/LiquidBlob.vue';
import { useScrollSpy } from '@/composables/useScrollSpy';
import { useSite } from '@/composables/useSite';
import { EASE_OUT } from '@/lib/motion';
import { yearRange } from '@/lib/text';
import { cn } from '@/lib/utils';
import { founder as founderRoute } from '@/routes';
import projectRoutes from '@/routes/projects';
import type { PublicLandingShowProps } from '@/types';

/**
 * A project's marketing case-study page (/p/{slug}).
 */

const props = defineProps<PublicLandingShowProps>();

const { brand } = useSite();

const isPersonal = computed(() => props.project.owner_type === 'founder');
const title = computed(
    () =>
        props.landingPage.seo_title ??
        `${props.project.title} — ${brand.value.name}`,
);
const headline = computed(
    () => props.landingPage.headline ?? props.project.title,
);
const subheadline = computed(
    () => props.landingPage.subheadline ?? props.project.summary,
);
const heroImages = computed(() =>
    props.landingPage.hero_media.filter((url): url is string => Boolean(url)),
);

/** A page may set its own accent, replacing the site's. */
const accentStyle = computed(() =>
    props.landingPage.accent_from
        ? {
              '--brand': props.landingPage.accent_from,
              '--scene-1': props.landingPage.accent_from,
              '--scene-2':
                  props.landingPage.accent_to ?? props.landingPage.accent_from,
          }
        : {},
);

const navSections = computed(() =>
    props.sections
        .filter((section) => Boolean(section.heading))
        .map((section) => ({
            id: `section-${section.id}`,
            label: section.heading as string,
        })),
);

const activeSection = useScrollSpy(navSections.value.map((entry) => entry.id));

const specs = computed(() =>
    [
        { label: 'Role', value: props.project.role },
        {
            label: 'Timeline',
            value:
                props.project.started_at || props.project.completed_at
                    ? yearRange(
                          props.project.started_at,
                          props.project.completed_at,
                          'Ongoing',
                      )
                    : null,
        },
        {
            label: 'Type',
            value: isPersonal.value
                ? 'Personal project'
                : `Built by ${brand.value.name}`,
        },
    ].filter((spec) => spec.value),
);

const backHref = computed(() =>
    isPersonal.value
        ? `${founderRoute().url}#projects`
        : projectRoutes.index().url,
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
        <section class="relative overflow-hidden">
            <LiquidBlob
                class="pointer-events-none absolute -top-32 -right-32 size-[40rem] opacity-80"
            />

            <div class="container-site relative pt-8 pb-16 sm:pt-12">
                <Link
                    :href="backHref"
                    class="meta inline-flex items-center gap-2 hover:text-ink"
                >
                    <ArrowLeft class="size-4" aria-hidden="true" /> Back to work
                </Link>

                <motion.div
                    class="mt-10"
                    :initial="{ opacity: 0, y: 32 }"
                    :animate="{ opacity: 1, y: 0 }"
                    :transition="{ duration: 0.9, ease: EASE_OUT }"
                >
                    <p class="eyebrow">
                        {{ landingPage.eyebrow || 'Case study' }}
                    </p>
                    <h1 class="display display-xl mt-6 max-w-[15ch]">
                        {{ headline }}
                    </h1>
                </motion.div>

                <div class="mt-12 grid gap-10 lg:grid-cols-12">
                    <motion.div
                        class="lg:col-span-7"
                        :initial="{ opacity: 0, y: 24 }"
                        :animate="{ opacity: 1, y: 0 }"
                        :transition="{
                            duration: 0.9,
                            ease: EASE_OUT,
                            delay: 0.15,
                        }"
                    >
                        <p v-if="subheadline" class="lead">{{ subheadline }}</p>
                        <div class="mt-10 flex flex-wrap gap-3">
                            <a
                                v-if="
                                    landingPage.primary_cta_label &&
                                    landingPage.primary_cta_url
                                "
                                :href="landingPage.primary_cta_url"
                                class="btn btn-ink"
                            >
                                {{ landingPage.primary_cta_label }}
                                <ArrowUpRight
                                    class="btn-arrow size-4"
                                    aria-hidden="true"
                                />
                            </a>
                            <a
                                v-if="
                                    landingPage.secondary_cta_label &&
                                    landingPage.secondary_cta_url
                                "
                                :href="landingPage.secondary_cta_url"
                                class="btn btn-outline"
                            >
                                {{ landingPage.secondary_cta_label }}
                            </a>
                        </div>
                    </motion.div>

                    <dl class="lg:col-span-4 lg:col-start-9">
                        <div
                            v-for="spec in specs"
                            :key="spec.label"
                            class="rule flex justify-between gap-6 py-3"
                        >
                            <dt class="meta">{{ spec.label }}</dt>
                            <dd class="text-right font-medium">
                                {{ spec.value }}
                            </dd>
                        </div>
                    </dl>
                </div>
            </div>

            <div class="container-site relative pb-10">
                <HeroDevicesCarousel
                    v-if="heroImages.length"
                    :images="heroImages"
                />
                <SmartImage
                    v-else-if="project.cover_image_url"
                    :src="project.cover_image_url"
                    :alt="project.title"
                    ratio="16/9"
                    eager
                    class="rounded-[1.5rem]"
                />
            </div>
        </section>

        <!-- In-page navigation, once the page has named sections. -->
        <nav
            v-if="navSections.length > 2"
            aria-label="Case study sections"
            class="sticky top-20 z-30 border-y border-line bg-paper/90 backdrop-blur sm:top-24"
        >
            <ul
                class="container-site mask-fade-x flex gap-1 overflow-x-auto py-3"
            >
                <li
                    v-for="entry in navSections"
                    :key="entry.id"
                    class="shrink-0"
                >
                    <a
                        :href="`#${entry.id}`"
                        :class="
                            cn(
                                'tag transition-colors hover:border-ink',
                                activeSection === entry.id && 'tag-active',
                            )
                        "
                    >
                        {{ entry.label }}
                    </a>
                </li>
            </ul>
        </nav>

        <SectionList
            :sections="sections"
            :anchor-for="(section) => `section-${section.id}`"
        />
    </div>
</template>
