<script setup lang="ts">
import { computed } from 'vue';
import Reveal from '@/components/site/Reveal.vue';
import SectionHeading from '@/components/site/SectionHeading.vue';
import { useSite } from '@/composables/useSite';
import { paragraphs } from '@/lib/text';
import type { FounderAboutProps } from '@/types';

/**
 * About the founder: the bio, the founder's note and the quick facts.
 */
defineProps<FounderAboutProps>();

const { founder } = useSite();

const facts = computed(() =>
    [
        { label: 'Role', value: founder.value.headline },
        { label: 'Based in', value: founder.value.location },
        {
            label: 'Email',
            value: founder.value.public_email,
            href: founder.value.public_email
                ? `mailto:${founder.value.public_email}`
                : null,
        },
    ].filter((fact) => fact.value),
);
</script>

<template>
    <section id="about" class="section scroll-mt-24">
        <div class="container-site grid gap-14 lg:grid-cols-12">
            <div class="lg:col-span-5">
                <SectionHeading
                    index="01"
                    :eyebrow="copy.eyebrow"
                    :title="copy.title"
                    :highlight="copy.highlight"
                />

                <dl class="mt-12">
                    <div
                        v-for="fact in facts"
                        :key="fact.label"
                        class="rule flex justify-between gap-6 py-4"
                    >
                        <dt class="meta">{{ fact.label }}</dt>
                        <dd class="text-right font-medium">
                            <a
                                v-if="fact.href"
                                :href="fact.href"
                                class="link-underline"
                                >{{ fact.value }}</a
                            >
                            <template v-else>{{ fact.value }}</template>
                        </dd>
                    </div>
                </dl>
            </div>

            <div class="lg:col-span-6 lg:col-start-7">
                <Reveal>
                    <p
                        v-for="(paragraph, i) in paragraphs(founder.bio)"
                        :key="i"
                        class="lead mb-6 last:mb-0"
                    >
                        {{ paragraph }}
                    </p>
                </Reveal>

                <Reveal v-if="founder.founder_message" :delay="0.1">
                    <figure class="paper-card mt-12 p-8 sm:p-10">
                        <p class="meta mb-5">{{ copy.message_label }}</p>
                        <blockquote class="grid gap-4">
                            <p
                                v-for="(paragraph, i) in paragraphs(
                                    founder.founder_message,
                                )"
                                :key="i"
                                class="display text-2xl leading-snug sm:text-3xl"
                            >
                                {{ paragraph }}
                            </p>
                        </blockquote>
                        <figcaption class="mt-6 font-medium">
                            — {{ founder.name }}
                        </figcaption>
                    </figure>
                </Reveal>
            </div>
        </div>
    </section>
</template>
