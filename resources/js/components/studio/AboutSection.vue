<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ArrowUpRight } from '@lucide/vue';
import { computed } from 'vue';
import Reveal from '@/components/site/Reveal.vue';
import SectionHeading from '@/components/site/SectionHeading.vue';
import { useSite } from '@/composables/useSite';
import { paragraphs } from '@/lib/text';
import { about } from '@/routes';
import type { AboutSectionProps } from '@/types';

/**
 * 05 — The studio in its own words: bio, mission and founding facts.
 */
defineProps<AboutSectionProps>();

const { studio } = useSite();

const facts = computed(() =>
    [
        { label: 'Founded', value: studio.value.founded_year },
        { label: 'Based in', value: studio.value.location },
        { label: 'Legal name', value: studio.value.legal_name },
    ].filter((fact) => fact.value),
);
</script>

<template>
    <section id="about" class="section scroll-mt-24 bg-paper-deep">
        <div class="container-site grid gap-14 lg:grid-cols-12">
            <div class="lg:col-span-5">
                <SectionHeading
                    index="05"
                    :eyebrow="content.eyebrow"
                    :title="content.title"
                    :highlight="content.highlight"
                />

                <dl v-if="facts.length" class="mt-12 grid gap-0">
                    <div
                        v-for="fact in facts"
                        :key="fact.label"
                        class="rule flex justify-between gap-6 py-4"
                    >
                        <dt class="meta">{{ fact.label }}</dt>
                        <dd class="font-medium">{{ fact.value }}</dd>
                    </div>
                </dl>
            </div>

            <div class="lg:col-span-6 lg:col-start-7">
                <Reveal>
                    <p
                        v-for="(paragraph, i) in paragraphs(studio.bio)"
                        :key="i"
                        class="lead mb-6 last:mb-0"
                    >
                        {{ paragraph }}
                    </p>
                </Reveal>

                <Reveal v-if="studio.mission" :delay="0.1">
                    <blockquote class="mt-12 border-l-2 border-brand pl-6">
                        <p class="meta mb-3">{{ content.mission_label }}</p>
                        <p class="display text-3xl leading-tight sm:text-4xl">
                            “{{ studio.mission }}”
                        </p>
                    </blockquote>
                </Reveal>

                <Reveal :delay="0.15" class="mt-12">
                    <Link :href="about().url" class="btn btn-outline">
                        {{ content.more_label }}
                        <ArrowUpRight
                            class="btn-arrow size-4"
                            aria-hidden="true"
                        />
                    </Link>
                </Reveal>
            </div>
        </div>
    </section>
</template>
