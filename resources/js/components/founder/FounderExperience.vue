<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import {
    ArrowUpRight,
    Briefcase,
    CircleCheck,
    GraduationCap,
} from '@lucide/vue';
import { computed } from 'vue';
import Reveal from '@/components/site/Reveal.vue';
import SectionHeader from '@/components/site/SectionHeader.vue';
import { contentConfig } from '@/lib/content';
import type { Education, Experience, Profile } from '@/types';

/**
 * Work history as a vertical timeline of cards, the current role marked live,
 * followed by education.
 */
type Props = {
    experiences: Experience[];
    educations: Education[];
};

defineProps<Props>();

const page = usePage<{ profile: Profile | null }>();
const copy = computed(
    () => contentConfig(page.props.profile).sections.experience,
);

function formatDate(value: string | null | undefined): string {
    if (!value) {
        return 'Present';
    }

    return new Date(value).toLocaleDateString('en', {
        month: 'short',
        year: 'numeric',
    });
}
</script>

<template>
    <section id="experience" class="site-section scroll-mt-24">
        <div class="site-container">
            <SectionHeader
                :eyebrow="copy.eyebrow"
                :title="copy.title"
                :highlight="copy.highlight"
                :description="copy.description"
            />

            <ol class="relative mx-auto mt-14 max-w-4xl">
                <span
                    class="absolute top-2 bottom-2 left-5 w-px bg-gradient-to-b from-[var(--grad-from)] via-border to-transparent sm:left-6"
                    aria-hidden="true"
                />
                <li
                    v-for="(job, i) in experiences"
                    :key="job.id"
                    class="relative pb-8 pl-14 last:pb-0 sm:pl-18"
                >
                    <span
                        class="absolute top-6 left-5 grid size-3 -translate-x-1/2 place-items-center rounded-full ring-4 ring-background sm:left-6"
                        :class="
                            !job.end_date
                                ? 'bg-gradient-to-br from-[var(--grad-from)] to-[var(--grad-to)]'
                                : 'bg-border'
                        "
                        aria-hidden="true"
                    />
                    <Reveal :delay="i * 0.05">
                        <article class="ui-card ui-card-hover p-6 sm:p-7">
                            <div
                                class="flex flex-wrap items-start justify-between gap-3"
                            >
                                <div class="flex items-center gap-3">
                                    <img
                                        v-if="job.logo_url"
                                        :src="job.logo_url"
                                        :alt="job.company"
                                        class="size-11 rounded-xl border border-border bg-white object-contain p-1.5"
                                    />
                                    <span v-else class="ui-icon-tile"
                                        ><Briefcase class="size-5"
                                    /></span>
                                    <div>
                                        <h3 class="ui-h3">{{ job.role }}</h3>
                                        <p
                                            class="text-sm text-muted-foreground"
                                        >
                                            <a
                                                v-if="job.company_url"
                                                :href="job.company_url"
                                                target="_blank"
                                                rel="noopener noreferrer"
                                                class="inline-flex items-center gap-0.5 font-medium text-brand hover:underline"
                                                >{{ job.company
                                                }}<ArrowUpRight class="size-3"
                                            /></a>
                                            <span
                                                v-else
                                                class="font-medium text-foreground"
                                                >{{ job.company }}</span
                                            >
                                            <template
                                                v-if="
                                                    job.employment_type ||
                                                    job.location
                                                "
                                            >
                                                ·
                                                {{
                                                    [
                                                        job.employment_type,
                                                        job.location,
                                                    ]
                                                        .filter(Boolean)
                                                        .join(' · ')
                                                }}
                                            </template>
                                        </p>
                                    </div>
                                </div>
                                <span
                                    class="ui-pill"
                                    :class="
                                        !job.end_date
                                            ? 'border-transparent bg-brand-subtle text-brand'
                                            : ''
                                    "
                                >
                                    {{ formatDate(job.start_date) }} —
                                    {{ formatDate(job.end_date) }}
                                </span>
                            </div>
                            <p
                                v-if="job.description"
                                class="mt-4 leading-relaxed text-pretty text-muted-foreground"
                            >
                                {{ job.description }}
                            </p>
                            <ul
                                v-if="job.highlights?.length"
                                class="mt-4 grid gap-2 text-sm"
                            >
                                <li
                                    v-for="item in job.highlights"
                                    :key="item"
                                    class="flex gap-2.5"
                                >
                                    <CircleCheck
                                        class="mt-0.5 size-4 shrink-0 text-brand"
                                    />
                                    <span>{{ item }}</span>
                                </li>
                            </ul>
                        </article>
                    </Reveal>
                </li>
            </ol>

            <template v-if="educations.length">
                <h3 class="ui-display mt-20 text-center text-2xl">Education</h3>
                <div class="mx-auto mt-8 grid max-w-4xl gap-5 md:grid-cols-2">
                    <Reveal
                        v-for="(item, i) in educations"
                        :key="item.id"
                        :delay="i * 0.08"
                    >
                        <article
                            class="ui-card ui-card-hover flex h-full gap-4 p-6"
                        >
                            <span class="ui-icon-tile shrink-0"
                                ><GraduationCap class="size-5"
                            /></span>
                            <div>
                                <h4 class="font-semibold">
                                    {{ item.degree
                                    }}<template v-if="item.field_of_study"
                                        >, {{ item.field_of_study }}</template
                                    >
                                </h4>
                                <p class="mt-0.5 text-sm text-muted-foreground">
                                    {{ item.institution }}
                                </p>
                                <p class="mt-2 text-xs text-muted-foreground">
                                    {{ formatDate(item.start_date) }} —
                                    {{ formatDate(item.end_date) }}
                                    <template v-if="item.grade">
                                        · {{ item.grade }}</template
                                    >
                                </p>
                                <p
                                    v-if="item.description"
                                    class="mt-3 text-sm leading-relaxed text-muted-foreground"
                                >
                                    {{ item.description }}
                                </p>
                            </div>
                        </article>
                    </Reveal>
                </div>
            </template>
        </div>
    </section>
</template>
