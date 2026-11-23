<script setup lang="ts">
import { Check } from '@lucide/vue';
import Reveal from '@/components/site/Reveal.vue';
import SectionHeading from '@/components/site/SectionHeading.vue';
import { resolveIcon } from '@/lib/iconResolver';
import type { ValueSectionProps } from '@/types';

/**
 * Why work with the studio: the promises, then the reasons.
 */
defineProps<ValueSectionProps>();
</script>

<template>
    <section id="value" class="section scroll-mt-24">
        <div class="container-site">
            <SectionHeading
                :eyebrow="content.eyebrow"
                :title="content.title"
                :highlight="content.highlight"
                :description="content.description"
                align="split"
            >
                <ul v-if="content.guarantees.length" class="mt-8 grid gap-3">
                    <li
                        v-for="item in content.guarantees"
                        :key="item"
                        class="flex items-center gap-3 font-medium"
                    >
                        <span
                            class="grid size-6 place-items-center rounded-full bg-brand text-brand-foreground"
                        >
                            <Check class="size-3.5" aria-hidden="true" />
                        </span>
                        {{ item }}
                    </li>
                </ul>
            </SectionHeading>

            <div class="mt-16 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                <Reveal
                    v-for="(item, itemIndex) in content.items"
                    :key="item.title"
                    :delay="(itemIndex % 3) * 0.08"
                >
                    <article
                        class="paper-card group h-full p-7 transition-colors duration-500 hover:border-ink"
                    >
                        <component
                            :is="resolveIcon(item.icon)"
                            class="size-7 text-brand transition-transform duration-500 group-hover:-rotate-6"
                            aria-hidden="true"
                        />
                        <h3 class="display mt-8 text-3xl">{{ item.title }}</h3>
                        <p class="body-copy mt-3">{{ item.summary }}</p>
                    </article>
                </Reveal>
            </div>
        </div>
    </section>
</template>
