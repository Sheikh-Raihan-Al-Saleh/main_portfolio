<script setup lang="ts">
import { GraduationCap } from '@lucide/vue';
import SmartImage from '@/components/media/SmartImage.vue';
import Reveal from '@/components/site/Reveal.vue';
import SectionHeading from '@/components/site/SectionHeading.vue';
import { monthYear, yearRange } from '@/lib/text';
import type { ExperienceSectionProps } from '@/types';

/**
 * Work history as an editorial timeline, followed by education.
 */
defineProps<ExperienceSectionProps>();
</script>

<template>
    <section id="experience" class="section scroll-mt-24">
        <div class="container-site">
            <SectionHeading
                index="04"
                :eyebrow="copy.eyebrow"
                :title="copy.title"
                :highlight="copy.highlight"
                :description="copy.description"
                align="split"
            />

            <ol class="mt-16">
                <Reveal
                    v-for="(job, i) in experiences"
                    :key="job.id"
                    as="li"
                    :delay="i * 0.05"
                    class="rule grid gap-6 py-10 lg:grid-cols-12"
                >
                    <div class="lg:col-span-3">
                        <p class="meta">
                            {{ monthYear(job.start_date) }} —
                            {{
                                job.is_current
                                    ? 'Present'
                                    : monthYear(job.end_date)
                            }}
                        </p>
                        <p
                            v-if="job.is_current"
                            class="mt-3 inline-flex items-center gap-2 text-sm text-ink-soft"
                        >
                            <span class="status-dot" /> Current role
                        </p>
                    </div>

                    <div class="lg:col-span-6">
                        <h3 class="display text-3xl sm:text-4xl">
                            {{ job.role }}
                        </h3>
                        <p class="mt-2 text-lg">
                            <a
                                v-if="job.company_url"
                                :href="job.company_url"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="link-underline font-medium"
                            >
                                {{ job.company }}
                            </a>
                            <span v-else class="font-medium">{{
                                job.company
                            }}</span>
                            <span class="text-stone">
                                <template v-if="job.employment_type">
                                    · {{ job.employment_type }}</template
                                >
                                <template v-if="job.location">
                                    · {{ job.location }}</template
                                >
                            </span>
                        </p>
                        <p v-if="job.description" class="body-copy mt-4">
                            {{ job.description }}
                        </p>

                        <ul
                            v-if="job.highlights.length"
                            class="mt-5 grid gap-2"
                        >
                            <li
                                v-for="highlight in job.highlights"
                                :key="highlight.id"
                                class="flex gap-3 text-ink-soft"
                            >
                                <span
                                    class="mt-2.5 size-1.5 shrink-0 rounded-full bg-brand"
                                    aria-hidden="true"
                                />
                                {{ highlight.body }}
                            </li>
                        </ul>
                    </div>

                    <div
                        class="hidden lg:col-span-2 lg:col-start-11 lg:flex lg:justify-end"
                    >
                        <SmartImage
                            v-if="job.logo_url"
                            :src="job.logo_url"
                            :alt="job.company"
                            fit="contain"
                            class="size-16 rounded-xl border border-line bg-card p-2"
                        />
                    </div>
                </Reveal>
            </ol>

            <div v-if="educations.length" class="mt-20">
                <h3 class="eyebrow mb-6">Education</h3>
                <ul class="grid gap-5 md:grid-cols-2">
                    <Reveal
                        v-for="(school, i) in educations"
                        :key="school.id"
                        as="li"
                        :delay="i * 0.08"
                        class="paper-card flex h-full gap-5 p-7"
                    >
                        <GraduationCap
                            class="size-6 shrink-0 text-brand"
                            aria-hidden="true"
                        />
                        <div>
                            <p class="meta">
                                {{
                                    yearRange(
                                        school.start_date,
                                        school.end_date,
                                    )
                                }}
                            </p>
                            <p class="display mt-2 text-2xl">
                                {{
                                    [school.degree, school.field_of_study]
                                        .filter(Boolean)
                                        .join(', ')
                                }}
                            </p>
                            <p class="mt-1 font-medium">
                                {{ school.institution }}
                            </p>
                            <p v-if="school.grade" class="meta mt-2">
                                Grade: {{ school.grade }}
                            </p>
                            <p
                                v-if="school.description"
                                class="body-copy mt-3 text-sm"
                            >
                                {{ school.description }}
                            </p>
                        </div>
                    </Reveal>
                </ul>
            </div>
        </div>
    </section>
</template>
