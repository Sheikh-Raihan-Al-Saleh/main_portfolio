<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ArrowRight, Quote } from '@lucide/vue';
import { computed } from 'vue';
import Reveal from '@/components/site/Reveal.vue';
import SectionHeader from '@/components/site/SectionHeader.vue';
import StudioFounderCard from '@/components/studio/StudioFounderCard.vue';
import { useStudioContent } from '@/composables/useStudioContent';
import type { Company, Profile } from '@/types';

/**
 * About the studio, on the home page: the bio and mission beside the founder
 * card. The full story lives on /about.
 */
type Props = {
    company: Company;
    profile: Profile;
};

const props = defineProps<Props>();

const content = useStudioContent(props.company, 'about');

const paragraphs = computed(() =>
    (props.company.bio ?? '')
        .split(/\n\s*\n/)
        .map((paragraph) => paragraph.trim())
        .filter(Boolean),
);
</script>

<template>
    <section id="about" class="site-section scroll-mt-24 bg-card/60">
        <div
            class="site-container grid items-start gap-12 lg:grid-cols-12 lg:gap-16"
        >
            <div class="flex flex-col gap-6 lg:col-span-6">
                <SectionHeader
                    align="left"
                    :eyebrow="content.eyebrow"
                    :title="content.title"
                    :highlight="content.highlight"
                    :description="company.tagline ?? ''"
                />

                <Reveal :delay="0.1" class="flex flex-col gap-4">
                    <p
                        v-for="paragraph in paragraphs.slice(0, 2)"
                        :key="paragraph.slice(0, 24)"
                        class="leading-relaxed text-pretty text-muted-foreground"
                    >
                        {{ paragraph }}
                    </p>
                </Reveal>

                <Reveal v-if="company.mission" :delay="0.15">
                    <blockquote class="ui-card flex gap-4 p-6">
                        <span class="ui-icon-tile shrink-0"
                            ><Quote class="size-5"
                        /></span>
                        <div>
                            <p class="text-sm font-medium text-brand">
                                {{ content.mission_label }}
                            </p>
                            <p class="mt-1 text-lg leading-snug font-semibold">
                                {{ company.mission }}
                            </p>
                        </div>
                    </blockquote>
                </Reveal>

                <Reveal :delay="0.2">
                    <Link
                        href="/about"
                        class="inline-flex items-center gap-2 font-semibold text-brand"
                    >
                        <span class="ui-link">{{ content.more_label }}</span>
                        <ArrowRight class="size-4" />
                    </Link>
                </Reveal>
            </div>

            <Reveal :delay="0.1" class="lg:col-span-6">
                <StudioFounderCard :profile="profile" />
            </Reveal>
        </div>
    </section>
</template>
